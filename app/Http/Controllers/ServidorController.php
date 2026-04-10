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
            'idgenero'        => 'nullable|exists:genero,id',
            'cargo'           => 'nullable|string|max:100',
        ], [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'telefono.required'        => 'El teléfono es obligatorio.',
        ]);

        $servidor = Servidor::create([
            'nombre_completo' => ucwords(strtolower(trim($request->nombre_completo))),
            'telefono'        => trim($request->telefono),
            'idgenero'        => $request->idgenero,
            'cargo'           => $request->cargo ? trim($request->cargo) : null,
            'estado'          => 'activo',
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
            'idgenero'        => 'nullable|exists:genero,id',
            'cargo'           => 'nullable|string|max:100',
        ]);

        $servidor->update([
            'nombre_completo' => ucwords(strtolower(trim($request->nombre_completo))),
            'telefono'        => trim($request->telefono),
            'idgenero'        => $request->idgenero,
            'cargo'           => $request->cargo ? trim($request->cargo) : null,
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
        $nuevoEstado = $servidor->estado === 'activo' ? 'inactivo' : 'activo';
        $servidor->update(['estado' => $nuevoEstado]);

        HistorialService::registrar(
            accion:         $nuevoEstado === 'activo' ? 'activar' : 'desactivar',
            modulo:         'servidores',
            descripcion:    ucfirst($nuevoEstado === 'activo' ? 'Activó' : 'Desactivó') . ' al servidor: ' . $servidor->nombre_completo,
            registro_id:    $servidor->id,
            tabla_afectada: 'servidores'
        );

        return redirect()->route('servidores.index')
            ->with('success', 'Servidor ' . $nuevoEstado . ' correctamente.');
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