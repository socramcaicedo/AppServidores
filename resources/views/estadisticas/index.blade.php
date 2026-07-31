@extends('layouts.app')
@section('titulo', 'Estadísticas Administrativas')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Administración</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Dashboard
    </a>
    <a href="{{ route('servidores.index') }}">
        <span class="icono">&#128101;</span> Servidores
    </a>
    @if(auth()->user()->tieneRol('secretario_general'))
    <a href="{{ route('admin.roles.index') }}">
        <span class="icono">&#128274;</span> Roles
    </a>
    <a href="{{ route('admin.usuarios.index') }}">
        <span class="icono">&#128100;</span> Usuarios
    </a>
    @endif
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Cultos</p>
    <a href="{{ route('cultos.index') }}">
        <span class="icono">&#128197;</span> Gestionar cultos
    </a>
</div>
@if(auth()->user()->tieneRol('secretario_general'))
<div class="sidebar-section">
    <p class="sidebar-title">Reportes</p>
    <a href="{{ route('historial.index') }}">
        <span class="icono">&#128203;</span> Historial
    </a>
</div>
@endif
<div class="sidebar-section">
    <p class="sidebar-title">Estadísticas</p>
    <a href="{{ route('estadisticas.index') }}" class="activo">
        <span class="icono">&#128200;</span> Panel de estadísticas
    </a>
</div>
@endsection

@section('main-class')estadisticas-view@endsection

@section('contenido')
<div class="page-header" style="margin-bottom:1rem;">
    <div>
        <h1 class="hide-mobile">&#128200; Estadísticas Administrativas</h1>
        <h1 class="show-mobile">&#128200; Estadísticas</h1>
        <p class="hide-mobile">Análisis de participación y rotación de servidores</p>
    </div>
</div>

{{-- ============================================
    TARJETAS DE RESUMEN GENERAL
============================================ --}}
<div class="stats-grid-compact" style="grid-template-columns:repeat(4,1fr); margin-bottom:1rem;">
    <div class="stat-card azul stat-compact">
        <span class="stat-valor">{{ $totales['total_servidores'] }}</span>
        <span class="stat-label">Servidores Activos</span>
    </div>

    <div class="stat-card amarillo stat-compact">
        <span class="stat-valor">{{ $totales['total_asignaciones'] }}</span>
        <span class="stat-label">Total Asignaciones</span>
    </div>

    <div class="stat-card verde stat-compact">
        <span class="stat-valor">{{ $totales['total_cultos'] }}</span>
        <span class="stat-label">Cultos Programados</span>
    </div>

    <div class="stat-card {{ $totales['tasa_cancelacion'] <= 15 ? 'verde' : ($totales['tasa_cancelacion'] <= 30 ? 'amarillo' : 'rojo') }} stat-compact">
        <span class="stat-valor">{{ $totales['tasa_cancelacion'] }}%</span>
        <span class="stat-label">Tasa Cancelación</span>
    </div>
</div>

