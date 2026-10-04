<?php

namespace App\Models;

use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tarea extends Model
{
    use RegistraActividad;

    protected $fillable = [
        'obra_id', 'rubro_id', 'contacto_id', 'nombre', 'color', 'fecha_inicio', 'fecha_fin', 'avance', 'es_hito', 'orden', 'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'es_hito' => 'boolean',
        ];
    }

    /** Color propio de la tarea; si no tiene, el de su rubro; si no, gris. */
    public function colorEfectivo(): string
    {
        return $this->color ?? $this->rubro?->color ?? 'gris';
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class);
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contacto::class);
    }

}
