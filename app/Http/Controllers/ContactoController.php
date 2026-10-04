<?php

namespace App\Http\Controllers;

use App\Enums\TipoContacto;
use App\Models\Contacto;
use App\Models\Obra;
use App\Models\Rubro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactoController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q'));
        $rubro = $request->query('rubro');
        $tipo = $request->query('tipo');

        $contactos = Contacto::query()
            ->with(['rubros', 'seguros' => fn ($q) => $q->vigentes()])
            ->when($rubro, fn ($q) => $q->whereHas('rubros', fn ($q) => $q->where('rubros.id', $rubro)))
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->when($busqueda, fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('empresa', 'like', "%{$busqueda}%")
                ->orWhere('telefono', 'like', "%{$busqueda}%")))
            ->orderBy('nombre')
            ->paginate(40)
            ->withQueryString();

        return view('contactos.index', [
            'contactos' => $contactos,
            'busqueda' => $busqueda,
            'rubro' => $rubro,
            'tipo' => $tipo,
            'rubros' => Rubro::orderBy('nombre')->pluck('nombre', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('contactos.form', ['contacto' => new Contacto(['tipo' => 'gremio']), 'rubros' => Rubro::orderBy('nombre')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$datos, $rubros] = $this->validar($request);

        $contacto = DB::transaction(function () use ($datos, $rubros) {
            $contacto = Contacto::create($datos);
            $contacto->rubros()->sync($rubros);

            return $contacto;
        });

        return redirect()->route('contactos.show', $contacto)->with('status', 'Contacto creado.');
    }

    public function show(Contacto $contacto): View
    {
        $contacto->load([
            'rubros',
            'seguros' => fn ($q) => $q->with(['obras', 'adjuntos'])->orderByDesc('vigencia_hasta'),
            'cotizaciones' => fn ($q) => $q->with(['obra', 'rubro', 'contacto', 'cliente', 'estudio'])->latest('fecha'),
            'tareas' => fn ($q) => $q->with('obra')->orderByDesc('fecha_inicio')->limit(20),
        ]);

        return view('contactos.show', [
            'contacto' => $contacto,
            'obras' => Obra::where('estado', '!=', 'terminada')->orderByDesc('numero')->get()->mapWithKeys(fn ($o) => [$o->id => $o->codigo.' · '.$o->nombre]),
        ]);
    }

    public function edit(Contacto $contacto): View
    {
        return view('contactos.form', ['contacto' => $contacto->load('rubros'), 'rubros' => Rubro::orderBy('nombre')->get()]);
    }

    public function update(Request $request, Contacto $contacto): RedirectResponse
    {
        [$datos, $rubros] = $this->validar($request);

        DB::transaction(function () use ($contacto, $datos, $rubros) {
            $contacto->update($datos);
            $contacto->rubros()->sync($rubros);
        });

        return redirect()->route('contactos.show', $contacto)->with('status', 'Contacto actualizado.');
    }

    public function destroy(Contacto $contacto): RedirectResponse
    {
        $contacto->delete();

        return redirect()->route('contactos.index')->with('status', 'Contacto eliminado.');
    }

    /** Alta rápida de un rubro nuevo desde el formulario de contacto. */
    public function crearRubro(Request $request): RedirectResponse
    {
        $datos = $request->validate(['nombre' => ['required', 'string', 'max:100', 'unique:rubros,nombre']]);
        Rubro::create($datos);

        return back()->withInput()->with('status', "Rubro «{$datos['nombre']}» creado.");
    }

    /** @return array{0: array, 1: array} */
    private function validar(Request $request): array
    {
        $datos = $request->validate([
            'tipo' => ['required', Rule::enum(TipoContacto::class)],
            'nombre' => ['required', 'string', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'cuit' => ['nullable', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'calificacion' => ['nullable', 'integer', 'between:1,5'],
            'notas' => ['nullable', 'string', 'max:5000'],
            'rubros' => ['nullable', 'array'],
            'rubros.*' => [Rule::exists('rubros', 'id')],
        ]);

        return [collect($datos)->except('rubros')->all(), $datos['rubros'] ?? []];
    }
}
