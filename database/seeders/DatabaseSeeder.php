<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\ChecklistPlantillaItem;
use App\Models\Rubro;
use App\Models\User;
use App\Support\Colores;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $rubros = [
            'Demolición', 'Movimiento de suelos', 'Hormigón armado', 'Albañilería', 'Construcción en seco',
            'Instalación eléctrica', 'Instalación sanitaria', 'Instalación de gas', 'Climatización',
            'Herrería', 'Carpintería', 'Aberturas', 'Vidriería', 'Revestimientos', 'Pintura',
            'Impermeabilización', 'Techos', 'Pisos', 'Paisajismo', 'Limpieza de obra',
        ];
        $paleta = array_keys(Colores::PALETA);
        foreach ($rubros as $i => $nombre) {
            Rubro::firstOrCreate(['nombre' => $nombre], ['color' => Colores::RUBROS_INICIALES[$nombre] ?? $paleta[$i % count($paleta)]]);
        }

        $checklist = [
            'Contrato firmado con el cliente',
            'Permiso de obra aprobado',
            'Seguro de responsabilidad civil de la obra',
            'ART / seguros de todos los gremios al día',
            'Cartel de obra colocado',
            'Conexión provisoria de luz y agua',
            'Replanteo realizado',
            'Acta de inicio de obra firmada',
        ];
        foreach ($checklist as $i => $descripcion) {
            ChecklistPlantillaItem::firstOrCreate(['descripcion' => $descripcion], ['orden' => $i + 1]);
        }

        // Usuario administrador inicial. La contraseña se muestra una sola vez en consola.
        if (! User::where('rol', Rol::Admin)->exists()) {
            $password = Str::password(16, symbols: false); // sin símbolos: más fácil de tipear
            $admin = User::create([
                'name' => env('ADMIN_NAME', 'Administrador'),
                'email' => env('ADMIN_EMAIL', 'admin@estudio.local'),
                'password' => $password,
            ]);
            $admin->rol = Rol::Admin;
            $admin->save();

            $this->command?->warn("Admin creado: {$admin->email} / {$password}  (cambiala al entrar)");
        }
    }
}
