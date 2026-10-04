<?php

namespace App\Support;

/**
 * Paleta para identificar tareas en el Gantt. Tonos apagados, todos legibles con texto oscuro.
 * Cada color tiene el tono de la barra y uno más intenso para la parte de avance.
 * Las clases .color-{clave} están definidas en resources/css/app.css.
 */
class Colores
{
    public const PALETA = [
        'terracota' => ['Terracota', '#efd2c3', '#d69a7c'],
        'ocre' => ['Ocre', '#f1e2b8', '#d4b25c'],
        'oliva' => ['Oliva', '#dfe4c3', '#a8b36a'],
        'verde' => ['Verde', '#cfe3d4', '#80b391'],
        'petroleo' => ['Petróleo', '#c9e0e1', '#6fa7ab'],
        'azul' => ['Azul', '#d0dbed', '#7f9cc8'],
        'lavanda' => ['Lavanda', '#ded6ec', '#a491c6'],
        'rosa' => ['Rosa', '#efd3dc', '#cc8aa3'],
        'arena' => ['Arena', '#e9e0d3', '#bba68a'],
        'gris' => ['Gris', '#e4e4e1', '#a8a8a3'],
    ];

    /** Colores sugeridos para los rubros de base: los que suelen coincidir en el tiempo quedan distintos. */
    public const RUBROS_INICIALES = [
        'Demolición' => 'gris', 'Movimiento de suelos' => 'arena', 'Hormigón armado' => 'azul',
        'Albañilería' => 'terracota', 'Construcción en seco' => 'lavanda', 'Instalación eléctrica' => 'ocre',
        'Instalación sanitaria' => 'petroleo', 'Instalación de gas' => 'rosa', 'Climatización' => 'verde',
        'Herrería' => 'oliva', 'Carpintería' => 'arena', 'Aberturas' => 'azul', 'Vidriería' => 'petroleo',
        'Revestimientos' => 'lavanda', 'Pintura' => 'rosa', 'Impermeabilización' => 'verde', 'Techos' => 'terracota',
        'Pisos' => 'oliva', 'Paisajismo' => 'verde', 'Limpieza de obra' => 'gris',
    ];

    public static function opciones(): array
    {
        return collect(self::PALETA)->map(fn ($c) => $c[0])->all();
    }

    public static function valido(?string $clave): bool
    {
        return $clave !== null && array_key_exists($clave, self::PALETA);
    }

    /** [claro, intenso] */
    public static function tonos(?string $clave): array
    {
        $c = self::PALETA[$clave] ?? self::PALETA['gris'];

        return [$c[1], $c[2]];
    }
}
