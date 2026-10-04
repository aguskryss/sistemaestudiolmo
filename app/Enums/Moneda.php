<?php

namespace App\Enums;

enum Moneda: string
{
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
