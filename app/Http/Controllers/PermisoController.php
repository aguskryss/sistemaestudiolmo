<?php

namespace App\Http\Controllers;

use App\Enums\EstadoPermiso;
use App\Models\Obra;
use App\Models\Opcion;
use App\Models\Permiso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermisoController extends Controller
{
    public function obra(Obra $obra): View
    {
        $obra->load(['cliente', 'estudio', 'permisos' => fn ($q) => $q->with(['adjuntos', 'tipoPermiso'])->orderByRaw('fecha_vencimiento IS NULL')->orderBy('fecha_vencimiento')]);

        return view('obras.permisos', ['obra' => $obra, 'tipos' => Opcion::lista('tipo_permiso')]);
    }

    public function store(Request $request, Obra $obra): RedirectResponse
    {
        $obra->permisos()->create($this->validar($request));

        return back()->with('status', 'Permiso cargado.');
    }

    public function update(Request $request, Permiso $permiso): RedirectResponse
    {
        $permiso->update($this->validar($request));

        return back()->with('status', 'Permiso actualizado.');
    }

    public function destroy(Permiso $permiso): RedirectResponse
    {
        $permiso->delete();

        return back()->with('status', 'Permiso eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'tipo_permiso_id' => ['required', Rule::exists('opciones', 'id')->where('grupo', 'tipo_permiso')],
            'organismo' => ['nullable', 'string', 'max:255'],
            'numero_expediente' => ['nullable', 'string', 'max:50'],
            'estado' => ['required', Rule::enum(EstadoPermiso::class)],
            'fecha_presentacion' => ['nullable', 'date'],
            'fecha_aprobacion' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ], [], ['tipo_permiso_id' => 'tipo de permiso']);
    }
}
