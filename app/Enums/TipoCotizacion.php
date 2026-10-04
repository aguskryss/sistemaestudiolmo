<?php

namespace App\Enums;

enum TipoCotizacion: string
{
    case Recibida = 'recibida';
    case Emitida = 'emitida';

    public function label(): string
    {
        return match ($this) {
            self::Recibida => 'Recibida',
            self::Emitida => 'Emitida',
        };
    }
}
