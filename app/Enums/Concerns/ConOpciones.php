<?php

namespace App\Enums\Concerns;

trait ConOpciones
{
    /** [valor => etiqueta] para los selects. */
    public static function opciones(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
