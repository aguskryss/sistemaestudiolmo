<?php

namespace App\Models;

use App\Enums\TipoCliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $fillable = ['tipo', 'nombre', 'cuit_dni', 'email', 'telefono', 'direccion'];

    protected function casts(): array
    {
        return ['tipo' => TipoCliente::class];
    }

    public function obras(): HasMany
    {
        return $this->hasMany(Obra::class);
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class)->orderByDesc('fijada')->latest();
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }
}
