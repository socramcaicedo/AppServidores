@extends('layouts.app')
@section('titulo', 'Gestión de Servidores')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Administración</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Dashboard
    </a>
    <a href="{{ route('servidores.index') }}" class="activo">
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
    <a href="#"><span class="icono">&#128197;</span> Gestionar cultos</a>
    <a href="#"><span class="icono">&#43;</span> Nuevo orden</a>
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
        <h1>Gestión de Servidores</h1>
        <p>Administra los servidores de la iglesia y su información de contacto</p>
    </div>
    <button class="btn btn-amarillo" onclick="toggleFormulario()">
        &#43; Nuevo servidor
    </button>
</div>

{{-- Formulario desplegable --}}
<div id="formulario-crear" style="display:none; margin-bottom:1.5rem;">
    <div class="form-card">
        <div class="form-card-header">
            <h2>Registrar nuevo servidor</h2>
            <button onclick="toggleFormulario()" class="btn-cerrar">&#10005;</button>
        </div>
        <form method="POST" action="{{ route('servidores.store') }}">
            @csrf
            <div class="form-grid-4">
                <div class="form-group">
                    <label>Nombre completo <span class="requerido">*</span></label>
                    <input type="text" name="nombre_completo"
                           value="{{ old('nombre_completo') }}"
                           placeholder="Nombres y apellidos"
                           class="{{ $errors->has('nombre_completo') ? 'input-error' : '' }}">
                    @error('nombre_completo')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Teléfono WhatsApp <span class="requerido">*</span></label>
                    <input type="text" name="telefono"
                           value="{{ old('telefono') }}"
                           placeholder="Ej: 573001234567"
                           class="{{ $errors->has('telefono') ? 'input-error' : '' }}">
                    @error('telefono')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Género</label>
                    <select name="idgenero">
                        <option value="">— Seleccionar —</option>
                        @foreach($generos as $g)
                            <option value="{{ $g->id }}" {{ old('idgenero') == $g->id ? 'selected' : '' }}>
                                {{ $g->denominacion }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Cargo</label>
                    <input type="text" name="cargo"
                           value="{{ old('cargo') }}"
                           placeholder="Ej: Ujier, Músico (opcional)">
                </div>
            </div>
            <div class="form-acciones">
                <button type="button" onclick="toggleFormulario()" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario">&#10003; Guardar servidor</button>
            </div>
        </form>
    </div>
</div>

{{-- Resumen --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr); margin-bottom:1.5rem;">
    <div class="stat-card azul">
        <span class="stat-label">Total servidores</span>
        <span class="stat-valor">{{ $servidores->count() }}</span>
        <span class="stat-sub">Registrados en el sistema</span>
    </div>
    <div class="stat-card verde">
        <span class="stat-label">Activos</span>
        <span class="stat-valor">{{ $servidores->where('estado','activo')->count() }}</span>
        <span class="stat-sub">Disponibles para servir</span>
    </div>
    <div class="stat-card rojo">
        <span class="stat-label">Inactivos</span>
        <span class="stat-valor">{{ $servidores->where('estado','inactivo')->count() }}</span>
        <span class="stat-sub">Temporalmente inactivos</span>
    </div>
</div>

{{-- Tabla --}}
<div class="tabla-wrapper">
    <div class="tabla-header">
        <h2>Listado de servidores</h2>
        <span style="font-size:13px; color:#999;">{{ $servidores->count() }} servidores en total</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre completo</th>
                <th>Cargo</th>
                <th>Género</th>
                <th>Teléfono</th>
                <th>Última participación</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($servidores as $servidor)
            <tr>
                <td style="color:#999;">{{ $loop->iteration }}</td>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="avatar">
                            {{ strtoupper(substr($servidor->nombre_completo, 0, 2)) }}
                        </div>
                        <strong>{{ $servidor->nombre_completo }}</strong>
                    </div>
                </td>
                <td style="color:#555;">{{ $servidor->cargo ?? '—' }}</td>
                <td>{{ $servidor->genero->denominacion ?? '—' }}</td>
                <td>
                    <a href="{{ $servidor->link_whatsapp }}"
                       target="_blank"
                       class="btn-whatsapp"
                       title="Contactar por WhatsApp">
                        &#128222; {{ $servidor->telefono }}
                    </a>
                </td>
                <td style="font-size:13px; color:#555;">
                    @if($servidor->ultimaParticipacion)
                        {{ $servidor->ultimaParticipacion->created_at->isoFormat('D MMM YYYY') }}
                    @else
                        <span style="color:#999;">Sin participaciones</span>
                    @endif
                </td>
                <td>
                    <span class="pill {{ $servidor->estado === 'activo' ? 'pill-activo' : 'pill-inactivo' }}">
                        {{ ucfirst($servidor->estado) }}
                    </span>
                </td>
                <td>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        {{-- Editar --}}
                        <button class="btn btn-primario"
                                style="padding:5px 10px; font-size:12px;"
                                onclick="abrirEditar(
                                    {{ $servidor->id }},
                                    '{{ addslashes($servidor->nombre_completo) }}',
                                    '{{ $servidor->telefono }}',
                                    '{{ $servidor->idgenero }}',
                                    '{{ addslashes($servidor->cargo ?? '') }}'
                                )">
                            Editar
                        </button>

                        {{-- Activar/Desactivar --}}
                        <form method="POST" action="{{ route('servidores.estado', $servidor->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="btn {{ $servidor->estado === 'activo' ? 'btn-secundario' : 'btn-amarillo' }}"
                                    style="padding:5px 10px; font-size:12px;">
                                {{ $servidor->estado === 'activo' ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>

                        {{-- Eliminar --}}
                        @if($servidor->asignaciones()->count() === 0)
                        <form method="POST" action="{{ route('servidores.destroy', $servidor->id) }}"
                              onsubmit="return confirm('¿Eliminar a {{ $servidor->nombre_completo }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-peligro"
                                    style="padding:5px 10px; font-size:12px;">
                                Eliminar
                            </button>
                        </form>
                        @else
                        <button class="btn btn-secundario"
                                style="padding:5px 10px; font-size:12px; opacity:0.5;"
                                disabled title="Tiene asignaciones registradas">
                            Eliminar
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#999; padding:2.5rem;">
                    No hay servidores registrados aún.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal de edición --}}
<div id="modal-editar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
     z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:2rem; width:100%;
                max-width:520px; border-top:4px solid #1A4FA8;">
        <div class="form-card-header" style="margin-bottom:1.25rem;">
            <h2 style="font-size:16px; color:#0D2F6E;">Editar servidor</h2>
            <button onclick="cerrarEditar()" class="btn-cerrar">&#10005;</button>
        </div>
        <form method="POST" id="form-editar">
            @csrf @method('PUT')
            <div class="form-grid-4" style="margin-bottom:1rem;">
                <div class="form-group" style="grid-column: span 2;">
                    <label>Nombre completo <span class="requerido">*</span></label>
                    <input type="text" name="nombre_completo" id="edit-nombre" placeholder="Nombres y apellidos">
                </div>
                <div class="form-group">
                    <label>Teléfono <span class="requerido">*</span></label>
                    <input type="text" name="telefono" id="edit-telefono" placeholder="573001234567">
                </div>
                <div class="form-group">
                    <label>Género</label>
                    <select name="idgenero" id="edit-genero">
                        <option value="">— Seleccionar —</option>
                        @foreach($generos as $g)
                            <option value="{{ $g->id }}">{{ $g->denominacion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Cargo</label>
                    <input type="text" name="cargo" id="edit-cargo" placeholder="Ej: Ujier, Músico (opcional)">
                </div>
            </div>
            <div class="form-acciones">
                <button type="button" onclick="cerrarEditar()" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario">&#10003; Actualizar servidor</button>
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
    .form-grid-4 {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 1rem; margin-bottom: 1.25rem;
    }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    label { font-size: 13px; font-weight: 600; color: #3a4255; }
    .requerido { color: #C0392B; }
    input[type="text"], select {
        padding: 9px 12px; border: 1px solid #D1DCF0; border-radius: 7px;
        font-size: 14px; color: #1a1a2e; outline: none;
        transition: border-color 0.2s; width: 100%;
    }
    input:focus, select:focus { border-color: #1A4FA8; }
    .input-error { border-color: #C0392B !important; }
    .error-msg { color: #C0392B; font-size: 12px; }
    .form-acciones { display: flex; justify-content: flex-end; gap: 10px; }
    .avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: #E8F0FB; color: #1A4FA8;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
    .btn-whatsapp {
        display: inline-flex; align-items: center; gap: 4px;
        color: #1A7A4A; font-size: 13px; text-decoration: none;
        font-weight: 500; transition: opacity 0.15s;
    }
    .btn-whatsapp:hover { opacity: 0.75; }
    @media (max-width: 900px) {
        .form-grid-4 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 500px) {
        .form-grid-4 { grid-template-columns: 1fr; }
    }
</style>

<script>
    function toggleFormulario() {
        const form = document.getElementById('formulario-crear');
        const visible = form.style.display === 'block';
        form.style.display = visible ? 'none' : 'block';
        if (!visible) document.querySelector('[name="nombre_completo"]').focus();
    }

    function abrirEditar(id, nombre, telefono, generoId, cargo) {
        document.getElementById('form-editar').action = '/servidores/' + id;
        document.getElementById('edit-nombre').value   = nombre;
        document.getElementById('edit-telefono').value = telefono;
        document.getElementById('edit-cargo').value    = cargo;
        if (generoId) document.getElementById('edit-genero').value = generoId;
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