<?php

namespace App\Http\Controllers;

use App\Enums\TipoCliente;
use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q'));

        $clientes = Cliente::query()
            ->withCount('obras')
            ->when($busqueda, fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('cuit_dni', 'like', "%{$busqueda}%")
                ->orWhere('email', 'like', "%{$busqueda}%")))
            ->orderBy('nombre')
            ->paginate(30)
            ->withQueryString();

        return view('clientes.index', compact('clientes', 'busqueda'));
    }

    public function create(): View
    {
        return view('clientes.form', ['cliente' => new Cliente]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cliente = Cliente::create($this->validar($request));

        if ($request->filled('volver_a_obra')) {
            return redirect()->route('obras.create', ['cliente_id' => $cliente->id])->with('status', 'Cliente creado.');
        }

        return redirect()->route('clientes.show', $cliente)->with('status', 'Cliente creado.');
    }

    public function show(Cliente $cliente): View
    {
        $cliente->load([
            'obras' => fn ($q) => $q->with('estudio')->latest('numero'),
            'notas' => fn ($q) => $q->with(['autor', 'obra']),
        ]);

        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente): View
    {
        return view('clientes.form', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($this->validar($request));

        return redirect()->route('clientes.show', $cliente)->with('status', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente): RedirectResponse
    {
        if ($cliente->obras()->exists()) {
            return back()->withErrors(['cliente' => 'No se puede eliminar: tiene obras asociadas.']);
        }

        $cliente->delete();

        return redirect()->route('clientes.index')->with('status', 'Cliente eliminado.');
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'tipo' => ['required', Rule::enum(TipoCliente::class)],
            'nombre' => ['required', 'string', 'max:255'],
            'cuit_dni' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
