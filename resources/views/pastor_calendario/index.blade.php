@extends('layouts.app')
@section('titulo', 'Calendario Pastoral')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Supervisión Pastoral</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Inicio
    </a>
    <a href="{{ route('pastor.calendario.index') }}" class="activo">
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
    $nombreMes = match($mes) {
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    };

    $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

    $primerDiaMes = date('N', strtotime("$anio-$mes-01"));
    $totalDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

    $mesAnterior = $mes == 1 ? 12 : $mes - 1;
    $anioAnterior = $mes == 1 ? $anio - 1 : $anio;

    $mesSiguiente = $mes == 12 ? 1 : $mes + 1;
    $anioSiguiente = $mes == 12 ? $anio + 1 : $anio;

    // Agrupar cultos por día
    $cultosPorDia = [];
    foreach($cultos as $culto) {
        $dia = $culto->fecha->format('j');
        if(!isset($cultosPorDia[$dia])) {
            $cultosPorDia[$dia] = [];
        }
        $cultosPorDia[$dia][] = $culto;
    }
@endphp

<div class="page-header">
    <div>
        <h1>&#128197; Calendario Pastoral</h1>
        <p>Supervisión de cultos programados y orientación espiritual</p>
    </div>
</div>

{{-- NAVEGACIÓN DEL MES --}}
<div style="background:linear-gradient(135deg, #1A7A4A 0%, #0F5C32 100%); border-radius:12px; padding:1.5rem; margin-bottom:2rem; color:#fff;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <a href="{{ route('pastor.calendario.index', ['mes' => $mesAnterior, 'anio' => $anioAnterior]) }}"
           style="color:#fff; text-decoration:none; font-size:18px; padding:0.5rem 1rem; border-radius:8px; transition:background 0.2s;"
           onmouseover="this.style.background='rgba(255,255,255,0.1)';"
           onmouseout="this.style.background='transparent';">
            &#8592; Mes anterior
        </a>

        <div style="text-align:center;">
            <h2 style="font-size:28px; font-weight:700; margin:0;">
                {{ $nombreMes }} {{ $anio }}
            </h2>
            <p style="font-size:14px; opacity:0.9; margin:0.5rem 0 0 0;">
                {{ $cultos->count() }} culto(s) programado(s)
            </p>
        </div>

        <a href="{{ route('pastor.calendario.index', ['mes' => $mesSiguiente, 'anio' => $anioSiguiente]) }}"
           style="color:#fff; text-decoration:none; font-size:18px; padding:0.5rem 1rem; border-radius:8px; transition:background 0.2s;"
           onmouseover="this.style.background='rgba(255,255,255,0.1)';"
           onmouseout="this.style.background='transparent';">
            Mes siguiente &#8594;
        </a>
    </div>
</div>

{{-- LEYENDA DE TIPOS DE CULTO --}}
<div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; padding:1rem; margin-bottom:1.5rem;">
    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
        <span style="font-size:16px;">&#128300;</span>
        <strong style="color:#0D2F6E; font-size:14px;">Tipos de culto:</strong>
    </div>
    <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
        @foreach(\App\Models\Culto::caracteres() as $key => $nombre)
        <div style="display:flex; align-items:center; gap:0.5rem; font-size:12px;">
            <div style="width:16px; height:16px; border-radius:50%; background:{{ $key === 'evangelistico' ? '#E74C3C' : ($key === 'escuela_dominical' ? '#3498DB' : '#F5C518') }};"></div>
            <span>{{ $nombre }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- CALENDARIO GRID (visible solo en desktop/tablet, oculto en móvil) --}}
