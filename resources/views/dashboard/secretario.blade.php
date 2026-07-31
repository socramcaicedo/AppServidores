@extends('layouts.app')
@section('titulo', 'Panel — Secretario General')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Administración</p>
    <a href="{{ route('dashboard') }}" class="activo">
        <span class="icono">&#127968;</span> Dashboard
    </a>

    <a href="{{ route('servidores.index') }}"
       class="{{ request()->routeIs('servidores*') ? 'activo' : '' }}">
        <span class="icono">&#128101;</span> Servidores
    </a>

    <a href="{{ route('cultos.index') }}"
       class="{{ request()->routeIs('cultos*') ? 'activo' : '' }}">
        <span class="icono">&#128197;</span> Gestionar cultos
    </a>

    <a href="{{ route('historial.index') }}"
       class="{{ request()->routeIs('historial*') ? 'activo' : '' }}">
        <span class="icono">&#128203;</span> Historial
    </a>
</div>

<div class="sidebar-section">
    <p class="sidebar-title">Sistema</p>
    <a href="{{ route('admin.roles.index') }}" class="{{ request()->routeIs('admin.roles*') ? 'activo' : '' }}">
        <span class="icono">&#128274;</span> Roles
    </a>
    <a href="{{ route('admin.usuarios.index') }}" class="{{ request()->routeIs('admin.usuarios*') ? 'activo' : '' }}">
        <span class="icono">&#128100;</span> Usuarios
    </a>
</div>

<div class="sidebar-section">
    <p class="sidebar-title">Reportes</p>
    <a href="{{ route('estadisticas.index') }}"
       class="{{ request()->routeIs('estadisticas*') ? 'activo' : '' }}">
        <span class="icono">&#128202;</span> Estadísticas
    </a>
</div>
@endsection

@section('contenido')
@php
    $servidoresActivos = \App\Models\Servidor::where('estado', 1)->count();
    $cultosEsteMes = \App\Models\Culto::whereMonth('fecha', now()->month)
        ->whereYear('fecha', now()->year)
        ->count();
    $asignacionesActivas = \App\Models\Asignacion::where('estado', 'asignado')
        ->whereHas('culto', function($q) {
            $q->whereDate('fecha', '>=', now());
        })->count();
    $reemplazosEsteMes = \App\Models\Asignacion::whereNotNull('reemplazado_por_id')
        ->whereMonth('updated_at', now()->month)
        ->whereYear('updated_at', now()->year)
        ->count();

    // Tasa de cancelación global
    $totalAsignaciones = \App\Models\Asignacion::count();
    $totalCancelaciones = \App\Models\Asignacion::whereNotNull('motivo_reemplazo')->count();
    $tasaCancelacion = $totalAsignaciones > 0 ? round(($totalCancelaciones / $totalAsignaciones) * 100, 2) : 0.0;

    // Calendario
    $mesActual = request()->query('mes') ? request()->query('mes') : now()->month;
    $anioActual = request()->query('anio') ? request()->query('anio') : now()->year;
    $primerDiaMes = \Carbon\Carbon::create($anioActual, $mesActual, 1);
    $ultimoDiaMes = $primerDiaMes->copy()->endOfMonth();
    $nombreMes = ucfirst($primerDiaMes->isoFormat('MMMM'));
    $primerDiaSemana = $primerDiaMes->dayOfWeek;
    $totalDiasMes = $ultimoDiaMes->day;

    // Obtener cultos del mes
    $cultosDelMesRaw = \App\Models\Culto::with(['asignaciones.servidor', 'mensajeAutor'])
        ->whereYear('fecha', $anioActual)
        ->whereMonth('fecha', $mesActual)
        ->orderBy('fecha')
        ->get();

    // Agrupar por día
    $cultosDelMes = $cultosDelMesRaw->groupBy(function($item) {
        return $item->fecha->format('Y-m-d');
    });

    $cultosPorDia = [];
    foreach($cultosDelMes as $fecha => $cultos) {
        $cultosPorDia[$fecha] = $cultos;
    }

    // Colores por tipo de culto
    $coloresPorCaracter = [
        'evangelistico'     => '#C0392B',
        'escuela_dominical' => '#1A7A4A',
        'jovenes'           => '#9B59B6',
        'damas_dorcas'      => '#E91E63',
        'damas_jovenes'     => '#FF69B4',
        'mision_juvenil'    => '#8E44AD',
        'caballeros'        => '#3498DB',
        'familia'           => '#F39C12',
        'parejas'           => '#E74C3C',
        'culto_oracion'     => '#16A085',
    ];

    $mesAnterior = $primerDiaMes->copy()->subMonth();
    $mesSiguiente = $primerDiaMes->copy()->addMonth();
