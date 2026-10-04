<?php

namespace App\Http\Controllers;

use App\Enums\EstadoMaterial;
use App\Models\Contacto;
use App\Models\Material;
use App\Models\Obra;
use App\Models\ObraMaterial;
use App\Models\Opcion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request, Obra $obra): View
    {
        $obra->load(['cliente', 'estudio']);
        $estado = $request->query('estado');

        $materiales = $obra->materiales()
            ->with(['material', 'proveedor', 'movimientos.usuario'])
            ->when($estado === 'pendientes', fn ($q) => $q->where('estado', '!=', EstadoMaterial::Entregado))
            ->when($estado && $estado !== 'pendientes', fn ($q) => $q->where('estado', $estado))
            ->orderByRaw("CASE estado WHEN 'necesito' THEN 0 WHEN 'pedido' THEN 1 WHEN 'entregado_parcial' THEN 2 ELSE 3 END")
            ->orderBy('fecha_necesaria')
            ->get();

        $conteo = $obra->materiales()->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');

        return view('obras.materiales', [
            'obra' => $obra,
            'materiales' => $materiales,
            'estado' => $estado,
            'conteo' => $conteo,
            'catalogo' => Material::where('activo', true)->orderBy('nombre')->get()->mapWithKeys(fn ($m) => [$m->id => "{$m->nombre} ({$m->unidad})"]),
            'unidades' => Opcion::lista('unidad')->values(),
            'proveedores' => Contacto::orderBy('nombre')->get()->mapWithKeys(fn ($c) => [$c->id => $c->nombre.($c->empresa ? " ({$c->empresa})" : '')]),
        ]);
    }

    public function store(Request $request, Obra $obra): RedirectResponse
    {
        $nuevo = ! $request->filled('material_id');
        $datos = $request->validate([
            'material_id' => ['nullable', Rule::exists('materiales', 'id')->where('activo', true)],
            'nombre' => [Rule::requiredIf($nuevo), 'nullable', 'string', 'max:255'],
            'unidad' => [Rule::requiredIf($nuevo), 'nullable', 'string', Rule::exists('opciones', 'nombre')->where('grupo', 'unidad')],
            'cantidad_necesaria' => ['required', 'numeric', 'gt:0', 'max:9999999'],
            'proveedor_id' => ['nullable', Rule::exists('contactos', 'id')],
            'fecha_necesaria' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ], [], ['material_id' => 'material', 'nombre' => 'nombre del material']);

        // Elegido del catálogo, o nuevo (queda agregado al catálogo para la próxima).
        $material = $nuevo
            ? Material::firstOrCreate(['nombre' => trim($datos['nombre']), 'unidad' => $datos['unidad']])
            : Material::findOrFail($datos['material_id']);

        $obra->materiales()->create(['material_id' => $material->id] + collect($datos)->except(['material_id', 'nombre', 'unidad'])->all());

        return back()->with('status', "{$material->nombre} agregado a la lista.");
    }

    public function update(Request $request, ObraMaterial $item): RedirectResponse
    {
        $item->update($request->validate([
            'cantidad_necesaria' => ['required', 'numeric', 'gt:0', 'max:9999999'],
            'proveedor_id' => ['nullable', Rule::exists('contactos', 'id')],
            'fecha_necesaria' => ['nullable', 'date'],
            'fecha_entrega_estimada' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
        ]));
        $item->recalcular();

        return back()->with('status', 'Material actualizado.');
    }

    /** Registra un pedido o una entrega (total o parcial). */
    public function movimiento(Request $request, ObraMaterial $item): RedirectResponse
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::in(['pedido', 'entrega'])],
            'cantidad' => ['required', 'numeric', 'gt:0', 'max:9999999'],
            'fecha' => ['required', 'date'],
            'remito' => ['nullable', 'string', 'max:50'],
            'observaciones' => ['nullable', 'string', 'max:255'],
            'proveedor_id' => ['nullable', Rule::exists('contactos', 'id')],
            'fecha_entrega_estimada' => ['nullable', 'date'],
        ]);

        DB::transaction(function () use ($item, $datos, $request) {
            $item->movimientos()->create(collect($datos)->only(['tipo', 'cantidad', 'fecha', 'remito', 'observaciones'])->all() + [
                'user_id' => $request->user()->id,
            ]);

            if ($datos['tipo'] === 'pedido') {
                $item->fill(array_filter([
                    'proveedor_id' => $datos['proveedor_id'] ?? null,
                    'fecha_entrega_estimada' => $datos['fecha_entrega_estimada'] ?? null,
                ]));
            }

            $item->recalcular();
        });

        return back()->with('status', $datos['tipo'] === 'pedido' ? 'Pedido registrado.' : 'Entrega registrada.');
    }

    public function destroy(ObraMaterial $item): RedirectResponse
    {
        $item->delete();

        return back()->with('status', 'Material quitado de la lista.');
    }
}
