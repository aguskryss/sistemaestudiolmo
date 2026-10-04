<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\Obra;
use App\Support\Archivos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArchivoController extends Controller
{
    public function index(Request $request, Obra $obra): View
    {
        $obra->load(['cliente', 'estudio']);
        $carpetas = $obra->carpetas()->withCount('documentos')->orderBy('orden')->orderBy('nombre')->get();

        $actual = $carpetas->firstWhere('id', $request->integer('carpeta')) ?? $carpetas->whereNull('parent_id')->first();

        $documentos = $actual
            ? $actual->documentos()->with(['versionActual.subidoPor', 'versiones.subidoPor'])->get()
            : collect();

        return view('obras.archivos', compact('obra', 'carpetas', 'actual', 'documentos'));
    }

    /** Sube uno o varios archivos. Si ya hay un documento con ese nombre en la carpeta, se guarda como versión nueva. */
    public function subir(Request $request, Obra $obra): RedirectResponse
    {
        $request->validate([
            'carpeta_id' => ['required', Rule::exists('carpetas', 'id')->where('obra_id', $obra->id)],
            'archivos' => ['required', 'array', 'max:20'],
            'archivos.*' => Archivos::reglas(),
            'comentario' => ['nullable', 'string', 'max:255'],
        ], [], ['archivos.*' => 'archivo']);

        $carpeta = Carpeta::findOrFail($request->integer('carpeta_id'));
        $nuevos = $versiones = $repetidos = 0;

        foreach ($request->file('archivos') as $archivo) {
            $nombre = $this->nombreLogico($archivo);
            $documento = $carpeta->documentos()->whereRaw('LOWER(nombre) = ?', [Str::lower($nombre)])->first();

            if ($documento && $documento->versionActual?->hash === hash_file('sha256', $archivo->getRealPath())) {
                $repetidos++;

                continue;
            }

            $documento ? $versiones++ : $nuevos++;
            $documento ??= $carpeta->documentos()->create(['obra_id' => $obra->id, 'nombre' => $nombre]);
            $this->agregarVersion($documento, $archivo, $request);
        }

        $partes = array_filter([
            $nuevos ? "{$nuevos} archivo(s) nuevo(s)" : null,
            $versiones ? "{$versiones} versión(es) nueva(s)" : null,
            $repetidos ? "{$repetidos} sin cambios (idénticos a la versión vigente)" : null,
        ]);

        return redirect()->route('obras.archivos', [$obra, 'carpeta' => $carpeta->id])
            ->with('status', ucfirst(implode(', ', $partes)).'.');
    }

    public function nuevaVersion(Request $request, Documento $documento): RedirectResponse
    {
        $request->validate([
            'archivo' => ['required', ...Archivos::reglas()],
            'comentario' => ['nullable', 'string', 'max:255'],
        ]);

        $version = $this->agregarVersion($documento, $request->file('archivo'), $request);

        return redirect()->route('obras.archivos', [$documento->obra_id, 'carpeta' => $documento->carpeta_id])
            ->with('status', "«{$documento->nombre}» actualizado a la versión {$version->numero}.");
    }

    public function descargar(Request $request, DocumentoVersion $version): StreamedResponse
    {
        Actividad::create([
            'user_id' => $request->user()->id,
            'accion' => 'descargado',
            'sujeto_type' => 'documento_version',
            'sujeto_id' => $version->id,
            'ip' => $request->ip(),
        ]);

        return Archivos::entregar($version->ruta, $version->nombre_original, $version->mime, $request->boolean('ver'));
    }

    public function eliminar(Documento $documento): RedirectResponse
    {
        // Borrado lógico: los archivos físicos quedan por si hay que recuperarlo.
        $documento->delete();

        return back()->with('status', "«{$documento->nombre}» eliminado.");
    }

    public function crearCarpeta(Request $request, Obra $obra): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', Rule::exists('carpetas', 'id')->where('obra_id', $obra->id)],
        ]);

        $carpeta = $obra->carpetas()->create($datos + ['orden' => (int) $obra->carpetas()->max('orden') + 1]);

        return redirect()->route('obras.archivos', [$obra, 'carpeta' => $carpeta->id]);
    }

    public function eliminarCarpeta(Carpeta $carpeta): RedirectResponse
    {
        if ($carpeta->documentos()->exists() || $carpeta->subcarpetas()->exists()) {
            return back()->withErrors(['carpeta' => 'Solo se pueden eliminar carpetas vacías.']);
        }

        $carpeta->delete();

        return redirect()->route('obras.archivos', $carpeta->obra_id)->with('status', 'Carpeta eliminada.');
    }

    private function agregarVersion(Documento $documento, UploadedFile $archivo, Request $request): DocumentoVersion
    {
        $datos = Archivos::guardar($archivo, "obras/{$documento->obra_id}");

        return DB::transaction(fn () => $documento->versiones()->create($datos + [
            'numero' => (int) $documento->versiones()->lockForUpdate()->max('numero') + 1,
            'subido_por' => $request->user()->id,
            'comentario' => $request->input('comentario'),
        ]));
    }

    /** El nombre incluye la extensión: "Planta.dwg" y "Planta.pdf" son documentos distintos. */
    private function nombreLogico(UploadedFile $archivo): string
    {
        return Str::limit(basename($archivo->getClientOriginalName()) ?: 'Archivo', 250, '');
    }
}