@endphp

<div class="page-header">
    <h1>Bienvenido, {{ $usuario->nombre }}</h1>
    <p>Panel de Secretario General &mdash; {{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
</div>

{{-- Estadísticas rápidas --}}
<div class="stats-grid">
    <div class="stat-card azul">
        <span class="stat-label">Servidores activos</span>
        <span class="stat-valor">{{ $servidoresActivos }}</span>
        <span class="stat-sub">Registrados en el sistema</span>
    </div>
    <div class="stat-card amarillo">
        <span class="stat-label">Cultos este mes</span>
        <span class="stat-valor">{{ $cultosEsteMes }}</span>
        <span class="stat-sub">{{ now()->locale('es')->isoFormat('MMMM YYYY') }}</span>
    </div>
    <div class="stat-card verde">
        <span class="stat-label">Asignaciones activas</span>
        <span class="stat-valor">{{ $asignacionesActivas }}</span>
        <span class="stat-sub">Confirmadas y pendientes</span>
    </div>
    <div class="stat-card rojo">
        <span class="stat-label">Reemplazos este mes</span>
        <span class="stat-valor">{{ $reemplazosEsteMes }}</span>
        <span class="stat-sub">Registrados este mes</span>
    </div>
    <div class="stat-card {{ $tasaCancelacion <= 15 ? 'verde' : ($tasaCancelacion <= 30 ? 'amarillo' : 'rojo') }}">
        <span class="stat-label">Tasa de cancelación</span>
        <span class="stat-valor">{{ $tasaCancelacion }}%</span>
        <span class="stat-sub">
            @if($tasaCancelacion <= 15)
                Nivel bajo
            @elseif($tasaCancelacion <= 30)
                Nivel moderado
            @else
                Nivel alto — revisar
            @endif
        </span>
    </div>
</div>

{{-- CALENDARIO COMPLETO --}}
<div style="margin-bottom:1.5rem;">
    {{-- Header del calendario --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:10px;">
        <button onclick="cambiarMes({{ $mesAnterior->month }}, {{ $mesAnterior->year }})"
                style="padding:10px 20px; background:#0D2F6E; color:#fff; border:none; border-radius:7px; cursor:pointer; font-size:14px; flex-shrink:0;">
            &#8592; Mes anterior
        </button>

        <div style="text-align:center; flex:1; min-width:200px;">
            <h2 style="font-size:24px; font-weight:700; color:#0D2F6E; margin:0;">
                {{ $nombreMes }} {{ $anioActual }}
            </h2>
        </div>

        <button onclick="cambiarMes({{ $mesSiguiente->month }}, {{ $mesSiguiente->year }})"
                style="padding:10px 20px; background:#0D2F6E; color:#fff; border:none; border-radius:7px; cursor:pointer; font-size:14px; flex-shrink:0;">
            Mes siguiente &#8594;
        </button>
    </div>

    {{-- CALENDARIO GRID (visible solo en desktop/tablet, oculto en móvil) --}}
    <div class="cal-mensual">
    {{-- Calendario --}}
    <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; overflow:hidden;">
        {{-- Días de la semana --}}
        <div style="display:grid; grid-template-columns:repeat(7, 1fr); background:#0D2F6E;">
            <div style="padding:12px; text-align:center; color:#fff; font-weight:600; font-size:13px; border-right:1px solid rgba(255,255,255,0.1);">
                DOM
            </div>
            <div style="padding:12px; text-align:center; color:#fff; font-weight:600; font-size:13px; border-right:1px solid rgba(255,255,255,0.1);">
                LUN
            </div>
            <div style="padding:12px; text-align:center; color:#fff; font-weight:600; font-size:13px; border-right:1px solid rgba(255,255,255,0.1);">
                MAR
            </div>
            <div style="padding:12px; text-align:center; color:#fff; font-weight:600; font-size:13px; border-right:1px solid rgba(255,255,255,0.1);">
                MIE
            </div>
            <div style="padding:12px; text-align:center; color:#fff; font-weight:600; font-size:13px; border-right:1px solid rgba(255,255,255,0.1);">
                JUE
            </div>
            <div style="padding:12px; text-align:center; color:#fff; font-weight:600; font-size:13px; border-right:1px solid rgba(255,255,255,0.1);">
                VIE
            </div>
            <div style="padding:12px; text-align:center; color:#fff; font-weight:600; font-size:13px;">
                SAB
            </div>
        </div>

        {{-- Días del mes --}}
        <div style="display:grid; grid-template-columns:repeat(7, 1fr); width:100%; overflow:hidden;">
            @for($i = 0; $i < $primerDiaSemana; $i++)
            <div style="min-height:100px; background:#F8F9FA; border-bottom:1px solid #eee; border-right:1px solid #eee; width:100%; box-sizing:border-box;"></div>
            @endfor

            @for($dia = 1; $dia <= $totalDiasMes; $dia++)
                @php
                    $fechaActual = \Carbon\Carbon::create($anioActual, $mesActual, $dia);
                    $fechaString = $fechaActual->format('Y-m-d');
                    $esHoy = $fechaActual->isToday();
                    $esFuturo = $fechaActual->isFuture();
                    $tieneCultos = isset($cultosPorDia[$fechaString]);
                    $cultosDelDia = $tieneCultos ? $cultosPorDia[$fechaString] : collect();

                    // Obtener color de fondo suave basado en el tipo de culto
                    if ($esHoy) {
                        $bgDia = '#FFF8DC'; // Amarillo claro para hoy
                    } elseif ($tieneCultos && $cultosDelDia->count() > 0) {
                        // Usar el color del primer culto del día con transparencia suave
                        $primerCulto = $cultosDelDia->first();
                        $colorCulto = $coloresPorCaracter[$primerCulto->caracter] ?? '#1A7A4A';

                        // Convertir hex a rgba para agregar transparencia (15% de opacidad)
                        $r = hexdec(substr($colorCulto, 1, 2));
                        $g = hexdec(substr($colorCulto, 3, 2));
                        $b = hexdec(substr($colorCulto, 5, 2));
                        $bgDia = "rgba({$r}, {$g}, {$b}, 0.15)";
                    } else {
                        $bgDia = '#fff';
                    }

                    $borderDia = $esHoy ? '3px solid #F5C518' : '1px solid #eee';
                    $cursor = $esFuturo ? 'pointer' : 'default';
                @endphp
                <div style="min-height:100px; background:{{ $bgDia }}; border-bottom:{{ $borderDia }}; border-right:1px solid #eee; padding:8px; position:relative; width:100%; box-sizing:border-box; overflow:hidden;">
                    @if($esHoy)
                    <div style="position:absolute; top:4px; right:4px; background:#F5C518; color:#0D2F6E; font-size:10px; font-weight:700; padding:2px 6px; border-radius:10px;">
                        Hoy
                    </div>
                    @endif
                    <div style="font-size:16px; font-weight:600; color:#0D2F6E; text-align:center; margin-bottom:4px;">
                        {{ $dia }}
                    </div>
                    @if($tieneCultos)
                    {{-- Área clickeable: Ver cultos existentes --}}
                    <div onclick="verCultosDia('{{ $fechaString }}', true)"
                         style="text-align:center; cursor:pointer; padding:4px; border-radius:6px; transition:background 0.2s;"
                         onmouseover="this.style.background='#F0F8FF'" onmouseout="this.style.background='transparent'">
                        <div style="display:inline-flex; gap:2px; flex-wrap:wrap; justify-content:center;">
                            @foreach($cultosDelDia as $culto)
                            @php
                                $colorCulto = $coloresPorCaracter[$culto->caracter] ?? '#1A7A4A';
                                $colorConOpacidad = $culto->fecha->isPast() ? '#95a5a6' : $colorCulto;
                            @endphp
                            <span style="width:8px; height:8px; border-radius:50%; background:{{ $colorConOpacidad }}; display:inline-block;" title="{{ $culto->caracter_nombre }} - {{ $culto->nombre_culto }}"></span>
                            @endforeach
                        </div>
                        <div style="font-size:9px; color:#555; text-align:center; margin-top:2px; font-weight:500;">
                            @if($cultosDelDia->count() == 1)
                                {{ $cultosDelDia->first()->caracter_nombre }}
                            @else
                                {{ $cultosDelDia->pluck('caracter_nombre')->implode(', ') }}
                            @endif
                        </div>
                    </div>
                    @if($esFuturo)
                    {{-- Área clickeable: Programar nuevo culto --}}
                    <div onclick="abrirModalCrear('{{ $fechaString }}')"
                         style="text-align:center; margin-top:4px; cursor:pointer; padding:4px; border-radius:6px; transition:background 0.2s;"
                         onmouseover="this.style.background='#E8F0FB'" onmouseout="this.style.background='transparent'">
                        <span style="font-size:10px; color:#1A4FA8; font-weight:600;">➕ Programar culto</span>
                    </div>
                    @endif
                    @elseif($esFuturo)
                    {{-- Área clickeable: Programar culto en día vacío --}}
                    <div onclick="abrirModalCrear('{{ $fechaString }}')"
                         style="text-align:center; margin-top:8px; cursor:pointer; padding:8px; border-radius:6px; transition:background 0.2s;"
                         onmouseover="this.style.background='#E8F0FB'" onmouseout="this.style.background='transparent'">
                        <span style="font-size:10px; color:#1A4FA8; font-weight:600;">➕ Programar culto</span>
                    </div>
                    @endif
                </div>
            @endfor

            @php
                $celdasVacias = (7 - (($primerDiaSemana + $totalDiasMes) % 7)) % 7;
            @endphp
            @for($i = 0; $i < $celdasVacias; $i++)
            <div style="min-height:100px; background:#F8F9FA; border-bottom:1px solid #eee; border-right:1px solid #eee; width:100%; box-sizing:border-box;"></div>
            @endfor
        </div>
    </div>
    </div>{{-- /cal-mensual --}}

    {{-- ==================== AGENDA MÓVIL (visible solo en celular) ==================== --}}
    <div class="cal-agenda">
        @php
            $cultosPorDiaOrdenados = $cultosDelMesRaw->sortBy('fecha')->groupBy(function($c) {
                return $c->fecha->format('Y-m-d');
            });
        @endphp

        @if($cultosPorDiaOrdenados->isEmpty())
            <div style="text-align:center; padding:2rem; background:#fff; border:1px solid #D1DCF0; border-radius:10px; color:#777;">
                <div style="font-size:36px; margin-bottom:0.5rem;">&#128197;</div>
                <p style="margin:0; font-size:14px;">No hay cultos programados para {{ $nombreMes }} {{ $anioActual }}.</p>
            </div>
        @else
            @foreach($cultosPorDiaOrdenados as $fechaString => $cultosDelDiaAgenda)
                @php
                    $fechaObj = \Carbon\Carbon::parse($fechaString);
                    $diaSemanaCorto = strtoupper($fechaObj->locale('es')->isoFormat('ddd'));
                    $diaMes = $fechaObj->day;
                    $mesCorto = strtolower($fechaObj->locale('es')->isoFormat('MMM'));
                    $esHoy = $fechaObj->isToday();
                    $esPasado = $fechaObj->lt(\Carbon\Carbon::today());
                    $totalAsignacionesAgenda = $cultosDelDiaAgenda->sum(fn($c) => $c->asignaciones->where('estado','asignado')->count());
                @endphp
                <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; margin-bottom:0.75rem; overflow:hidden;">
                    {{-- Header del día --}}
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; background:#F8F9FA; border-bottom:1px solid #eee;">
                        <div>
                            <div style="font-size:13px; color:#0D2F6E; font-weight:700;">
                                {{ $diaSemanaCorto }} {{ $diaMes }} {{ $mesCorto }}
                            </div>
                            <div style="font-size:11px; color:#777;">
                                {{ $cultosDelDiaAgenda->count() }} culto(s) &middot; {{ $totalAsignacionesAgenda }} servidor(es)
                            </div>
                        </div>
                        @if($esHoy)
                            <span class="pill pill-pendiente" style="font-size:10px;">Hoy</span>
                        @elseif($esPasado)
                            <span class="pill pill-activo" style="font-size:10px;">Realizado</span>
                        @endif
                    </div>

                    {{-- Lista de cultos del día --}}
                    @foreach($cultosDelDiaAgenda as $culto)
                        @php
                            $colorCulto = $coloresPorCaracter[$culto->caracter] ?? '#1A7A4A';
                            $numAsignados = $culto->asignaciones->where('estado','asignado')->count();
                        @endphp
                        <div style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-bottom:1px solid #f0f0f0;">
                            <div style="width:6px; height:36px; border-radius:3px; background:{{ $colorCulto }}; flex-shrink:0;"></div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:14px; font-weight:600; color:#0D2F6E; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $culto->nombre_culto }}
                                </div>
                                <div style="font-size:11px; color:#777; margin-top:2px;">
                                    {{ $culto->fecha->format('g:i A') }} &middot; {{ $culto->caracter_nombre }}
                                    @if($numAsignados > 0)
                                        &middot; {{ $numAsignados }} servidor(es)
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('cultos.show', $culto->id) }}"
                               style="flex-shrink:0; padding:10px 14px; background:#1A4FA8; color:#fff; text-decoration:none; border-radius:7px; font-size:12px; font-weight:600; min-height:40px; display:inline-flex; align-items:center;">
                                Ver
                            </a>
                        </div>
                    @endforeach

                    {{-- Footer: ver todos los cultos del día en el modal compartido --}}
                    <button onclick="verCultosDia('{{ $fechaString }}', true)"
                            type="button"
                            style="display:block; width:100%; padding:0.75rem; background:#F4F6FA; border:none; border-top:1px solid #eee; color:#1A4FA8; font-size:12px; font-weight:600; cursor:pointer; min-height:44px;">
                        &#128203; Ver todos los cultos de este día
                    </button>
                </div>
            @endforeach
        @endif

        {{-- Acceso rápido para programar en días futuros del mes --}}
        @php
            $hoyCal = \Carbon\Carbon::today();
            $ultimoDiaMesCal = \Carbon\Carbon::create($anioActual, $mesActual, 1)->endOfMonth();
            $diasDisponibles = [];
            for($f = $hoyCal->copy(); $f <= $ultimoDiaMesCal; $f = $f->addDay()) {
                if(!isset($cultosPorDia[$f->format('Y-m-d')])) {
                    $diasDisponibles[] = $f->copy();
                    if(count($diasDisponibles) >= 8) break;
                }
            }
        @endphp
        @if(!empty($diasDisponibles))
        <div style="margin-top:1rem; padding:1rem; background:#fff; border:1px dashed #D1DCF0; border-radius:10px;">
            <div style="font-size:13px; font-weight:600; color:#0D2F6E; margin-bottom:0.6rem;">
                &#10133; Programar en otro día
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(85px, 1fr)); gap:6px;">
                @foreach($diasDisponibles as $fecha)
                    <button onclick="abrirModalCrear('{{ $fecha->format('Y-m-d') }}')"
                            type="button"
                            style="padding:10px 4px; background:#F4F6FA; border:1px solid #D1DCF0; border-radius:7px; cursor:pointer; font-size:11px; font-weight:600; color:#1A4FA8; min-height:44px;">
                        {{ $fecha->locale('es')->isoFormat('ddd D') }}
                    </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>{{-- /cal-agenda --}}

    {{-- Resumen del mes --}}
    <div style="margin-top:1rem; padding:1rem; background:#fff; border:1px solid #D1DCF0; border-radius:10px;">
        <h3 style="font-size:16px; font-weight:600; color:#0D2F6E; margin:0 0 1rem 0;">
            &#128197; Resumen de {{ $nombreMes }} {{ $anioActual }}
        </h3>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem;">
            <div style="text-align:center; padding:1rem; background:#F8F9FA; border-radius:8px;">
                <div style="font-size:24px; font-weight:700; color:#1A4FA8;">{{ $cultosDelMesRaw->count() }}</div>
                <div style="font-size:12px; color:#555;">Total cultos</div>
            </div>
            <div style="text-align:center; padding:1rem; background:#F8F9FA; border-radius:8px;">
                @php $cultosRealizados = $cultosDelMesRaw->filter(fn($c) => $c->fecha->isPast())->count(); @endphp
                <div style="font-size:24px; font-weight:700; color:#1A7A4A;">{{ $cultosRealizados }}</div>
                <div style="font-size:12px; color:#555;">Realizados</div>
            </div>
            <div style="text-align:center; padding:1rem; background:#F8F9FA; border-radius:8px;">
                @php $cultosFuturos = $cultosDelMesRaw->filter(fn($c) => $c->fecha->isFuture())->count(); @endphp
                <div style="font-size:24px; font-weight:700; color:#F5C518;">{{ $cultosFuturos }}</div>
                <div style="font-size:12px; color:#555;">Por realizar</div>
            </div>
            <div style="text-align:center; padding:1rem; background:#F8F9FA; border-radius:8px;">
                @php $cultosHoy = $cultosDelMesRaw->filter(fn($c) => $c->fecha->isToday())->count(); @endphp
                <div style="font-size:24px; font-weight:700; color:#C0392B;">{{ $cultosHoy }}</div>
                <div style="font-size:12px; color:#555;">Hoy</div>
            </div>
        </div>
    </div>

    {{-- Leyenda de colores por tipo de culto --}}
    <div style="margin-top:1rem; padding:1rem; background:#fff; border:1px solid #D1DCF0; border-radius:10px;">
        <h3 style="font-size:14px; font-weight:600; color:#0D2F6E; margin:0 0 0.75rem 0;">
            &#127912; Tipos de culto
        </h3>
        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:0.75rem;">
            @foreach($coloresPorCaracter as $caracter => $color)
            @php
                $nombreCaracter = \App\Models\Culto::caracteres()[$caracter] ?? ucfirst($caracter);
                $count = $cultosDelMesRaw->where('caracter', $caracter)->count();
            @endphp
            <div style="display:flex; align-items:center; gap:6px; padding:0.5rem; background:#F8F9FA; border-radius:6px; font-size:12px;">
                <span style="width:12px; height:12px; border-radius:50%; background:{{ $color }}; flex-shrink:0;"></span>
                <span style="color:#555; flex:1;">{{ $nombreCaracter }}</span>
                <span class="pill pill-pendiente" style="font-size:10px;">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Modal para crear culto --}}
