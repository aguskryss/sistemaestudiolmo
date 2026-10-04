<?php

namespace App\Enums;

enum TipoMovimientoMaterial: string
{
    use Concerns\ConOpciones;

    case Pedido = 'pedido';
    case Entrega = 'entrega';
    case Ajuste = 'ajuste';

    public function label(): string
    {
        return match ($this) {
            self::Pedido => 'Pedido',
            self::Entrega => 'Entrega',
            self::Ajuste => 'Ajuste',
        };
    }
}
