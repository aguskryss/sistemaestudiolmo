<?php

namespace App\Models;

use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tarea de la agenda de un arquitecto (no confundir con las tareas del Gantt de una obra). */
class AgendaTarea extends Model
{
    use RegistraActividad;

    protected $table = 'agenda_tareas';

    protected $fillable = ['obra_id', 'titulo', 'descripcion', 'fecha', 'fecha_fin', 'hora'];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_fin' => 'date',
            'completada_en' => 'datetime',
        ];
    }

    /** Tareas que ocupan algún día del rango [desde, hasta]. */
    public function scopeEntre(Builder $query, $desde, $hasta): void
    {
        $query->whereDate('fecha', '<=', $hasta)
            ->where(fn ($q) => $q->whereDate('fecha_fin', '>=', $desde)->orWhere(fn ($q) => $q->whereNull('fecha_fin')->whereDate('fecha', '>=', $desde)));
    }

    public function ocupaDia(\Carbon\CarbonInterface $dia): bool
    {
        return $dia->betweenIncluded($this->fecha, $this->fecha_fin ?? $this->fecha);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }
}
