<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Culto;
use App\Models\Servidor;
use App\Services\HistorialService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;

class CultoController extends Controller
{
    public function index()
    {
        $cultos = Culto::with(['asignaciones.servidor', 'mensajeAutor'])
            ->orderBy('fecha', 'desc')
            ->get();

        // Filtrar asignaciones reemplazadas para que las vistas solo vean las activas
        $cultos->each(function ($c) {
            $c->setRelation('asignaciones', $c->asignaciones->where('estado', 'asignado'));
        });

        // Cargar servidores disponibles para el formulario (SIEMPRE)
        $servidores = \App\Models\Servidor::where('estado', 1)
            ->with('genero')
            ->orderBy('nombre_completo')
            ->get();

        $generos = \App\Models\Genero::all();

        $usuario = auth()->user();
        $rol = strtolower($usuario->rol->nombre_rol ?? '');

        // Retornar vista específica según el rol
        return match($rol) {
            'pastor' => view('cultos.pastor', compact('cultos', 'servidores', 'generos', 'usuario')),
            default  => view('cultos.index', compact('cultos', 'servidores', 'generos')),
        };
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_culto' => 'required|string|max:150',
            'caracter'     => 'required|in:' . implode(',', array_keys(Culto::caracteres())),
            'fecha'        => 'required|date',
            'hora'         => 'nullable|string',
            'descripcion'  => 'nullable|string|max:500',
        ], [
            'nombre_culto.required' => 'El nombre del culto es obligatorio.',
            'fecha.required'        => 'La fecha es obligatoria.',
            'fecha.date'            => 'La fecha no es válida.',
        ]);

        // Construir fecha completa
        // El campo datetime-local envía "2026-06-26T22:39" (ya incluye hora)
        // Si fecha ya tiene hora (contiene 'T'), usarla directamente
        // Si es solo fecha "2026-06-26", combinar con el campo hora
        $fechaInput = $request->fecha;
        if (str_contains($fechaInput, 'T')) {
            // datetime-local ya trae fecha y hora
            $fechaCompleta = $fechaInput;
        } elseif ($request->hora) {
            // fecha sola + hora separada
            $fechaCompleta = $fechaInput . ' ' . $request->hora;
        } else {
            // solo fecha sin hora
            $fechaCompleta = $fechaInput;
        }

        $culto = Culto::create([
            'nombre_culto' => ucfirst(trim($request->nombre_culto)),
            'caracter'     => $request->caracter,
            'fecha'        => $fechaCompleta,
            'descripcion'  => $request->descripcion,
        ]);

        HistorialService::registrar(
            accion:         'crear',
            modulo:         'cultos',
            descripcion:    'Creó el culto: ' . $culto->nombre_culto . ' para el ' . $culto->fecha->isoFormat('D [de] MMMM [de] YYYY') . ' a las ' . $culto->fecha->format('g:i A'),
            registro_id:    $culto->id,
            tabla_afectada: 'cultos'
        );

        // Procesar servidores asignados desde el formulario de creación
        $servidoresInput = $request->input('servidores', []);
        $servidoresProcesados = 0;
        $servidoresDuplicados = [];
        $rolesValidos = array_values(Asignacion::rolesServicio());

        if (is_array($servidoresInput)) {
            $servidoresYaAsignados = [];

            foreach ($servidoresInput as $index => $servidorData) {
                $servidorId = $servidorData['servidor_id'] ?? null;
                $rolServicio = $servidorData['rol_servicio'] ?? null;

                if (!$servidorId || !$rolServicio) {
                    continue;
                }

                // Verificar que el rol sea valido
                if (!in_array($rolServicio, $rolesValidos)) {
                    continue;
                }

                // Verificar que el servidor exista y esté activo
                $servidor = Servidor::find($servidorId);
                if (!$servidor || $servidor->estado != 1) {
                    continue;
                }

                // Evitar duplicados dentro del mismo formulario
                if (in_array($servidorId, $servidoresYaAsignados)) {
                    $servidoresDuplicados[] = $servidor->nombre_completo;
                    continue;
                }
                $servidoresYaAsignados[] = $servidorId;

                Asignacion::create([
                    'culto_id'     => $culto->id,
                    'servidor_id'  => $servidorId,
                    'rol_servicio' => ucfirst(trim($rolServicio)),
                    'estado'       => 'asignado',
                    'confirmado'   => isset($servidorData['confirmado']),
                ]);

                HistorialService::registrar(
                    accion:         'asignar',
                    modulo:         'cultos',
                    descripcion:    'Asignó a ' . $servidor->nombre_completo . ' como ' . $rolServicio . ' en: ' . $culto->nombre_culto,
                    registro_id:    $culto->id,
                    tabla_afectada: 'asignaciones'
                );

                $servidoresProcesados++;
            }
        }

        $mensaje = 'Culto programado correctamente.';
        if ($servidoresProcesados > 0) {
            $mensaje .= " Se asignaron {$servidoresProcesados} servidor(es).";
        }
        if (!empty($servidoresDuplicados)) {
            $nombres = implode(', ', $servidoresDuplicados);
            $mensaje .= " Se ignoraron duplicados: {$nombres}.";
        }

        return redirect()->route('cultos.show', $culto->id)
            ->with('success', $mensaje);
    }

    public function update(Request $request, Culto $culto)
    {
        $request->validate([
            'nombre_culto' => 'required|string|max:150',
            'caracter'     => 'required|in:' . implode(',', array_keys(Culto::caracteres())),
            'fecha'        => 'required|date',
            'descripcion'  => 'nullable|string|max:500',
        ], [
            'nombre_culto.required' => 'El nombre del culto es obligatorio.',
            'caracter.required'     => 'El tipo de culto es obligatorio.',
            'caracter.in'           => 'El tipo seleccionado no es válido.',
            'fecha.required'        => 'La fecha es obligatoria.',
            'fecha.date'            => 'La fecha no es válida.',
        ]);

        $culto->update([
            'nombre_culto' => ucfirst(trim($request->nombre_culto)),
            'caracter'     => $request->caracter,
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
        $asignacionesActivas = $culto->asignaciones()->where('estado', 'asignado')->count();

        if ($asignacionesActivas > 0) {
            return redirect()->route('cultos.index')
                ->with('error', 'No se puede eliminar un culto con asignaciones activas.');
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

        // Filtrar asignaciones reemplazadas para que la vista solo vea las activas
        $culto->setRelation('asignaciones', $culto->asignaciones->where('estado', 'asignado'));
        $servidores = Servidor::where('estado', 1)
            ->with('genero')
            ->withCount('asignacionesActivas')
            ->orderBy('nombre_completo')
            ->get();
        $generos = \App\Models\Genero::all();

        $usuario = auth()->user();
        $rol = strtolower($usuario->rol->nombre_rol ?? '');

        // Retornar vista específica según el rol
        return match($rol) {
            'pastor' => view('cultos.pastor_show', compact('culto', 'servidores', 'generos', 'usuario')),
            default  => view('cultos.show', compact('culto', 'servidores', 'generos')),
        };
    }

    public function mensaje(Request $request, Culto $culto)
    {
        $request->validate([
            'mensaje' => 'nullable|string|max:1000',
        ]);

        // Si el mensaje está vacío, se elimina
        if (empty(trim($request->mensaje))) {
            $culto->update([
                'mensaje'          => null,
                'mensaje_autor_id' => null,
            ]);

            HistorialService::registrar(
                accion:         'eliminar_mensaje',
                modulo:         'cultos',
                descripcion:    'Eliminó el mensaje pastoral del culto: ' . $culto->nombre_culto,
                registro_id:    $culto->id,
                tabla_afectada: 'cultos'
            );

            return redirect()->route('cultos.show', $culto->id)
                ->with('success', 'Mensaje pastoral eliminado correctamente.');
        }

        $culto->update([
            'mensaje'          => trim($request->mensaje),
            'mensaje_autor_id' => auth()->id(),
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

    public function descargarPDF(Culto $culto)
    {
        // Cargar las relaciones necesarias
        $culto->load(['asignaciones.servidor', 'mensajeAutor']);

        // Agrupar asignaciones por rol para mejor organización
        $asignacionesPorRol = $culto->asignaciones->where('estado', 'asignado')
            ->sortBy('rol_servicio')
            ->groupBy('rol_servicio');

        // Generar el PDF
        $pdf = PDF::loadView('cultos.pdf', [
            'culto' => $culto,
            'asignacionesPorRol' => $asignacionesPorRol,
        ]);

        // Mostrar el PDF en el navegador (stream en lugar de download)
        return $pdf->stream('orden_culto_' . $culto->fecha->format('Y-m-d') . '_' . Str::slug($culto->nombre_culto) . '.pdf');
    }

    public function obtenerCultosDia($fecha)
    {
        try {
            // Log para debugging
            \Log::info('Buscando cultos para la fecha: ' . $fecha);

            // Obtener cultos de la fecha específica
            $cultos = Culto::with(['asignaciones.servidor', 'mensajeAutor'])
                ->whereDate('fecha', $fecha)
                ->orderBy('fecha')
                ->get();

            \Log::info('Cultos encontrados: ' . $cultos->count());

            // Formatear los datos para enviar al frontend
            $cultosFormateados = $cultos->map(function ($culto) {
                return [
                    'id' => $culto->id,
                    'nombre_culto' => $culto->nombre_culto,
                    'fecha' => $culto->fecha->format('Y-m-d'),
                    'hora' => $culto->fecha->format('g:i A'),
                    'hora_24' => $culto->fecha->format('H:i'),
                    'tipo' => $culto->caracter_nombre,
                    'descripcion' => $culto->descripcion,
                    'mensaje' => $culto->mensaje,
                    'mensaje_autor' => $culto->mensajeAutor?->nombre_completo,
                    'estado' => $culto->estado,
                    'asignaciones' => $culto->asignaciones->where('estado', 'asignado')->map(function ($asignacion) {
                        return [
                            'id' => $asignacion->id,
                            'servidor_nombre' => $asignacion->servidor?->nombre_completo ?? 'No asignado',
                            'rol' => $asignacion->rol_servicio,
                            'confirmado' => $asignacion->confirmado,
                        ];
                    })->values(),
                ];
            });

            return response()->json([
                'cultos' => $cultosFormateados,
                'total' => $cultosFormateados->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en obtenerCultosDia: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al consultar los cultos del día.',
            ], 500);
        }
    }


}