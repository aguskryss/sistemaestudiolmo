<?php

namespace App\Models;

use App\Enums\EstadoCotizacion;
use App\Enums\Moneda;
use App\Enums\TipoCotizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cotizacion extends Model
{
    use SoftDeletes;

    protected $table = 'cotizaciones';

    protected $attributes = ['estado' => 'borrador', 'moneda' => 'ARS', 'total' => 0];

    protected $fillable = [
        'tipo', 'numero', 'titulo', 'obra_id', 'cliente_id', 'contacto_id', 'rubro_id', 'fecha', 'valida_hasta',
        'moneda', 'tipo_cambio', 'estado', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoCotizacion::class,
            'estado' => EstadoCotizacion::class,
            'moneda' => Moneda::class,
            'fecha' => 'date',
            'valida_hasta' => 'date',
            'tipo_cambio' => 'decimal:4',
            'total' => 'decimal:2',
        ];
    }

    public function scopeRecibidas(Builder $query): void
    {
        $query->where('tipo', TipoCotizacion::Recibida);
    }

    public function scopeEmitidas(Builder $query): void
    {
        $query->where('tipo', TipoCotizacion::Emitida);
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contacto::class);
    }

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CotizacionItem::class)->orderBy('orden');
    }

    public function adjuntos(): MorphMany
    {
        return $this->morphMany(Adjunto::class, 'adjuntable');
    }

    public function recalcularTotal(): void
    {
        $this->total = $this->items()->sum('subtotal');
        $this->save();
    }
}
