<?php

namespace App\Models;

use App\Enums\TipoContacto;
use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contacto extends Model
{
    use RegistraActividad, SoftDeletes;

    protected $fillable = [
        'tipo', 'nombre', 'empresa', 'cuit', 'telefono', 'email', 'direccion', 'calificacion', 'notas',
    ];

    protected function casts(): array
    {
        return ['tipo' => TipoContacto::class];
    }

    public function rubros(): BelongsToMany
    {
        return $this->belongsToMany(Rubro::class);
    }

    public function seguros(): HasMany
    {
        return $this->hasMany(Seguro::class);
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class);
    }
}
