<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $usuario = $request->user();

        if (!$usuario || !$usuario->rol) {
            return redirect()->route('login')
                ->with('error', 'No tienes sesión activa.');
        }

        $rolActual = strtolower($usuario->rol->nombre_rol);

        foreach ($roles as $rol) {
            if ($rolActual === strtolower($rol)) {
                return $next($request);
            }
        }

        return redirect()->route('dashboard')
            ->with('error', 'No tienes permiso para acceder a esa sección.');
    }
}