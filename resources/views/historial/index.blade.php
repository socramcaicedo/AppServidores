@extends('layouts.app')
@section('titulo', 'Historial de Acciones')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Navegación</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Dashboard
    </a>
    @if(auth()->user()->tieneRol('secretario_general'))
    <a href="{{ route('admin.usuarios.index') }}">
        <span class="icono">&#128100;</span> Usuarios
    </a>
    <a href="{{ route('admin.roles.index') }}">
        <span class="icono">&#128274;</span> Roles
    </a>
    @endif
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Reportes</p>
    <a href="{{ route('historial.index') }}" class="activo">
        <span class="icono">&#128203;</span> Historial
    </a>
</div>
@endsection

@section('contenido')
<div class="page-header">
    <h1>Historial de Acciones</h1>
    <p>Registro de todas las acciones realizadas en el sistema</p>
</div>

{{-- Filtros --}}
<div class="filtros-card">
    <form method="GET" action="{{ route('historial.index') }}">
        <div class="filtros-grid">
            <div class="form-group">
                <label>Fecha inicio</label>
                <input type="date" name="fecha_inicio"
                       value="{{ request('fecha_inicio') }}"
                       max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Fecha fin</label>
                <input type="date" name="fecha_fin"
                       value="{{ request('fecha_fin') }}"
                       max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="filtros-acciones">
                <button type="submit" class="btn btn-primario">
                    &#128269; Filtrar
                </button>
                @if(request('fecha_inicio') || request('fecha_fin'))
                <a href="{{ route('historial.index') }}" class="btn btn-secundario">
                    &#10005; Limpiar
                </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Resumen --}}
<div class="stats-grid" style="grid-template-columns: repeat(3,1fr); margin-bottom:1.5rem;">
    <div class="stat-card azul">
        <span class="stat-label">Total acciones</span>
        <span class="stat-valor">{{ $acciones->total() }}</span>
        <span class="stat-sub">Registradas en el sistema</span>
    </div>
    <div class="stat-card amarillo">
        <span class="stat-label">Hoy</span>
        <span class="stat-valor">{{ \App\Models\HistorialAccion::whereDate('fecha_accion', today())->count() }}</span>
        <span class="stat-sub">Acciones del día</span>
    </div>
    <div class="stat-card verde">
        <span class="stat-label">Este mes</span>
        <span class="stat-valor">{{ \App\Models\HistorialAccion::whereMonth('fecha_accion', now()->month)->count() }}</span>
        <span class="stat-sub">{{ now()->locale('es')->isoFormat('MMMM YYYY') }}</span>
    </div>
</div>

{{-- Tabla --}}
<div class="tabla-wrapper">
    <div class="tabla-header">
        <h2>Registro de acciones</h2>
        <span style="font-size:13px; color:#999;">
            Mostrando {{ $acciones->firstItem() ?? 0 }}–{{ $acciones->lastItem() ?? 0 }}
            de {{ $acciones->total() }} registros
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha y hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Módulo</th>
                <th>Descripción</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($acciones as $accion)
            <tr>
                <td style="white-space:nowrap; color:#555;">
                    {{ $accion->fecha_accion?->isoFormat('D [de] MMM [de] YYYY') }}
                    <span style="color:#999; font-size:12px;">
                        {{ $accion->fecha_accion?->format('g:i A') }}
                    </span>
                </td>
                <td>
                    @if($accion->usuario)
                    <div style="display:flex; align-items:center; gap:7px;">
                        <div class="avatar-sm">
                            {{ strtoupper(substr($accion->usuario->nombre,0,1).substr($accion->usuario->apellido,0,1)) }}
                        </div>
                        <span>{{ $accion->usuario->nombre_completo }}</span>
                    </div>
                    @else
                        <span style="color:#999;">Sistema</span>
                    @endif
                </td>
                <td>
                    <span class="pill-accion {{ accionColor($accion->accion) }}">
                        {{ ucfirst($accion->accion) }}
                    </span>
                </td>
                <td>
                    <span style="font-size:12px; background:#E8F0FB; color:#1A4FA8;
                                 padding:3px 9px; border-radius:20px; font-weight:600;">
                        {{ ucfirst($accion->modulo) }}
                    </span>
                </td>
                <td style="font-size:13px; color:#555; max-width:220px;">
                    {{ $accion->descripcion ?? '—' }}
                </td>
                <td style="font-size:12px; color:#999; white-space:nowrap;">
                    {{ $accion->ip_usuario ?? '—' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#999; padding:3rem;">
                    No hay acciones registradas
                    @if(request('fecha_inicio') || request('fecha_fin'))
                        en el rango de fechas seleccionado.
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Paginación --}}
    @if($acciones->hasPages())
    <div class="paginacion">
        {{ $acciones->links('historial.paginacion') }}
    </div>
    @endif
</div>

<style>
    .filtros-card {
        background: #fff;
        border: 1px solid #D1DCF0;
        border-radius: 10px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .filtros-grid {
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .filtros-grid .form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 160px;
    }
    .filtros-grid label {
        font-size: 13px;
        font-weight: 600;
        color: #3a4255;
    }
    .filtros-grid input[type="date"] {
        padding: 8px 12px;
        border: 1px solid #D1DCF0;
        border-radius: 7px;
        font-size: 14px;
        color: #1a1a2e;
        outline: none;
    }
    .filtros-grid input[type="date"]:focus { border-color: #1A4FA8; }
    .filtros-acciones {
        display: flex;
        gap: 8px;
        align-items: center;
        padding-bottom: 1px;
    }
    .avatar-sm {
        width: 28px; height: 28px; border-radius: 50%;
        background: #E8F0FB; color: #1A4FA8;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px; font-weight: 700; flex-shrink: 0;
    }
    .pill-accion {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .accion-crear    { background: #edfaf3; color: #1A7A4A; }
    .accion-editar   { background: #E8F0FB; color: #1A4FA8; }
    .accion-eliminar { background: #fdf0ef; color: #C0392B; }
    .accion-login    { background: #FFF8DC; color: #8a6200; }
    .accion-default  { background: #F4F6FA; color: #555E6D; }
    .paginacion {
        padding: 1rem 1.25rem;
        border-top: 1px solid #D1DCF0;
        display: flex;
        justify-content: center;
    }
</style>
@endsection