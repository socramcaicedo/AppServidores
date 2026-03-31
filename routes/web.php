<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;

// ── Página inicial ─────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Autenticación ──────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Área privada ───────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Secretario General
    Route::middleware('rol:secretario_general')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Roles
            Route::get('roles',          [RolController::class, 'index'])->name('roles.index');
            Route::post('roles',         [RolController::class, 'store'])->name('roles.store');
            Route::put('roles/{rol}',    [RolController::class, 'update'])->name('roles.update');
            Route::delete('roles/{rol}', [RolController::class, 'destroy'])->name('roles.destroy');

            // Usuarios
            Route::get('usuarios',                    [UsuarioController::class, 'index'])->name('usuarios.index');
            Route::post('usuarios',                   [UsuarioController::class, 'store'])->name('usuarios.store');
            Route::put('usuarios/{usuario}',          [UsuarioController::class, 'update'])->name('usuarios.update');
            Route::patch('usuarios/{usuario}/estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.estado');
            Route::delete('usuarios/{usuario}',       [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        });

});