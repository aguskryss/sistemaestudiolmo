<?php

use App\Http\Controllers\AdjuntoController;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\EstudioController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\ObraController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RecordatorioController;
use App\Http\Controllers\SeguroController;
use App\Http\Controllers\TareaController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', InicioController::class)->name('inicio');
    Route::get('calendario', CalendarioController::class)->name('calendario');
    Route::view('/perfil', 'perfil.show')->name('perfil');

    // Las acciones de verificación en dos pasos piden reingresar la contraseña.
    // Esta ruta pasa por esa confirmación y vuelve al perfil.
    Route::get('/perfil/seguridad', fn () => redirect()->to(route('perfil').'#dos-pasos'))
        ->middleware('password.confirm')
        ->name('perfil.seguridad');

    // Obras y sus pestañas
    Route::resource('obras', ObraController::class);
    Route::prefix('obras/{obra}')->group(function () {
        Route::post('checklist', [ChecklistController::class, 'store'])->name('obras.checklist.store');

        Route::get('archivos', [ArchivoController::class, 'index'])->name('obras.archivos');
        Route::post('archivos', [ArchivoController::class, 'subir'])->name('obras.archivos.subir');
        Route::post('carpetas', [ArchivoController::class, 'crearCarpeta'])->name('obras.carpetas.store');

        Route::get('materiales', [MaterialController::class, 'index'])->name('obras.materiales');
        Route::post('materiales', [MaterialController::class, 'store'])->name('obras.materiales.store');

        Route::get('calendario', [TareaController::class, 'index'])->name('obras.gantt');
        Route::post('tareas', [TareaController::class, 'store'])->name('obras.tareas.store');

        Route::get('cotizaciones', [CotizacionController::class, 'obra'])->name('obras.cotizaciones');

        Route::get('permisos', [PermisoController::class, 'obra'])->name('obras.permisos');
        Route::post('permisos', [PermisoController::class, 'store'])->name('obras.permisos.store');

        Route::get('seguros', [SeguroController::class, 'obra'])->name('obras.seguros');
        Route::post('seguros', [SeguroController::class, 'vincular'])->name('obras.seguros.vincular');
        Route::delete('seguros/{seguro}', [SeguroController::class, 'desvincular'])->name('obras.seguros.desvincular');
    });

    Route::patch('checklist/{item}', [ChecklistController::class, 'alternar'])->name('checklist.alternar');
    Route::delete('checklist/{item}', [ChecklistController::class, 'destroy'])->name('checklist.destroy');

    Route::post('documentos/{documento}/version', [ArchivoController::class, 'nuevaVersion'])->name('documentos.version');
    Route::delete('documentos/{documento}', [ArchivoController::class, 'eliminar'])->name('documentos.destroy');
    Route::get('versiones/{version}/descargar', [ArchivoController::class, 'descargar'])->name('versiones.descargar');
    Route::delete('carpetas/{carpeta}', [ArchivoController::class, 'eliminarCarpeta'])->name('carpetas.destroy');

    Route::put('materiales/{item}', [MaterialController::class, 'update'])->name('materiales.update');
    Route::post('materiales/{item}/movimientos', [MaterialController::class, 'movimiento'])->name('materiales.movimiento');
    Route::delete('materiales/{item}', [MaterialController::class, 'destroy'])->name('materiales.destroy');

    Route::put('tareas/{tarea}', [TareaController::class, 'update'])->name('tareas.update');
    Route::patch('tareas/{tarea}/mover', [TareaController::class, 'mover'])->name('tareas.mover');
    Route::delete('tareas/{tarea}', [TareaController::class, 'destroy'])->name('tareas.destroy');

    Route::put('permisos/{permiso}', [PermisoController::class, 'update'])->name('permisos.update');
    Route::delete('permisos/{permiso}', [PermisoController::class, 'destroy'])->name('permisos.destroy');

    // Cotizaciones
    Route::resource('cotizaciones', CotizacionController::class)->parameters(['cotizaciones' => 'cotizacion']);
    Route::patch('cotizaciones/{cotizacion}/estado', [CotizacionController::class, 'estado'])->name('cotizaciones.estado');

    // Estudios contratantes, clientes y notas
    Route::resource('estudios', EstudioController::class);
    Route::resource('clientes', ClienteController::class);
    Route::post('clientes/{cliente}/notas', [NotaController::class, 'store'])->name('clientes.notas.store');
    Route::patch('notas/{nota}/fijar', [NotaController::class, 'fijar'])->name('notas.fijar');
    Route::delete('notas/{nota}', [NotaController::class, 'destroy'])->name('notas.destroy');

    // Contactos de rubro y seguros
    Route::resource('contactos', ContactoController::class);
    Route::post('rubros', [ContactoController::class, 'crearRubro'])->name('rubros.store');
    Route::post('contactos/{contacto}/seguros', [SeguroController::class, 'store'])->name('contactos.seguros.store');
    Route::put('seguros/{seguro}', [SeguroController::class, 'update'])->name('seguros.update');
    Route::delete('seguros/{seguro}', [SeguroController::class, 'destroy'])->name('seguros.destroy');

    // Adjuntos (permisos, seguros, cotizaciones)
    Route::post('adjuntos', [AdjuntoController::class, 'store'])->name('adjuntos.store');
    Route::get('adjuntos/{adjunto}', [AdjuntoController::class, 'descargar'])->name('adjuntos.descargar');
    Route::delete('adjuntos/{adjunto}', [AdjuntoController::class, 'destroy'])->name('adjuntos.destroy');

    // Recordatorios
    Route::get('recordatorios', [RecordatorioController::class, 'index'])->name('recordatorios.index');
    Route::post('recordatorios', [RecordatorioController::class, 'store'])->name('recordatorios.store');
    Route::patch('recordatorios/{recordatorio}/completar', [RecordatorioController::class, 'completar'])->name('recordatorios.completar');
    Route::delete('recordatorios/{recordatorio}', [RecordatorioController::class, 'destroy'])->name('recordatorios.destroy');

    // Administración
    Route::middleware('can:admin')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except(['show', 'destroy']);
        Route::patch('usuarios/{usuario}/activo', [UsuarioController::class, 'alternarActivo'])->name('usuarios.activo');
        Route::post('usuarios/{usuario}/restablecer', [UsuarioController::class, 'restablecer'])->name('usuarios.restablecer');
    });
});