<div id="modal-crear-culto" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.50); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:2rem; width:100%; max-width:520px; max-height:90vh; overflow-y:auto;">
        <div class="form-card-header" style="margin-bottom:1.25rem;">
            <h2 style="font-size:16px; color:#0D2F6E;">Programar nuevo culto</h2>
            <button onclick="cerrarModalCrear()" style="background:transparent; border:none; font-size:20px; color:#999; cursor:pointer; padding:4px 8px; border-radius:4px;" onmouseover="this.style.background='#f4f6fa'" onmouseout="this.style.background='transparent'">
                &#10005;
            </button>
        </div>
        <form method="POST" action="{{ route('cultos.store') }}" id="form-crear-culto" onsubmit="return validarHorarioCulto(event)">
            @csrf
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Nombre del culto <span class="requerido">*</span></label>
                    <input type="text" name="nombre_culto" id="crear-nombre"
                           placeholder="Ej: Culto Dominical" required onchange="marcarFormularioModificado()">
                </div>
                <div class="form-group">
                    <label>Carácter del culto <span class="requerido">*</span></label>
                    <select name="caracter" id="crear-caracter" required onchange="marcarFormularioModificado()">
                        <option value="">— Seleccionar tipo —</option>
                        @foreach(\App\Models\Culto::caracteres() as $key => $nombre)
                            <option value="{{ $key }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha y hora <span class="requerido">*</span></label>
                    <input type="datetime-local" name="fecha" id="crear-fecha" required onchange="actualizarResumenFecha(); marcarFormularioModificado()">
                </div>
                <div class="form-group" style="grid-column: span 3;">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" id="crear-descripcion" placeholder="Descripción opcional" onchange="marcarFormularioModificado()">
                </div>
            </div>

            {{-- Resumen de fecha y hora seleccionada --}}
            <div id="resumen-fecha" style="background:#F8F9FA; border:1px solid #D1DCF0; border-radius:7px; padding:12px; margin-bottom:1rem; display:none;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <input type="checkbox" id="confirmar-fecha" required
                           style="width:18px; height:18px; cursor:pointer;">
                    <label for="confirmar-fecha" style="font-size:13px; color:#333; cursor:pointer; margin:0;">
                        Confirmo que la fecha y hora son correctas:
                        <strong id="resumen-texto" style="color:#0D2F6E;"></strong>
                    </label>
                </div>
            </div>

            <div class="form-acciones">
                <button type="button" onclick="confirmarCerrarModalCrear()" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario" id="btn-guardar" disabled>&#10003; Guardar culto</button>
            </div>
        </form>
    </div>
