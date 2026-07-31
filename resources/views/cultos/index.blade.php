@extends('layouts.app')
@section('titulo', 'Gestión de Cultos')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Administración</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Dashboard
    </a>
    <a href="{{ route('servidores.index') }}">
        <span class="icono">&#128101;</span> Servidores
    </a>
    @if(auth()->user()->tieneRol('secretario_general'))
    <a href="{{ route('admin.roles.index') }}">
        <span class="icono">&#128274;</span> Roles
    </a>
    <a href="{{ route('admin.usuarios.index') }}">
        <span class="icono">&#128100;</span> Usuarios
    </a>
    @endif
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
<div class="page-header flex-between" style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
    <div style="flex:1;">
        <h1>Gestión de Cultos</h1>
        <p>Programa y administra los cultos de la iglesia</p>
    </div>
    <button class="btn btn-amarillo" onclick="toggleFormulario()">
        &#43; <span class="hide-mobile">Nuevo culto</span>
    </button>
</div>

{{-- Formulario desplegable --}}
<div id="formulario-crear" style="display:none; margin-bottom:1.5rem;">
    <div class="form-card">
        <div class="form-card-header">
            <h2>Programar nuevo culto</h2>
            <button onclick="toggleFormulario()" class="btn-cerrar">&#10005;</button>
        </div>
        <form method="POST" action="{{ route('cultos.store') }}">
            @csrf

            {{-- Seccion: Informacion del culto --}}
            <div class="form-section">
                <p class="form-section-title">&#128197; Informacion del culto</p>
                <div class="form-grid-responsive">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Nombre del culto <span class="requerido">*</span></label>
                        <input type="text" name="nombre_culto"
                               value="{{ old('nombre_culto') }}"
                               placeholder="Ej: Culto Dominical"
                               class="{{ $errors->has('nombre_culto') ? 'input-error' : '' }} input-touch">
                        @error('nombre_culto')
                            <p class="error-msg">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Tipo de culto <span class="requerido">*</span></label>
                        <select name="caracter" class="{{ $errors->has('caracter') ? 'input-error' : '' }} input-touch">
                            <option value="">— Seleccionar tipo —</option>
                            @foreach(\App\Models\Culto::caracteres() as $key => $nombre)
                                <option value="{{ $key }}" {{ old('caracter') === $key ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('caracter')
                            <p class="error-msg">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Fecha y hora <span class="requerido">*</span></label>
                        <input type="datetime-local" name="fecha"
                               value="{{ old('fecha') }}"
                               class="{{ $errors->has('fecha') ? 'input-error' : '' }} input-touch">
                        @error('fecha')
                            <p class="error-msg">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Descripcion</label>
                        <input type="text" name="descripcion"
                               value="{{ old('descripcion') }}"
                               placeholder="Descripcion opcional del culto"
                               class="input-touch">
                    </div>
                </div>
            </div>

            {{-- Seccion: Asignacion de servidores --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-title-group">
                        <p class="form-section-title">&#128101; Asignar Servidores</p>
                        <p class="form-section-sub">Opcional: Puedes asignar servidores ahora o despues</p>
                    </div>
                    <button type="button" onclick="agregarServidor()" class="btn btn-amarillo btn-agregar-servidor">
                        &#43; Agregar Servidor
                    </button>
                </div>

                <div id="servidores-container" style="display:flex; flex-direction:column; gap:1rem;">
                </div>

                <div id="sin-servidores" class="sin-servidores-msg">
                    <p>&#128101; No hay servidores asignados. Presiona "Agregar Servidor" para comenzar.</p>
                </div>
            </div>

            {{-- Botones de accion --}}
            <div class="form-acciones">
                <button type="button" onclick="toggleFormulario()" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario btn-submit">
                    &#10003; Programar Culto
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Resumen --}}
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr); margin-bottom:1.5rem;">
    <div class="stat-card azul">
        <span class="stat-label">Total cultos</span>
        <span class="stat-valor">{{ $cultos->count() }}</span>
        <span class="stat-sub hide-mobile">Registrados</span>
    </div>
    <div class="stat-card amarillo">
        <span class="stat-label">Programados</span>
        <span class="stat-valor">{{ $cultos->filter(fn($c) => $c->fecha->isFuture())->count() }}</span>
        <span class="stat-sub hide-mobile">Próximos</span>
    </div>
    <div class="stat-card verde">
        <span class="stat-label">Hoy</span>
        <span class="stat-valor">{{ $cultos->filter(fn($c) => $c->fecha->isToday())->count() }}</span>
        <span class="stat-sub hide-mobile">Cultos de hoy</span>
    </div>
    <div class="stat-card rojo">
        <span class="stat-label">Realizados</span>
        <span class="stat-valor">{{ $cultos->filter(fn($c) => $c->estado === 'realizado')->count() }}</span>
        <span class="stat-sub hide-mobile">Completados</span>
    </div>
</div>

@php
    // Agrupar cultos por carácter
    $cultosPorCaracter = $cultos->sortBy('fecha')->groupBy('caracter');
    $caracteresInfo = \App\Models\Culto::caracteres();

    // Definir iconos y colores para cada tipo
    $iconosPorCaracter = [
        'evangelistico'     => '&#10013;', // Cruz
        'escuela_dominical' => '&#128214;', // Libro
        'jovenes'           => '&#127891;', // Persona joven
        'damas_dorcas'      => '&#128105;', // Mujer
        'damas_jovenes'     => '&#128105;&#127891;', // Mujer joven
        'mision_juvenil'    => '&#127775;&#10013;', // Mundo + Cruz
        'caballeros'        => '&#128104;', // Hombre
        'familia'           => '&#128101;', // Familia
        'parejas'           => '&#128105;&#128104;', // Pareja
        'culto_oracion'     => '&#128336;&#10013;', // Oración + Cruz
    ];

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
        'culto_oracion'     => '#2E86C1',
    ];

    // Cultos por estado para filtros
    $cultosRealizados = $cultos->filter(fn($c) => $c->estado === 'realizado');
    $cultosPorRealizar = $cultos->filter(fn($c) => $c->estado === 'programado');
