<?php

namespace App\Enums;

enum Rol: string
{
    use Concerns\ConOpciones;

    case Admin = 'admin';
    case Miembro = 'miembro';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Miembro => 'Miembro',
        };
    }
}
