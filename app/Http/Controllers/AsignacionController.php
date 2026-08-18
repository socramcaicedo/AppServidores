<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Culto;
use App\Models\Servidor;
use App\Services\HistorialService;
use Illuminate\Http\Request;

class AsignacionController extends Controller
{
    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request, Culto $culto)
    {
        $request->validate([
            'servidor_id' => 'required|exists:servidores,id',
            'rol_servicio' => 'required|string|in:' . implode(',', array_values(Asignacion::rolesServicio())),
        ], [
            'servidor_id.required' => 'Debe seleccionar un servidor.',
            'servidor_id.exists' => 'El servidor seleccionado no es válido.',
            'rol_servicio.required' => 'El rol de servicio es obligatorio.',
            'rol_servicio.in' => 'El rol seleccionado no es válido.',
        ]);

        $servidor = Servidor::findOrFail($request->servidor_id);

        if ($servidor->estado != 1) {
            return redirect()->route('cultos.show', $culto->id)
                ->with('error', 'No se puede asignar un servidor inactivo.');
        }

        $existente = Asignacion::where('culto_id', $culto->id)
            ->where('servidor_id', $request->servidor_id)
            ->first();

        if ($existente) {
            return redirect()->route('cultos.show', $culto->id)
                ->with('error', 'Este servidor ya está asignado a este culto.');
        }

        Asignacion::create([
            'culto_id' => $culto->id,
            'servidor_id' => $request->servidor_id,
            'rol_servicio' => ucfirst(trim($request->rol_servicio)),
            'estado' => 'asignado',
            'confirmado' => false,
            'orden' => ($culto->asignaciones()->max('orden') ?? 0) + 1,
        ]);

        HistorialService::registrar(
            accion:         'asignar',
            modulo:         'cultos',
            descripcion:    'Asignó a ' . $servidor->nombre_completo . ' como ' . $request->rol_servicio . ' en: ' . $culto->nombre_culto,
            registro_id:    $culto->id,
            tabla_afectada: 'asignaciones'
        );

        return redirect()->route('cultos.show', $culto->id)
            ->with('success', 'Servidor asignado correctamente.');
    }

    /**
     * Toggle confirmado state
     */
    public function toggleConfirmado(Culto $culto, Asignacion $asignacion)
    {
        if ($asignacion->culto_id !== $culto->id) {
            abort(403, 'La asignación no pertenece a este culto.');
        }

        $nuevoEstado = !$asignacion->confirmado;
        $asignacion->update(['confirmado' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'confirmó' : 'desconfirmó';

        HistorialService::registrar(
            accion:         'confirmar_asignacion',
            modulo:         'cultos',
            descripcion:    ucfirst($accion) . ' a ' . $asignacion->servidor->nombre_completo . ' en: ' . $culto->nombre_culto,
            registro_id:    $culto->id,
            tabla_afectada: 'asignaciones'
        );

        return redirect()->route('cultos.show', $culto->id)
            ->with('success', 'Servidor ' . $accion . ' correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Culto $culto, Asignacion $asignacion)
    {
        if ($asignacion->culto_id !== $culto->id) {
            abort(403, 'La asignación no pertenece a este culto.');
        }

        $servidorNombre = $asignacion->servidor->nombre_completo;

        HistorialService::registrar(
            accion:         'eliminar_asignacion',
            modulo:         'cultos',
            descripcion:    'Eliminó la asignación de ' . $servidorNombre . ' del culto: ' . $culto->nombre_culto,
            registro_id:    $culto->id,
            tabla_afectada: 'asignaciones'
        );

        $asignacion->delete();

        return redirect()->route('cultos.show', $culto->id)
            ->with('success', 'Asignación eliminada correctamente.');
    }

    /**
     * Reemplazar un servidor asignado por otro.
     * Implementa RF7 de la ERS.
     */
    public function reemplazar(Request $request, Culto $culto, Asignacion $asignacion)
    {
        if ($asignacion->culto_id !== $culto->id) {
            abort(403, 'La asignación no pertenece a este culto.');
        }

        // PASO 1: Validar los datos recibidos
        $request->validate([
            'nuevo_servidor_id' => 'required|exists:servidores,id',
            'motivo_reemplazo' => 'required|string|in:' . implode(',', array_keys(Asignacion::motivosReemplazo())),
            'motivo_descripcion' => 'nullable|string|max:500',
        ], [
            'nuevo_servidor_id.required' => 'Debe seleccionar un nuevo servidor.',
            'nuevo_servidor_id.exists' => 'El servidor seleccionado no es válido.',
            'motivo_reemplazo.required' => 'Debe seleccionar un motivo de reemplazo.',
            'motivo_reemplazo.in' => 'El motivo seleccionado no es válido.',
        ]);

        // PASO 2: Buscar el nuevo servidor
        $nuevoServidor = Servidor::findOrFail($request->nuevo_servidor_id);

        // PASO 3: Verificar que el nuevo servidor esté activo
        if ($nuevoServidor->estado != 1) {
            return redirect()->route('cultos.show', $culto->id)
                ->with('error', 'No se puede asignar un servidor inactivo.');
        }

        // PASO 4: Verificar que el nuevo servidor no ya esté asignado a este culto
        $existente = Asignacion::where('culto_id', $culto->id)
            ->where('servidor_id', $request->nuevo_servidor_id)
            ->where('id', '!=', $asignacion->id)
            ->first();

        if ($existente) {
            return redirect()->route('cultos.show', $culto->id)
                ->with('error', 'Este servidor ya está asignado a este culto.');
        }

        // PASO 5: Obtener nombres para el mensaje antes de modificar
        $servidorAnterior = $asignacion->servidor->nombre_completo;
        $motivoTexto = Asignacion::motivosReemplazo()[$request->motivo_reemplazo];
        $rolServicio = $asignacion->rol_servicio;

        // PASO 6: Actualizar la asignación anterior como reemplazada (NO eliminar)
        $asignacion->update([
            'estado' => 'reemplazado',
            'motivo_reemplazo' => $request->motivo_reemplazo,
            'motivo_descripcion' => $request->motivo_descripcion,
        ]);

        // PASO 7: Crear la nueva asignación vinculada al reemplazo
        // (hereda el orden del reemplazado para conservar su puesto en el culto)
        Asignacion::create([
            'culto_id' => $culto->id,
            'servidor_id' => $request->nuevo_servidor_id,
            'rol_servicio' => $rolServicio,
            'estado' => 'asignado',
            'confirmado' => false,
            'reemplazado_por_id' => $asignacion->id,
            'orden' => $asignacion->orden,
        ]);

        // PASO 8: Registrar en el historial
        HistorialService::registrar(
            accion:         'reemplazar',
            modulo:         'cultos',
            descripcion:    "Reemplazó a {$servidorAnterior} por {$nuevoServidor->nombre_completo} en el rol '{$rolServicio}'. Motivo: {$motivoTexto}",
            registro_id:    $culto->id,
            tabla_afectada: 'asignaciones'
        );

        // PASO 9: Redireccionar con mensaje de éxito
        return redirect()->route('cultos.show', $culto->id)
            ->with('success', "Servidor reemplazado correctamente: {$servidorAnterior} → {$nuevoServidor->nombre_completo}");
    }
}
