<?php

namespace App\Enums;

enum EstadoObra: string
{
    case Proyecto = 'proyecto';
    case EnObra = 'en_obra';
    case Pausada = 'pausada';
    case Terminada = 'terminada';

    public function label(): string
    {
        return match ($this) {
            self::Proyecto => 'En proyecto',
            self::EnObra => 'En obra',
            self::Pausada => 'Pausada',
            self::Terminada => 'Terminada',
        };
    }
}
