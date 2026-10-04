<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Actividad extends Model
{
    protected $table = 'actividad';

    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'accion', 'sujeto_type', 'sujeto_id', 'cambios', 'ip'];

    protected function casts(): array
    {
        return ['cambios' => 'array'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sujeto(): MorphTo
    {
        return $this->morphTo();
    }
}
