@extends('layouts.app')
@section('titulo', 'Detalles Pastoral - ' . $culto->nombre_culto)

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Supervisión Pastoral</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Inicio
    </a>
    <a href="{{ route('pastor.calendario.index') }}">
        <span class="icono">&#128197;</span> Calendario Pastoral
    </a>
    <a href="{{ route('cultos.index') }}">
        <span class="icono">&#128301;</span> Supervisar Cultos
    </a>
    <a href="{{ route('estadisticas.index') }}">
        <span class="icono">&#128200;</span> Estadísticas
    </a>
    <a href="{{ route('historial.index') }}">
        <span class="icono">&#128203;</span> Historial
    </a>
</div>
@endsection

@section('contenido')
@php
    $estado = $culto->estado;
@endphp

{{-- HEADER DEL CULTO --}}
<div style="background:linear-gradient(135deg, #1A7A4A 0%, #0F5C32 100%); border-radius:16px; padding:2rem; margin-bottom:2rem; color:#fff;">
    <div style="display:flex; justify-content:space-between; align-items:start;">
        <div style="flex:1;">
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                <span style="font-size:28px;">&#128197;</span>
                <h1 style="font-size:28px; font-weight:700; margin:0;">{{ $culto->nombre_culto }}</h1>
            </div>
            <p style="font-size:16px; opacity:0.9; margin:0.5rem 0;">
                {{ $culto->fecha->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                <span style="opacity:0.8;">&mdash;</span>
                {{ $culto->fecha->format('g:i A') }}
            </p>
            <p style="color:#F5C518; font-size:14px; margin:0.5rem 0 0 0;">
                <strong>{{ $culto->caracter_nombre }}</strong>
            </p>
        </div>
        <div style="text-align:right;">
            <span class="pill" style="background:rgba(255,255,255,0.2); color:#fff; font-size:13px; padding:6px 16px; border-radius:20px; display:inline-block; margin-bottom:0.5rem;">
                {{ ucfirst($estado) }}
            </span>
            <div style="font-size:12px; opacity:0.9; margin-top:0.5rem;">
                {{ $culto->asignaciones->count() }} servidor(es) asignado(s)
            </div>
        </div>
    </div>
    @if($culto->descripcion)
    <div style="background:rgba(255,255,255,0.1); border-radius:8px; padding:1rem; margin-top:1rem;">
        <p style="font-size:14px; margin:0; opacity:0.9;">{{ $culto->descripcion }}</p>
    </div>
    @endif
</div>

{{-- SECCIÓN: SERVIDORES ASIGNADOS --}}
<div style="background:#fff; border:1px solid #D1DCF0; border-radius:12px; overflow:hidden; margin-bottom:2rem;">
    <div style="background:#F8F9FA; padding:1.5rem; border-bottom:1px solid #D1DCF0;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <span style="font-size:32px;">&#128101;</span>
                <div>
                    <h2 style="font-size:20px; font-weight:600; color:#0D2F6E; margin:0;">
                        Servidores Asignados
                    </h2>
                    <p style="font-size:13px; color:#555; margin:0;">
                        Organización del servicio (Solo lectura)
                    </p>
                </div>
            </div>
            <span class="pill pill-activo" style="font-size:12px;">
                {{ $culto->asignaciones->count() }} servidor(es)
            </span>
        </div>
    </div>

    <div style="padding:1.5rem;">
        @if($culto->asignaciones->count() > 0)
        <div style="display:grid; gap:1rem;">
            @foreach($culto->asignaciones as $asignacion)
            @php
                $confirmadoBg = $asignacion->confirmado ? '#E8F5E9' : '#FFF3E0';
                $confirmadoIcon = $asignacion->confirmado ? '&#10003;' : '&#9203;';
                $confirmadoTexto = $asignacion->confirmado ? 'Confirmado' : 'Pendiente';
            @endphp
            <div style="background:{{ $confirmadoBg }}; border:1px solid #D1DCF0; border-radius:10px; overflow:hidden;">
                <div style="padding:1rem; display:flex; align-items:center; gap:1rem;">
                    <div style="background:linear-gradient(135deg, #1A4FA8 0%, #0D2F6E 100%); color:#fff; width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:20px; font-weight:700;">
                        {{ strtoupper(substr($asignacion->servidor->nombre_completo, 0, 1)) }}
                    </div>
                    <div style="flex:1;">
                        <h3 style="font-size:15px; font-weight:600; color:#0D2F6E; margin:0 0 0.25rem 0;">
                            {{ $asignacion->servidor->nombre_completo }}
                        </h3>
                        <p style="color:#555; font-size:13px; margin:0;">
                            <strong>{{ $asignacion->rol_servicio }}</strong>
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <span class="pill" style="background:{{ $asignacion->confirmado ? '#1A7A4A' : '#F5C518' }}; color:{{ $asignacion->confirmado ? '#fff' : '#0D2F6E' }}; font-size:11px; padding:4px 10px; border-radius:15px; display:inline-flex; align-items:center; gap:4px;">
                            {{ $confirmadoIcon }} {{ $confirmadoTexto }}
                        </span>
                    </div>
                </div>

                @if($asignacion->motivo_reemplazo)
                <div style="background:#FFF3E0; padding:0.75rem 1rem; border-top:1px solid #FFE0B2;">
                    <p style="font-size:12px; color:#E65100; margin:0;">
                        <strong>Nota:</strong> {{ \App\Models\Asignacion::motivosReemplazo()[$asignacion->motivo_reemplazo] ?? $asignacion->motivo_reemplazo }}
                    </p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center; padding:2rem; background:#F8F9FA; border-radius:8px;">
            <p style="color:#999; font-style:italic; margin:0; font-size:14px;">
                No hay servidores asignados a este culto aún
            </p>
        </div>
        @endif
    </div>
</div>

{{-- SECCIÓN: MENSAJES PASTORALES CON HISTORIAL --}}
<div style="background:#fff; border:2px solid #F5C518; border-radius:12px; overflow:hidden;">
    <div style="background:linear-gradient(135deg, #FFF8DC 0%, #FFF4CC 100%); padding:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <span style="font-size:32px;">&#128172;</span>
                <div>
                    <h2 style="font-size:20px; font-weight:600; color:#0D2F6E; margin:0;">
                        Mensajes Pastorales
                    </h2>
                    <p style="font-size:13px; color:#555; margin:0;">
                        Historial completo de orientación espiritual
                    </p>
                </div>
            </div>
            <span class="pill" style="background:#1A7A4A; color:#fff; font-size:11px;">
                {{ $culto->mensajesPastorales->count() }} mensaje(s)
            </span>
        </div>
    </div>

    <div style="padding:1.5rem;">
        {{-- FORMULARIO PARA NUEVO MENSAJE --}}
        <div style="background:#F8F9FA; border:1px solid #D1DCF0; border-radius:10px; padding:1.5rem; margin-bottom:2rem;">
            <h3 style="font-size:16px; font-weight:600; color:#0D2F6E; margin:0 0 1rem 0;">
                &#10003; Dejar un nuevo mensaje pastoral
            </h3>
            <form method="POST" action="{{ route('pastor.calendario.mensaje', $culto->id) }}">
                @csrf
                <div>
                    <textarea name="mensaje" rows="4"
                              placeholder="Escribe tu orientación espiritual, palabra de aliento, o instrucciones pastorales para los servidores de este culto..."
                              style="width:100%; padding:12px 14px; border:2px solid #D1DCF0; border-radius:8px; font-size:14px; resize:vertical; font-family:inherit; outline:none; color:#1a1a2e; transition:border-color 0.2s;"
                              onfocus="this.style.borderColor='#1A7A4A';"
                              onblur="this.style.borderColor='#D1DCF0';">{{ old('mensaje') }}</textarea>
                </div>
                <div style="margin-top:1rem; text-align:right;">
                    <button type="submit" class="btn" style="background:#1A7A4A; color:#fff; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:500; font-size:14px;">
                        &#10003; Publicar Mensaje Pastoral
                    </button>
                </div>
            </form>
        </div>

        {{-- HISTORIAL DE MENSAJES --}}
        @if($culto->mensajesPastorales->count() > 0)
        <h3 style="font-size:16px; font-weight:600; color:#0D2F6E; margin:0 0 1rem 0;">
            &#128196; Historial de mensajes pastorales
        </h3>

        <div style="display:flex; flex-direction:column; gap:1rem;">
            @foreach($culto->mensajesPastorales as $mensaje)
            <div style="background:#FFF8DC; border:1px solid #F5C518; border-radius:10px; overflow:hidden;">
                <div style="background:linear-gradient(135deg, #FFF8DC 0%, #FFF4CC 100%); padding:1rem; border-bottom:1px solid #F5C518;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <div style="background:linear-gradient(135deg, #1A7A4A 0%, #0F5C32 100%); color:#fff; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:700;">
                                {{ strtoupper(substr($mensaje->usuario->nombre_completo, 0, 1)) }}
                            </div>
                            <div>
                                <p style="font-size:14px; font-weight:600; color:#0D2F6E; margin:0;">
                                    {{ $mensaje->usuario->nombre_completo }}
                                </p>
                                <p style="font-size:12px; color:#555; margin:0;">
                                    {{ $mensaje->fecha_publicacion->isoFormat('D [de] MMMM [de] YYYY') }}
                                    <span style="color:#999;">&mdash;</span>
                                    {{ $mensaje->fecha_publicacion->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                        <span class="pill" style="background:#1A7A4A; color:#fff; font-size:10px; padding:3px 8px; border-radius:12px;">
                            {{ $mensaje->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
                <div style="padding:1rem;">
                    <p style="font-size:14px; color:#333; margin:0; line-height:1.6; font-style:italic;">
                        "{{ $mensaje->mensaje }}"
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center; padding:2rem; background:#F8F9FA; border-radius:8px;">
            <p style="color:#999; font-style:italic; margin:0; font-size:14px;">
                No hay mensajes pastorales para este culto aún. Deja el primero arriba.
            </p>
        </div>
        @endif
    </div>
</div>

{{-- ACCIONES RÁPIDAS --}}
<div style="margin-top:2rem; display:flex; gap:1rem; flex-wrap:wrap;">
    <a href="{{ route('pastor.calendario.index') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; background:#F8F9FA; color:#555; padding:10px 20px; border-radius:8px; text-decoration:none; font-size:13px; font-weight:500;">
        &#8592; Volver al calendario
    </a>

    @if($estado !== 'realizado')
    <a href="{{ route('cultos.pdf', $culto->id) }}"
       target="_blank"
       style="display:inline-flex; align-items:center; gap:0.5rem; background:#E74C3C; color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none; font-size:13px; font-weight:500;">
        &#128196; Descargar Orden PDF
    </a>
    @endif
</div>

<style>
.pill {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.pill-activo {
    background: #E8F5E9;
    color: #1A7A4A;
}

.pill-pendiente {
    background: #FFF3E0;
    color: #F5C518;
}

@media (max-width: 768px) {
    .form-grid-3 {
        grid-template-columns: 1fr;
    }
}
</style>

@endsection