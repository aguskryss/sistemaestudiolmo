<?php

namespace App\Http\Controllers;

use App\Enums\Moneda;
use App\Enums\TipoSeguro;
use App\Models\Contacto;
use App\Models\Obra;
use App\Models\Seguro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeguroController extends Controller
{
    /** Pestaña de la obra: seguros asociados y gremios trabajando sin seguro vigente. */
    public function obra(Obra $obra): View
    {
        $obra->load(['cliente', 'estudio', 'seguros' => fn ($q) => $q->with(['contacto', 'adjuntos'])->orderBy('vigencia_hasta')]);

        $conSeguro = $obra->seguros->filter->estaVigente()->pluck('contacto_id');
        $sinSeguro = Contacto::whereIn('id', $obra->tareas()->whereNotNull('contacto_id')->select('contacto_id'))
            ->whereNotIn('id', $conSeguro)
            ->orderBy('nombre')
            ->get();

        $disponibles = Seguro::with('contacto')
            ->whereNotIn('id', $obra->seguros->pluck('id'))
            ->whereDate('vigencia_hasta', '>=', today())
            ->get()
            ->sortBy('contacto.nombre')
            ->mapWithKeys(fn ($s) => [$s->id => $s->contacto->nombre.' · '.$s->tipo->label().' · vence '.$s->vigencia_hasta->format('d.m.Y')]);

        return view('obras.seguros', compact('obra', 'sinSeguro', 'disponibles'));
    }

    public function store(Request $request, Contacto $contacto): RedirectResponse
    {
        [$datos, $obras] = $this->validar($request);

        DB::transaction(function () use ($contacto, $datos, $obras) {
            $seguro = $contacto->seguros()->create($datos);
            $seguro->obras()->sync($obras);
        });

        return back()->with('status', 'Seguro cargado.');
    }

    public function update(Request $request, Seguro $seguro): RedirectResponse
    {
        [$datos, $obras] = $this->validar($request);

        DB::transaction(function () use ($seguro, $datos, $obras) {
            $seguro->update($datos);
            $seguro->obras()->sync($obras);
        });

        return back()->with('status', 'Seguro actualizado.');
    }

    public function vincular(Request $request, Obra $obra): RedirectResponse
    {
        $request->validate(['seguro_id' => ['required', Rule::exists('seguros', 'id')->whereNull('deleted_at')]]);
        $obra->seguros()->syncWithoutDetaching([$request->integer('seguro_id')]);

        return back()->with('status', 'Seguro asociado a la obra.');
    }

    public function desvincular(Obra $obra, Seguro $seguro): RedirectResponse
    {
        $obra->seguros()->detach($seguro->id);

        return back()->with('status', 'Seguro quitado de la obra.');
    }

    public function destroy(Seguro $seguro): RedirectResponse
    {
        $seguro->delete();

        return back()->with('status', 'Seguro eliminado.');
    }

    /** @return array{0: array, 1: array} */
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::enum(TipoSeguro::class)],
            'aseguradora' => ['required', 'string', 'max:255'],
            'numero_poliza' => ['nullable', 'string', 'max:50'],
            'vigencia_desde' => ['required', 'date'],
            'vigencia_hasta' => ['required', 'date', 'after_or_equal:vigencia_desde'],
            'suma_asegurada' => ['nullable', 'numeric', 'min:0'],
            'moneda' => ['nullable', Rule::enum(Moneda::class)],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'obras' => ['nullable', 'array'],
            'obras.*' => [Rule::exists('obras', 'id')],
        ]);

        $datos['moneda'] ??= 'ARS';

        return [collect($datos)->except('obras')->all(), $datos['obras'] ?? []];
    }
}
