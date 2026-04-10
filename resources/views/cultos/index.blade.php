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
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
    <div>
        <h1>Gestión de Cultos</h1>
        <p>Programa y administra los cultos de la iglesia</p>
    </div>
    <button class="btn btn-amarillo" onclick="toggleFormulario()">
        &#43; Nuevo culto
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
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Nombre del culto <span class="requerido">*</span></label>
                    <input type="text" name="nombre_culto"
                           value="{{ old('nombre_culto') }}"
                           placeholder="Ej: Culto Dominical"
                           class="{{ $errors->has('nombre_culto') ? 'input-error' : '' }}">
                    @error('nombre_culto')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Fecha y hora <span class="requerido">*</span></label>
                    <input type="datetime-local" name="fecha"
                           value="{{ old('fecha') }}"
                           class="{{ $errors->has('fecha') ? 'input-error' : '' }}">
                    @error('fecha')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="descripcion"
                           value="{{ old('descripcion') }}"
                           placeholder="Descripción opcional">
                </div>
            </div>
            <div class="form-acciones">
                <button type="button" onclick="toggleFormulario()" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario">&#10003; Guardar culto</button>
            </div>
        </form>
    </div>
</div>

{{-- Resumen --}}
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr); margin-bottom:1.5rem;">
    <div class="stat-card azul">
        <span class="stat-label">Total cultos</span>
        <span class="stat-valor">{{ $cultos->count() }}</span>
        <span class="stat-sub">Registrados</span>
    </div>
    <div class="stat-card amarillo">
        <span class="stat-label">Programados</span>
        <span class="stat-valor">{{ $cultos->filter(fn($c) => $c->fecha->isFuture())->count() }}</span>
        <span class="stat-sub">Próximos</span>
    </div>
    <div class="stat-card verde">
        <span class="stat-label">Hoy</span>
        <span class="stat-valor">{{ $cultos->filter(fn($c) => $c->fecha->isToday())->count() }}</span>
        <span class="stat-sub">Cultos de hoy</span>
    </div>
    <div class="stat-card rojo">
        <span class="stat-label">Realizados</span>
        <span class="stat-valor">{{ $cultos->filter(fn($c) => $c->fecha->isPast())->count() }}</span>
        <span class="stat-sub">Completados</span>
    </div>
</div>

{{-- Tabla --}}
<div class="tabla-wrapper">
    <div class="tabla-header">
        <h2>Cultos programados</h2>
        <span style="font-size:13px; color:#999;">{{ $cultos->count() }} cultos en total</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre del culto</th>
                <th>Fecha y hora</th>
                <th>Descripción</th>
                <th>Asignaciones</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cultos as $culto)
            @php
                $estado = $culto->fecha->isPast() ? 'realizado' : ($culto->fecha->isToday() ? 'hoy' : 'programado');
                $pillColor = match($estado) {
                    'realizado'  => 'pill-inactivo',
                    'hoy'        => 'pill-pendiente',
                    'programado' => 'pill-activo',
                };
            @endphp
            <tr>
                <td style="color:#999;">{{ $loop->iteration }}</td>
                <td><strong>{{ $culto->nombre_culto }}</strong></td>
                <td style="white-space:nowrap;">
                    {{ $culto->fecha->isoFormat('ddd D MMM YYYY') }}
                    <span style="color:#999; font-size:12px;">
                        {{ $culto->fecha->format('H:i') }}
                    </span>
                </td>
                <td style="font-size:13px; color:#555;">
                    {{ $culto->descripcion ?? '—' }}
                </td>
                <td>
                    <span class="pill pill-pendiente">
                        {{ $culto->asignaciones->count() }} asignado(s)
                    </span>
                </td>
                <td>
                    <span class="pill {{ $pillColor }}">
                        {{ ucfirst($estado) }}
                    </span>
                </td>
                <td>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        {{-- Ver detalle --}}
                        <a href="{{ route('cultos.show', $culto->id) }}"
                           class="btn btn-amarillo"
                           style="padding:5px 10px; font-size:12px;">
                            Ver
                        </a>

                        {{-- Editar --}}
                        @if($estado !== 'realizado')
                        <button class="btn btn-primario"
                                style="padding:5px 10px; font-size:12px;"
                                onclick="abrirEditar(
                                      {{ $culto->id }},
    '{{ addslashes($culto->nombre_culto) }}',
    '{{ $culto->caracter }}',
    '{{ $culto->fecha->format('Y-m-d\TH:i') }}',
    '{{ addslashes($culto->descripcion ?? '') }}'
                                )">
                            Editar
                        </button>
                        @endif

                        {{-- Eliminar --}}
                        @if($culto->asignaciones->count() === 0)
                        <form method="POST" action="{{ route('cultos.destroy', $culto->id) }}"
                              onsubmit="return confirm('¿Eliminar el culto {{ $culto->nombre_culto }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-peligro"
                                    style="padding:5px 10px; font-size:12px;">
                                Eliminar
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#999; padding:2.5rem;">
                    No hay cultos programados aún.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal editar --}}
