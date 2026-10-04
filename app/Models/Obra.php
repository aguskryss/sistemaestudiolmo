<?php

namespace App\Models;

use App\Enums\EstadoObra;
use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obra extends Model
{
    use RegistraActividad, SoftDeletes;

    protected $attributes = ['estado' => 'proyecto'];

    protected $fillable = [
        'numero', 'cliente_id', 'estudio_id', 'codigo_estudio', 'estudio_contacto_id', 'responsable_id', 'nombre', 'direccion', 'localidad', 'tipo_obra_id', 'estado',
        'superficie_m2', 'fecha_inicio_prevista', 'fecha_inicio_real', 'fecha_fin_prevista',
        'fecha_fin_real', 'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoObra::class,
            'superficie_m2' => 'decimal:2',
            'fecha_inicio_prevista' => 'date',
            'fecha_inicio_real' => 'date',
            'fecha_fin_prevista' => 'date',
            'fecha_fin_real' => 'date',
        ];
    }

    /** "Obra N° 014" */
    protected function codigo(): Attribute
    {
        return Attribute::get(fn () => 'Obra N° '.str_pad((string) $this->numero, 3, '0', STR_PAD_LEFT));
    }

    /** "N° 014", para listados angostos */
    protected function codigoCorto(): Attribute
    {
        return Attribute::get(fn () => 'N° '.str_pad((string) $this->numero, 3, '0', STR_PAD_LEFT));
    }

    public static function siguienteNumero(): int
    {
        return (int) static::withTrashed()->max('numero') + 1;
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Estudio que nos subcontrató la obra; null si es una obra directa. */
    public function estudio(): BelongsTo
    {
        return $this->belongsTo(Estudio::class);
    }

    /** Persona del estudio contratante con la que se trata esta obra. */
    public function estudioContacto(): BelongsTo
    {
        return $this->belongsTo(EstudioContacto::class);
    }

    public function tipoObra(): BelongsTo
    {
        return $this->belongsTo(Opcion::class, 'tipo_obra_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(ObraChecklistItem::class)->orderBy('orden');
    }

    public function carpetas(): HasMany
    {
        return $this->hasMany(Carpeta::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function materiales(): HasMany
    {
        return $this->hasMany(ObraMaterial::class);
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class)->orderBy('orden')->orderBy('fecha_inicio');
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class);
    }

    public function seguros(): BelongsToMany
    {
        return $this->belongsToMany(Seguro::class, 'obra_seguro');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class);
    }
}
