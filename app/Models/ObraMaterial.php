<?php

namespace App\Models;

use App\Enums\EstadoMaterial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObraMaterial extends Model
{
    protected $table = 'obra_materiales';

    protected $attributes = ['estado' => 'necesito', 'cantidad_pedida' => 0, 'cantidad_entregada' => 0];

    protected $fillable = [
        'obra_id', 'material_id', 'proveedor_id', 'cantidad_necesaria', 'fecha_necesaria',
        'fecha_entrega_estimada', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoMaterial::class,
            'cantidad_necesaria' => 'decimal:2',
            'cantidad_pedida' => 'decimal:2',
            'cantidad_entregada' => 'decimal:2',
            'fecha_necesaria' => 'date',
            'fecha_entrega_estimada' => 'date',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Contacto::class, 'proveedor_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(ObraMaterialMovimiento::class)->latest('fecha');
    }

    /** Recalcula cantidades y estado a partir de los movimientos. */
    public function recalcular(): void
    {
        $pedida = (float) $this->movimientos()->whereIn('tipo', ['pedido', 'ajuste'])->sum('cantidad');
        $entregada = (float) $this->movimientos()->where('tipo', 'entrega')->sum('cantidad');

        $this->cantidad_pedida = $pedida;
        $this->cantidad_entregada = $entregada;
        $this->estado = match (true) {
            $entregada > 0 && $entregada >= (float) $this->cantidad_necesaria => EstadoMaterial::Entregado,
            $entregada > 0 => EstadoMaterial::EntregadoParcial,
            $pedida > 0 => EstadoMaterial::Pedido,
            default => EstadoMaterial::Necesito,
        };
        $this->save();
    }
}
