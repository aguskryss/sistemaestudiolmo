<?php

namespace App\Models;

use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Estudio de arquitectura que nos subcontrata una obra. */
class Estudio extends Model
{
    use RegistraActividad, SoftDeletes;

    protected $fillable = [
        'nombre', 'razon_social', 'cuit', 'email', 'telefono', 'direccion',
        'contacto_nombre', 'contacto_telefono', 'contacto_email', 'notas',
    ];

    public function obras(): HasMany
    {
        return $this->hasMany(Obra::class);
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }
}
