<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Services\HistorialService;
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
        ]);

        $rol = Rol::create([
            'nombre_rol'  => strtolower(trim($request->nombre_rol)),
            'descripcion' => $request->descripcion,
            'estado'      => 'activo',
        ]);

        HistorialService::registrar(
            accion:         'crear',
            modulo:         'roles',
            descripcion:    'Creó el rol: ' . $rol->nombre_rol,
            registro_id:    $rol->id,
            tabla_afectada: 'roles'
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function update(Request $request, Rol $rol)
    {
        $request->validate([
            'nombre_rol'  => 'required|string|max:100|unique:roles,nombre_rol,' . $rol->id,
            'descripcion' => 'nullable|string|max:255',
            'estado'      => 'required|in:activo,inactivo',
        ]);

        $rolAnterior = $rol->nombre_rol;

        $rol->update([
            'nombre_rol'  => strtolower(trim($request->nombre_rol)),
            'descripcion' => $request->descripcion,
            'estado'      => $request->estado,
        ]);

        HistorialService::registrar(
            accion:         'editar',
            modulo:         'roles',
            descripcion:    'Editó el rol: ' . $rolAnterior . ' → ' . $rol->nombre_rol,
            registro_id:    $rol->id,
            tabla_afectada: 'roles'
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Rol $rol)
    {
        if ($rol->usuarios()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'No se puede eliminar un rol con usuarios asignados.');
        }

        HistorialService::registrar(
            accion:         'eliminar',
            modulo:         'roles',
            descripcion:    'Eliminó el rol: ' . $rol->nombre_rol,
            registro_id:    $rol->id,
            tabla_afectada: 'roles'
        );

        $rol->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}