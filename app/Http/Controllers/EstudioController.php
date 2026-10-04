<?php

namespace App\Http\Controllers;

use App\Models\Estudio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EstudioController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q'));

        $estudios = Estudio::query()
            ->withCount(['obras', 'obras as obras_activas_count' => fn ($q) => $q->whereIn('estado', ['proyecto', 'en_obra', 'pausada'])])
            ->when($busqueda, fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('razon_social', 'like', "%{$busqueda}%")
                ->orWhere('contacto_nombre', 'like', "%{$busqueda}%")))
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
        $estudio = Estudio::create($this->validar($request));

        return redirect()->route('estudios.show', $estudio)->with('status', 'Estudio creado.');
    }

    public function show(Estudio $estudio): View
    {
        $estudio->load(['obras' => fn ($q) => $q->with('cliente')->latest('numero')]);

        return view('estudios.show', compact('estudio'));
    }

    public function edit(Estudio $estudio): View
    {
        return view('estudios.form', compact('estudio'));
    }

    public function update(Request $request, Estudio $estudio): RedirectResponse
    {
        $estudio->update($this->validar($request));

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

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'razon_social' => ['nullable', 'string', 'max:255'],
            'cuit' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'contacto_nombre' => ['nullable', 'string', 'max:255'],
            'contacto_telefono' => ['nullable', 'string', 'max:50'],
            'contacto_email' => ['nullable', 'email', 'max:255'],
            'notas' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
