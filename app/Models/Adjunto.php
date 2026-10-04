<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Adjunto extends Model
{
    protected $fillable = ['ruta', 'nombre_original', 'mime', 'tamano', 'subido_por'];

    protected $hidden = ['ruta'];

    public function adjuntable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}
