@extends('layouts.app')
@section('titulo', 'Panel — Líder de Comité')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Navegacion</p>
    <a href="{{ route('dashboard') }}" class="activo">
        <span class="icono">&#127968;</span> Dashboard
    </a>

    <a href="{{ route('servidores.index') }}"
       class="{{ request()->routeIs('servidores*') ? 'activo' : '' }}">
        <span class="icono">&#128101;</span> Ver servidores
    </a>

    <a href="{{ route('cultos.index') }}"
       class="{{ request()->routeIs('cultos*') ? 'activo' : '' }}">
        <span class="icono">&#128197;</span> Gestionar cultos
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
    $proximoCulto = \App\Models\Culto::whereDate('fecha', '>=', now())
        ->orderBy('fecha')
        ->first();
@endphp

<div class="page-header">
    <h1>Hola, {{ $usuario->nombre }}</h1>
    <p>Panel de Líder de Comité &mdash; {{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
</div>

<div class="stats-grid">
    <div class="stat-card azul">
        <span class="stat-label">Servidores disponibles</span>
        <span class="stat-valor">{{ $servidoresActivos }}</span>
        <span class="stat-sub">Para asignar en cultos</span>
    </div>
    <div class="stat-card amarillo">
        <span class="stat-label">Próximo culto</span>
        @if($proximoCulto)
            <span class="stat-valor" style="font-size:16px; padding-top:4px;">
                {{ $proximoCulto->fecha->format('d/m') }}
            </span>
            <span class="stat-sub">{{ $proximoCulto->nombre_culto }}</span>
        @else
            <span class="stat-valor" style="font-size:14px; padding-top:4px;">Sin programar</span>
            <span class="stat-sub">—</span>
        @endif
    </div>
</div>

{{-- Calendario en grande --}}
@php
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

    // Filtrar asignaciones reemplazadas
    $cultosDelMesRaw->each(function ($c) {
        $c->setRelation('asignaciones', $c->asignaciones->where('estado', 'asignado'));
    });

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

<div style="margin-bottom:1.5rem;">
    {{-- Header del calendario --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <button onclick="cambiarMes({{ $mesAnterior->month }}, {{ $mesAnterior->year }})"
                style="padding:10px 20px; background:#0D2F6E; color:#fff; border:none; border-radius:7px; cursor:pointer; font-size:14px;">
            &#8592; Mes anterior
        </button>

        <h2 style="font-size:24px; font-weight:700; color:#0D2F6E; text-align:center; margin:0;">
            {{ $nombreMes }} {{ $anioActual }}
        </h2>

        <button onclick="cambiarMes({{ $mesSiguiente->month }}, {{ $mesSiguiente->year }})"
                style="padding:10px 20px; background:#0D2F6E; color:#fff; border:none; border-radius:7px; cursor:pointer; font-size:14px;">
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
        <div style="display:grid; grid-template-columns:repeat(7, 1fr);">
            @for($i = 0; $i < $primerDiaSemana; $i++)
            <div style="min-height:100px; background:#F8F9FA; border-bottom:1px solid #eee; border-right:1px solid #eee;"></div>
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
                <div style="min-height:100px; background:{{ $bgDia }}; border-bottom:{{ $borderDia }}; border-right:1px solid #eee; padding:8px; position:relative;">
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
            <div style="min-height:100px; background:#F8F9FA; border-bottom:1px solid #eee; border-right:1px solid #eee;"></div>
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

{{-- Modal para ver detalles de los cultos del día --}}
<div id="modal-cultos-dia" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.50); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:0; width:100%; max-width:700px; max-height:85vh; overflow-y:auto;">
        <!-- Header del modal -->
        <div style="background:#0D2F6E; padding:1.25rem 1.5rem; border-radius:12px 12px 0 0; position:sticky; top:0; z-index:10;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 id="modal-titulo-fecha" style="font-size:18px; font-weight:600; color:#fff; margin:0;">
                        Cultos del día
                    </h2>
                    <p id="modal-subtitulo-fecha" style="font-size:13px; color:#cbd5e8; margin:4px 0 0 0;"></p>
                </div>
                <button onclick="cerrarModalCultos()" style="background:rgba(255,255,255,0.15); border:none; color:#fff; width:36px; height:36px; border-radius:50%; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center; transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    &times;
                </button>
            </div>
        </div>

        <!-- Contenido del modal -->
        <div id="modal-contenido-cultos" style="padding:1.5rem;">
            <!-- Aquí se cargarán los cultos dinámicamente -->
        </div>
    </div>
</div>

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
// Variable para rastrear si el formulario ha sido modificado
let formularioModificado = false;
// Variable para almacenar los cultos del día actual
let cultosDelDiaActual = [];

// URLs base (respetan el subdirectorio de la app, p. ej. /App_Serv_Ipuc/public)
const APP_URLS = {
    cultosDia: @json(url('cultos/dia')),
    cultosBase: @json(url('cultos')),
};

function marcarFormularioModificado() {
    formularioModificado = true;
}

function abrirModalCrear(fecha) {
    const modal = document.getElementById('modal-crear-culto');
    const inputFecha = document.getElementById('crear-fecha');

    // Establecer la fecha y hora por defecto (6:45 PM)
    const fechaObj = new Date(fecha + 'T18:45:00');
    const year = fechaObj.getFullYear();
    const month = String(fechaObj.getMonth() + 1).padStart(2, '0');
    const day = String(fechaObj.getDate()).padStart(2, '0');
    const hours = String(fechaObj.getHours()).padStart(2, '0');
    const minutes = String(fechaObj.getMinutes()).padStart(2, '0');

    inputFecha.value = `${year}-${month}-${day}T${hours}:${minutes}`;

    // Cargar cultos del día para validación de horarios
    cargarCultosDelDia(fecha);

    // Resetear confirmación y estado
    formularioModificado = false;
    document.getElementById('confirmar-fecha').checked = false;
    document.getElementById('btn-guardar').disabled = true;
    document.getElementById('resumen-fecha').style.display = 'block';
    actualizarResumenFecha();

    modal.style.display = 'flex';
}

function cargarCultosDelDia(fecha) {
    fetch(`${APP_URLS.cultosDia}/${fecha}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        cultosDelDiaActual = data.cultos || [];
    })
    .catch(error => {
        console.error('Error al cargar cultos del día:', error);
        cultosDelDiaActual = [];
    });
}

function validarHorarioCulto(event) {
    const inputFecha = document.getElementById('crear-fecha');
    const fechaSeleccionada = new Date(inputFecha.value);

    // Obtener hora y minutos de la fecha seleccionada
    const horaSeleccionada = fechaSeleccionada.getHours().toString().padStart(2, '0');
    const minutosSeleccionados = fechaSeleccionada.getMinutes().toString().padStart(2, '0');
    const horaSeleccionadaStr = `${horaSeleccionada}:${minutosSeleccionados}`;

    // Obtener la fecha en formato YYYY-MM-DD
    const fechaSeleccionadaStr = fechaSeleccionada.toISOString().split('T')[0];

    // Verificar si hay algún culto en la misma fecha y hora
    const horarioOcupado = cultosDelDiaActual.find(culto => {
        return culto.fecha === fechaSeleccionadaStr && culto.hora_24 === horaSeleccionadaStr;
    });

    if (horarioOcupado) {
        alert(`⚠️ Ya existe un culto programado para esta hora:\n\n${horarioOcupado.nombre_culto}\n${horarioOcupado.tipo} - ${horarioOcupado.hora}\n\nPor favor, selecciona una hora diferente.`);
        event.preventDefault();
        return false;
    }

    return true;
}

function actualizarResumenFecha() {
    const inputFecha = document.getElementById('crear-fecha');
    const resumenFecha = document.getElementById('resumen-fecha');
    const resumenTexto = document.getElementById('resumen-texto');
    const confirmCheckbox = document.getElementById('confirmar-fecha');
    const btnGuardar = document.getElementById('btn-guardar');

    if (!inputFecha.value) {
        resumenFecha.style.display = 'none';
        return;
    }

    // Formatear la fecha para mostrar
    const fechaObj = new Date(inputFecha.value);
    const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    const fechaFormateada = fechaObj.toLocaleDateString('es-ES', opciones);

    resumenTexto.textContent = fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1);
    resumenFecha.style.display = 'block';

    // Habilitar/deshabilitar botón guardar según checkbox
    confirmCheckbox.addEventListener('change', function() {
        btnGuardar.disabled = !this.checked;
        if (this.checked) {
            btnGuardar.style.background = '#1A4FA8';
            btnGuardar.style.opacity = '1';
            btnGuardar.style.cursor = 'pointer';
        } else {
            btnGuardar.style.background = '#ccc';
            btnGuardar.style.opacity = '0.6';
            btnGuardar.style.cursor = 'not-allowed';
        }
    });
}

function confirmarCerrarModalCrear() {
    if (formularioModificado) {
        if (confirm('¿Estás seguro de que quieres cerrar? Se perderán los datos que has ingresado.')) {
            cerrarModalCrear();
        }
    } else {
        cerrarModalCrear();
    }
}

function cerrarModalCrear() {
    document.getElementById('modal-crear-culto').style.display = 'none';
    document.getElementById('form-crear-culto').reset();
    document.getElementById('confirmar-fecha').checked = false;
    document.getElementById('btn-guardar').disabled = true;
    document.getElementById('resumen-fecha').style.display = 'none';
    formularioModificado = false;
}

function cambiarMes(mes, anio) {
    const url = new URL(window.location);
    url.searchParams.set('mes', mes);
    url.searchParams.set('anio', anio);
    window.location.href = url.toString();
}

function verCultosDia(fecha, tieneCultos) {
    if (!tieneCultos) {
        alert('No hay cultos programados para este día.');
        return;
    }

    const modal = document.getElementById('modal-cultos-dia');
    const contenido = document.getElementById('modal-contenido-cultos');
    const titulo = document.getElementById('modal-titulo-fecha');
    const subtitulo = document.getElementById('modal-subtitulo-fecha');

    const fechaParseada = new Date(fecha + 'T00:00:00');
    const opcionesFecha = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const fechaFormateada = fechaParseada.toLocaleDateString('es-ES', opcionesFecha);

    titulo.textContent = 'Cultos del día';
    subtitulo.textContent = fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1);

    contenido.innerHTML = '<div style="text-align:center; padding:2rem;"><div style="font-size:24px; margin-bottom:1rem;">&#8987;</div><p style="color:#555;">Cargando cultos...</p></div>';
    modal.style.display = 'flex';

    fetch(`${APP_URLS.cultosDia}/${fecha}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.cultos.length === 0) {
                contenido.innerHTML = `
                    <div style="text-align:center; padding:3rem 2rem;">
                        <div style="font-size:48px; margin-bottom:1rem;">&#128533;</div>
                        <p style="font-size:16px; color:#555; margin-bottom:0.5rem;">No hay cultos programados</p>
                        <p style="font-size:13px; color:#999;">No se encontraron cultos para esta fecha</p>
                    </div>
                `;
                return;
            }

            let html = '';
            data.cultos.forEach((culto) => {
                const estado = culto.estado === 'realizado' ? 'Completado' : (culto.estado === 'hoy' ? 'Hoy' : 'Programado');
                const colorEstado = culto.estado === 'realizado' ? '#1A7A4A' : (culto.estado === 'hoy' ? '#F5C518' : '#1A4FA8');

                html += `
                    <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; margin-bottom:1.5rem; overflow:hidden;">
                        <div style="background:${colorEstado}; padding:1rem 1.25rem;">
                            <div style="display:flex; justify-content:space-between; align-items:start;">
                                <div style="flex:1;">
                                    <h3 style="font-size:16px; font-weight:700; color:#fff; margin:0 0 4px 0;">
                                        ${culto.nombre_culto}
                                    </h3>
                                    <div style="display:flex; gap:15px; font-size:12px; color:#fff; opacity:0.9;">
                                        <span>&#128336; ${culto.hora}</span>
                                        <span>&#128100; ${culto.tipo}</span>
                                    </div>
                                </div>
                                <span style="background:rgba(255,255,255,0.2); color:#fff; font-size:11px; font-weight:600; padding:4px 12px; border-radius:12px; text-transform:uppercase; letter-spacing:0.3px;">
                                    ${estado}
                                </span>
                            </div>
                            ${culto.descripcion ? `<p style="font-size:12px; color:#fff; margin:8px 0 0 0; opacity:0.9; font-style:italic;">"${culto.descripcion}"</p>` : ''}
                        </div>

                        <div style="padding:1.25rem;">
                            ${culto.mensaje ? `
                                <div style="background:#FFF8DC; border-left:4px solid #F5C518; padding:12px; border-radius:6px; margin-bottom:1rem;">
                                    <p style="font-size:12px; color:#333; margin:0; line-height:1.6; font-style:italic;">
                                        &#128172; ${culto.mensaje}
                                    </p>
                                    ${culto.mensaje_autor ? `<p style="font-size:10px; color:#777; margin:6px 0 0 0; text-align:right;">— ${culto.mensaje_autor}</p>` : ''}
                                </div>
                            ` : ''}

                            <div style="text-align:right; margin-top:1rem;">
                                <a href="${APP_URLS.cultosBase}/${culto.id}" style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; background:#1A4FA8; color:#fff; text-decoration:none; border-radius:7px; font-size:12px; font-weight:500; transition:opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                    &#128065; Ver detalles completos
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });

            contenido.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            contenido.innerHTML = `
                <div style="text-align:center; padding:3rem 2rem;">
                    <div style="font-size:48px; margin-bottom:1rem;">&#9888;</div>
                    <p style="font-size:16px; color:#C0392B; margin-bottom:0.5rem;">Error al cargar los cultos</p>
                    <p style="font-size:12px; color:#999;">Por favor, intenta nuevamente</p>
                </div>
            `;
        });
}

function cerrarModalCultos() {
    document.getElementById('modal-cultos-dia').style.display = 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        confirmarCerrarModalCrear();
        cerrarModalCultos();
    }
});

document.getElementById('modal-crear-culto').addEventListener('click', function(e) {
    if (e.target === this) {
        confirmarCerrarModalCrear();
    }
});

document.getElementById('modal-cultos-dia').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalCultos();
    }
});

// Cerrar modal crear culto al tocar fuera
document.getElementById('modal-crear-culto').addEventListener('click', function(e) {
    if (e.target === this) {
        if (typeof confirmarCerrarModalCrear === 'function') confirmarCerrarModalCrear();
    }
});
</script>

@endsection
