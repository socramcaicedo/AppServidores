<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Culto;
use App\Models\Servidor;
use Illuminate\Http\Request;

class EstadisticaController extends Controller
{
    /**
     * Muestra el panel de estadísticas administrativas.
     * Implementa RF10 de la ERS.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // ============================================
        // SERVIDORES MÁS UTILIZADOS
        // ============================================
        $masUtilizados = Servidor::withCount('asignaciones')
            ->where('estado', 1)
            ->orderBy('asignaciones_count', 'desc')
            ->take(10)
            ->get();

        // ============================================
        // SERVIDORES MENOS UTILIZADOS
        // ============================================
        $menosUtilizados = Servidor::withCount('asignaciones')
            ->where('estado', 1)
            ->orderBy('asignaciones_count', 'asc')
            ->take(10)
            ->get();

        // ============================================
        // MOTIVOS DE CANCELACIONES
        // ============================================
        $totalCancelaciones = Asignacion::whereNotNull('motivo_reemplazo')->count();

        $cancelacionesPorMotivo = Asignacion::whereNotNull('motivo_reemplazo')
            ->selectRaw('motivo_reemplazo, COUNT(*) as total')
            ->groupBy('motivo_reemplazo')
            ->orderByDesc('total')
            ->get();

        // Convertir códigos a nombres legibles y calcular porcentaje
        $cancelacionesPorMotivo->transform(function ($item) use ($totalCancelaciones) {
            $item->motivo_nombre = Asignacion::motivosReemplazo()[$item->motivo_reemplazo] ?? $item->motivo_reemplazo;
            $item->porcentaje = $totalCancelaciones > 0 ? round(($item->total / $totalCancelaciones) * 100, 1) : 0;
            return $item;
        });

        // ============================================
        // SERVIDORES CON MÁS CANCELACIONES
        // ============================================
        $servidoresConMasCancelaciones = Servidor::whereHas('asignaciones', function ($query) {
            $query->whereNotNull('motivo_reemplazo');
        })
        ->withCount(['asignaciones as cancelaciones_count' => function ($query) {
            $query->whereNotNull('motivo_reemplazo');
        }])
        ->where('estado', 1)
        ->orderByDesc('cancelaciones_count')
        ->take(10)
        ->get();

        // ============================================
        // TOTALES GENERALES
        // ============================================
        $totales = [
            'total_servidores' => Servidor::where('estado', 1)->count(),
            'total_asignaciones' => Asignacion::count(),
            'total_cultos' => Culto::count(),
            'tasa_cancelacion' => $this->calcularTasaCancelacion(),
        ];

        return view('estadisticas.index', compact(
            'masUtilizados',
            'menosUtilizados',
            'cancelacionesPorMotivo',
            'servidoresConMasCancelaciones',
            'totales'
        ));
    }

    /**
     * Calcula la tasa de cancelación (porcentaje)
     */
    private function calcularTasaCancelacion(): float
    {
        $totalAsignaciones = Asignacion::count();
        $totalCancelaciones = Asignacion::whereNotNull('motivo_reemplazo')->count();

        if ($totalAsignaciones === 0) {
            return 0.0;
        }

        return round(($totalCancelaciones / $totalAsignaciones) * 100, 2);
    }
}
