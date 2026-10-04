<?php

namespace App\Enums;

enum EstadoObra: string
{
    use Concerns\ConOpciones;

    case EnCotizacion = 'en_cotizacion';
    case Proyecto = 'proyecto';
    case EnObra = 'en_obra';
    case Pausada = 'pausada';
    case Terminada = 'terminada';

    public function label(): string
    {
        return match ($this) {
            self::EnCotizacion => 'En cotización',
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
            self::EnCotizacion => 'etiqueta etiqueta-alerta',
            self::Pausada => 'etiqueta etiqueta-tenue etiqueta-alerta',
            self::Terminada => 'etiqueta etiqueta-tenue',
        };
    }
}
