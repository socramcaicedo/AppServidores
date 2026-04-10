<?php

namespace App\Http\Controllers;

use App\Models\HistorialAccion;
use Illuminate\Http\Request;

class HistorialAccionController extends Controller  
{
    public function index(Request $request)
    {
        $query = HistorialAccion::with('usuario')
            ->orderBy('fecha_accion', 'desc');

        // Filtro por fecha inicio
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_accion', '>=', $request->fecha_inicio);
        }

        // Filtro por fecha fin
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_accion', '<=', $request->fecha_fin);
        }

        $acciones = $query->paginate(20)->withQueryString();

        return view('historial.index', compact('acciones'));
    }
}