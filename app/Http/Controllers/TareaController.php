<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\Obra;
use App\Models\Rubro;
use App\Models\Tarea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TareaController extends Controller
{
    public function index(Obra $obra): View
    {
        $obra->load(['cliente', 'estudio']);
        $tareas = $obra->tareas()->with(['rubro', 'contacto'])->get();

        return view('obras.gantt', [
            'obra' => $obra,
            'tareas' => $tareas,
            'gantt' => $tareas->map(fn (Tarea $t) => [
                'id' => (string) $t->id,
                'name' => $t->nombre.($t->contacto ? ' · '.$t->contacto->nombre : ''),
                'start' => $t->fecha_inicio->format('Y-m-d'),
                'end' => $t->fecha_fin->format('Y-m-d'),
                'progress' => $t->avance,
                'custom_class' => $t->es_hito ? 'hito' : ($t->avance >= 100 ? 'completa' : ''),
            ])->values(),
            'rubros' => Rubro::where('activo', true)->orderBy('nombre')->pluck('nombre', 'id'),
            'contactos' => Contacto::orderBy('nombre')->get()->mapWithKeys(fn ($c) => [$c->id => $c->nombre.($c->empresa ? " ({$c->empresa})" : '')]),
        ]);
    }

    public function store(Request $request, Obra $obra): RedirectResponse
    {
        $datos = $this->validar($request);

        $obra->tareas()->create($datos + ['orden' => (int) $obra->tareas()->max('orden') + 1]);

        return back()->with('status', 'Tarea agregada.');
    }

    public function update(Request $request, Tarea $tarea): RedirectResponse
    {
        $tarea->update($this->validar($request));

        return back()->with('status', 'Tarea actualizada.');
    }

    /** Arrastre de barras en el Gantt. */
    public function mover(Request $request, Tarea $tarea): JsonResponse
    {
        $datos = $request->validate([
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_fin' => ['sometimes', 'required', 'date', 'after_or_equal:fecha_inicio'],
            'avance' => ['sometimes', 'required', 'integer', 'between:0,100'],
        ]);

        $tarea->update($datos);

        return response()->json(['ok' => true]);
    }

    public function destroy(Tarea $tarea): RedirectResponse
    {
        $tarea->delete();

        return back()->with('status', 'Tarea eliminada.');
    }

    private function validar(Request $request): array
    {
        $request->merge(['es_hito' => $request->boolean('es_hito')]);

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'rubro_id' => ['nullable', Rule::exists('rubros', 'id')],
            'contacto_id' => ['nullable', Rule::exists('contactos', 'id')],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'avance' => ['nullable', 'integer', 'between:0,100'],
            'es_hito' => ['boolean'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ]);
        $datos['avance'] = (int) ($datos['avance'] ?? 0);

        return $datos;
    }
}
