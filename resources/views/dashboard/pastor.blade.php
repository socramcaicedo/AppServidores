@extends('layouts.app')
@section('titulo', 'Panel Pastoral - Supervisor')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Supervisión Pastoral</p>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'activo' : '' }}">
        <span class="icono">&#127968;</span> Inicio
    </a>
    <a href="{{ route('cultos.index') }}" class="{{ request()->routeIs('cultos*') ? 'activo' : '' }}">
        <span class="icono">&#128197;</span> Gestionar cultos
    </a>
    <a href="{{ route('estadisticas.index') }}" class="{{ request()->is('estadisticas*') ? 'activo' : '' }}">
        <span class="icono">&#128200;</span> Estadísticas
    </a>
    <a href="{{ route('historial.index') }}" class="{{ request()->routeIs('historial*') ? 'activo' : '' }}">
        <span class="icono">&#128203;</span> Historial
    </a>
</div>
@endsection

@section('contenido')
@php
    $cultos = \App\Models\Culto::with(['asignaciones.servidor', 'mensajeAutor'])
        ->orderBy('fecha', 'desc')
        ->limit(3)
        ->get();

    $proximosCultos = \App\Models\Culto::whereDate('fecha', '>=', now())
        ->orderBy('fecha')
        ->limit(3)
        ->get();

    $totalCultos = \App\Models\Culto::count();
    $cultosEsteMes = \App\Models\Culto::whereMonth('fecha', now()->month)
        ->whereYear('fecha', now()->year)
        ->count();
@endphp

<div class="page-header">
    <div>
        <h1>&#128196; Panel Pastoral</h1>
        <p>Supervisión de cultos y orientación espiritual</p>
    </div>
