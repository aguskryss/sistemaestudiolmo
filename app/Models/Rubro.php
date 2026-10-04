<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubro extends Model
{
    protected $fillable = ['nombre', 'color', 'activo'];

    protected $attributes = ['activo' => true];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function contactos(): BelongsToMany
    {
        return $this->belongsToMany(Contacto::class);
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class);
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }
}