<div class="cal-mensual">
{{-- CALENDARIO --}}
<div style="background:#fff; border:1px solid #D1DCF0; border-radius:12px; overflow:hidden;">
    {{-- Días de la semana --}}
    <div style="display:grid; grid-template-columns:repeat(7, 1fr); background:#F8F9FA; border-bottom:1px solid #D1DCF0;">
        @foreach($diasSemana as $dia)
        <div style="padding:1rem; text-align:center; font-weight:600; color:#555; font-size:13px;">
            {{ $dia }}
        </div>
        @endforeach
    </div>

    {{-- Días del mes --}}
    <div style="display:grid; grid-template-columns:repeat(7, 1fr);">
        {{-- Días vacíos antes del primer día del mes --}}
        @for($i = 1; $i < $primerDiaMes; $i++)
        <div style="padding:1rem; background:#FAFBFC; border-right:1px solid #F0F0F0; border-bottom:1px solid #F0F0F0; min-height:80px;">
            <span style="color:#CCC; font-size:14px;">&nbsp;</span>
        </div>
        @endfor

        {{-- Días del mes --}}
        @for($dia = 1; $dia <= $totalDiasMes; $dia++)
        @php
            $fechaActual = "$anio-$mes-" . str_pad($dia, 2, '0', STR_PAD_LEFT);
            $esHoy = $fechaActual === now()->format('Y-m-d');
            $tieneCultos = isset($cultosPorDia[$dia]);
            $fondoDia = $esHoy ? '#FFF8DC' : ($tieneCultos ? '#E8F5E9' : '#fff');
            $cursor = $tieneCultos ? 'cursor:pointer;' : 'cursor:default;';
        @endphp
        <div style="padding:0.5rem; background:{{ $fondoDia }}; border-right:1px solid #F0F0F0; border-bottom:1px solid #F0F0F0; min-height:80px; {{ $cursor }} transition:background 0.2s;"
             @if($tieneCultos)
             onclick="verCultosDia('{{ $fechaActual }}')"
             onmouseover="this.style.background='#D1E7DD';"
             onmouseout="this.style.background='{{ $fondoDia }}';"
             @endif>
            <div style="display:flex; justify-content:space-between; align-items:start;">
                <span style="font-size:14px; font-weight:{{ $esHoy ? '700' : '400' }}; color:{{ $esHoy ? '#1A7A4A' : '#555' }};">
                    {{ $dia }}
                </span>
                @if($esHoy)
                <span class="pill pill-pendiente" style="font-size:9px; padding:2px 6px;">Hoy</span>
                @endif
            </div>

            @if($tieneCultos)
            <div style="margin-top:0.5rem;">
                @foreach($cultosPorDia[$dia] as $culto)
                <div style="font-size:10px; padding:3px 6px; border-radius:4px; margin-bottom:2px; background:{{ $culto->caracter === 'evangelistico' ? '#E74C3C' : ($culto->caracter === 'escuela_dominical' ? '#3498DB' : '#F5C518') }}; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; truncate: true;">
                    {{ $culto->fecha->format('g:i A') }} {{ Str::limit($culto->nombre_culto, 15) }}
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endfor
    </div>
</div>
</div>{{-- /cal-mensual --}}

{{-- ==================== AGENDA MÓVIL (visible solo en celular) ==================== --}}
<div class="cal-agenda">
    @php
        // Reagrupar cultos por fecha Y-m-d (en la vista del pastor ya están filtrados a activos)
        $cultosAgendaPastor = $cultos->sortBy('fecha')->groupBy(function($c) {
            return $c->fecha->format('Y-m-d');
        });
    @endphp

    @if($cultosAgendaPastor->isEmpty())
        <div style="text-align:center; padding:2rem; background:#fff; border:1px solid #D1DCF0; border-radius:10px; color:#777;">
            <div style="font-size:36px; margin-bottom:0.5rem;">&#128197;</div>
            <p style="margin:0; font-size:14px;">No hay cultos programados para {{ $nombreMes }} {{ $anio }}.</p>
        </div>
    @else
        @foreach($cultosAgendaPastor as $fechaString => $cultosDelDiaAgenda)
            @php
                $fechaObj = \Carbon\Carbon::parse($fechaString);
                $diaSemanaCorto = strtoupper($fechaObj->locale('es')->isoFormat('ddd'));
                $diaMes = $fechaObj->day;
                $mesCorto = strtolower($fechaObj->locale('es')->isoFormat('MMM'));
                $esHoy = $fechaObj->isToday();
                $esPasado = $fechaObj->lt(\Carbon\Carbon::today());
                $totalAsignacionesAgenda = $cultosDelDiaAgenda->sum(fn($c) => $c->asignaciones->count());
                $totalMensajesAgenda = $cultosDelDiaAgenda->sum(fn($c) => $c->mensajesPastorales->count());
            @endphp
            <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; margin-bottom:0.75rem; overflow:hidden;">
                {{-- Header del día --}}
                <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; background:#F8F9FA; border-bottom:1px solid #eee;">
                    <div>
                        <div style="font-size:13px; color:#0F5C32; font-weight:700;">
                            {{ $diaSemanaCorto }} {{ $diaMes }} {{ $mesCorto }}
                        </div>
                        <div style="font-size:11px; color:#777;">
                            {{ $cultosDelDiaAgenda->count() }} culto(s) &middot; {{ $totalAsignacionesAgenda }} servidor(es) &middot; {{ $totalMensajesAgenda }} mensaje(s)
                        </div>
                    </div>
                    @if($esHoy)
                        <span class="pill" style="background:#1A7A4A; color:#fff; font-size:10px;">Hoy</span>
                    @elseif($esPasado)
                        <span class="pill pill-activo" style="font-size:10px;">Realizado</span>
                    @endif
                </div>

                {{-- Lista de cultos del día --}}
                @foreach($cultosDelDiaAgenda as $culto)
                    @php
                        $numMensajes = $culto->mensajesPastorales->count();
                    @endphp
                    <div style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; border-bottom:1px solid #f0f0f0;">
                        <div style="width:6px; height:36px; border-radius:3px; background:#1A7A4A; flex-shrink:0;"></div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:14px; font-weight:600; color:#0F5C32; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $culto->nombre_culto }}
                            </div>
                            <div style="font-size:11px; color:#777; margin-top:2px;">
                                {{ $culto->fecha->format('g:i A') }} &middot; {{ $culto->caracter_nombre }}
                                @if($numMensajes > 0)
                                    &middot; &#128172; {{ $numMensajes }}
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('pastor.calendario.show', $culto->id) }}"
                           style="flex-shrink:0; padding:10px 14px; background:#1A7A4A; color:#fff; text-decoration:none; border-radius:7px; font-size:12px; font-weight:600; min-height:40px; display:inline-flex; align-items:center;">
                            Ver
                        </a>
                    </div>
                @endforeach

                {{-- Footer: ver todos los cultos del día en el modal pastoral --}}
                <button onclick="verCultosDia('{{ $fechaString }}')"
                        type="button"
                        style="display:block; width:100%; padding:0.75rem; background:#F4F8F5; border:none; border-top:1px solid #eee; color:#1A7A4A; font-size:12px; font-weight:600; cursor:pointer; min-height:44px;">
                    &#128203; Ver todos los cultos de este día
                </button>
            </div>
        @endforeach
    @endif
