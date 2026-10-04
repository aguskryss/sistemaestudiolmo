<?php

namespace App\Support;

use App\Enums\Moneda;

class Formato
{
    /** 1234.5 → "$ 1.234,50" / "US$ 1.234,50" */
    public static function dinero(float|string|null $monto, Moneda|string|null $moneda = Moneda::ARS): string
    {
        $moneda = $moneda instanceof Moneda ? $moneda : Moneda::tryFrom((string) $moneda) ?? Moneda::ARS;
        $simbolo = $moneda === Moneda::USD ? 'US$' : '$';

        return $simbolo.' '.number_format((float) $monto, 2, ',', '.');
    }
}
