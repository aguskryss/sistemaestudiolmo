<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    protected $table = 'materiales';

    protected $fillable = ['nombre', 'unidad', 'rubro_id'];

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class);
    }

    public function usos(): HasMany
    {
        return $this->hasMany(ObraMaterial::class);
    }
}