@endphp

{{-- Filtros --}}
<div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; padding:1rem; margin-bottom:1.5rem;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
        <h3 style="font-size:16px; font-weight:600; color:#0D2F6E; margin:0;">
            &#128197; Filtros
        </h3>
    </div>
    {{-- Búsqueda por nombre --}}
    <div style="margin-bottom:1rem;">
        <input type="text" id="buscar-culto" placeholder="&#128269; Buscar culto por nombre..."
               style="width:100%; padding:10px 14px; border:1px solid #D1DCF0; border-radius:8px; font-size:14px; outline:none;"
               onfocus="this.style.borderColor='#1A4FA8'" onblur="this.style.borderColor='#D1DCF0'">
    </div>
    <div class="grid-auto" style="gap:10px;">
        <button onclick="filtrarCultos('todos')" class="btn-filtro btn-filtro-activo" id="filtro-todos" data-filtro="todos">
            &#128196; Todos <span class="hide-mobile">({{ $cultos->count() }})</span>
        </button>
        <button onclick="filtrarCultos('realizados')" class="btn-filtro" id="filtro-realizados" data-filtro="realizados">
            &#10003; Realizados <span class="hide-mobile">({{ $cultosRealizados->count() }})</span>
        </button>
        <button onclick="filtrarCultos('por_realizar')" class="btn-filtro" id="filtro-por_realizar" data-filtro="por_realizar">
            &#128197; Por realizar <span class="hide-mobile">({{ $cultosPorRealizar->count() }})</span>
        </button>
    </div>
</div>

