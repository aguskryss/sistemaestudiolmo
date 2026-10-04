<?php

namespace App\Enums;

enum EstadoObra: string
{
    use Concerns\ConOpciones;

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

    /** Clase CSS de la etiqueta de estado. */
    public function clase(): string
    {
        return match ($this) {
            self::EnObra => 'etiqueta etiqueta-llena',
            self::Proyecto => 'etiqueta',
            self::Pausada => 'etiqueta etiqueta-alerta',
            self::Terminada => 'etiqueta etiqueta-tenue',
        };
    }
}
