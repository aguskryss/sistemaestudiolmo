<?php

use App\Http\Controllers\InicioController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', InicioController::class)->name('inicio');
    Route::view('/perfil', 'perfil.show')->name('perfil');

    // Las acciones de verificación en dos pasos piden reingresar la contraseña.
    // Esta ruta pasa por esa confirmación y vuelve al perfil.
    Route::get('/perfil/seguridad', fn () => redirect()->to(route('perfil').'#dos-pasos'))
        ->middleware('password.confirm')
        ->name('perfil.seguridad');
});