{{-- Cultos agrupados por carácter --}}
@if($cultos->count() > 0)
    @foreach($cultosPorCaracter as $caracter => $cultosGrupo)
    <div class="accordion-wrapper" data-caracter="{{ $caracter }}" style="margin-bottom:1rem;">
        {{-- Header del accordion --}}
        <div class="accordion-header" onclick="toggleAccordion('{{ $caracter }}')"
             style="display:flex; align-items:center; justify-content:space-between; padding:1rem; background:#fff; border:1px solid #D1DCF0; border-radius:10px; cursor:pointer; transition:background 0.2s;"
             onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='#fff'">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:24px;">{!! $iconosPorCaracter[$caracter] ?? '&#128197;' !!}</span>
                <div>
                    <h2 style="font-size:16px; font-weight:600; color:#0D2F6E; margin:0;">
                        {{ $caracteresInfo[$caracter] ?? ucfirst($caracter) }}
                    </h2>
                    <span class="pill pill-pendiente" style="font-size:11px;">{{ $cultosGrupo->count() }} culto(s)</span>
                </div>
            </div>
            <span class="accordion-icon" id="icono-{{ $caracter }}" style="font-size:20px; color:#555; transition:transform 0.3s;">
                &#9660;
            </span>
        </div>

        {{-- Contenido del accordion --}}
        <div class="accordion-content" id="contenido-{{ $caracter }}" style="display:none; margin-top:0.5rem;">
            <div class="table-responsive">
                <table style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; overflow:hidden;">
                    <thead>
                        <tr>
                            <th class="hide-mobile" width="60">#</th>
                            <th>Nombre del culto</th>
                            <th>Fecha y hora</th>
                            <th class="hide-mobile">Descripción</th>
                            <th>Asignaciones</th>
                            <th>Estado</th>
                            <th width="160">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cultosGrupo as $culto)
                        @php
                            $estado = $culto->estado;
                            $pillColor = match($estado) {
                                'realizado'  => 'pill-inactivo',
                                'hoy'        => 'pill-pendiente',
                                'programado' => 'pill-activo',
                            };
                        @endphp
                        <tr class="fila-culto"
                            data-estado="{{ $estado }}"
                            data-nombre="{{ strtolower($culto->nombre_culto) }}"
                            style="border-left: 3px solid {{ $coloresPorCaracter[$caracter] ?? '#0D2F6E' }};">
                            <td data-label="#" class="hide-mobile" style="color:#999;">{{ $loop->iteration }}</td>
                            <td data-label="Culto">
                                <strong>{{ $culto->nombre_culto }}</strong>
                                @if($culto->mensaje)
                                <div class="hide-mobile" style="font-size:11px; color:#777; margin-top:4px;">
                                    &#128221; {{ Str::limit($culto->mensaje, 50) }}
                                </div>
                                @endif
                                @if($culto->descripcion)
                                <div class="show-mobile" style="font-size:11px; color:#777; margin-top:4px;">
                                    {{ $culto->descripcion }}
                                </div>
                                @endif
                            </td>
                            <td data-label="Fecha" style="white-space:nowrap;">
                                <div>
                                    <strong>{{ $culto->fecha->isoFormat('ddd D MMM') }}</strong>
                                    <div style="color:#999; font-size:12px;">
                                        {{ $culto->fecha->format('g:i A') }}
                                    </div>
                                </div>
                            </td>
                            <td data-label="Descripción" class="hide-mobile" style="font-size:13px; color:#555;">
                                {{ $culto->descripcion ?? '—' }}
                            </td>
                            <td data-label="Asignados">
                                <span class="pill pill-pendiente">
                                    {{ $culto->asignaciones->count() }}
                                </span>
                            </td>
                            <td data-label="Estado">
                                <span class="pill {{ $pillColor }}" style="font-size:11px;">
                                    {{ ucfirst($estado) }}
                                </span>
                            </td>
                            <td data-label="Acciones">
                                <div style="display:flex; gap:6px; flex-wrap:wrap; flex-direction:column;" class="btn-group">
                                    {{-- Ver detalle --}}
                                    <a href="{{ route('cultos.show', $culto->id) }}"
                                       class="btn btn-amarillo btn-touch"
                                       style="padding:8px 12px; font-size:12px;"
                                       title="Ver detalles">
                                        &#128065; Ver
                                    </a>

                                    {{-- Editar --}}
                                    @if($estado !== 'realizado')
                                    <button class="btn btn-primario btn-touch"
                                            style="padding:8px 12px; font-size:12px;"
                                            onclick="abrirEditar(
                                                  {{ $culto->id }},
                                                '{{ addslashes(e($culto->nombre_culto)) }}',
                                                '{{ addslashes(e($culto->caracter)) }}',
                                                '{{ $culto->fecha->format('Y-m-d\TH:i') }}',
                                                '{{ addslashes(e($culto->descripcion ?? '')) }}'
                                            )"
                                            title="Editar culto">
                                        &#9998; Editar
                                    </button>
                                    @endif

                                    {{-- Eliminar --}}
                                    @php
                                        $asignacionesActivas = $culto->asignaciones->where('estado', 'asignado')->count();
                                    @endphp
                                    @if($asignacionesActivas === 0)
                                    <form method="POST" action="{{ route('cultos.destroy', $culto->id) }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('¿Eliminar el culto {{ addslashes(e($culto->nombre_culto)) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-peligro btn-touch"
                                                style="padding:8px 12px; font-size:12px;"
                                                title="Eliminar culto">
                                            &times; Eliminar
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
@else
    <div class="tabla-wrapper">
        <div class="tabla-header">
            <h2>Cultos programados</h2>
        </div>
        <div style="text-align:center; padding:3rem 2rem; color:#999;">
            <div style="font-size:48px; margin-bottom:1rem;">&#128197;</div>
            <p style="font-size:16px; margin-bottom:0.5rem;">No hay cultos programados aún</p>
            <p style="font-size:13px;">Haz clic en "Nuevo culto" para programar el primero</p>
        </div>
    </div>
