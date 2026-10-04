<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Valores de las listas configurables (tipos de obra, tipos de permiso, unidades).
 */
class Opcion extends Model
{
    protected $table = 'opciones';

    /** Grupos que se administran desde Configuración: clave => [singular, plural]. */
    public const GRUPOS = [
        'tipo_obra' => ['Tipo de obra', 'Tipos de obra'],
        'tipo_permiso' => ['Tipo de permiso', 'Tipos de permiso'],
        'unidad' => ['Unidad', 'Unidades'],
    ];

    protected $fillable = ['grupo', 'nombre', 'orden', 'activo'];

    protected $attributes = ['activo' => true, 'orden' => 0];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function scopeDelGrupo(Builder $query, string $grupo): void
    {
        $query->where('grupo', $grupo)->orderBy('orden')->orderBy('nombre');
    }

    /**
     * [id => nombre] de las opciones activas del grupo, más la seleccionada aunque esté desactivada
     * (para no perder el valor al editar un registro viejo).
     */
    public static function lista(string $grupo, ?int $incluir = null): Collection
    {
        return static::delGrupo($grupo)
            ->where(fn ($q) => $q->where('activo', true)->when($incluir, fn ($q) => $q->orWhere('id', $incluir)))
            ->pluck('nombre', 'id');
    }
}
