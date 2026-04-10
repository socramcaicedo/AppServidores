<?php

namespace App\Http\Controllers;

use App\Models\Culto;
use App\Models\Servidor;
use App\Services\HistorialService;
use Illuminate\Http\Request;

class CultoController extends Controller
{
    public function index()
    {
        $cultos = Culto::with(['asignaciones.servidor', 'mensajeAutor'])
            ->orderBy('fecha', 'desc')
            ->get();

        return view('cultos.index', compact('cultos'));
    }

    public function store(Request $request)
    {
        $request->validate([
           'nombre_culto' => 'required|string|max:150',
        'caracter'     => 'required|in:' . implode(',', array_keys(Culto::caracteres())),
        'fecha'        => 'required|date',
        'descripcion'  => 'nullable|string|max:500',
        ], [
            'nombre_culto.required' => 'El nombre del culto es obligatorio.',
            'fecha.required'        => 'La fecha es obligatoria.',
            'fecha.date'            => 'La fecha no es válida.',
        ]);

        $culto = Culto::create([
            'nombre_culto' => ucfirst(trim($request->nombre_culto)),
            'fecha'        => $request->fecha,
            'descripcion'  => $request->descripcion,
        ]);

        HistorialService::registrar(
            accion:         'crear',
            modulo:         'cultos',
            descripcion:    'Creó el culto: ' . $culto->nombre_culto . ' para el ' . $culto->fecha->format('d/m/Y'),
            registro_id:    $culto->id,
            tabla_afectada: 'cultos'
        );

        return redirect()->route('cultos.index')
            ->with('success', 'Culto programado correctamente.');
    }

    public function update(Request $request, Culto $culto)
    {
        $request->validate([
            'nombre_culto' => ucfirst(trim($request->nombre_culto)),
        'caracter'     => $request->caracter,
        'fecha'        => $request->fecha,
        'descripcion'  => $request->descripcion,
        ]);

        $culto->update([
            'nombre_culto' => ucfirst(trim($request->nombre_culto)),
            'fecha'        => $request->fecha,
            'descripcion'  => $request->descripcion,
        ]);

        HistorialService::registrar(
            accion:         'editar',
            modulo:         'cultos',
            descripcion:    'Editó el culto: ' . $culto->nombre_culto,
            registro_id:    $culto->id,
            tabla_afectada: 'cultos'
        );

        return redirect()->route('cultos.index')
            ->with('success', 'Culto actualizado correctamente.');
    }

    public function destroy(Culto $culto)
    {
        if ($culto->asignaciones()->count() > 0) {
            return redirect()->route('cultos.index')
                ->with('error', 'No se puede eliminar un culto con asignaciones registradas.');
        }

        HistorialService::registrar(
            accion:         'eliminar',
            modulo:         'cultos',
            descripcion:    'Eliminó el culto: ' . $culto->nombre_culto,
            registro_id:    $culto->id,
            tabla_afectada: 'cultos'
        );

        $culto->delete();

        return redirect()->route('cultos.index')
            ->with('success', 'Culto eliminado correctamente.');
    }

    public function show(Culto $culto)
    {
        $culto->load(['asignaciones.servidor', 'mensajeAutor']);
        $servidores = Servidor::where('estado', 'activo')->orderBy('nombre_completo')->get();
        return view('cultos.show', compact('culto', 'servidores'));
    }

    public function mensaje(Request $request, Culto $culto)
    {
        // Solo pastor y secretario
        $request->validate([
            'mensaje' => 'required|string|max:1000',
        ], [
            'mensaje.required' => 'El mensaje no puede estar vacío.',
        ]);

        $culto->update([
            'mensaje'           => trim($request->mensaje),
            'mensaje_autor_id'  => auth()->id(),
        ]);

        HistorialService::registrar(
            accion:         'mensaje',
            modulo:         'cultos',
            descripcion:    'Dejó un mensaje en el culto: ' . $culto->nombre_culto,
            registro_id:    $culto->id,
            tabla_afectada: 'cultos'
        );

        return redirect()->route('cultos.show', $culto->id)
         
        ->with('success', 'Mensaje guardado correctamente.');
    }


    
}