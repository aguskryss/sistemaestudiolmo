<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Adjunto;
use App\Support\Archivos;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Archivos sueltos asociados a cotizaciones, permisos y seguros. */
class AdjuntoController extends Controller
{
    private const TIPOS = ['cotizacion', 'permiso', 'seguro'];

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'adjuntable_type' => ['required', Rule::in(self::TIPOS)],
            'adjuntable_id' => ['required', 'integer'],
            'archivos' => ['required', 'array', 'max:10'],
            'archivos.*' => Archivos::reglas(),
        ], [], ['archivos.*' => 'archivo']);

        $clase = Relation::getMorphedModel($request->input('adjuntable_type'));
        $modelo = $clase::findOrFail($request->integer('adjuntable_id'));

        foreach ($request->file('archivos') as $archivo) {
            $datos = Archivos::guardar($archivo, 'adjuntos/'.$request->input('adjuntable_type'));
            $modelo->adjuntos()->create(collect($datos)->except('hash')->all() + ['subido_por' => $request->user()->id]);
        }

        return back()->with('status', 'Archivo(s) adjuntado(s).');
    }

    public function descargar(Request $request, Adjunto $adjunto): StreamedResponse
    {
        Actividad::create([
            'user_id' => $request->user()->id,
            'accion' => 'descargado',
            'sujeto_type' => $adjunto->adjuntable_type,
            'sujeto_id' => $adjunto->adjuntable_id,
            'cambios' => ['archivo' => $adjunto->nombre_original],
            'ip' => $request->ip(),
        ]);

        return Archivos::entregar($adjunto->ruta, $adjunto->nombre_original, $adjunto->mime, $request->boolean('ver'));
    }

    public function destroy(Adjunto $adjunto): RedirectResponse
    {
        Archivos::eliminar($adjunto->ruta);
        $adjunto->delete();

        return back()->with('status', 'Adjunto eliminado.');
    }
}
