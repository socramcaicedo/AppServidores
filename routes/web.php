<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\HistorialAccionController;
use App\Http\Controllers\ServidorController;
use App\Http\Controllers\CultoController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\PastorCalendarioController;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1'); // Máximo 5 intentos por minuto
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'prevent.back'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Secretario General
    Route::middleware(['rol:secretario_general', 'prevent.back'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('roles',          [RolController::class, 'index'])->name('roles.index');
            Route::post('roles',         [RolController::class, 'store'])->name('roles.store');
            Route::put('roles/{rol}',    [RolController::class, 'update'])->name('roles.update');
            Route::delete('roles/{rol}', [RolController::class, 'destroy'])->name('roles.destroy');

            Route::get('usuarios',                    [UsuarioController::class, 'index'])->name('usuarios.index');
            Route::post('usuarios',                   [UsuarioController::class, 'store'])->name('usuarios.store');
            Route::put('usuarios/{usuario}',          [UsuarioController::class, 'update'])->name('usuarios.update');
            Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.estado');
            Route::delete('usuarios/{usuario}',       [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        });

    // Historial — Secretario y Pastor
    Route::middleware(['rol:secretario_general,pastor', 'prevent.back'])
        ->name('historial.')
        ->group(function () {
            Route::get('historial', [HistorialAccionController::class, 'index'])->name('index');
        });
// Servidores — Secretario y Líder
Route::middleware(['rol:secretario_general,lider_comite', 'prevent.back'])
    ->name('servidores.')
    ->group(function () {
        Route::get('servidores',                       [ServidorController::class, 'index'])->name('index');
        Route::post('servidores',                      [ServidorController::class, 'store'])->name('store');
        Route::put('servidores/{servidor}',            [ServidorController::class, 'update'])->name('update');
        Route::patch('servidores/{servidor}/estado',   [ServidorController::class, 'toggleEstado'])->name('estado');
    });

// Eliminar servidor — Solo Secretario General
Route::middleware(['rol:secretario_general', 'prevent.back'])
    ->delete('servidores/{servidor}', [ServidorController::class, 'destroy'])
    ->name('servidores.destroy');


    // Cultos — Secretario, Pastor y Líder
Route::middleware(['rol:secretario_general,pastor,lider_comite', 'prevent.back'])
    ->name('cultos.')
    ->group(function () {
        Route::get('cultos',                  [CultoController::class, 'index'])->name('index');
        Route::post('cultos',                 [CultoController::class, 'store'])->name('store');
        Route::put('cultos/{culto}',          [CultoController::class, 'update'])->name('update');
        Route::delete('cultos/{culto}',       [CultoController::class, 'destroy'])->name('destroy');
        Route::get('cultos/{culto}',          [CultoController::class, 'show'])->name('show');
        Route::get('cultos/{culto}/pdf',      [CultoController::class, 'descargarPDF'])->name('pdf');
    });

// Asignaciones de servidores a cultos — Solo Secretario y Líder
Route::middleware(['rol:secretario_general,lider_comite', 'prevent.back'])
    ->name('cultos.')
    ->group(function () {
        Route::post('cultos/{culto}/asignaciones',                          [AsignacionController::class, 'store'])->name('asignaciones.store');
        Route::post('cultos/{culto}/asignaciones/{asignacion}/reemplazar', [AsignacionController::class, 'reemplazar'])->name('asignaciones.reemplazar');
        Route::patch('cultos/{culto}/asignaciones/{asignacion}/mover', [AsignacionController::class, 'mover'])->name('asignaciones.mover');
        Route::patch('cultos/{culto}/asignaciones/{asignacion}/confirmar',  [AsignacionController::class, 'toggleConfirmado'])->name('asignaciones.confirmar');
        Route::delete('cultos/{culto}/asignaciones/{asignacion}',           [AsignacionController::class, 'destroy'])->name('asignaciones.destroy');
    });

// Ruta AJAX para obtener cultos de un día específico (todos los roles que gestionan cultos)
Route::middleware(['auth', 'rol:secretario_general,pastor,lider_comite'])
    ->get('cultos/dia/{fecha}', [CultoController::class, 'obtenerCultosDia'])
    ->name('cultos.dia');

// Mensaje en culto — Secretario y Pastor
Route::middleware(['rol:secretario_general,pastor', 'prevent.back'])
    ->group(function () {
        Route::post('cultos/{culto}/mensaje', [CultoController::class, 'mensaje'])->name('cultos.mensaje');
    });

// Estadísticas — Todos los roles
Route::middleware(['rol:secretario_general,pastor,lider_comite', 'prevent.back'])
    ->name('estadisticas.')
    ->group(function () {
        Route::get('estadisticas', [EstadisticaController::class, 'index'])->name('index');
    });


// Calendario Pastoral — Solo Pastor (Rutas completamente independientes)
Route::middleware(['rol:pastor', 'prevent.back'])
    ->name('pastor.calendario.')
    ->group(function () {
        Route::get('pastor/calendario', [PastorCalendarioController::class, 'index'])->name('index');
        Route::get('pastor/calendario/{culto}', [PastorCalendarioController::class, 'show'])->name('show');
        Route::post('pastor/calendario/{culto}/mensaje', [PastorCalendarioController::class, 'guardarMensaje'])->name('mensaje');
        Route::get('pastor/calendario/dia/{fecha}', [PastorCalendarioController::class, 'obtenerCultosDia'])->name('dia');
    });


});