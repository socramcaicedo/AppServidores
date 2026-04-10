<?php

namespace App\Services;

use App\Models\HistorialAccion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class HistorialService
{
    public static function registrar(
        string $accion,
        string $modulo,
        string $descripcion = null,
        int    $registro_id = null,
        string $tabla_afectada = null
    ): void {
        HistorialAccion::create([
            'user_id'        => Auth::id(),
            'accion'         => $accion,
            'modulo'         => $modulo,
            'descripcion'    => $descripcion,
            'registro_id'    => $registro_id,
            'tabla_afectada' => $tabla_afectada,
            'ip_usuario'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'fecha_accion'   => now(),
        ]);
    }
}