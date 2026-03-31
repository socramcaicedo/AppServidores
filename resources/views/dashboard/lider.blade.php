@extends('layouts.app')
@section('titulo', 'Panel — Líder de Comité')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Servidores</p>
    <a href="#" class="activo">
        <span class="icono">&#128101;</span> Ver servidores
    </a>
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Cultos</p>
    <a href="#">
        <span class="icono">&#43;</span> Crear orden de culto
    </a>
    <a href="#">
        <span class="icono">&#8646;</span> Registrar reemplazo
    </a>
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Historial</p>
    <a href="#">
        <span class="icono">&#128203;</span> Ver historial
    </a>
</div>
@endsection

@section('contenido')
<div class="page-header">
    <h1>Hola, {{ $usuario->nombre }}</h1>
    <p>Panel de Líder de Comité &mdash; {{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
</div>

<div class="stats-grid">
    <div class="stat-card azul">
        <span class="stat-label">Servidores disponibles</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">Para asignar en cultos</span>
    </div>
    <div class="stat-card amarillo">
        <span class="stat-label">Próximo culto</span>
        <span class="stat-valor" style="font-size:16px; padding-top:4px;">Sin programar</span>
        <span class="stat-sub">—</span>
    </div>
    <div class="stat-card verde">
        <span class="stat-label">Asignaciones activas</span>
        <span class="stat-valor">0</span>
        <span class="stat-sub">Activas actualmente</span>
    </div>
</div>

<div class="tabla-wrapper">
    <div class="tabla-header">
        <h2>Próximos cultos programados</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Culto</th>
                <th>Fecha</th>
                <th>Asignaciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" style="text-align:center; color:#999; padding: 2rem;">
                    No hay cultos programados aún.
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection