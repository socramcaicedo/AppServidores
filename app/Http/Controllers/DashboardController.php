<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();
        $rol = strtolower($usuario->rol->nombre_rol ?? '');

        return match($rol) {
            'secretario_general' => view('dashboard.secretario', compact('usuario')),
            'lider_comite'       => view('dashboard.lider', compact('usuario')),
            'pastor'             => view('dashboard.pastor', compact('usuario')),
            default              => abort(403, 'No tienes un rol asignado.'),
        };
    }
}