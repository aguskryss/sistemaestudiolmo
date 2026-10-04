<?php

namespace App\Enums;

enum Repeticion: string
{
    use Concerns\ConOpciones;

    case Diaria = 'diaria';
    case Semanal = 'semanal';
    case Mensual = 'mensual';
    case Anual = 'anual';

    public function label(): string
    {
        return match ($this) {
            self::Diaria => 'Diaria',
            self::Semanal => 'Semanal',
            self::Mensual => 'Mensual',
            self::Anual => 'Anual',
        };
    }
}
