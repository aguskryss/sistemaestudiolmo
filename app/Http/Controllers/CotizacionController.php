<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCotizacion;
use App\Enums\Moneda;
use App\Enums\TipoCotizacion;
use App\Models\Cliente;
use App\Models\Contacto;
use App\Models\Cotizacion;
use App\Models\Estudio;
use App\Models\Obra;
use App\Models\Rubro;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CotizacionController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->only(['tipo', 'estado', 'rubro', 'q']);

        return view('cotizaciones.index', [
            'cotizaciones' => $this->consulta($filtros)->paginate(40)->withQueryString(),
            'filtros' => $filtros,
            'rubros' => Rubro::orderBy('nombre')->pluck('nombre', 'id'),
        ]);
    }

    /** Pestaña de la obra: listado + comparación de cotizaciones recibidas por rubro. */
    public function obra(Obra $obra): View
    {
        $obra->load(['cliente', 'estudio']);
        $cotizaciones = $this->consulta([])->where('obra_id', $obra->id)->get();

        return view('obras.cotizaciones', [
            'obra' => $obra,
            'cotizaciones' => $cotizaciones,
            'comparacion' => $cotizaciones
                ->where('tipo', TipoCotizacion::Recibida)
                ->whereNotNull('rubro_id')
                ->groupBy('rubro_id')
                ->filter(fn ($grupo) => $grupo->count() > 1),
        ]);
    }

    public function create(Request $request): View
    {
        $cotizacion = new Cotizacion([
            'tipo' => $request->query('tipo', 'recibida'),
            'obra_id' => $request->integer('obra_id') ?: null,
            'fecha' => today(),
            'moneda' => 'ARS',
            'estado' => 'pendiente',
        ]);

        if ($cotizacion->obra_id && $obra = Obra::find($cotizacion->obra_id)) {
            $cotizacion->cliente_id = $obra->cliente_id;
            $cotizacion->estudio_id = $obra->estudio_id;
        }

        return view('cotizaciones.form', $this->datosFormulario($cotizacion));
    }

    public function store(Request $request): RedirectResponse
    {
        [$datos, $items, $total] = $this->validar($request);

        $cotizacion = DB::transaction(function () use ($datos, $items, $total, $request) {
            $cotizacion = new Cotizacion($datos);
            $cotizacion->creado_por = $request->user()->id;
            $cotizacion->save();
            $this->guardarItems($cotizacion, $items, $total);

            return $cotizacion;
        });

        return redirect()->route('cotizaciones.show', $cotizacion)->with('status', 'Cotización guardada.');
    }

    public function show(Cotizacion $cotizacion): View
    {
        $cotizacion->load(['obra', 'cliente', 'estudio', 'contacto', 'rubro', 'items', 'adjuntos', 'creadoPor']);

        return view('cotizaciones.show', compact('cotizacion'));
    }

    public function edit(Cotizacion $cotizacion): View
    {
        $cotizacion->load('items');

        return view('cotizaciones.form', $this->datosFormulario($cotizacion));
    }

    public function update(Request $request, Cotizacion $cotizacion): RedirectResponse
    {
        [$datos, $items, $total] = $this->validar($request);

        DB::transaction(function () use ($cotizacion, $datos, $items, $total) {
            $cotizacion->update($datos);
            $this->guardarItems($cotizacion, $items, $total);
        });

        return redirect()->route('cotizaciones.show', $cotizacion)->with('status', 'Cotización actualizada.');
    }

    public function estado(Request $request, Cotizacion $cotizacion): RedirectResponse
    {
        $cotizacion->update($request->validate(['estado' => ['required', Rule::enum(EstadoCotizacion::class)]]));

        return back()->with('status', 'Estado: '.$cotizacion->estado->label().'.');
    }

    public function destroy(Cotizacion $cotizacion): RedirectResponse
    {
        $obraId = $cotizacion->obra_id;
        $cotizacion->delete();

        return $obraId
            ? redirect()->route('obras.cotizaciones', $obraId)->with('status', 'Cotización eliminada.')
            : redirect()->route('cotizaciones.index')->with('status', 'Cotización eliminada.');
    }

    private function consulta(array $filtros): Builder
    {
        return Cotizacion::query()
            ->with(['obra', 'cliente', 'estudio', 'contacto', 'rubro'])
            ->when($filtros['tipo'] ?? null, fn ($q, $v) => $q->where('tipo', $v))
            ->when($filtros['estado'] ?? null, fn ($q, $v) => $q->where('estado', $v))
            ->when($filtros['rubro'] ?? null, fn ($q, $v) => $q->where('rubro_id', $v))
            ->when(trim($filtros['q'] ?? ''), fn ($q, $v) => $q->where(fn ($q) => $q
                ->where('titulo', 'like', "%{$v}%")
                ->orWhere('numero', 'like', "%{$v}%")
                ->orWhereHas('contacto', fn ($q) => $q->where('nombre', 'like', "%{$v}%"))
                ->orWhereHas('obra', fn ($q) => $q->where('nombre', 'like', "%{$v}%"))))
            ->orderByDesc('fecha')
            ->orderByDesc('id');
    }

    private function datosFormulario(Cotizacion $cotizacion): array
    {
        return [
            'cotizacion' => $cotizacion,
            'obras' => Obra::orderByDesc('numero')->get()->mapWithKeys(fn ($o) => [$o->id => $o->codigo.' · '.$o->nombre]),
            'clientes' => Cliente::orderBy('nombre')->pluck('nombre', 'id'),
            'estudios' => Estudio::orderBy('nombre')->pluck('nombre', 'id'),
            'contactos' => Contacto::orderBy('nombre')->get()->mapWithKeys(fn ($c) => [$c->id => $c->nombre.($c->empresa ? " ({$c->empresa})" : '')]),
            'rubros' => Rubro::where('activo', true)->orWhere('id', $cotizacion->rubro_id)->orderBy('nombre')->pluck('nombre', 'id'),
        ];
    }

    /** @return array{0: array, 1: array, 2: float} */
    private function validar(Request $request): array
    {
        $tipo = $request->input('tipo');

        $datos = $request->validate([
            'tipo' => ['required', Rule::enum(TipoCotizacion::class)],
            'titulo' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:30'],
            'obra_id' => ['nullable', Rule::exists('obras', 'id')],
            'contacto_id' => [Rule::requiredIf($tipo === 'recibida'), 'nullable', Rule::exists('contactos', 'id')],
            'cliente_id' => ['nullable', Rule::exists('clientes', 'id')],
            'estudio_id' => ['nullable', Rule::exists('estudios', 'id')],
            'rubro_id' => ['nullable', Rule::exists('rubros', 'id')],
            'fecha' => ['required', 'date'],
            'valida_hasta' => ['nullable', 'date', 'after_or_equal:fecha'],
            'moneda' => ['required', Rule::enum(Moneda::class)],
            'tipo_cambio' => ['nullable', 'numeric', 'gt:0'],
            'estado' => ['required', Rule::enum(EstadoCotizacion::class)],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            'total' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'items' => ['nullable', 'array', 'max:200'],
            'items.*.descripcion' => ['required_with:items.*.precio_unitario', 'nullable', 'string', 'max:255'],
            'items.*.unidad' => ['nullable', 'string', 'max:20'],
            'items.*.cantidad' => ['nullable', 'numeric', 'min:0'],
            'items.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
        ], [], ['contacto_id' => 'gremio / proveedor', 'items.*.descripcion' => 'descripción']);

        if ($tipo === 'emitida' && empty($datos['cliente_id']) && empty($datos['estudio_id'])) {
            back()->withInput()->withErrors(['cliente_id' => 'Indicá a quién va dirigida: un estudio o un cliente.'])->throwResponse();
        }

        // Una recibida no tiene destinatario; una emitida no tiene proveedor.
        if ($tipo === 'recibida') {
            $datos['cliente_id'] = $datos['estudio_id'] = null;
        } else {
            $datos['contacto_id'] = null;
        }

        $items = collect($datos['items'] ?? [])->filter(fn ($i) => filled($i['descripcion'] ?? null))->values()->all();

        return [collect($datos)->except(['items', 'total'])->all(), $items, (float) ($datos['total'] ?? 0)];
    }

    private function guardarItems(Cotizacion $cotizacion, array $items, float $totalManual): void
    {
        $cotizacion->items()->delete();

        foreach ($items as $orden => $item) {
            $cotizacion->items()->create([
                'orden' => $orden,
                'descripcion' => $item['descripcion'],
                'unidad' => $item['unidad'] ?? null,
                'cantidad' => $item['cantidad'] ?? 1,
                'precio_unitario' => $item['precio_unitario'] ?? 0,
            ]);
        }

        // Sin ítems, el total se carga a mano (una cotización recibida en PDF, por ejemplo).
        if ($items) {
            $cotizacion->recalcularTotal();
        } else {
            $cotizacion->forceFill(['total' => $totalManual])->save();
        }
    }
}
