<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Nota;
use App\Models\Obra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotaController extends Controller
{
    public function store(Request $request, Cliente $cliente): RedirectResponse
    {
        $datos = $request->validate([
            'contenido' => ['required', 'string', 'max:10000'],
            'obra_id' => ['nullable', Rule::exists('obras', 'id')->where('cliente_id', $cliente->id)],
        ]);

        $nota = $cliente->notas()->make($datos);
        $nota->user_id = $request->user()->id;
        $nota->save();

        return back()->with('status', 'Nota agregada.');
    }

    /** Nota desde la ficha de la obra (la obra puede no tener cliente). */
    public function storeObra(Request $request, Obra $obra): RedirectResponse
    {
        $datos = $request->validate(['contenido' => ['required', 'string', 'max:10000']]);

        $nota = new Nota($datos + ['obra_id' => $obra->id, 'cliente_id' => $obra->cliente_id]);
        $nota->user_id = $request->user()->id;
        $nota->save();

        return back()->with('status', 'Nota agregada.');
    }

    public function fijar(Nota $nota): RedirectResponse
    {
        $nota->update(['fijada' => ! $nota->fijada]);

        return back();
    }

    public function destroy(Nota $nota): RedirectResponse
    {
        $nota->delete();

        return back()->with('status', 'Nota eliminada.');
    }
}