<div id="modal-editar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
     z-index:999; align-items:center; justify-content:center;">
     <div class="form-group" style="grid-column:span 3;">
    <label>Carácter del culto <span class="requerido">*</span></label>
    <select name="caracter" id="edit-caracter">
        <option value="">— Seleccionar tipo —</option>
        @foreach(\App\Models\Culto::caracteres() as $key => $nombre)
            <option value="{{ $key }}">{{ $nombre }}</option>
        @endforeach
    </select>
</div>
    <div style="background:#fff; border-radius:12px; padding:2rem; width:100%;
                max-width:520px; border-top:4px solid #1A4FA8;">
        <div class="form-card-header" style="margin-bottom:1.25rem;">
            <h2 style="font-size:16px; color:#0D2F6E;">Editar culto</h2>
            <button onclick="cerrarEditar()" class="btn-cerrar">&#10005;</button>
        </div>
        <form method="POST" id="form-editar">
            @csrf @method('PUT')
           <div class="form-grid-3">
    <div class="form-group">
        <label>Nombre del culto <span class="requerido">*</span></label>
        <input type="text" name="nombre_culto"
               value="{{ old('nombre_culto') }}"
               placeholder="Ej: Culto Dominical"
               class="{{ $errors->has('nombre_culto') ? 'input-error' : '' }}">
        @error('nombre_culto')
            <p class="error-msg">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label>Carácter del culto <span class="requerido">*</span></label>
        <select name="caracter" class="{{ $errors->has('caracter') ? 'input-error' : '' }}">
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
               class="{{ $errors->has('fecha') ? 'input-error' : '' }}">
        @error('fecha')
            <p class="error-msg">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group" style="grid-column: span 3;">
        <label>Descripción</label>
        <input type="text" name="descripcion"
               value="{{ old('descripcion') }}"
               placeholder="Descripción opcional">
    </div>
</div>
                <div class="form-group" style="grid-column:span 2;">
                    <label>Fecha y hora <span class="requerido">*</span></label>
                    <input type="datetime-local" name="fecha" id="edit-fecha">
                </div>
                <div class="form-group" style="grid-column:span 3;">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" id="edit-descripcion" placeholder="Descripción opcional">
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
    .form-card {
        background: #fff; border: 1px solid #D1DCF0;
        border-radius: 10px; border-top: 4px solid #F5C518; padding: 1.5rem;
    }
    .form-card-header {
        display: flex; justify-content: space-between;
        align-items: center; margin-bottom: 1.25rem;
    }
    .form-card-header h2 { font-size: 15px; font-weight: 600; color: #0D2F6E; }
    .btn-cerrar {
        background: transparent; border: none; font-size: 16px;
        color: #999; cursor: pointer; padding: 4px 8px; border-radius: 4px;
    }
    .btn-cerrar:hover { background: #f4f6fa; color: #333; }
    .form-grid-3 {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 1rem; margin-bottom: 1.25rem;
    }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    label { font-size: 13px; font-weight: 600; color: #3a4255; }
    .requerido { color: #C0392B; }
    input[type="text"], input[type="datetime-local"] {
        padding: 9px 12px; border: 1px solid #D1DCF0; border-radius: 7px;
        font-size: 14px; color: #1a1a2e; outline: none;
        transition: border-color 0.2s; width: 100%;
    }
    input:focus { border-color: #1A4FA8; }
    .input-error { border-color: #C0392B !important; }
    .error-msg { color: #C0392B; font-size: 12px; }
    .form-acciones { display: flex; justify-content: flex-end; gap: 10px; }
    @media (max-width: 600px) {
        .form-grid-3 { grid-template-columns: 1fr; }
    }
</style>

<script>
    function toggleFormulario() {
        const form = document.getElementById('formulario-crear');
        const visible = form.style.display === 'block';
        form.style.display = visible ? 'none' : 'block';
        if (!visible) document.querySelector('[name="nombre_culto"]').focus();
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
</script>
@endsection