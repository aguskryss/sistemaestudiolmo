<?php

namespace App\Http\Controllers;

use App\Models\ChecklistPlantillaItem;
use App\Models\Material;
use App\Models\Obra;
use App\Models\Opcion;
use App\Models\Permiso;
use App\Models\Rubro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ABM de las listas que alimentan los selects: tipos de obra, tipos de permiso, unidades,
 * rubros, catálogo de materiales y checklist de inicio de obra.
 *
 * Lo que está en uso no se borra: se desactiva (deja de aparecer en los selects, pero los registros viejos lo conservan).
 */
class ConfiguracionController extends Controller
{
    public const SECCIONES = [
        'tipo_obra' => 'Tipos de obra',
        'rubros' => 'Rubros',
        'materiales' => 'Materiales',
        'unidad' => 'Unidades',
        'tipo_permiso' => 'Tipos de permiso',
        'checklist' => 'Checklist de inicio',
    ];

    public function index(string $seccion = 'tipo_obra'): View
    {
        abort_unless(array_key_exists($seccion, self::SECCIONES), 404);

        $items = match ($seccion) {
            'rubros' => Rubro::withCount(['contactos', 'materiales', 'tareas', 'cotizaciones'])->orderBy('nombre')->get()
                ->each(fn ($r) => $r->usos = $r->contactos_count + $r->materiales_count + $r->tareas_count + $r->cotizaciones_count),
            'materiales' => Material::with('rubro')->withCount('usos')->orderBy('nombre')->get()
                ->each(fn ($m) => $m->usos = $m->usos_count),
            'checklist' => ChecklistPlantillaItem::orderBy('orden')->get(),
            default => Opcion::delGrupo($seccion)->get()->each(fn ($o) => $o->usos = $this->usosDeOpcion($o)),
        };

        return view('configuracion.index', [
            'seccion' => $seccion,
            'items' => $items,
            'unidades' => Opcion::lista('unidad'),
            'rubros' => Rubro::where('activo', true)->orderBy('nombre')->pluck('nombre', 'id'),
        ]);
    }

    public function store(Request $request, string $seccion): RedirectResponse
    {
        abort_unless(array_key_exists($seccion, self::SECCIONES), 404);

        match ($seccion) {
            'rubros' => Rubro::create($request->validate(['nombre' => ['required', 'string', 'max:100', 'unique:rubros,nombre']])),
            'materiales' => Material::create($this->validarMaterial($request)),
            'checklist' => ChecklistPlantillaItem::create($request->validate(['descripcion' => ['required', 'string', 'max:255']]) + [
                'orden' => (int) ChecklistPlantillaItem::max('orden') + 1,
            ]),
            default => Opcion::create($request->validate([
                'nombre' => ['required', 'string', 'max:100', Rule::unique('opciones', 'nombre')->where('grupo', $seccion)],
            ]) + ['grupo' => $seccion, 'orden' => (int) Opcion::where('grupo', $seccion)->max('orden') + 1]),
        };

        return back()->with('status', 'Agregado.');
    }

    public function update(Request $request, string $seccion, int $id): RedirectResponse
    {
        $request->merge(['activo' => $request->boolean('activo')]);

        match ($seccion) {
            'rubros' => tap(Rubro::findOrFail($id), fn ($r) => $r->update($request->validate([
                'nombre' => ['required', 'string', 'max:100', Rule::unique('rubros', 'nombre')->ignore($id)],
                'activo' => ['boolean'],
            ]))),
            'materiales' => tap(Material::findOrFail($id), fn ($m) => $m->update($this->validarMaterial($request, $id) + ['activo' => $request->boolean('activo')])),
            'checklist' => tap(ChecklistPlantillaItem::findOrFail($id), fn ($c) => $c->update(array_filter($request->validate([
                'descripcion' => ['required', 'string', 'max:255'],
                'orden' => ['nullable', 'integer', 'min:0', 'max:999'],
                'activo' => ['boolean'],
            ]), fn ($v) => $v !== null))),
            default => $this->actualizarOpcion($request, $seccion, $id),
        };

        return back()->with('status', 'Guardado.');
    }

    public function destroy(string $seccion, int $id): RedirectResponse
    {
        $modelo = match ($seccion) {
            'rubros' => Rubro::withCount(['contactos', 'materiales', 'tareas', 'cotizaciones'])->findOrFail($id),
            'materiales' => Material::withCount('usos')->findOrFail($id),
            'checklist' => ChecklistPlantillaItem::findOrFail($id),
            default => Opcion::where('grupo', $seccion)->findOrFail($id),
        };

        $usos = match ($seccion) {
            'rubros' => $modelo->contactos_count + $modelo->materiales_count + $modelo->tareas_count + $modelo->cotizaciones_count,
            'materiales' => $modelo->usos_count,
            'checklist' => 0, // cada obra tiene su propia copia del checklist
            default => $this->usosDeOpcion($modelo),
        };

        if ($usos > 0) {
            $modelo->update(['activo' => false]);

            return back()->with('status', 'Está en uso, así que se desactivó en lugar de borrarse: ya no aparece para elegir.');
        }

        $modelo->delete();

        return back()->with('status', 'Eliminado.');
    }

    private function actualizarOpcion(Request $request, string $grupo, int $id): void
    {
        $opcion = Opcion::where('grupo', $grupo)->findOrFail($id);
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('opciones', 'nombre')->where('grupo', $grupo)->ignore($id)],
            'activo' => ['boolean'],
        ]);

        DB::transaction(function () use ($opcion, $datos, $grupo) {
            // Las unidades se guardan como texto en el catálogo de materiales: se renombran ahí también.
            if ($grupo === 'unidad' && $opcion->nombre !== $datos['nombre']) {
                Material::where('unidad', $opcion->nombre)->update(['unidad' => $datos['nombre']]);
            }
            $opcion->update($datos);
        });
    }

    private function usosDeOpcion(Opcion $opcion): int
    {
        return match ($opcion->grupo) {
            'tipo_obra' => Obra::withTrashed()->where('tipo_obra_id', $opcion->id)->count(),
            'tipo_permiso' => Permiso::withTrashed()->where('tipo_permiso_id', $opcion->id)->count(),
            'unidad' => Material::where('unidad', $opcion->nombre)->count(),
            default => 0,
        };
    }

    private function validarMaterial(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('materiales', 'nombre')->where('unidad', $request->input('unidad'))->ignore($id)],
            'unidad' => ['required', 'string', Rule::exists('opciones', 'nombre')->where('grupo', 'unidad')],
            'rubro_id' => ['nullable', Rule::exists('rubros', 'id')],
        ], ['nombre.unique' => 'Ya existe ese material con esa unidad.']);
    }
}