<div class="estadisticas-grid" style="display:grid; grid-template-columns: repeat(2, 1fr); gap:1rem;">

    {{-- ============================================
        SERVIDORES MÁS UTILIZADOS
    ============================================= --}}
    <div class="tabla-wrapper">
        <div class="tabla-header flex-between">
            <h2>&#128200; Servidores Más Utilizados</h2>
        </div>
        <table class="tabla-sin-scroll">
            <thead>
                <tr>
                    <th width="30" class="hide-mobile">#</th>
                    <th>Servidor</th>
                    <th width="70" style="text-align:center;">Total</th>
                    <th width="60" class="hide-mobile">Tend.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($masUtilizados as $index => $servidor)
                <tr>
                    <td class="hide-mobile" style="color:#999; font-size:12px;">{{ $index + 1 }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div class="avatar hide-mobile" style="width:28px; height:28px; font-size:10px;">
                                {{ strtoupper(substr($servidor->nombre_completo, 0, 2)) }}
                            </div>
                            <strong style="font-size:12px;">{{ $servidor->nombre_completo }}</strong>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <span class="pill pill-pendiente" style="font-size:10px; padding:3px 8px;">
                            {{ $servidor->asignaciones_count }}
                        </span>
                    </td>
                    <td class="hide-mobile" style="font-size:10px;">
                        @if($index === 0)
                        <span style="color:#C0392B;">&#9650;&#9650;&#9650;</span>
                        @elseif($index < 3)
                        <span style="color:#F5C518;">&#9650;&#9650;</span>
                        @else
                        <span style="color:#1A7A4A;">&#8659;</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#999; padding:1.5rem;">
                        No hay datos disponibles
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============================================
        SERVIDORES MENOS UTILIZADOS
    ============================================= --}}
    <div class="tabla-wrapper">
        <div class="tabla-header flex-between">
            <h2>&#128301; Servidores Menos Utilizados</h2>
        </div>
        <table class="tabla-sin-scroll">
            <thead>
                <tr>
                    <th width="30" class="hide-mobile">#</th>
                    <th>Servidor</th>
                    <th width="70" style="text-align:center;">Total</th>
                    <th width="70" class="hide-mobile">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menosUtilizados as $index => $servidor)
                <tr>
                    <td class="hide-mobile" style="color:#999; font-size:12px;">{{ $index + 1 }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div class="avatar hide-mobile" style="width:28px; height:28px; font-size:10px;">
                                {{ strtoupper(substr($servidor->nombre_completo, 0, 2)) }}
                            </div>
                            <strong style="font-size:12px;">{{ $servidor->nombre_completo }}</strong>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <span class="pill pill-pendiente" style="font-size:10px; padding:3px 8px;">
                            {{ $servidor->asignaciones_count }}
                        </span>
                    </td>
                    <td class="hide-mobile" style="font-size:10px;">
                        @if($servidor->asignaciones_count === 0)
                        <span style="color:#C0392B;">&#9888;</span>
                        @elseif($servidor->asignaciones_count < 3)
                        <span style="color:#F5C518;">&#8969;</span>
                        @else
                        <span style="color:#1A7A4A;">&#10003;</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#999; padding:1.5rem;">
                        No hay datos disponibles
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:1.5rem; margin-top:1.5rem;">

    {{-- ============================================
        MOTIVOS DE CANCELACIONES
    ============================================= --}}
    <div class="tabla-wrapper">
        <div class="tabla-header flex-between">
            <h2>&#9888; Motivos de Cancelaciones</h2>
        </div>
        <table class="tabla-sin-scroll">
            <thead>
                <tr>
                    <th>Motivo</th>
                    <th width="60" style="text-align:center;">Total</th>
                    <th width="65" style="text-align:center;" class="hide-mobile">%</th>
                    <th width="60" class="hide-mobile">Grav.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cancelacionesPorMotivo as $index => $cancelacion)
                <tr>
                    <td style="font-size:12px;">
                        <strong>{{ $cancelacion->motivo_nombre }}</strong>
                    </td>
                    <td style="text-align:center;">
                        <span class="pill pill-pendiente" style="font-size:10px; padding:3px 8px;">
                            {{ $cancelacion->total }}
                        </span>
                    </td>
                    <td style="text-align:center;" class="hide-mobile">
                        <span class="pill" style="background:#E8F0FB; color:#1A4FA8; font-size:10px; padding:3px 8px;">
                            {{ $cancelacion->porcentaje }}%
                        </span>
                    </td>
                    <td class="hide-mobile" style="font-size:10px;">
                        @if($index === 0)
                        <span style="color:#C0392B;">&#9650;&#9650;&#9650;</span>
                        @elseif($index < 2)
                        <span style="color:#F5C518;">&#9650;</span>
                        @else
                        <span style="color:#1A7A4A;">&#8659;</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#999; padding:1.5rem;">
                        No hay cancelaciones registradas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============================================
        SERVIDORES CON MÁS CANCELACIONES
    ============================================= --}}
    <div class="tabla-wrapper">
        <div class="tabla-header flex-between">
            <h2>&#128483; Servidores con Más Cancelaciones</h2>
        </div>
        <table class="tabla-sin-scroll">
            <thead>
                <tr>
                    <th width="30" class="hide-mobile">#</th>
                    <th>Servidor</th>
                    <th width="75" style="text-align:center;">Canc.</th>
                    <th width="60" class="hide-mobile">Nivel</th>
                </tr>
            </thead>
            <tbody>
                @forelse($servidoresConMasCancelaciones as $index => $servidor)
                <tr>
                    <td class="hide-mobile" style="color:#999; font-size:12px;">{{ $index + 1 }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div class="avatar hide-mobile" style="width:28px; height:28px; font-size:10px; background:#FADBD8; color:#C0392B;">
                                {{ strtoupper(substr($servidor->nombre_completo, 0, 2)) }}
                            </div>
                            <strong style="font-size:12px;">{{ $servidor->nombre_completo }}</strong>
                        </div>
                    </td>
                    <td style="text-align:center;">
                        <span class="pill pill-pendiente" style="font-size:10px; padding:3px 8px;">
                            {{ $servidor->cancelaciones_count }}
                        </span>
                    </td>
                    <td class="hide-mobile" style="font-size:10px;">
                        @if($servidor->cancelaciones_count >= 5)
                        <span style="color:#C0392B;">&#9888;</span>
                        @elseif($servidor->cancelaciones_count >= 3)
                        <span style="color:#F5C518;">&#8969;</span>
                        @else
                        <span style="color:#1A7A4A;">&#10003;</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#999; padding:1.5rem;">
                        No hay cancelaciones registradas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<style>
    .avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: #E8F0FB; color: #1A4FA8;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }

    /* ============================================
       ESTADÍSTICAS COMPACTAS - OCUPAR TODA LA PANTALLA
    ============================================ */
    .stats-grid-compact {
        display: grid;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .stat-compact {
        padding: 0.875rem 1rem !important;
        min-height: auto;
    }

    .stat-compact .stat-valor {
        font-size: 24px !important;
        line-height: 1;
        margin-bottom: 4px;
    }

    .stat-compact .stat-label {
        font-size: 11px !important;
        line-height: 1.2;
    }

    .stat-compact .stat-sub {
        display: none;
    }

    /* Grid de estadísticas optimizado */
    .estadisticas-grid {
        display: grid;
        gap: 1rem;
    }

    .estadisticas-grid .tabla-wrapper {
        margin: 0;
    }

    .estadisticas-grid .tabla-header {
        padding: 0.75rem 1rem;
    }

    .estadisticas-grid .tabla-header h2 {
        font-size: 14px;
    }

    /* Ajustes para pantallas pequeñas */
    @media (max-width: 768px) {
        .stats-grid-compact {
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 0.5rem;
        }

        .stat-compact {
            padding: 0.625rem 0.75rem !important;
        }

        .stat-compact .stat-valor {
            font-size: 20px !important;
        }

        .stat-compact .stat-label {
            font-size: 10px !important;
        }

        .estadisticas-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .estadisticas-grid .tabla-header {
            padding: 0.625rem 0.875rem;
        }

        .estadisticas-grid .tabla-header h2 {
            font-size: 13px;
        }
    }

    /* Ajustes para móviles muy pequeños */
    @media (max-width: 480px) {
        .stats-grid-compact {
            grid-template-columns: 1fr !important;
        }

        .page-header {
            margin-bottom: 0.75rem !important;
        }

        .page-header h1 {
            font-size: 18px;
        }
    }

    /* Ajustes para tablets */
    @media (min-width: 769px) and (max-width: 1024px) {
        .stats-grid-compact {
            gap: 0.625rem;
        }

        .estadisticas-grid {
            gap: 0.875rem;
        }
    }

    /* ============================================
       TABLAS SIN SCROLL HORIZONTAL
    ============================================ */
    .tabla-sin-scroll {
        width: 100%;
        table-layout: auto;
        border-collapse: collapse;
    }

    .tabla-sin-scroll th,
    .tabla-sin-scroll td {
        padding: 8px 10px;
        font-size: 12px;
        white-space: nowrap;
    }

    .tabla-sin-scroll th {
        background: var(--azul-claro);
        color: var(--azul-oscuro);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .tabla-sin-scroll td {
        border-bottom: 1px solid var(--borde);
        color: #2a2a3e;
    }

    .tabla-sin-scroll tr:last-child td {
        border-bottom: none;
    }

    .tabla-sin-scroll tr:hover td {
        background: var(--gris-claro);
    }

    /* Ajustes específicos para móvil */
    @media (max-width: 768px) {
        .tabla-sin-scroll th,
        .tabla-sin-scroll td {
            padding: 6px 8px;
            font-size: 11px;
        }

        .tabla-sin-scroll {
            font-size: 11px;
        }
    }

    /* Ajustes para móviles muy pequeños */
    @media (max-width: 480px) {
        .tabla-sin-scroll th,
        .tabla-sin-scroll td {
            padding: 5px 6px;
            font-size: 10px;
        }

        .tabla-sin-scroll .pill {
            font-size: 9px !important;
            padding: 2px 6px !important;
        }
    }
</style>

@endsection