</div>{{-- /cal-agenda --}}

{{-- MODAL DE DETALLES DE CULTO --}}
<div id="modal-culto" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:999; align-items:center; justify-content:center; overflow-y:auto;">
    <div style="background:#fff; border-radius:16px; width:100%; max-width:700px; max-height:90vh; overflow-y:auto; margin:0.75rem; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
        <div id="modal-content" style="padding:2rem;">
            <div style="text-align:center; padding:2rem;">
                <div style="font-size:48px; margin-bottom:1rem;">&#128197;</div>
                <p style="color:#777; font-size:16px;">Cargando información del culto...</p>
            </div>
        </div>
    </div>
</div>

<script>
function verCultosDia(fecha) {
    // Mostrar modal con loading
    document.getElementById('modal-culto').style.display = 'flex';

    // Hacer solicitud AJAX para obtener cultos del día
    fetch(`{{ route('pastor.calendario.dia', ':fecha') }}`.replace(':fecha', fecha))
        .then(response => response.json())
        .then(data => {
            if (data.cultos && data.cultos.length > 0) {
                mostrarCultos(data.cultos);
            } else {
                mostrarSinCultos(fecha);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError();
        });
}

function mostrarCultos(cultos) {
    let html = `
        <div style="background:linear-gradient(135deg, #1A7A4A 0%, #0F5C32 100%); border-radius:16px 16px 0 0; padding:1.25rem; color:#fff;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="font-size:18px; font-weight:700; margin:0 0 0.25rem 0;">
                        ${cultos.length} Culto(s) Programado(s)
                    </h2>
                    <p style="font-size:13px; opacity:0.9; margin:0;">
                        ${cultos[0].fecha}
                    </p>
                </div>
                <button onclick="cerrarModal()" style="background:rgba(255,255,255,0.2); border:none; color:#fff; font-size:20px; cursor:pointer; min-width:44px; min-height:44px; display:flex; align-items:center; justify-content:center; border-radius:8px;">
                    &times;
                </button>
            </div>
        </div>
    `;

    html += '<div style="padding:1.25rem;">';

    cultos.forEach((culto, index) => {
        const estadoClass = culto.estado === 'hoy' ? 'background:#FFF8DC; color:#0D2F6E;' : 'background:#E8F5E9; color:#1A7A4A;';

        html += `
            <div style="background:#F8F9FA; border:1px solid #D1DCF0; border-radius:10px; overflow:hidden; margin-bottom:1rem;">
                <div style="background:${culto.tipo === 'Culto Evangelístico' ? '#E74C3C' : (culto.tipo === 'Escuela Dominical' ? '#3498DB' : '#F5C518')}; color:#fff; padding:1rem;">
                    <h3 style="font-size:16px; font-weight:600; margin:0;">${culto.nombre_culto}</h3>
                    <p style="font-size:13px; margin:0.5rem 0 0 0; opacity:0.9;">
                        ${culto.hora} &mdash; ${culto.tipo}
                    </p>
                </div>
                <div style="padding:1rem;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:6px;">
                        <span style="font-size:12px; color:#777;">${culto.total_asignaciones} servidor(es)</span>
                        <span class="pill" style="${estadoClass} font-size:11px; padding:4px 10px; border-radius:15px;">${culto.estado.toUpperCase()}</span>
                    </div>
                    ${culto.ultimo_mensaje ? `
                    <div style="background:#FFF8DC; border-left:4px solid #F5C518; padding:0.75rem; border-radius:7px; margin-bottom:1rem;">
                        <p style="font-size:13px; color:#555; margin:0; font-style:italic;">
                            "${culto.ultimo_mensaje}"
                        </p>
                    </div>
                    ` : ''}
                    <a href="/pastor/calendario/${culto.id}"
                       style="display:block; text-align:center; color:#1A7A4A; text-decoration:none; font-size:14px; font-weight:500; padding:0.75rem 1rem; background:#E8F5E9; border-radius:7px; min-height:44px; line-height:1.4;">
                        &#128172; Ver detalles y mensajes pastorales
                    </a>
                </div>
            </div>
        `;
    });

    html += '</div>';

    document.getElementById('modal-content').innerHTML = html;
}

function mostrarSinCultos(fecha) {
    const [anio, mes, dia] = fecha.split('-');
    const fechaFormateada = new Date(anio, mes - 1, dia).toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    let html = `
        <div style="background:linear-gradient(135deg, #1A7A4A 0%, #0F5C32 100%); border-radius:16px 16px 0 0; padding:1.25rem; color:#fff;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="font-size:18px; font-weight:700; margin:0;">Sin Cultos Programados</h2>
                    <p style="font-size:13px; opacity:0.9; margin:0.25rem 0 0 0;">
                        ${fechaFormateada}
                    </p>
                </div>
                <button onclick="cerrarModal()" style="background:rgba(255,255,255,0.2); border:none; color:#fff; font-size:20px; cursor:pointer; min-width:44px; min-height:44px; display:flex; align-items:center; justify-content:center; border-radius:8px;">
                    &times;
                </button>
            </div>
        </div>
        <div style="padding:1.5rem; text-align:center;">
            <p style="color:#777; font-size:14px; margin:0;">
                No hay cultos programados para este día. Puedes programar uno contactando al secretario general.
            </p>
        </div>
    `;

    document.getElementById('modal-content').innerHTML = html;
}

function mostrarError() {
    let html = `
        <div style="background:#E74C3C; border-radius:16px 16px 0 0; padding:1.25rem; color:#fff;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h2 style="font-size:18px; font-weight:700; margin:0;">Error</h2>
                    <p style="font-size:13px; margin:0.25rem 0 0 0; opacity:0.9;">
                        No se pudo cargar la información
                    </p>
                </div>
                <button onclick="cerrarModal()" style="background:rgba(255,255,255,0.2); border:none; color:#fff; font-size:20px; cursor:pointer; min-width:44px; min-height:44px; display:flex; align-items:center; justify-content:center; border-radius:8px;">
                    &times;
                </button>
            </div>
        </div>
        <div style="padding:1.5rem; text-align:center;">
            <p style="color:#777; font-size:14px; margin:0;">
                Por favor, intenta nuevamente más tarde.
            </p>
        </div>
    `;

    document.getElementById('modal-content').innerHTML = html;
}

function cerrarModal() {
    document.getElementById('modal-culto').style.display = 'none';
}

// Cerrar modal con ESC
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') cerrarModal();
});

// Cerrar modal al hacer clic fuera
document.getElementById('modal-culto').addEventListener('click', e => {
    if (e.target.id === 'modal-culto') {
        cerrarModal();
    }
});
</script>

<style>
@media (max-width: 600px) {
    #modal-culto > div {
        margin: 0.5rem;
        max-width: calc(100% - 1rem) !important;
        border-radius: 12px;
    }
    #modal-culto h2 { font-size: 18px !important; }
    #modal-culto h3 { font-size: 15px !important; }
}
</style>

@endsection