<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carpeta extends Model
{
    protected $fillable = ['obra_id', 'parent_id', 'nombre', 'orden'];

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class, 'parent_id');
    }

    public function subcarpetas(): HasMany
    {
        return $this->hasMany(Carpeta::class, 'parent_id')->orderBy('orden')->orderBy('nombre');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class)->orderBy('nombre');
    }
}
