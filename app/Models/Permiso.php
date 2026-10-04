<?php

namespace App\Models;

use App\Enums\EstadoPermiso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permiso extends Model
{
    use SoftDeletes;

    protected $attributes = ['estado' => 'a_presentar'];

    protected $fillable = [
        'obra_id', 'tipo', 'organismo', 'numero_expediente', 'estado', 'fecha_presentacion',
        'fecha_aprobacion', 'fecha_vencimiento', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPermiso::class,
            'fecha_presentacion' => 'date',
            'fecha_aprobacion' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function adjuntos(): MorphMany
    {
        return $this->morphMany(Adjunto::class, 'adjuntable');
    }

    public function recordatorios(): MorphMany
    {
        return $this->morphMany(Recordatorio::class, 'recordable');
    }
}