</div>

 {{-- TARJETAS DE ACCESSO RÁPIDO --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1.5rem; margin-bottom:2rem;">

    {{-- TARJETA: GESTIÓN DE CULTOS --}}
    <a href="{{ route('cultos.index') }}"
       class="tarjeta-acceso"
       style="text-decoration:none; color:inherit;">
        <div style="background:linear-gradient(135deg, #1A4FA8 0%, #0D2F6E 100%);
                    border-radius:16px;
                    padding:2rem;
                    color:#fff;
                    position:relative;
                    overflow:hidden;
                    transition:all 0.3s;
                    box-shadow:0 4px 15px rgba(26, 79, 168, 0.2);"
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(26, 79, 168, 0.3)';"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(26, 79, 168, 0.2)';">

            {{-- Icono de fondo --}}
            <div style="position:absolute; top:-20px; right:-20px; font-size:150px; opacity:0.1;">
                &#128197;
            </div>

            <div style="position:relative; z-index:1;">
                <div style="font-size:48px; margin-bottom:1rem;">&#128197;</div>
                <h2 style="font-size:24px; font-weight:700; margin:0 0 0.5rem 0;">
                    Gestionar Cultos
                </h2>
                <p style="font-size:14px; opacity:0.9; margin:0 0 1rem 0;">
                    Programa y administra todos los cultos de la iglesia
                </p>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="pill" style="background:rgba(255,255,255,0.2); color:#fff; font-size:12px; padding:4px 12px; border-radius:20px;">
                        {{ $totalCultos }} cultos
                    </span>
                    <span style="font-size:12px; opacity:0.8;">
                        Administrar &#8594;
                    </span>
                </div>
            </div>
        </div>
    </a>

    {{-- TARJETA: CALENDARIO PASTORAL --}}
    <a href="{{ route('pastor.calendario.index') }}"
       class="tarjeta-acceso"
       style="text-decoration:none; color:inherit;">
        <div style="background:linear-gradient(135deg, #F5C518 0%, #E5B418 100%);
                    border-radius:16px;
                    padding:2rem;
                    color:#0D2F6E;
                    position:relative;
                    overflow:hidden;
                    transition:all 0.3s;
                    box-shadow:0 4px 15px rgba(245, 197, 24, 0.2);"
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(245, 197, 24, 0.3)';"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(245, 197, 24, 0.2)';">

            <div style="position:absolute; top:-20px; right:-20px; font-size:150px; opacity:0.1;">
                &#128197;
            </div>

            <div style="position:relative; z-index:1;">
                <div style="font-size:48px; margin-bottom:1rem;">&#128197;</div>
                <h2 style="font-size:24px; font-weight:700; margin:0 0 0.5rem 0;">
                    Calendario Pastoral
                </h2>
                <p style="font-size:14px; opacity:0.8; margin:0 0 1rem 0;">
                    Visualiza cultos y deja mensajes pastorales
                </p>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="pill" style="background:rgba(13, 47, 110, 0.1); color:#0D2F6E; font-size:12px; padding:4px 12px; border-radius:20px;">
                        {{ $cultosEsteMes }} este mes
                    </span>
                    <span style="font-size:12px; opacity:0.8;">
                        Ver calendario &#8594;
                    </span>
                </div>
            </div>
        </div>
    </a>

    {{-- TARJETA: MENSAJES PASTORALES --}}
    <div onclick="abrirSeccionMensajes()"
         class="tarjeta-acceso"
         style="cursor:pointer;
                background:linear-gradient(135deg, #1A7A4A 0%, #0F5C32 100%);
                border-radius:16px;
                padding:2rem;
                color:#fff;
                position:relative;
                overflow:hidden;
                transition:all 0.3s;
                box-shadow:0 4px 15px rgba(26, 122, 74, 0.2);"
         onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(26, 122, 74, 0.3)';"
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(26, 122, 74, 0.2)';">

        <div style="position:absolute; top:-20px; right:-20px; font-size:150px; opacity:0.1;">
            &#128172;
        </div>

        <div style="position:relative; z-index:1;">
            <div style="font-size:48px; margin-bottom:1rem;">&#128172;</div>
            <h2 style="font-size:24px; font-weight:700; margin:0 0 0.5rem 0;">
                Mensajes Pastorales
            </h2>
            <p style="font-size:14px; opacity:0.9; margin:0 0 1rem 0;">
                Deja orientación espiritual para los servidores
            </p>
            <div style="display:flex; gap:10px; align-items:center;">
                <span class="pill" style="background:rgba(255,255,255,0.2); color:#fff; font-size:12px; padding:4px 12px; border-radius:20px;">
                    Exclusivo pastor
                </span>
                <span style="font-size:12px; opacity:0.8;">
                    Gestionar &#8594;
                </span>
            </div>
        </div>
    </div>

    {{-- TARJETA: ESTADÍSTICAS --}}
    <a href="{{ route('estadisticas.index') }}"
       class="tarjeta-acceso"
       style="text-decoration:none; color:inherit;">
        <div style="background:linear-gradient(135deg, #9B59B6 0%, #7D3C98 100%);
                    border-radius:16px;
                    padding:2rem;
                    color:#fff;
                    position:relative;
                    overflow:hidden;
                    transition:all 0.3s;
                    box-shadow:0 4px 15px rgba(155, 89, 182, 0.2);"
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(155, 89, 182, 0.3)';"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(155, 89, 182, 0.2)';">

            <div style="position:absolute; top:-20px; right:-20px; font-size:150px; opacity:0.1;">
                &#128200;
            </div>

            <div style="position:relative; z-index:1;">
                <div style="font-size:48px; margin-bottom:1rem;">&#128200;</div>
                <h2 style="font-size:24px; font-weight:700; margin:0 0 0.5rem 0;">
                    Estadísticas
                </h2>
                <p style="font-size:14px; opacity:0.9; margin:0 0 1rem 0;">
                    Análisis y métricas del ministerio
                </p>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="pill" style="background:rgba(255,255,255,0.2); color:#fff; font-size:12px; padding:4px 12px; border-radius:20px;">
                        Análisis completo
                    </span>
                    <span style="font-size:12px; opacity:0.8;">
                        Ver estadísticas &#8594;
                    </span>
                </div>
            </div>
        </div>
    </a>

    {{-- TARJETA: HISTORIAL --}}
    <a href="{{ route('historial.index') }}"
       class="tarjeta-acceso"
       style="text-decoration:none; color:inherit;">
        <div style="background:linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
                    border-radius:16px;
                    padding:2rem;
                    color:#fff;
                    position:relative;
                    overflow:hidden;
                    transition:all 0.3s;
                    box-shadow:0 4px 15px rgba(231, 76, 60, 0.2);"
             onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(231, 76, 60, 0.3)';"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(231, 76, 60, 0.2)';">

            <div style="position:absolute; top:-20px; right:-20px; font-size:150px; opacity:0.1;">
                &#128203;
            </div>

            <div style="position:relative; z-index:1;">
                <div style="font-size:48px; margin-bottom:1rem;">&#128203;</div>
                <h2 style="font-size:24px; font-weight:700; margin:0 0 0.5rem 0;">
                    Historial
                </h2>
                <p style="font-size:14px; opacity:0.9; margin:0 0 1rem 0;">
                    Registro de todas las acciones del sistema
                </p>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="pill" style="background:rgba(255,255,255,0.2); color:#fff; font-size:12px; padding:4px 12px; border-radius:20px;">
                        Auditoría
                    </span>
                    <span style="font-size:12px; opacity:0.8;">
                        Ver historial &#8594;
                    </span>
                </div>
            </div>
        </div>
    </a>
</div>

{{-- SECCIÓN: PRÓXIMOS CULTOS --}}
<div style="margin-bottom:2rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="font-size:20px; font-weight:600; color:#0D2F6E; margin:0;">
            &#128197; Próximos Cultos
        </h2>
        <a href="{{ route('cultos.index') }}" style="color:#1A4FA8; text-decoration:none; font-size:14px; font-weight:500;">
            Ver todos &#8594;
        </a>
    </div>

    @if($proximosCultos->count() > 0)
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:1rem;">
            @foreach($proximosCultos as $culto)
            @php
                $estado = $culto->fecha->isToday() ? 'hoy' : 'programado';
                $bgColor = $estado === 'hoy' ? '#FFF8DC' : '#E8F0FB';
                $pillColor = $estado === 'hoy' ? 'pill-pendiente' : 'pill-activo';
            @endphp
            <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; overflow:hidden; transition:all 0.3s;"
                 onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';"
                 onmouseout="this.style.boxShadow='none';">
                <div style="background:{{ $bgColor }}; padding:1rem;">
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                        <div style="flex:1;">
                            <h3 style="font-size:16px; font-weight:600; color:#0D2F6E; margin:0 0 0.5rem 0;">
                                {{ $culto->nombre_culto }}
                            </h3>
                            <p style="color:#555; font-size:13px; margin:0;">
                                {{ $culto->fecha->isoFormat('dddd D [de] MMMM') }}
                                <span style="color:#1A4FA8; font-weight:500;">&mdash; {{ $culto->fecha->format('g:i A') }}</span>
                            </p>
                            <p style="color:#1A4FA8; font-size:12px; margin:0.25rem 0 0 0;">
                                <strong>{{ $culto->caracter_nombre }}</strong>
                            </p>
                        </div>
                        <span class="pill {{ $pillColor }}" style="font-size:11px;">
                            {{ ucfirst($estado) }}
                        </span>
                    </div>
                    @if($culto->mensaje)
                    <div style="padding:0.75rem 1rem; background:#FFF8DC; border-left:3px solid #F5C518; margin-top:0.5rem;">
                        <p style="font-size:11px; color:#555; margin:0; font-style:italic;">
                            "{{ Str::limit($culto->mensaje, 80) }}"
                        </p>
                    </div>
                    @endif
                    <div style="padding:0.75rem 1rem; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:12px; color:#777;">
                            {{ $culto->asignaciones->count() }} servidor(es)
                        </span>
                        <a href="{{ route('cultos.show', $culto->id) }}"
                           style="color:#1A4FA8; text-decoration:none; font-size:12px; font-weight:500;">
                            Ver detalles &#8594;
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; padding:2rem; text-align:center;">
            <div style="font-size:48px; margin-bottom:1rem; opacity:0.3;">&#128197;</div>
            <p style="color:#777; margin:0;">No hay cultos programados</p>
        </div>
    @endif
</div>

{{-- Sección de mensajes pastorales (oculta por defecto) --}}
<div id="seccion-mensajes" style="display:none;">
    @php
        $todosCultos = \App\Models\Culto::with(['asignaciones.servidor', 'mensajeAutor'])
            ->orderBy('fecha', 'desc')
            ->get();
    @endphp

    <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; padding:2rem; margin-bottom:2rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h2 style="font-size:20px; font-weight:600; color:#0D2F6E; margin:0;">
                &#128172; Mensajes Pastorales
            </h2>
            <button onclick="cerrarSeccionMensajes()" style="background:#f4f6fa; border:none; color:#555; padding:8px 16px; border-radius:7px; cursor:pointer; font-size:13px;">
                &times; Cerrar
            </button>
        </div>

        @foreach($todosCultos as $culto)
        <div style="background:#fff; border:1px solid #D1DCF0; border-radius:12px; overflow:hidden; margin-bottom:1.5rem;">
            <div style="background:#F8F9FA; padding:1.25rem; border-bottom:1px solid #D1DCF0;">
                <h3 style="font-size:16px; font-weight:600; color:#0D2F6E; margin:0 0 0.5rem 0;">
                    &#128197; {{ $culto->nombre_culto }}
                </h3>
                <p style="color:#555; font-size:13px; margin:0;">
                    {{ $culto->fecha->isoFormat('dddd D [de] MMMM [de] YYYY') }} &mdash; {{ $culto->fecha->format('g:i A') }}
                </p>
            </div>

            <div style="padding:1.25rem;">
                @if($culto->mensaje)
                <div style="background:#FFF8DC; border-left:4px solid #F5C518; padding:1rem; border-radius:7px; margin-bottom:1rem;">
                    <p style="font-size:13px; color:#333; margin:0; line-height:1.6; font-style:italic;">
                        "{{ $culto->mensaje }}"
                    </p>
                    @if($culto->mensajeAutor)
                    <p style="font-size:11px; color:#777; margin:6px 0 0 0; text-align:right;">
                        — {{ $culto->mensajeAutor->nombre_completo }}
                    </p>
                    @endif
                </div>
                @else
                <p style="color:#999; font-style:italic; text-align:center; padding:1rem;">
                    No hay mensaje pastoral aún
                </p>
                @endif

                <form method="POST" action="{{ route('cultos.mensaje', $culto->id) }}">
                    @csrf
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#3a4255; display:block; margin-bottom:8px;">
                            {{ $culto->mensaje ? 'Actualizar mensaje pastoral' : 'Dejar un mensaje pastoral' }}
                        </label>
                        <textarea name="mensaje" rows="3"
                                  placeholder="Escribe tu orientación, sugerencia pastoral, o palabra de aliento para los servidores..."
                                  style="width:100%; padding:10px 12px; border:1px solid #D1DCF0; border-radius:8px; font-size:13px; resize:vertical; font-family:inherit; outline:none; color:#1a1a2e;">{{ old('mensaje', $culto->mensaje) }}</textarea>
                    </div>
                    <div style="margin-top:10px; text-align:right;">
                        <button type="submit" class="btn btn-primario" style="padding:10px 20px;">
                            &#10003; {{ $culto->mensaje ? 'Actualizar Mensaje' : 'Guardar Mensaje' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
.tarjeta-acceso:hover {
    cursor: pointer;
}

@media (max-width: 768px) {
    .tarjeta-acceso div {
        padding: 1.5rem !important;
    }

    .tarjeta-acceso h2 {
        font-size: 20px !important;
    }

    .tarjeta-acceso div:first-child {
        font-size: 36px !important;
    }
}
</style>

<script>
function abrirSeccionMensajes() {
    document.getElementById('seccion-mensajes').style.display = 'block';
    document.getElementById('seccion-mensajes').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function cerrarSeccionMensajes() {
    document.getElementById('seccion-mensajes').style.display = 'none';
}

// Verificar si hay parámetro en URL para abrir vista específica
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const vista = urlParams.get('view');

    if (vista === 'calendar') {
        // Scroll hacia calendario si existe en la página
        const calendario = document.querySelector('[style*="grid-template-columns:repeat(7, 1fr)"]');
        if (calendario) {
            calendario.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});
</script>

@endsection