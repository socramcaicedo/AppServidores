@extends('layouts.app')
@section('titulo', $culto->nombre_culto)

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Administración</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Dashboard
    </a>
    <a href="{{ route('servidores.index') }}">
        <span class="icono">&#128101;</span> Servidores
    </a>
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Cultos</p>
    <a href="{{ route('cultos.index') }}" class="activo">
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
@endsection

@section('contenido')
@php
    $estado = $culto->fecha->isPast() ? 'realizado' : ($culto->fecha->isToday() ? 'hoy' : 'programado');
    $pillColor = match($estado) {
        'realizado'  => 'pill-inactivo',
        'hoy'        => 'pill-pendiente',
        'programado' => 'pill-activo',
    };
@endphp

{{-- Encabezado --}}
<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem;">
    <div>
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
            <a href="{{ route('cultos.index') }}"
               style="color:#1A4FA8; font-size:13px; text-decoration:none;">
                &#8592; Volver a cultos
            </a>
        </div>
        <h1 style="font-size:22px; font-weight:600; color:#0D2F6E;">
            {{ $culto->nombre_culto }}
        </h1>
        <p style="color:#555; font-size:14px; margin-top:4px;">
            {{ $culto->fecha->isoFormat('dddd D [de] MMMM [de] YYYY') }}
            &mdash; {{ $culto->fecha->format('H:i') }} hrs
            &nbsp;<span class="pill {{ $pillColor }}">{{ ucfirst($estado) }}</span>
        </p>
        @if($culto->descripcion)
        <p style="color:#777; font-size:13px; margin-top:6px;">{{ $culto->descripcion }}</p>
        @endif
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 360px; gap:1.5rem;">

    {{-- Columna principal: Asignaciones --}}
    <div>
        <div class="tabla-wrapper">
            <div class="tabla-header">
                <h2>Servidores asignados</h2>
                <span style="font-size:13px; color:#999;">
                    {{ $culto->asignaciones->count() }} asignaciones
                </span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Servidor</th>
                        <th>Rol de servicio</th>
                        <th>Confirmado</th>
                        <th>Estado</th>
                        <th>WhatsApp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($culto->asignaciones as $asignacion)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div class="avatar">
                                    {{ strtoupper(substr($asignacion->servidor->nombre_completo ?? 'S', 0, 2)) }}
                                </div>
                                {{ $asignacion->servidor->nombre_completo ?? '—' }}
                            </div>
                        </td>
                        <td>{{ $asignacion->rol_servicio }}</td>
                        <td>
                            <span class="pill {{ $asignacion->confirmado ? 'pill-activo' : 'pill-pendiente' }}">
                                {{ $asignacion->confirmado ? 'Confirmado' : 'Pendiente' }}
                            </span>
                        </td>
                        <td>
                            <span class="pill {{ $asignacion->estado === 'activo' ? 'pill-activo' : 'pill-inactivo' }}">
                                {{ ucfirst($asignacion->estado) }}
                            </span>
                        </td>
                        <td>
                            @if($asignacion->servidor)
                            <a href="{{ $asignacion->servidor->link_whatsapp }}"
                               target="_blank"
                               style="color:#1A7A4A; font-size:13px; text-decoration:none; font-weight:500;">
                                &#128222; Contactar
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:#999; padding:2rem;">
                            No hay servidores asignados a este culto aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Columna lateral: Mensaje --}}
    <div>
        <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; overflow:hidden;">
            <div style="background:#0D2F6E; padding:1rem 1.25rem;">
                <h2 style="font-size:14px; font-weight:600; color:#fff;">
                    &#128172; Mensaje pastoral
                </h2>
                <p style="font-size:12px; color:#93aad4; margin-top:2px;">
                    Visible para todos los roles
                </p>
            </div>

            {{-- Mensaje existente --}}
            @if($culto->mensaje)
            <div style="padding:1.25rem; border-bottom:1px solid #D1DCF0;">
                <p style="font-size:14px; color:#2a2a3e; line-height:1.7;">
                    {{ $culto->mensaje }}
                </p>
                <p style="font-size:12px; color:#999; margin-top:8px;">
                    — {{ $culto->mensajeAutor?->nombre_completo ?? 'Desconocido' }}
                    &middot; {{ $culto->updated_at->isoFormat('D MMM YYYY, H:mm') }}
                </p>
            </div>
            @else
            <div style="padding:1.25rem; border-bottom:1px solid #D1DCF0;">
                <p style="font-size:13px; color:#999; text-align:center; padding:1rem 0;">
                    No hay mensaje registrado aún.
                </p>
            </div>
            @endif

            {{-- Formulario para dejar mensaje --}}
            @if(auth()->user()->tieneRol('secretario_general') || auth()->user()->tieneRol('pastor'))
            <div style="padding:1.25rem;">
                <form method="POST" action="{{ route('cultos.mensaje', $culto->id) }}">
                    @csrf
                    <div style="margin-bottom:10px;">
                        <label style="font-size:13px; font-weight:600; color:#3a4255; display:block; margin-bottom:5px;">
                            {{ $culto->mensaje ? 'Actualizar mensaje' : 'Dejar un mensaje' }}
                        </label>
                        <textarea name="mensaje" rows="4"
                                  placeholder="Escribe una sugerencia, instrucción o mensaje para este culto..."
                                  style="width:100%; padding:9px 12px; border:1px solid #D1DCF0;
                                         border-radius:7px; font-size:13px; resize:vertical;
                                         font-family:inherit; outline:none; color:#1a1a2e;">{{ old('mensaje', $culto->mensaje) }}</textarea>
                        @error('mensaje')
                            <p style="color:#C0392B; font-size:12px; margin-top:4px;">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primario" style="width:100%; justify-content:center;">
                        &#10003; {{ $culto->mensaje ? 'Actualizar mensaje' : 'Guardar mensaje' }}
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

</div>

<style>
    .avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: #E8F0FB; color: #1A4FA8;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
</style>
@endsection