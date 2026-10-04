<?php

namespace App\Models;

use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tarea extends Model
{
    use RegistraActividad;

    protected $fillable = [
        'obra_id', 'rubro_id', 'contacto_id', 'nombre', 'fecha_inicio', 'fecha_fin', 'avance', 'es_hito', 'orden', 'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'es_hito' => 'boolean',
        ];
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

    /** Tareas que tienen que terminar antes de que empiece esta. */
    public function dependeDe(): BelongsToMany
    {
        return $this->belongsToMany(Tarea::class, 'tarea_dependencias', 'tarea_id', 'depende_de_id');
    }

    public function bloqueaA(): BelongsToMany
    {
        return $this->belongsToMany(Tarea::class, 'tarea_dependencias', 'depende_de_id', 'tarea_id');
    }
}
