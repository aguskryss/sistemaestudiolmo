<?php

namespace App\Models;

use App\Enums\Moneda;
use App\Enums\TipoSeguro;
use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seguro extends Model
{
    use RegistraActividad, SoftDeletes;

    protected $attributes = ['moneda' => 'ARS'];

    protected $fillable = [
        'contacto_id', 'tipo', 'aseguradora', 'numero_poliza', 'vigencia_desde', 'vigencia_hasta',
        'suma_asegurada', 'moneda', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoSeguro::class,
            'moneda' => Moneda::class,
            'vigencia_desde' => 'date',
            'vigencia_hasta' => 'date',
            'suma_asegurada' => 'decimal:2',
        ];
    }

    public function scopeVigentes(Builder $query): void
    {
        $query->whereDate('vigencia_desde', '<=', today())->whereDate('vigencia_hasta', '>=', today());
    }

    public function scopeVencenEn(Builder $query, int $dias): void
    {
        $query->whereBetween('vigencia_hasta', [today(), today()->addDays($dias)]);
    }

    public function estaVigente(): bool
    {
        return today()->betweenIncluded($this->vigencia_desde, $this->vigencia_hasta);
    }

    /** [texto, clase CSS] de la etiqueta de vigencia. */
    public function situacion(): array
    {
        return match (true) {
            $this->vigencia_hasta->lt(today()) => ['Vencido', 'etiqueta etiqueta-alerta'],
            $this->vigencia_desde->gt(today()) => ['Desde '.$this->vigencia_desde->format('d.m'), 'etiqueta etiqueta-tenue'],
            $this->vigencia_hasta->lte(today()->addDays(15)) => ['Vence '.$this->vigencia_hasta->format('d.m'), 'etiqueta'],
            default => ['Vigente', 'etiqueta etiqueta-llena'],
        };
    }

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contacto::class);
    }

    public function obras(): BelongsToMany
    {
        return $this->belongsToMany(Obra::class, 'obra_seguro');
    }

    public function adjuntos(): MorphMany
    {
        return $this->morphMany(Adjunto::class, 'adjuntable');
    }

    public function recordatorios(): MorphMany
    {
        return $this->morphMany(Recordatorio::class, 'recordable');
    }
}
