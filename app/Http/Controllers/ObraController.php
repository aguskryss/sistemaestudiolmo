<?php

namespace App\Http\Controllers;

use App\Enums\EstadoObra;
use App\Models\ChecklistPlantillaItem;
use App\Models\Cliente;
use App\Models\Estudio;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ObraController extends Controller
{
    /** Carpetas con las que nace cada obra. */
    private const CARPETAS_INICIALES = ['Planos', 'Documentación', 'Contratos', 'Fotos de obra'];

    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q'));
        $estado = $request->query('estado', 'activas');
        $estudioId = $request->query('estudio');

        $obras = Obra::query()
            ->with(['cliente', 'estudio', 'responsable'])
            ->when($estado === 'activas', fn ($q) => $q->where('estado', '!=', EstadoObra::Terminada))
            ->when($estado && $estado !== 'activas' && $estado !== 'todas', fn ($q) => $q->where('estado', $estado))
            ->when($estudioId === 'directas', fn ($q) => $q->whereNull('estudio_id'))
            ->when($estudioId && $estudioId !== 'directas', fn ($q) => $q->where('estudio_id', $estudioId))
            ->when($busqueda, fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('direccion', 'like', "%{$busqueda}%")
                ->orWhere('codigo_estudio', 'like', "%{$busqueda}%")
                ->orWhere('numero', ltrim($busqueda, '0') ?: -1)
                ->orWhereHas('cliente', fn ($q) => $q->where('nombre', 'like', "%{$busqueda}%"))))
            ->orderByDesc('numero')
            ->paginate(30)
            ->withQueryString();

        return view('obras.index', [
            'obras' => $obras,
            'busqueda' => $busqueda,
            'estado' => $estado,
            'estudioId' => $estudioId,
            'estudios' => Estudio::orderBy('nombre')->pluck('nombre', 'id'),
        ]);
    }

    public function create(Request $request): View
    {
        $obra = new Obra([
            'estudio_id' => $request->integer('estudio_id') ?: null,
            'cliente_id' => $request->integer('cliente_id') ?: null,
            'responsable_id' => $request->user()->id,
        ]);

        return view('obras.form', $this->datosFormulario($obra));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $obra = DB::transaction(function () use ($datos) {
            $obra = new Obra($datos);
            $obra->numero = Obra::siguienteNumero();
            $obra->save();

            foreach (ChecklistPlantillaItem::where('activo', true)->orderBy('orden')->get() as $item) {
                $obra->checklist()->create(['descripcion' => $item->descripcion, 'orden' => $item->orden]);
            }

            foreach (self::CARPETAS_INICIALES as $i => $nombre) {
                $obra->carpetas()->create(['nombre' => $nombre, 'orden' => $i]);
            }

            return $obra;
        });

        return redirect()->route('obras.show', $obra)->with('status', "{$obra->codigo} creada.");
    }

    public function show(Obra $obra): View
    {
        $obra->load([
            'cliente', 'estudio', 'responsable',
            'checklist.completadoPor',
            'permisos' => fn ($q) => $q->orderBy('fecha_vencimiento'),
            'seguros.contacto',
            'notas' => fn ($q) => $q->with('autor')->orderByDesc('fijada')->latest(),
        ]);

        $resumen = [
            'materiales_pendientes' => $obra->materiales()->where('estado', '!=', 'entregado')->count(),
            'tareas' => $obra->tareas()->count(),
            'avance' => (int) round($obra->tareas()->avg('avance') ?? 0),
            'documentos' => $obra->documentos()->count(),
        ];

        return view('obras.show', compact('obra', 'resumen'));
    }

    public function edit(Obra $obra): View
    {
        return view('obras.form', $this->datosFormulario($obra));
    }

    public function update(Request $request, Obra $obra): RedirectResponse
    {
        $obra->update($this->validar($request));

        return redirect()->route('obras.show', $obra)->with('status', 'Obra actualizada.');
    }

    public function destroy(Obra $obra): RedirectResponse
    {
        $obra->delete();

        return redirect()->route('obras.index')->with('status', "{$obra->codigo} eliminada.");
    }

    private function datosFormulario(Obra $obra): array
    {
        return [
            'obra' => $obra,
            'clientes' => Cliente::orderBy('nombre')->pluck('nombre', 'id'),
            'estudios' => Estudio::orderBy('nombre')->pluck('nombre', 'id'),
            'usuarios' => User::where('activo', true)->orderBy('name')->pluck('name', 'id'),
        ];
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'estudio_id' => ['nullable', Rule::exists('estudios', 'id')->whereNull('deleted_at')],
            'codigo_estudio' => ['nullable', 'string', 'max:50'],
            'responsable_id' => ['nullable', Rule::exists('users', 'id')],
            'estado' => ['required', Rule::enum(EstadoObra::class)],
            'tipo' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'localidad' => ['nullable', 'string', 'max:255'],
            'superficie_m2' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'fecha_inicio_prevista' => ['nullable', 'date'],
            'fecha_inicio_real' => ['nullable', 'date'],
            'fecha_fin_prevista' => ['nullable', 'date', 'after_or_equal:fecha_inicio_prevista'],
            'fecha_fin_real' => ['nullable', 'date'],
            'descripcion' => ['nullable', 'string', 'max:10000'],
        ]);
    }
}
