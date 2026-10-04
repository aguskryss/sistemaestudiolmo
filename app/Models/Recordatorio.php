<?php

namespace App\Models;

use App\Enums\Repeticion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Recordatorio extends Model
{
    protected $fillable = [
        'user_id', 'titulo', 'descripcion', 'fecha_hora', 'repeticion', 'recordable_type', 'recordable_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'repeticion' => Repeticion::class,
            'automatico' => 'boolean',
            'enviado_en' => 'datetime',
            'completado_en' => 'datetime',
        ];
    }

    /** Los que el cron tiene que mandar por mail ahora. */
    public function scopePendientesDeEnvio(Builder $query): void
    {
        $query->whereNull('enviado_en')->whereNull('completado_en')->where('fecha_hora', '<=', now());
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /** Obra, permiso, seguro, cotización... a lo que se refiera el recordatorio. */
    public function recordable(): MorphTo
    {
        return $this->morphTo();
    }
}
