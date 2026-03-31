<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::with('rol')->orderBy('created_at', 'desc')->get();
        $roles    = Rol::where('estado', 'activo')->orderBy('nombre_rol')->get();
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
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'apellido.required'  => 'El apellido es obligatorio.',
            'usuario.required'   => 'El nombre de usuario es obligatorio.',
            'usuario.unique'     => 'Ese nombre de usuario ya está en uso.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'genero.required'    => 'El género es obligatorio.',
            'edad.required'      => 'La edad es obligatoria.',
            'rol_id.required'    => 'Debes asignar un rol.',
            'rol_id.exists'      => 'El rol seleccionado no existe.',
        ]);

        Usuario::create([
            'nombre'   => ucfirst(trim($request->nombre)),
            'apellido' => ucfirst(trim($request->apellido)),
            'usuario'  => strtolower(trim($request->usuario)),
            'password' => Hash::make($request->password),
            'genero'   => $request->genero,
            'edad'     => $request->edad,
            'rol_id'   => $request->rol_id,
        ]);

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
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'apellido.required'  => 'El apellido es obligatorio.',
            'usuario.required'   => 'El nombre de usuario es obligatorio.',
            'usuario.unique'     => 'Ese nombre de usuario ya está en uso.',
            'password.min'       => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'genero.required'    => 'El género es obligatorio.',
            'edad.required'      => 'La edad es obligatoria.',
            'rol_id.required'    => 'Debes asignar un rol.',
        ]);

        $datos = [
            'nombre'   => ucfirst(trim($request->nombre)),
            'apellido' => ucfirst(trim($request->apellido)),
            'usuario'  => strtolower(trim($request->usuario)),
            'genero'   => $request->genero,
            'edad'     => $request->edad,
            'rol_id'   => $request->rol_id,
        ];

        // Solo actualizar password si se ingresó uno nuevo
        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleEstado(Usuario $usuario)
    {
        // Evitar que el secretario se desactive a sí mismo
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No puedes desactivarte a ti mismo.');
        }

        // Usar campo email como bandera de estado (activo = tiene email o null)
        // Usamos una columna virtual: si password empieza con 'INACTIVO:' está desactivado
        $estaInactivo = str_starts_with($usuario->password, 'INACTIVO:');

        if ($estaInactivo) {
            $usuario->update([
                'password' => substr($usuario->password, 9),
            ]);
            $msg = 'Usuario activado correctamente.';
        } else {
            $usuario->update([
                'password' => 'INACTIVO:' . $usuario->password,
            ]);
            $msg = 'Usuario desactivado correctamente.';
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', $msg);
    }

    public function destroy(Usuario $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}