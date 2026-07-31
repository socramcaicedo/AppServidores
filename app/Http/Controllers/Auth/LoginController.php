<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validación de entrada
        try {
            $request->validate([
                'usuario'  => 'required|string',
                'password' => 'required|string',
            ]);
        } catch (Exception $e) {
            return back()
                ->withInput($request->only('usuario'))
                ->withErrors(['usuario' => 'Datos de entrada inválidos.']);
        }

        // Intento de autenticación blindado (BD caída, sesión corrupta, etc.)
        try {
            $credenciales = [
                'usuario'  => $request->usuario,
                'password' => $request->password,
            ];

            if (Auth::attempt($credenciales, $request->boolean('remember'))) {
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard'));
            }

            return back()
                ->withInput($request->only('usuario'))
                ->withErrors(['usuario' => 'Usuario o contraseña incorrectos.']);
        } catch (Exception $e) {
            // Registrar el error real para el administrador
            Log::error('Error inesperado en login: ' . $e->getMessage(), [
                'usuario_intentado' => $request->usuario,
                'trace'             => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput($request->only('usuario'))
                ->withErrors(['usuario' => 'No se pudo iniciar sesión en este momento. Intenta nuevamente.']);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Cerrar sesión del usuario
            Auth::guard('web')->logout();

            // Invalidar y destruir completamente la sesión
            $request->session()->invalidate();

            // Regenerar el token CSRF para prevenir ataques
            $request->session()->regenerateToken();

            // Limpiar cualquier cookie adicional
            foreach ($request->cookies->keys() as $cookie) {
                if (str_starts_with($cookie, 'remember_') || str_starts_with($cookie, 'session')) {
                    \Cookie::queue(\Cookie::forget($cookie));
                }
            }

            return redirect()->route('login')
                ->with('success', 'Has cerrado sesión correctamente.');
        } catch (Exception $e) {
            Log::warning('Error al cerrar sesión: ' . $e->getMessage());

            // Aunque falle algo, forzamos redirección al login
            return redirect()->route('login')
                ->with('success', 'Has cerrado sesión.');
        }
    }
}
