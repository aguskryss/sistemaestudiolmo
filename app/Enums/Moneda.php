<?php

namespace App\Enums;

enum Moneda: string
{
    use Concerns\ConOpciones;

    case ARS = 'ARS';
    case USD = 'USD';

    public function label(): string
    {
        return match ($this) {
            self::ARS => 'Pesos',
            self::USD => 'Dólares',
        };
    }
}
