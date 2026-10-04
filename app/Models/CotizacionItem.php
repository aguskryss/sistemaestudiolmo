<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CotizacionItem extends Model
{
    protected $fillable = ['cotizacion_id', 'orden', 'descripcion', 'unidad', 'cantidad', 'precio_unitario'];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (CotizacionItem $item) {
            $item->subtotal = round((float) $item->cantidad * (float) $item->precio_unitario, 2);
        });
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class);
    }
}