@endif

{{-- Modal editar --}}
<div id="modal-editar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
     z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:2rem; width:100%;
                max-width:520px; border-top:4px solid #1A4FA8; max-height:90vh; overflow-y:auto;">
        <div class="form-card-header" style="margin-bottom:1.25rem;">
            <h2 style="font-size:16px; color:#0D2F6E;">Editar culto</h2>
            <button onclick="cerrarEditar()" class="btn-cerrar">&#10005;</button>
        </div>
        <form method="POST" id="form-editar">
            @csrf @method('PUT')
            <div class="form-grid-3">
                <div class="form-group" style="grid-column: span 3;">
                    <label>Nombre del culto <span class="requerido">*</span></label>
                    <input type="text" name="nombre_culto" id="edit-nombre"
                           placeholder="Ej: Culto Dominical"
                           class="input-touch">
                </div>

                <div class="form-group">
                    <label>Carácter del culto <span class="requerido">*</span></label>
                    <select name="caracter" id="edit-caracter" class="input-touch">
                        <option value="">— Seleccionar tipo —</option>
                        @foreach(\App\Models\Culto::caracteres() as $key => $nombre)
                            <option value="{{ $key }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Fecha y hora <span class="requerido">*</span></label>
                    <input type="datetime-local" name="fecha" id="edit-fecha" class="input-touch">
                </div>

                <div class="form-group" style="grid-column: span 3;">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" id="edit-descripcion" placeholder="Descripción opcional" class="input-touch">
                </div>
            </div>
            <div class="form-acciones">
                <button type="button" onclick="cerrarEditar()" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario">&#10003; Actualizar culto</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Formulario card */
    .form-card {
        background: #fff; border: 1px solid #D1DCF0;
        border-radius: 10px; border-top: 4px solid #F5C518; padding: 1.5rem;
    }
    .form-card-header {
        display: flex; justify-content: space-between;
        align-items: center; margin-bottom: 1.5rem;
        padding-bottom: 1rem; border-bottom: 1px solid #E8EEF5;
    }
    .form-card-header h2 { font-size: 17px; font-weight: 600; color: #0D2F6E; }
    .btn-cerrar {
        background: transparent; border: none; font-size: 18px;
        color: #999; cursor: pointer; padding: 6px 10px; border-radius: 6px;
    }
    .btn-cerrar:hover { background: #f4f6fa; color: #333; }

    /* Secciones del formulario */
    .form-section {
        margin-bottom: 1.5rem;
        padding: 1.25rem;
        background: #F8FAFD;
        border-radius: 10px;
        border: 1px solid #E8EEF5;
    }
    .form-section-title {
        font-size: 15px; font-weight: 600; color: #0D2F6E;
        margin: 0 0 1rem 0;
    }
    .form-section-header {
        display: flex; justify-content: space-between; align-items: flex-start;
        gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;
    }
    .form-section-title-group { flex: 1; min-width: 200px; }
    .form-section-title-group .form-section-title { margin-bottom: 0.25rem; }
    .form-section-sub {
        font-size: 13px; color: #777; margin: 0;
    }
    .btn-agregar-servidor {
        padding: 10px 18px; white-space: nowrap; flex-shrink: 0;
    }

    /* Grid responsive */
    .form-grid-responsive {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    label { font-size: 14px; font-weight: 600; color: #3a4255; }
    .requerido { color: #C0392B; }

    /* Inputs touch-friendly: 16px previene zoom en iOS */
    .input-touch,
    input[type="text"], input[type="datetime-local"], select, textarea {
        padding: 12px 14px;
        border: 1px solid #D1DCF0;
        border-radius: 8px;
        font-size: 16px;
        color: #1a1a2e;
        outline: none;
        transition: border-color 0.2s;
        width: 100%;
        min-height: 48px;
        background: #fff;
    }
    input:focus, select:focus { border-color: #1A4FA8; box-shadow: 0 0 0 3px rgba(26,79,168,0.1); }
    .input-error { border-color: #C0392B !important; }
    .error-msg { color: #C0392B; font-size: 13px; margin-top: 2px; }

    /* Botones de accion */
    .form-acciones {
        display: flex; justify-content: flex-end; gap: 12px;
        margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #E8EEF5;
    }
    .btn-submit { background: #1A4FA8; }

    /* Mensaje sin servidores */
    .sin-servidores-msg {
        text-align: center; padding: 2rem 1rem;
        background: #fff; border-radius: 10px; border: 2px dashed #D1DCF0;
    }
    .sin-servidores-msg p { color: #999; font-size: 14px; margin: 0; }

    /* Servidor item */
    .servidor-item {
        background: #fff; border: 1px solid #D1DCF0; border-radius: 10px;
        padding: 1rem; padding-right: 2.5rem; position: relative;
    }
    .btn-eliminar-servidor {
        position: absolute; top: 10px; right: 10px;
        background: #C0392B; color: #fff; border: none;
        width: 32px; height: 32px; border-radius: 50%;
        cursor: pointer; font-size: 18px; line-height: 1;
        display: flex; align-items: center; justify-content: center;
    }
    .servidor-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .servidor-grid select {
        width: 100%; padding: 12px 14px; border: 1px solid #D1DCF0;
        border-radius: 8px; font-size: 16px; color: #1a1a2e;
        outline: none; min-height: 48px; background: #fff;
    }
    .info-servidor {
        background: #E8F0FB; padding: 0.75rem; border-radius: 7px; margin-top: 0.75rem;
    }
    .info-servidor p { font-size: 13px; color: #555; margin: 0; }
    .servidor-confirm label {
        display: flex; align-items: center; gap: 8px;
        font-size: 14px; cursor: pointer; margin-top: 0.75rem;
    }
    .servidor-confirm input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; }

    /* Estilos de filtros */
    .btn-filtro {
        padding: 8px 16px; border: 2px solid #D1DCF0; background: #fff;
        color: #555; border-radius: 8px; font-size: 13px; font-weight: 500;
        cursor: pointer; transition: all 0.2s;
    }
    .btn-filtro:hover { border-color: #1A4FA8; color: #1A4FA8; }
    .btn-filtro-activo { background: #1A4FA8; color: #fff; border-color: #1A4FA8; }
    .btn-filtro-activo:hover { background: #0D2F6E; border-color: #0D2F6E; color: #fff; }

    /* Accordion */
    .accordion-header:hover { background: #F8F9FA !important; }
    .accordion-icon.rotado { transform: rotate(180deg); }

    /* Responsive */
    @media (max-width: 600px) {
        .form-card { padding: 1rem; border-radius: 8px; }
        .form-card-header { margin-bottom: 1rem; padding-bottom: 0.75rem; }
        .form-card-header h2 { font-size: 15px; }
        .form-section { padding: 1rem; margin-bottom: 1rem; }
        .form-grid-responsive { grid-template-columns: 1fr; }
        .servidor-grid { grid-template-columns: 1fr; }
        .form-section-header { flex-direction: column; }
        .btn-agregar-servidor { width: 100%; text-align: center; }
        .form-acciones { flex-direction: column; }
        .form-acciones .btn { width: 100%; justify-content: center; min-height: 48px; font-size: 15px; }
        .sin-servidores-msg { padding: 1.5rem 1rem; }

        /* Modal responsive */
        #modal-editar > div {
            margin: 0.75rem;
            max-width: calc(100% - 1.5rem) !important;
        }
        #modal-editar .btn-cerrar {
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #modal-editar .form-grid-3 { grid-template-columns: 1fr; }
        #modal-editar input[type="text"],
        #modal-editar input[type="datetime-local"],
        #modal-editar select {
            font-size: 16px !important;
        }
        #modal-editar .form-acciones {
            flex-direction: column;
        }
        #modal-editar .form-acciones .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    // Función para alternar el accordion
    function toggleAccordion(caracter) {
        const contenido = document.getElementById('contenido-' + caracter);
        const icono = document.getElementById('icono-' + caracter);
        const visible = contenido.style.display === 'block';

        contenido.style.display = visible ? 'none' : 'block';
        icono.classList.toggle('rotado', !visible);
    }

    // Función para filtrar cultos
    function filtrarCultos(filtro) {
        // Actualizar botones activos
        document.querySelectorAll('.btn-filtro').forEach(btn => {
            btn.classList.remove('btn-filtro-activo');
        });
        document.getElementById('filtro-' + filtro).classList.add('btn-filtro-activo');

        // Obtener término de búsqueda
        const terminoBusqueda = document.getElementById('buscar-culto')
            ? document.getElementById('buscar-culto').value.toLowerCase().trim()
            : '';

        // Filtrar filas
        document.querySelectorAll('.fila-culto').forEach(fila => {
            const estado = fila.getAttribute('data-estado');
            const nombre = fila.getAttribute('data-nombre');
            let mostrar = false;

            // Filtro por estado
            if (filtro === 'todos') {
                mostrar = true;
            } else if (filtro === 'realizados') {
                mostrar = estado === 'realizado';
            } else if (filtro === 'por_realizar') {
                mostrar = estado === 'programado' || estado === 'hoy';
            }

            // Filtro por búsqueda
            if (mostrar && terminoBusqueda) {
                mostrar = nombre.includes(terminoBusqueda);
            }

            fila.style.display = mostrar ? '' : 'none';
        });

        // Ocultar accordions vacíos
        document.querySelectorAll('.accordion-wrapper').forEach(wrapper => {
            const filasVisibles = wrapper.querySelectorAll('.fila-culto');
            const algunaVisible = Array.from(filasVisibles).some(f => f.style.display !== 'none');
            wrapper.style.display = algunaVisible ? '' : 'none';
        });
    }

    // Búsqueda en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        const buscarInput = document.getElementById('buscar-culto');
        if (buscarInput) {
            buscarInput.addEventListener('input', function() {
                // Determinar qué filtro está activo
                const filtroActivo = document.querySelector('.btn-filtro-activo');
                const filtro = filtroActivo ? filtroActivo.getAttribute('data-filtro') : 'todos';
                filtrarCultos(filtro);
            });
        }
    });

    function toggleFormulario() {
        const form = document.getElementById('formulario-crear');
        const visible = form.style.display === 'block';
        form.style.display = visible ? 'none' : 'block';
        if (!visible) document.querySelector('[name="nombre_culto"]').focus();
    }

    // Variables globales para el sistema de asignación de servidores
    let contadorServidores = 0;
    const servidoresData = @json($servidores ?? []);
    const rolesServicio = @json(\App\Models\Asignacion::rolesServicio());

    // Función para agregar un nuevo servidor al formulario
    function agregarServidor() {
        contadorServidores++;
        const container = document.getElementById('servidores-container');
        const sinServidores = document.getElementById('sin-servidores');

        if (sinServidores) {
            sinServidores.style.display = 'none';
        }

        const servidorHTML = `
            <div id="servidor-${contadorServidores}" class="servidor-item">
                <button type="button" onclick="eliminarServidor(${contadorServidores})" class="btn-eliminar-servidor">&times;</button>

                <div class="servidor-grid">
                    <div>
                        <label>Servidor <span style="color:#C0392B;">*</span></label>
                        <select name="servidores[${contadorServidores}][servidor_id]" required
                                onchange="actualizarInfoServidor(${contadorServidores}, this.value)">
                            <option value="">— Seleccionar servidor —</option>
                            ${servidoresData.map(servidor =>
                                '<option value="' + servidor.id + '">' + servidor.nombre_completo + '</option>'
                            ).join('')}
                        </select>
                    </div>
                    <div>
                        <label>Rol en el culto <span style="color:#C0392B;">*</span></label>
                        <select name="servidores[${contadorServidores}][rol_servicio]" required>
                            <option value="">— Seleccionar rol —</option>
                            ${Object.entries(rolesServicio).map(([key, nombre]) =>
                                '<option value="' + nombre + '">' + nombre + '</option>'
                            ).join('')}
                        </select>
                    </div>
                </div>

                <div id="info-servidor-${contadorServidores}" class="info-servidor" style="display:none;">
                    <p><strong>Informacion:</strong> <span id="detalles-servidor-${contadorServidores}"></span></p>
                </div>

                <div class="servidor-confirm">
                    <label>
                        <input type="checkbox" name="servidores[${contadorServidores}][confirmado]" value="1">
                        Servidor confirmado
                    </label>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', servidorHTML);
    }

    // Función para eliminar un servidor del formulario
    function eliminarServidor(id) {
        const servidorElement = document.getElementById(`servidor-${id}`);
        if (servidorElement) {
            servidorElement.remove();
        }

        // Verificar si quedan servidores
        const container = document.getElementById('servidores-container');
        const servidoresRestantes = container.querySelectorAll('.servidor-item');

        if (servidoresRestantes.length === 0) {
            const sinServidores = document.getElementById('sin-servidores');
            if (sinServidores) {
                sinServidores.style.display = 'block';
            }
        }
    }

    // Función para actualizar la información del servidor seleccionado
    function actualizarInfoServidor(id, servidorId) {
        const infoDiv = document.getElementById('info-servidor-' + id);
        const detallesSpan = document.getElementById('detalles-servidor-' + id);

        if (!servidorId) {
            infoDiv.style.display = 'none';
            return;
        }

        const servidor = servidoresData.find(s => s.id == servidorId);
        if (servidor) {
            const partes = [];
            if (servidor.genero) partes.push(servidor.genero.denominacion);
            if (servidor.cargo) partes.push(servidor.cargo);
            if (servidor.telefono) partes.push(servidor.telefono);
            detallesSpan.textContent = partes.join(' • ');
            infoDiv.style.display = 'block';
        }
    }

    function abrirEditar(id, nombre, caracter, fecha, descripcion) {
        document.getElementById('form-editar').action = '/cultos/' + id;
        document.getElementById('edit-nombre').value      = nombre;
        document.getElementById('edit-caracter').value    = caracter;
        document.getElementById('edit-fecha').value       = fecha;
        document.getElementById('edit-descripcion').value = descripcion;
        document.getElementById('modal-editar').style.display = 'flex';
    }

    function cerrarEditar() {
        document.getElementById('modal-editar').style.display = 'none';
    }

    @if($errors->any())
        document.getElementById('formulario-crear').style.display = 'block';
    @endif

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') cerrarEditar();
    });

    // Cerrar modal al tocar fuera
    document.getElementById('modal-editar').addEventListener('click', function(e) {
        if (e.target === this) cerrarEditar();
    });
</script>
@endsection
