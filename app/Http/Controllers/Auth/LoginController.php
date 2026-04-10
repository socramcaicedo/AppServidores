<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
{
    $request->validate([
        'usuario'  => 'required|string',
        'password' => 'required|string',
    ]);

    $credenciales = [
        'usuario'  => $request->usuario,
        'password' => $request->password,
    ];

    if (Auth::attempt($credenciales, $request->boolean('remember'))) {
        $request->session()->regenerate();

        // Registrar login en historial
        \App\Services\HistorialService::registrar(
            accion:      'login',
            modulo:      'autenticacion',
            descripcion: 'Inició sesión en el sistema',
        );

        return redirect()->intended(route('dashboard'));
    }

    return back()
        ->withInput($request->only('usuario'))
        ->withErrors(['usuario' => 'Usuario o contraseña incorrectos.']);
}

public function logout(Request $request)
{
    // Registrar logout antes de cerrar sesión
    \App\Services\HistorialService::registrar(
        accion:      'logout',
        modulo:      'autenticacion',
        descripcion: 'Cerró sesión en el sistema',
    );

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
}}
