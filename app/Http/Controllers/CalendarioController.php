<?php

namespace App\Http\Controllers;

use App\Enums\EstadoObra;
use App\Models\Obra;
use App\Models\Tarea;
use Illuminate\View\View;

/** Vista general: todas las obras activas en un mismo Gantt, más lo que pasa esta semana. */
class CalendarioController extends Controller
{
    public function __invoke(): View
    {
        $obras = Obra::query()
            ->where('estado', '!=', EstadoObra::Terminada)
            ->withMin('tareas', 'fecha_inicio')
            ->withMax('tareas', 'fecha_fin')
            ->withAvg('tareas', 'avance')
            ->with('estudio')
            ->orderBy('numero')
            ->get();

        $barras = $obras->map(function (Obra $o) {
            $inicio = $o->tareas_min_fecha_inicio ?? $o->fecha_inicio_real ?? $o->fecha_inicio_prevista;
            $fin = $o->tareas_max_fecha_fin ?? $o->fecha_fin_real ?? $o->fecha_fin_prevista;

            if (! $inicio || ! $fin) {
                return null;
            }

            return [
                'id' => (string) $o->id,
                'name' => $o->codigo.' · '.$o->nombre.($o->estudio ? ' ('.$o->estudio->nombre.')' : ''),
                'start' => substr((string) $inicio, 0, 10),
                'end' => substr((string) $fin, 0, 10),
                'progress' => (int) round($o->tareas_avg_avance ?? 0),
                'custom_class' => $o->estado === EstadoObra::Pausada ? 'pausada' : '',
            ];
        })->filter()->values();

        $semana = Tarea::query()
            ->with(['obra', 'contacto'])
            ->whereHas('obra', fn ($q) => $q->where('estado', '!=', EstadoObra::Terminada))
            ->where('fecha_inicio', '<=', today()->endOfWeek())
            ->where('fecha_fin', '>=', today()->startOfWeek())
            ->where('avance', '<', 100)
            ->orderBy('fecha_inicio')
            ->get();

        return view('calendario', [
            'barras' => $barras,
            'sinFechas' => $obras->count() - $barras->count(),
            'semana' => $semana,
        ]);
    }
}
