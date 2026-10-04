<?php

namespace App\Models;

use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use RegistraActividad, SoftDeletes;

    protected $fillable = ['obra_id', 'carpeta_id', 'nombre', 'descripcion'];

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class);
    }

    public function versiones(): HasMany
    {
        return $this->hasMany(DocumentoVersion::class)->orderByDesc('numero');
    }

    /** La vigente: siempre la última que se subió. */
    public function versionActual(): HasOne
    {
        return $this->hasOne(DocumentoVersion::class)->ofMany('numero', 'max');
    }
}
