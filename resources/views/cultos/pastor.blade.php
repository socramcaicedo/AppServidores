@extends('layouts.app')
@section('titulo', 'Supervisión Pastoral de Cultos')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Supervisión Pastoral</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Inicio
    </a>
    <a href="{{ route('cultos.index') }}" class="activo">
        <span class="icono">&#128197;</span> Supervisar Cultos
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
    $cultosHoy = $cultos->filter(fn($c) => $c->fecha->isToday());
    $proximosCultos = $cultos->filter(fn($c) => $c->fecha->isFuture());
    $cultosPasados = $cultos->filter(fn($c) => $c->fecha->isPast());
@endphp

<div class="page-header">
    <div>
        <h1>&#128301; Supervisión Pastoral de Cultos</h1>
        <p>Supervisa la programación espiritual y deja orientación pastoral</p>
    </div>
</div>

{{-- ESTADÍSTICAS RÁPIDAS --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:2rem;">
    <div style="background:linear-gradient(135deg, #1A7A4A 0%, #0F5C32 100%); border-radius:12px; padding:1.5rem; color:#fff;">
        <div style="font-size:28px; margin-bottom:0.5rem;">&#128197;</div>
        <div style="font-size:32px; font-weight:700;">{{ $proximosCultos->count() }}</div>
        <div style="font-size:12px; opacity:0.9;">Próximos cultos</div>
    </div>
    <div style="background:linear-gradient(135deg, #F5C518 0%, #E5B418 100%); border-radius:12px; padding:1.5rem; color:#0D2F6E;">
        <div style="font-size:28px; margin-bottom:0.5rem;">&#128337;</div>
        <div style="font-size:32px; font-weight:700;">{{ $cultosHoy->count() }}</div>
        <div style="font-size:12px; opacity:0.8;">Cultos hoy</div>
    </div>
    <div style="background:linear-gradient(135deg, #1A4FA8 0%, #0D2F6E 100%); border-radius:12px; padding:1.5rem; color:#fff;">
        <div style="font-size:28px; margin-bottom:0.5rem;">&#128101;</div>
        <div style="font-size:32px; font-weight:700;">{{ $cultos->sum(fn($c) => $c->asignaciones->count()) }}</div>
        <div style="font-size:12px; opacity:0.9;">Total asignaciones</div>
    </div>
    <div style="background:linear-gradient(135deg, #9B59B6 0%, #7D3C98 100%); border-radius:12px; padding:1.5rem; color:#fff;">
        <div style="font-size:28px; margin-bottom:0.5rem;">&#128172;</div>
        <div style="font-size:32px; font-weight:700;">{{ $cultos->filter(fn($c) => $c->mensaje)->count() }}</div>
        <div style="font-size:12px; opacity:0.9;">Con mensaje pastoral</div>
    </div>
</div>

{{-- CULTOS DE HOY --}}
@if($cultosHoy->count() > 0)
<div style="margin-bottom:2rem;">
    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
        <span style="font-size:24px;">&#128337;</span>
        <h2 style="font-size:20px; font-weight:600; color:#0D2F6E; margin:0;">
            Cultos de Hoy
        </h2>
        <span class="pill pill-pendiente" style="font-size:11px; margin-left:auto;">
            {{ $cultosHoy->count() }} culto(s)
        </span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(350px, 1fr)); gap:1rem;">
        @foreach($cultosHoy as $culto)
        <div style="background:#fff; border:2px solid #F5C518; border-radius:12px; overflow:hidden;">
            <div style="background:linear-gradient(135deg, #FFF8DC 0%, #FFF4CC 100%); padding:1.25rem;">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div style="flex:1;">
                        <h3 style="font-size:16px; font-weight:600; color:#0D2F6E; margin:0 0 0.5rem 0;">
                            {{ $culto->nombre_culto }}
                        </h3>
                        <p style="color:#555; font-size:13px; margin:0;">
                            {{ $culto->fecha->isoFormat('dddd D [de] MMMM') }}
                            <span style="color:#1A7A4A; font-weight:500;">&mdash; {{ $culto->fecha->format('g:i A') }}</span>
                        </p>
                        <p style="color:#1A4FA8; font-size:12px; margin:0.25rem 0 0 0;">
                            <strong>{{ $culto->caracter_nombre }}</strong>
                        </p>
                    </div>
                    <span class="pill pill-pendiente" style="font-size:11px;">
                        &#9889; HOY
                    </span>
                </div>
            </div>
            <div style="padding:1rem;">
                @if($culto->mensaje)
                <div style="background:#FFF8DC; border-left:4px solid #F5C518; padding:0.75rem; border-radius:7px; margin-bottom:0.75rem;">
                    <p style="font-size:12px; color:#333; margin:0; font-style:italic;">
                        "{{ Str::limit($culto->mensaje, 60) }}"
                    </p>
                </div>
                @endif

                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:12px; color:#777;">
                        {{ $culto->asignaciones->count() }} servidor(es) asignado(s)
                    </span>
                    <a href="{{ route('cultos.show', $culto->id) }}"
                       style="color:#1A7A4A; text-decoration:none; font-size:13px; font-weight:500;">
                        &#128301; Supervisar &#8594;
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- PRÓXIMOS CULTOS --}}
@if($proximosCultos->count() > 0)
<div style="margin-bottom:2rem;">
    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
        <span style="font-size:24px;">&#128197;</span>
        <h2 style="font-size:20px; font-weight:600; color:#0D2F6E; margin:0;">
            Próximos Cultos
        </h2>
        <span class="pill pill-activo" style="font-size:11px; margin-left:auto;">
            {{ $proximosCultos->count() }} programado(s)
        </span>
    </div>

    <div style="background:#fff; border:1px solid #D1DCF0; border-radius:12px; overflow:hidden;">
        @foreach($proximosCultos as $culto)
        <div style="padding:1rem; border-bottom:1px solid #E8EEF5; display:flex; align-items:center; gap:1rem;">
            <div style="background:linear-gradient(135deg, #1A4FA8 0%, #0D2F6E 100%); color:#fff; width:60px; height:60px; border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0;">
                <div style="font-size:18px; font-weight:700;">{{ $culto->fecha->format('d') }}</div>
                <div style="font-size:10px; text-transform:uppercase;">{{ $culto->fecha->format('M') }}</div>
            </div>
            <div style="flex:1;">
                <h3 style="font-size:15px; font-weight:600; color:#0D2F6E; margin:0 0 0.25rem 0;">
                    {{ $culto->nombre_culto }}
                </h3>
                <p style="color:#555; font-size:12px; margin:0;">
                    {{ $culto->fecha->isoFormat('dddd D [de] MMMM') }} &mdash; {{ $culto->fecha->format('g:i A') }}
                </p>
                <p style="color:#1A4FA8; font-size:11px; margin:0.25rem 0 0 0;">
                    <strong>{{ $culto->caracter_nombre }}</strong> &bull; {{ $culto->asignaciones->count() }} servidor(es)
                </p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                @if($culto->mensaje)
                <span class="pill" style="background:#FFF8DC; color:#0D2F6E; font-size:10px;" title="Tiene mensaje pastoral">
                    &#128172; Mensaje
                </span>
                @endif
                <a href="{{ route('cultos.show', $culto->id) }}"
                   style="color:#1A7A4A; text-decoration:none; font-size:12px; font-weight:500; white-space:nowrap;">
                    &#128301; Ver detalles
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- CULTOS PASADOS --}}
@if($cultosPasados->count() > 0)
<div>
    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
        <span style="font-size:24px;">&#128197;</span>
        <h2 style="font-size:20px; font-weight:600; color:#0D2F6E; margin:0;">
            Cultos Realizados
        </h2>
        <span class="pill" style="background:#E8EEF5; color:#777; font-size:11px; margin-left:auto;">
            {{ $cultosPasados->count() }} histórico
        </span>
    </div>

    <div style="background:#fff; border:1px solid #D1DCF0; border-radius:12px; overflow:hidden;">
        @foreach($cultosPasados->take(10) as $culto)
        <div style="padding:1rem; border-bottom:1px solid #E8EEF5; display:flex; align-items:center; gap:1rem;">
            <div style="background:#E8EEF5; color:#777; width:60px; height:60px; border-radius:10px; display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0;">
                <div style="font-size:18px; font-weight:700;">{{ $culto->fecha->format('d') }}</div>
                <div style="font-size:10px; text-transform:uppercase;">{{ $culto->fecha->format('M') }}</div>
            </div>
            <div style="flex:1;">
                <h3 style="font-size:15px; font-weight:600; color:#0D2F6E; margin:0 0 0.25rem 0;">
                    {{ $culto->nombre_culto }}
                </h3>
                <p style="color:#777; font-size:12px; margin:0;">
                    {{ $culto->fecha->isoFormat('dddd D [de] MMMM [de] YYYY') }} &mdash; {{ $culto->fecha->format('g:i A') }}
                </p>
                <p style="color:#555; font-size:11px; margin:0.25rem 0 0 0;">
                    <strong>{{ $culto->caracter_nombre }}</strong> &bull; {{ $culto->asignaciones->count() }} servidor(es)
                </p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                @if($culto->mensaje)
                <span class="pill" style="background:#FFF8DC; color:#0D2F6E; font-size:10px;">
                    &#128172; Con mensaje
                </span>
                @endif
                <a href="{{ route('cultos.show', $culto->id) }}"
                   style="color:#1A4FA8; text-decoration:none; font-size:12px; font-weight:500; white-space:nowrap;">
                    &#128301; Ver detalles
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection