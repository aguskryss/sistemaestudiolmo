<?php

namespace App\Models;

use App\Enums\TipoMovimientoMaterial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObraMaterialMovimiento extends Model
{
    protected $fillable = ['obra_material_id', 'tipo', 'cantidad', 'fecha', 'remito', 'user_id', 'observaciones'];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMovimientoMaterial::class,
            'cantidad' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    public function obraMaterial(): BelongsTo
    {
        return $this->belongsTo(ObraMaterial::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
