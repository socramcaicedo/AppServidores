<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Services\HistorialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::with('rol')->orderBy('created_at', 'desc')->get();
        $roles    = Rol::where('estado', true)->orderBy('nombre_rol')->get();
        return view('admin.usuarios.index', compact('usuarios', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'usuario'  => 'required|string|max:60|unique:usuarios,usuario',
            'password' => 'required|string|min:6|confirmed',
            'genero'   => 'required|in:masculino,femenino,otro',
            'edad'     => 'required|integer|min:1|max:120',
            'rol_id'   => 'required|exists:roles,id',
        ]);

        $usuario = Usuario::create([
            'nombre'   => ucfirst(trim($request->nombre)),
            'apellido' => ucfirst(trim($request->apellido)),
            'usuario'  => strtolower(trim($request->usuario)),
            'password' => Hash::make($request->password),
            'genero'   => $request->genero,
            'edad'     => $request->edad,
            'rol_id'   => $request->rol_id,
            'estado'   => 1,
        ]);

        HistorialService::registrar(
            accion:         'crear',
            modulo:         'usuarios',
            descripcion:    'Creó el usuario: ' . $usuario->nombre_completo . ' con rol ' . ($usuario->rol->nombre_rol ?? ''),
            registro_id:    $usuario->id,
            tabla_afectada: 'usuarios'
        );

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, Usuario $usuario)
    {
        $request->validate([
            'nombre'   => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'usuario'  => 'required|string|max:60|unique:usuarios,usuario,' . $usuario->id,
            'password' => 'nullable|string|min:6|confirmed',
            'genero'   => 'required|in:masculino,femenino,otro',
            'edad'     => 'required|integer|min:1|max:120',
            'rol_id'   => 'required|exists:roles,id',
        ]);

        $datos = [
            'nombre'   => ucfirst(trim($request->nombre)),
            'apellido' => ucfirst(trim($request->apellido)),
            'usuario'  => strtolower(trim($request->usuario)),
            'genero'   => $request->genero,
            'edad'     => $request->edad,
            'rol_id'   => $request->rol_id,
        ];

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        HistorialService::registrar(
            accion:         'editar',
            modulo:         'usuarios',
            descripcion:    'Editó el usuario: ' . $usuario->nombre_completo,
            registro_id:    $usuario->id,
            tabla_afectada: 'usuarios'
        );

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleEstado(Usuario $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No puedes desactivarte a ti mismo.');
        }

        $nuevoEstado = $usuario->estado == 1 ? 0 : 1;
        $usuario->update(['estado' => $nuevoEstado]);

        HistorialService::registrar(
            accion:         $nuevoEstado == 1 ? 'activar' : 'desactivar',
            modulo:         'usuarios',
            descripcion:    ucfirst($nuevoEstado == 1 ? 'Activó' : 'Desactivó') . ' al usuario: ' . $usuario->nombre_completo,
            registro_id:    $usuario->id,
            tabla_afectada: 'usuarios'
        );

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario ' . ($nuevoEstado == 1 ? 'activado' : 'desactivado') . ' correctamente.');
    }

    public function destroy(Usuario $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        HistorialService::registrar(
            accion:         'eliminar',
            modulo:         'usuarios',
            descripcion:    'Eliminó al usuario: ' . $usuario->nombre_completo,
            registro_id:    $usuario->id,
            tabla_afectada: 'usuarios'
        );

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}