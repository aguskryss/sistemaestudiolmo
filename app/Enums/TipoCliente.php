<?php

namespace App\Enums;

enum TipoCliente: string
{
    use Concerns\ConOpciones;

    case Persona = 'persona';
    case Empresa = 'empresa';

    public function label(): string
    {
        return match ($this) {
            self::Persona => 'Persona',
            self::Empresa => 'Empresa',
        };
    }
}
