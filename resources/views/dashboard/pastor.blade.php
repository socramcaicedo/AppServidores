@extends('layouts.app')
@section('titulo', 'Panel — Pastor')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Supervisión</p>
    <a href="#" class="activo">
        <span class="icono">&#128101;</span> Servidores
    </a>
    <a href="#">
        <span class="icono">&#128203;</span> Historial de participación
    </a>
    <a href="#">
        <span class="icono">&#128202;</span> Estadísticas
    </a>

    <a href="{{ route('historial.index') }}"
   class="{{ request()->routeIs('historial*') ? 'activo' : '' }}">
    <span class="icono">&#128203;</span> Historial de participación
</a>
</div>
@endsection

@section('contenido')
<div class="page-header">
    <h1>Bienvenido, {{ $usuario->nombre }}</h1>
    <p>Panel Pastoral &mdash; {{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
</div>

<div class="stats-grid">
    <div class="stat-card azul">
        <span class="stat-label">Total servidores</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">En la congregación</span>
    </div>
    <div class="stat-card verde">
        <span class="stat-label">Activos</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">Sirviendo actualmente</span>
    </div>
    <div class="stat-card amarillo">
        <span class="stat-label">Cultos este mes</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">{{ now()->isoFormat('MMMM YYYY') }}</span>
    </div>
    <div class="stat-card rojo">
        <span class="stat-label">Reemplazos este mes</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">Registrados</span>
    </div>
</div>

<div class="tabla-wrapper">
    <div class="tabla-header">
        <h2>Servidores más activos este mes</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Servidor</th>
                <th>Participaciones</th>
                <th>Último servicio</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" style="text-align:center; color:#999; padding: 2rem;">
                    Sin datos de participación este mes.
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection