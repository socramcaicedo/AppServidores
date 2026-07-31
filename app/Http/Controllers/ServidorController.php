<?php

namespace App\Http\Controllers;

use App\Models\Servidor;
use App\Models\Genero;
use App\Services\HistorialService;
use Illuminate\Http\Request;

class ServidorController extends Controller
{
    public function index()
    {
        $servidores = Servidor::with(['genero', 'ultimaParticipacion.culto'])
            ->where('estado', 1)
            ->orderBy('nombre_completo')
            ->get();

        $generos = Genero::orderBy('denominacion')->get();

        return view('servidores.index', compact('servidores', 'generos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'telefono'        => 'required|string|max:20',
            'idgenero'        => 'nullable|exists:genero,idgenero',
            'cargo'           => 'nullable|string|max:100',
            'fecha_nacimiento'=> 'required|date|before_or_equal:today',
        ], [
            'nombre_completo.required'  => 'El nombre completo es obligatorio.',
            'telefono.required'         => 'El teléfono es obligatorio.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date'     => 'La fecha de nacimiento debe ser una fecha válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        ]);

        $servidor = Servidor::create([
            'nombre_completo' => ucwords(strtolower(trim($request->nombre_completo))),
            'telefono'        => trim($request->telefono),
            'idgenero'        => $request->idgenero,
            'cargo'           => $request->cargo ? trim($request->cargo) : null,
            'fecha_nacimiento'=> $request->fecha_nacimiento,
            'estado'          => 1,
        ]);

        HistorialService::registrar(
            accion:         'crear',
            modulo:         'servidores',
            descripcion:    'Registró al servidor: ' . $servidor->nombre_completo,
            registro_id:    $servidor->id,
            tabla_afectada: 'servidores'
        );

        return redirect()->route('servidores.index')
            ->with('success', 'Servidor registrado correctamente.');
    }

    public function update(Request $request, Servidor $servidor)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:150',
            'telefono'        => 'required|string|max:20',
            'idgenero'        => 'nullable|exists:genero,idgenero',
            'cargo'           => 'nullable|string|max:100',
            'fecha_nacimiento'=> 'required|date|before_or_equal:today',
        ], [
            'nombre_completo.required'  => 'El nombre completo es obligatorio.',
            'telefono.required'         => 'El teléfono es obligatorio.',
            'idgenero.exists'           => 'El género seleccionado no es válido.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date'     => 'La fecha de nacimiento debe ser una fecha válida.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        ]);

        $servidor->update([
            'nombre_completo' => ucwords(strtolower(trim($request->nombre_completo))),
            'telefono'        => trim($request->telefono),
            'idgenero'        => $request->idgenero,
            'cargo'           => $request->cargo ? trim($request->cargo) : null,
            'fecha_nacimiento'=> $request->fecha_nacimiento,
        ]);

        HistorialService::registrar(
            accion:         'editar',
            modulo:         'servidores',
            descripcion:    'Editó al servidor: ' . $servidor->nombre_completo,
            registro_id:    $servidor->id,
            tabla_afectada: 'servidores'
        );

        return redirect()->route('servidores.index')
            ->with('success', 'Servidor actualizado correctamente.');
    }

    public function toggleEstado(Servidor $servidor)
    {
        $nuevoEstado = $servidor->estado === 1 ? 0 : 1;
        $servidor->update(['estado' => $nuevoEstado]);

        HistorialService::registrar(
            accion:         $nuevoEstado === 1 ? 'activar' : 'desactivar',
            modulo:         'servidores',
            descripcion:    ucfirst($nuevoEstado === 1 ? 'Activó' : 'Desactivó') . ' al servidor: ' . $servidor->nombre_completo,
            registro_id:    $servidor->id,
            tabla_afectada: 'servidores'
        );

        $textoEstado = $nuevoEstado === 1 ? 'activo' : 'inactivo';

        return redirect()->route('servidores.index')
            ->with('success', 'Servidor ' . $textoEstado . ' correctamente.');
    }

    public function destroy(Servidor $servidor)
    {
        if ($servidor->asignaciones()->count() > 0) {
            return redirect()->route('servidores.index')
                ->with('error', 'No se puede eliminar un servidor con asignaciones registradas.');
        }

        HistorialService::registrar(
            accion:         'eliminar',
            modulo:         'servidores',
            descripcion:    'Eliminó al servidor: ' . $servidor->nombre_completo,
            registro_id:    $servidor->id,
            tabla_afectada: 'servidores'
        );

        $servidor->delete();

        return redirect()->route('servidores.index')
            ->with('success', 'Servidor eliminado correctamente.');
    }
}