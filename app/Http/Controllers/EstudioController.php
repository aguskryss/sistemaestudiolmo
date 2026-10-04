<?php

namespace App\Http\Controllers;

use App\Models\Estudio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EstudioController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q'));

        $estudios = Estudio::query()
            ->with('contactos')
            ->withCount(['obras', 'obras as obras_activas_count' => fn ($q) => $q->where('estado', '!=', 'terminada')])
            ->when($busqueda, fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('razon_social', 'like', "%{$busqueda}%")
                ->orWhereHas('contactos', fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%"))))
            ->orderBy('nombre')
            ->paginate(30)
            ->withQueryString();

        return view('estudios.index', compact('estudios', 'busqueda'));
    }

    public function create(): View
    {
        return view('estudios.form', ['estudio' => new Estudio]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$datos, $contactos] = $this->validar($request);
        $estudio = DB::transaction(function () use ($datos, $contactos) {
            $estudio = Estudio::create($datos);
            $this->guardarContactos($estudio, $contactos);

            return $estudio;
        });

        return redirect()->route('estudios.show', $estudio)->with('status', 'Estudio creado.');
    }

    public function show(Estudio $estudio): View
    {
        $estudio->load(['contactos', 'obras' => fn ($q) => $q->with(['cliente', 'estudioContacto'])->latest('numero')]);

        return view('estudios.show', compact('estudio'));
    }

    public function edit(Estudio $estudio): View
    {
        return view('estudios.form', ['estudio' => $estudio->load('contactos')]);
    }

    public function update(Request $request, Estudio $estudio): RedirectResponse
    {
        [$datos, $contactos] = $this->validar($request);
        DB::transaction(function () use ($estudio, $datos, $contactos) {
            $estudio->update($datos);
            $this->guardarContactos($estudio, $contactos);
        });

        return redirect()->route('estudios.show', $estudio)->with('status', 'Estudio actualizado.');
    }

    public function destroy(Estudio $estudio): RedirectResponse
    {
        if ($estudio->obras()->exists()) {
            return back()->withErrors(['estudio' => 'No se puede eliminar: tiene obras asociadas.']);
        }

        $estudio->delete();

        return redirect()->route('estudios.index')->with('status', 'Estudio eliminado.');
    }

    /** @return array{0: array, 1: array} */
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'cuit' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:5000'],
            'contactos' => ['nullable', 'array', 'max:30'],
            'contactos.*.id' => ['nullable', 'integer'],
            'contactos.*.nombre' => ['nullable', 'string', 'max:255'],
            'contactos.*.cargo' => ['nullable', 'string', 'max:100'],
            'contactos.*.telefono' => ['nullable', 'string', 'max:50'],
            'contactos.*.email' => ['nullable', 'email', 'max:255'],
        ], [], ['contactos.*.email' => 'email del contacto']);

        $contactos = collect($datos['contactos'] ?? [])->filter(fn ($c) => filled($c['nombre'] ?? null))->values()->all();

        return [collect($datos)->except('contactos')->all(), $contactos];
    }

    /** Sincroniza los contactos del estudio: actualiza los existentes, crea los nuevos y borra los quitados. */
    private function guardarContactos(Estudio $estudio, array $contactos): void
    {
        $conservar = [];

        foreach ($contactos as $datos) {
            $campos = collect($datos)->only(['nombre', 'cargo', 'telefono', 'email'])->all();
            $contacto = ! empty($datos['id']) ? $estudio->contactos()->find($datos['id']) : null;

            if ($contacto) {
                $contacto->update($campos);
            } else {
                $contacto = $estudio->contactos()->create($campos);
            }

            $conservar[] = $contacto->id;
        }

        $estudio->contactos()->whereNotIn('id', $conservar)->delete();
    }
}
