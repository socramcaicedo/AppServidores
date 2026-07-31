@extends('layouts.app')
@section('titulo', 'Gestión de Roles')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Administración</p>
    <a href="#">
        <span class="icono">&#128101;</span> Servidores
    </a>
    <a href="{{ route('admin.roles.index') }}" class="activo">
        <span class="icono">&#128274;</span> Roles
    </a>
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Cultos</p>
    <a href="#">
        <span class="icono">&#128197;</span> Gestionar cultos
    </a>
    <a href="#">
        <span class="icono">&#43;</span> Nuevo orden de culto
    </a>
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Reportes</p>
    <a href="#">
        <span class="icono">&#128203;</span> Historial
    </a>
    <a href="#">
        <span class="icono">&#128202;</span> Estadísticas
    </a>
</div>
@endsection

@section('contenido')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
    <div>
        <h1>Gestión de Roles</h1>
        <p>Administra los roles del sistema y sus estados</p>
    </div>
    <button class="btn btn-amarillo" onclick="toggleFormulario()">
        &#43; Nuevo rol
    </button>
</div>

{{-- Formulario desplegable --}}
<div id="formulario-crear" style="display:none; margin-bottom:1.5rem;">
    <div class="form-card">
        <div class="form-card-header">
            <h2>Crear nuevo rol</h2>
            <button onclick="toggleFormulario()" class="btn-cerrar">&#10005;</button>
        </div>
        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre_rol">Nombre del rol <span class="requerido">*</span></label>
                    <input
                        type="text"
                        id="nombre_rol"
                        name="nombre_rol"
                        value="{{ old('nombre_rol') }}"
                        placeholder="Ej: coordinador"
                        class="{{ $errors->has('nombre_rol') ? 'input-error' : '' }}"
                    >
                    @error('nombre_rol')
                        <p class="error-msg">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <input
                        type="text"
                        id="descripcion"
                        name="descripcion"
                        value="{{ old('descripcion') }}"
                        placeholder="Descripción del rol (opcional)"
                    >
                </div>
            </div>
            <div class="form-acciones">
                <button type="button" onclick="toggleFormulario()" class="btn btn-secundario">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primario">
                    &#10003; Guardar rol
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tabla de roles --}}
<div class="tabla-wrapper">
    <div class="tabla-header">
        <h2>Roles registrados</h2>
        <span style="font-size:13px; color:#999;">{{ $roles->count() }} roles en total</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre del rol</th>
                <th>Descripción</th>
                <th>Usuarios asignados</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roles as $rol)
            <tr>
                <td style="color:#999;">{{ $loop->iteration }}</td>
                <td><strong>{{ $rol->nombre_rol }}</strong></td>
                <td>{{ $rol->descripcion ?? '—' }}</td>
                <td>
                    <span class="pill {{ $rol->usuarios_count > 0 ? 'pill-activo' : 'pill-pendiente' }}">
                        {{ $rol->usuarios_count }} usuario(s)
                    </span>
                </td>
                <td>
                    <span class="pill {{ $rol->estado == 1 ? 'pill-activo' : 'pill-inactivo' }}">
                        {{ $rol->estado == 1 ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td>
                    <div style="display:flex; gap:6px;">
                        {{-- Botón editar --}}
                        <button
                            class="btn btn-primario"
                            style="padding:5px 10px; font-size:12px;"
                            onclick="abrirEditar({{ $rol->id }}, '{{ addslashes(e($rol->nombre_rol)) }}', '{{ addslashes(e($rol->descripcion ?? '')) }}', '{{ $rol->estado }}')"
                        >
                            Editar
                        </button>

                        {{-- Botón eliminar --}}
                        @if($rol->usuarios_count === 0)
                        <form method="POST" action="{{ route('admin.roles.destroy', $rol->id) }}"
                              onsubmit="return confirm('¿Eliminar el rol {{ addslashes(e($rol->nombre_rol)) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-peligro" style="padding:5px 10px; font-size:12px;">
                                Eliminar
                            </button>
                        </form>
                        @else
                        <button class="btn btn-secundario" style="padding:5px 10px; font-size:12px;" disabled title="Tiene usuarios asignados">
                            Eliminar
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#999; padding:2rem;">
                    No hay roles registrados aún.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal de edición --}}
<div id="modal-editar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:2rem; width:100%; max-width:480px; border-top:4px solid #1A4FA8; max-height:90vh; overflow-y:auto;">
        <div class="form-card-header" style="margin-bottom:1.25rem;">
            <h2 style="font-size:16px; color:#0D2F6E;">Editar rol</h2>
            <button onclick="cerrarEditar()" class="btn-cerrar">&#10005;</button>
        </div>
        <form method="POST" id="form-editar">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom:1rem;">
                <label>Nombre del rol <span class="requerido">*</span></label>
                <input type="text" name="nombre_rol" id="edit-nombre" placeholder="Nombre del rol">
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label>Descripción</label>
                <input type="text" name="descripcion" id="edit-descripcion" placeholder="Descripción (opcional)">
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label>Estado</label>
                <select name="estado" id="edit-estado">
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="form-acciones">
                <button type="button" onclick="cerrarEditar()" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario">&#10003; Actualizar rol</button>
            </div>
        </form>
    </div>
</div>

<style>
    .form-card {
        background: #fff;
        border: 1px solid #D1DCF0;
        border-radius: 10px;
        border-top: 4px solid #F5C518;
        padding: 1.5rem;
    }

    .form-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    .form-card-header h2 {
        font-size: 15px;
        font-weight: 600;
        color: #0D2F6E;
    }

    .btn-cerrar {
        background: transparent;
        border: none;
        font-size: 16px;
        color: #999;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .btn-cerrar:hover { background: #f4f6fa; color: #333; }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .form-group { display: flex; flex-direction: column; gap: 5px; }

    label {
        font-size: 13px;
        font-weight: 600;
        color: #3a4255;
    }

    .requerido { color: #C0392B; }

    input[type="text"],
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

    input:focus, select:focus { border-color: #1A4FA8; }
    .input-error { border-color: #C0392B !important; }
    .error-msg { color: #C0392B; font-size: 12px; }

    .form-acciones {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    @media (max-width: 600px) {
        .form-grid { grid-template-columns: 1fr; }

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
        #modal-editar input[type="text"],
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
    // Mostrar/ocultar formulario de crear
    function toggleFormulario() {
        const form = document.getElementById('formulario-crear');
        const visible = form.style.display === 'block';
        form.style.display = visible ? 'none' : 'block';
        if (!visible) {
            document.getElementById('nombre_rol').focus();
        }
    }

    // Abrir modal de editar con datos del rol
    function abrirEditar(id, nombre, descripcion, estado) {
        document.getElementById('form-editar').action = '/admin/roles/' + id;
        document.getElementById('edit-nombre').value = nombre;
        document.getElementById('edit-descripcion').value = descripcion !== 'null' ? descripcion : '';
        document.getElementById('edit-estado').value = estado;
        const modal = document.getElementById('modal-editar');
        modal.style.display = 'flex';
    }

    function cerrarEditar() {
        document.getElementById('modal-editar').style.display = 'none';
    }

    // Abrir formulario si hay errores de validación
    @if($errors->any())
        document.getElementById('formulario-crear').style.display = 'block';
    @endif

    // Cerrar modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') cerrarEditar();
    });

    // Cerrar modal al tocar fuera
    document.getElementById('modal-editar').addEventListener('click', function(e) {
        if (e.target === this) cerrarEditar();
    });
</script>
@endsection