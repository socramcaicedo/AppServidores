<?php

namespace App\Http\Controllers;

use App\Models\Culto;
use App\Models\MensajePastoral;
use App\Services\HistorialService;
use Illuminate\Http\Request;

class PastorCalendarioController extends Controller
{
    /**
     * Muestra el calendario pastoral del mes actual
     */
    public function index(Request $request)
    {
        $mes = $request->get('mes', now()->month);
        $anio = $request->get('anio', now()->year);

        // Obtener cultos del mes seleccionado
        $cultos = Culto::with(['asignaciones.servidor', 'mensajesPastorales.usuario'])
            ->whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->orderBy('fecha')
            ->get();

        // Filtrar asignaciones reemplazadas
        $cultos->each(function ($c) {
            $c->setRelation('asignaciones', $c->asignaciones->where('estado', 'asignado'));
        });

        return view('pastor_calendario.index', compact('cultos', 'mes', 'anio'));
    }

    /**
     * Obtiene los cultos de un día específico (AJAX)
     */
    public function obtenerCultosDia($fecha)
    {
        try {
            $cultos = Culto::with(['asignaciones.servidor', 'mensajesPastorales' => function($query) {
                    $query->orderBy('fecha_publicacion', 'desc')->limit(5);
                }])
                ->whereDate('fecha', $fecha)
                ->orderBy('fecha')
                ->get();

            $cultosFormateados = $cultos->map(function ($culto) {
                return [
                    'id' => $culto->id,
                    'nombre_culto' => $culto->nombre_culto,
                    'fecha' => $culto->fecha->format('Y-m-d'),
                    'hora' => $culto->fecha->format('g:i A'),
                    'tipo' => $culto->caracter_nombre,
                    'descripcion' => $culto->descripcion,
                    'estado' => $culto->estado,
                    'total_asignaciones' => $culto->asignaciones->where('estado', 'asignado')->count(),
                    'total_mensajes' => $culto->mensajesPastorales->count(),
                    'ultimo_mensaje' => $culto->mensajesPastorales->first()?->mensaje,
                ];
            });

            return response()->json([
                'cultos' => $cultosFormateados,
                'total' => $cultosFormateados->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al consultar cultos del día: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al consultar los cultos del día. Intente de nuevo.',
            ], 500);
        }
    }

    /**
     * Muestra los detalles de un culto con sistema de mensajes pastorales
     */
    public function show(Culto $culto)
    {
        $culto->load(['asignaciones.servidor', 'mensajesPastorales.usuario']);

        // Filtrar asignaciones reemplazadas
        $culto->setRelation('asignaciones', $culto->asignaciones->where('estado', 'asignado'));

        return view('pastor_calendario.show', compact('culto'));
    }

    /**
     * Guarda un nuevo mensaje pastoral
     */
    public function guardarMensaje(Request $request, Culto $culto)
    {
        $request->validate([
            'mensaje' => 'required|string|max:2000',
        ], [
            'mensaje.required' => 'El mensaje no puede estar vacío.',
            'mensaje.max' => 'El mensaje no puede exceder los 2000 caracteres.',
        ]);

        $mensajePastoral = MensajePastoral::create([
            'culto_id' => $culto->id,
            'usuario_id' => auth()->id(),
            'mensaje' => trim($request->mensaje),
        ]);

        HistorialService::registrar(
            accion:         'mensaje_pastoral',
            modulo:         'calendario_pastoral',
            descripcion:    'Dejó un mensaje pastoral en: ' . $culto->nombre_culto,
            registro_id:    $culto->id,
            tabla_afectada: 'mensajes_pastorales'
        );

        return redirect()->route('pastor.calendario.show', $culto->id)
            ->with('success', 'Mensaje pastoral guardado correctamente.');
    }
}
