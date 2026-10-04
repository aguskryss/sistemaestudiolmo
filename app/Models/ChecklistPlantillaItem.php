<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistPlantillaItem extends Model
{
    protected $fillable = ['descripcion', 'orden', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
