<?php

namespace App\Enums;

enum EstadoCotizacion: string
{
    use Concerns\ConOpciones;

    case Borrador = 'borrador';
    case Pendiente = 'pendiente';
    case Aceptada = 'aceptada';
    case Rechazada = 'rechazada';
    case Vencida = 'vencida';

    public function label(): string
    {
        return match ($this) {
            self::Borrador => 'Borrador',
            self::Pendiente => 'Pendiente',
            self::Aceptada => 'Aceptada',
            self::Rechazada => 'Rechazada',
            self::Vencida => 'Vencida',
        };
    }
}
