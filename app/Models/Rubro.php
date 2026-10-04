<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rubro extends Model
{
    protected $fillable = ['nombre'];

    public function contactos(): BelongsToMany
    {
        return $this->belongsToMany(Contacto::class);
    }
}
