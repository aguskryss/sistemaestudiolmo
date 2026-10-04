<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoVersion extends Model
{
    protected $table = 'documento_versiones';

    protected $fillable = [
        'documento_id', 'numero', 'ruta', 'nombre_original', 'mime', 'tamano', 'hash', 'subido_por', 'comentario',
    ];

    protected $hidden = ['ruta'];

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}
