@extends('layouts.app')
@section('titulo', 'Panel — Secretario General')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Administración</p>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'activo' : '' }}">
        <span class="icono">&#127968;</span> Dashboard
    </a>
    <a href="#" class="">
        <span class="icono">&#128101;</span> Servidores
    </a>
    <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles*') ? 'activo' : '' }}">
        <span class="icono">&#128274;</span> Roles
    </a>
    <a href="{{ route('admin.usuarios.index') }}" class="{{ request()->routeIs('admin.usuarios*') ? 'activo' : '' }}">
        <span class="icono">&#128100;</span> Usuarios
    </a>
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Cultos</p>
    <a href="#"><span class="icono">&#128197;</span> Gestionar cultos</a>
    <a href="#"><span class="icono">&#43;</span> Nuevo orden</a>
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Reportes</p>
    <a href="#"><span class="icono">&#128203;</span> Historial</a>
    <a href="#"><span class="icono">&#128202;</span> Estadísticas</a>
</div>
@endsection

@section('contenido')
<div class="page-header">
    <h1>Bienvenido, {{ $usuario->nombre }}</h1>
    <p>Panel de Secretario General &mdash; {{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
</div>

<div class="stats-grid">
    <div class="stat-card azul">
        <span class="stat-label">Servidores activos</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">Registrados en el sistema</span>
    </div>
    <div class="stat-card amarillo">
        <span class="stat-label">Cultos este mes</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">{{ now()->isoFormat('MMMM YYYY') }}</span>
    </div>
    <div class="stat-card verde">
        <span class="stat-label">Asignaciones activas</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">Confirmadas y pendientes</span>
    </div>
    <div class="stat-card rojo">
        <span class="stat-label">Reemplazos este mes</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">Registrados este mes</span>
    </div>
</div>

<div class="tabla-wrapper">
    <div class="tabla-header">
        <h2>Estado del sistema</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Módulo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Control de acceso y roles</td>
                <td><span class="pill pill-activo">&#10003; Completado</span></td>
            </tr>
            <tr>
                <td>Login con roles</td>
                <td><span class="pill pill-activo">&#10003; Completado</span></td>
            </tr>
            <tr>
                <td>Dashboards por rol</td>
                <td><span class="pill pill-activo">&#10003; Completado</span></td>
            </tr>
            <tr>
                <td>Gestión de servidores</td>
                <td><span class="pill pill-pendiente">Pendiente</span></td>
            </tr>
            <tr>
                <td>Gestión de cultos</td>
                <td><span class="pill pill-pendiente">Pendiente</span></td>
            </tr>
            <tr>
                <td>Estadísticas</td>
                <td><span class="pill pill-pendiente">Pendiente</span></td>
            </tr>
        </tbody>
    </table>
</div>
@endsection