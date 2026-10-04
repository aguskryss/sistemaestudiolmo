<?php

namespace App\Enums;

enum TipoSeguro: string
{
    use Concerns\ConOpciones;

    case Art = 'art';
    case Ap = 'ap';
    case Rc = 'rc';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Art => 'ART',
            self::Ap => 'Accidentes personales',
            self::Rc => 'Responsabilidad civil',
            self::Otro => 'Otro',
        };
    }
}
