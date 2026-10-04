<?php

namespace App\Enums;

enum TipoContacto: string
{
    case Gremio = 'gremio';
    case Proveedor = 'proveedor';
    case Profesional = 'profesional';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Gremio => 'Gremio',
            self::Proveedor => 'Proveedor',
            self::Profesional => 'Profesional',
            self::Otro => 'Otro',
        };
    }
}
