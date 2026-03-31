<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount('usuarios')->orderBy('created_at')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_rol'  => 'required|string|max:100|unique:roles,nombre_rol',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre_rol.required' => 'El nombre del rol es obligatorio.',
            'nombre_rol.unique'   => 'Ya existe un rol con ese nombre.',
            'nombre_rol.max'      => 'El nombre no puede superar 100 caracteres.',
        ]);

        Rol::create([
            'nombre_rol'  => strtolower(trim($request->nombre_rol)),
            'descripcion' => $request->descripcion,
            'estado'      => 'activo',
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function update(Request $request, Rol $rol)
    {
        $request->validate([
            'nombre_rol'  => 'required|string|max:100|unique:roles,nombre_rol,' . $rol->id,
            'descripcion' => 'nullable|string|max:255',
            'estado'      => 'required|in:activo,inactivo',
        ], [
            'nombre_rol.required' => 'El nombre del rol es obligatorio.',
            'nombre_rol.unique'   => 'Ya existe un rol con ese nombre.',
        ]);

        $rol->update([
            'nombre_rol'  => strtolower(trim($request->nombre_rol)),
            'descripcion' => $request->descripcion,
            'estado'      => $request->estado,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Rol $rol)
    {
        if ($rol->usuarios()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'No se puede eliminar un rol que tiene usuarios asignados.');
        }

        $rol->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}