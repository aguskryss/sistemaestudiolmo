<?php

namespace App\Enums;

enum EstadoPermiso: string
{
    use Concerns\ConOpciones;

    case APresentar = 'a_presentar';
    case Presentado = 'presentado';
    case Observado = 'observado';
    case Aprobado = 'aprobado';
    case Vencido = 'vencido';

    public function label(): string
    {
        return match ($this) {
            self::APresentar => 'A presentar',
            self::Presentado => 'Presentado',
            self::Observado => 'Observado',
            self::Aprobado => 'Aprobado',
            self::Vencido => 'Vencido',
        };
    }
}
