<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarPermiso
{
    public function handle(Request $request, Closure $next, string $permiso): mixed
    {
        $usuario = $request->user();

        if (!$usuario || !$usuario->tienePermiso($permiso)) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes el permiso requerido.');
        }

        return $next($request);
    }
}