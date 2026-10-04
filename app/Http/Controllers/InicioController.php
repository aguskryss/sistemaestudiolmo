<?php

namespace App\Http\Controllers;

use App\Enums\EstadoObra;
use App\Models\Obra;
use App\Models\Permiso;
use App\Models\Recordatorio;
use App\Models\Seguro;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('inicio', [
            'obrasEnCurso' => Obra::where('estado', EstadoObra::EnObra)->count(),
            'obrasEnProyecto' => Obra::where('estado', EstadoObra::Proyecto)->count(),
            'segurosPorVencer' => Seguro::vencenEn(15)->count(),
            'permisosPorVencer' => Permiso::whereBetween('fecha_vencimiento', [today(), today()->addDays(30)])->count(),
            'recordatorios' => Recordatorio::where('user_id', $request->user()->id)
                ->whereNull('completado_en')
                ->whereBetween('fecha_hora', [today(), today()->addDays(7)->endOfDay()])
                ->orderBy('fecha_hora')
                ->limit(6)
                ->get(),
        ]);
    }
}
