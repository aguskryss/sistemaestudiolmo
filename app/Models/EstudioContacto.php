<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Persona de contacto dentro de un estudio contratante. */
class EstudioContacto extends Model
{
    protected $fillable = ['nombre', 'cargo', 'telefono', 'email'];

    public function estudio(): BelongsTo
    {
        return $this->belongsTo(Estudio::class);
    }
}
