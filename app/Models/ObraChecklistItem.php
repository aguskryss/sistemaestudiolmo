<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObraChecklistItem extends Model
{
    protected $fillable = ['obra_id', 'descripcion', 'orden', 'completado_en', 'completado_por', 'observaciones'];

    protected function casts(): array
    {
        return ['completado_en' => 'datetime'];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function completadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completado_por');
    }
}
