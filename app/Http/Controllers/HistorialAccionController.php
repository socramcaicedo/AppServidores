<?php

namespace App\Http\Controllers;

use App\Models\HistorialAccion;
use Illuminate\Http\Request;

class HistorialAccionController extends Controller
{
    /**
     * Mostrar listado
     */
    public function index()
    {
        $historial = HistorialAccion::latest()->paginate(10);
        return view('historial_acciones.index', compact('historial'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('historial_acciones.create');
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $request->validate([
            'accion' => 'required|string|max:100',
            'modulo' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'registro_id' => 'nullable|integer',
            'tabla_afectada' => 'nullable|string|max:100',
        ]);

        HistorialAccion::create([
            'user_id' => auth()->id(),
            'accion' => $request->accion,
            'modulo' => $request->modulo,
            'descripcion' => $request->descripcion,
            'registro_id' => $request->registro_id,
            'tabla_afectada' => $request->tabla_afectada,
            'ip_usuario' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'fecha_accion' => now(),
        ]);

        return redirect()->route('historial-acciones.index')
            ->with('success', 'Acción registrada correctamente');
    }

    /**
     * Mostrar un registro específico
     */
    public function show(HistorialAccion $historialAccion)
    {
        return view('historial_acciones.show', compact('historialAccion'));
    }

  

  
}