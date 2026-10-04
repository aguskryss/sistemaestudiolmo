<?php

namespace App\Http\Controllers;

use App\Enums\EstadoMaterial;
use App\Enums\EstadoObra;
use App\Models\Obra;
use App\Models\ObraMaterial;
use App\Models\Permiso;
use App\Models\Recordatorio;
use App\Models\Seguro;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function __invoke(Request $request): View
    {
        $activa = fn ($q) => $q->where('estado', '!=', EstadoObra::Terminada);

        return view('inicio', AgendaController::semana($request, $request->user()) + [
            'obras' => AgendaController::obrasParaSelect(),
            'usuarios' => User::where('activo', true)->orderBy('name')->get(),
            'obrasEnCotizacion' => Obra::where('estado', EstadoObra::EnCotizacion)->count(),
            'obrasEnCurso' => Obra::where('estado', EstadoObra::EnObra)->count(),
            'obrasEnProyecto' => Obra::where('estado', EstadoObra::Proyecto)->count(),
            'recordatorios' => Recordatorio::where('user_id', $request->user()->id)
                ->whereNull('completado_en')
                ->where('fecha_hora', '<=', today()->addDays(7)->endOfDay())
                ->orderBy('fecha_hora')
                ->limit(8)
                ->get(),
            'segurosPorVencer' => Seguro::with('contacto')
                ->whereBetween('vigencia_hasta', [today()->subDays(7), today()->addDays(15)])
                ->orderBy('vigencia_hasta')
                ->get(),
            'permisosPorVencer' => Permiso::with(['obra', 'tipoPermiso'])
                ->whereHas('obra', $activa)
                ->whereBetween('fecha_vencimiento', [today(), today()->addDays(30)])
                ->orderBy('fecha_vencimiento')
                ->get(),
            'materialesUrgentes' => ObraMaterial::with(['obra', 'material'])
                ->whereHas('obra', $activa)
                ->where('estado', '!=', EstadoMaterial::Entregado)
                ->whereNotNull('fecha_necesaria')
                ->where('fecha_necesaria', '<=', today()->addDays(7))
                ->orderBy('fecha_necesaria')
                ->limit(10)
                ->get(),
            'tareasSemana' => Tarea::with(['obra', 'contacto'])
                ->whereHas('obra', $activa)
                ->where('fecha_inicio', '<=', today()->endOfWeek())
                ->where('fecha_fin', '>=', today()->startOfWeek())
                ->where('avance', '<', 100)
                ->orderBy('fecha_inicio')
                ->limit(10)
                ->get(),
        ]);
    }
}
