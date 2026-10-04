<?php

namespace App\Enums;

enum EstadoMaterial: string
{
    case Necesito = 'necesito';
    case Pedido = 'pedido';
    case EntregadoParcial = 'entregado_parcial';
    case Entregado = 'entregado';

    public function label(): string
    {
        return match ($this) {
            self::Necesito => 'Necesito',
            self::Pedido => 'Pedido',
            self::EntregadoParcial => 'Entregado parcial',
            self::Entregado => 'Entregado',
        };
    }
}