</div>

@include('dashboard.modals_cultos_dia')

<style>
.form-card {
    background: #fff;
    border: 1px solid #D1DCF0;
    border-radius: 10px;
    border-top: 4px solid #F5C518;
    padding: 0;
}

.form-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0;
    margin: 0;
}

.form-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

label {
    font-size: 13px;
    font-weight: 600;
    color: #3a4255;
}

.requerido {
    color: #C0392B;
}

input[type="text"],
input[type="datetime-local"],
select {
    padding: 9px 12px;
    border: 1px solid #D1DCF0;
    border-radius: 7px;
    font-size: 14px;
    color: #1a1a2e;
    outline: none;
    transition: border-color 0.2s;
    width: 100%;
}

input:focus,
select:focus {
    border-color: #1A4FA8;
}

.form-acciones {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

#btn-guardar:disabled {
    background: #ccc !important;
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 600px) {
    .form-grid-3 {
        grid-template-columns: 1fr;
    }

    /* Modales responsive */
    #modal-crear-culto > div,
    #modal-cultos-dia > div {
        margin: 0.75rem;
        max-width: calc(100% - 1.5rem) !important;
    }
    #modal-crear-culto input[type="text"],
    #modal-crear-culto input[type="datetime-local"],
    #modal-crear-culto select,
    #modal-cultos-dia input,
    #modal-cultos-dia select {
        font-size: 16px !important;
    }
    #modal-crear-culto .form-acciones {
        flex-direction: column;
    }
    #modal-crear-culto .form-acciones .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// URLs base inyectadas desde el servidor (respetan el subdirectorio de la app)
window.APP_URLS = {
    cultosDia: @json(url('cultos/dia')),
    cultosBase: @json(url('cultos')),
};
</script>
<script src="{{ asset('js/funciones-calendario.js') }}"></script>
<script>
function cambiarMes(mes, anio) {
    const url = new URL(window.location);
    url.searchParams.set('mes', mes);
    url.searchParams.set('anio', anio);
    window.location.href = url.toString();
}

// Cerrar modales al tocar fuera
document.getElementById('modal-crear-culto').addEventListener('click', function(e) {
    if (e.target === this) {
        if (typeof confirmarCerrarModalCrear === 'function') confirmarCerrarModalCrear();
    }
});
document.getElementById('modal-cultos-dia').addEventListener('click', function(e) {
    if (e.target === this) {
        if (typeof cerrarModalCultos === 'function') cerrarModalCultos();
    }
});
</script>

@endsection