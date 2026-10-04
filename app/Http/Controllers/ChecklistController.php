<?php

namespace App\Http\Controllers;

use App\Models\Obra;
use App\Models\ObraChecklistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function store(Request $request, Obra $obra): RedirectResponse
    {
        $datos = $request->validate(['descripcion' => ['required', 'string', 'max:255']]);

        $obra->checklist()->create($datos + ['orden' => (int) $obra->checklist()->max('orden') + 1]);

        return back();
    }

    public function alternar(Request $request, ObraChecklistItem $item): RedirectResponse
    {
        $item->forceFill($item->completado_en
            ? ['completado_en' => null, 'completado_por' => null]
            : ['completado_en' => now(), 'completado_por' => $request->user()->id])->save();

        return back();
    }

    public function destroy(ObraChecklistItem $item): RedirectResponse
    {
        $item->delete();

        return back();
    }
}
