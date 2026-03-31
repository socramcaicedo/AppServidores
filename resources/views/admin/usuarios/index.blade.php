@extends('layouts.app')
@section('titulo', 'Gestión de Usuarios')

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Administración</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Dashboard
    </a>
    <a href="#">
        <span class="icono">&#128101;</span> Servidores
    </a>
    <a href="{{ route('admin.roles.index') }}"
       class="{{ request()->routeIs('admin.roles*') ? 'activo' : '' }}">
        <span class="icono">&#128274;</span> Roles
    </a>
    <a href="{{ route('admin.usuarios.index') }}"
       class="{{ request()->routeIs('admin.usuarios*') ? 'activo' : '' }}">
        <span class="icono">&#128100;</span> Usuarios
    </a>
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Cultos</p>
    <a href="#"><span class="icono">&#128197;</span> Gestionar cultos</a>
    <a href="#"><span class="icono">&#43;</span> Nuevo orden</a>
</div>
<div class="sidebar-section">
    <p class="sidebar-title">Reportes</p>
    <a href="#"><span class="icono">&#128203;</span> Historial</a>
    <a href="#"><span class="icono">&#128202;</span> Estadísticas</a>
</div>
@endsection

@section('contenido')
<div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-start;">
    <div>
        <h1>Gestión de Usuarios</h1>
        <p>Solo el Secretario General puede administrar los usuarios del sistema</p>
    </div>
    <button class="btn btn-amarillo" onclick="toggleFormulario()">
        &#43; Nuevo usuario
    </button>
</div>

{{-- Formulario desplegable --}}
<div id="formulario-crear" style="display:none; margin-bottom:1.5rem;">
    <div class="form-card">
        <div class="form-card-header">
            <h2>Crear nuevo usuario</h2>
            <button onclick="toggleFormulario()" class="btn-cerrar">&#10005;</button>
        </div>
        <form method="POST" action="{{ route('admin.usuarios.store') }}">
            @csrf
            <div class="form-grid-3">
                <div class="form-group">
                    <label>Nombre <span class="requerido">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                           placeholder="Nombre" class="{{ $errors->has('nombre') ? 'input-error' : '' }}">
                    @error('nombre')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>Apellido <span class="requerido">*</span></label>
                    <input type="text" name="apellido" value="{{ old('apellido') }}"
                           placeholder="Apellido" class="{{ $errors->has('apellido') ? 'input-error' : '' }}">
                    @error('apellido')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>Usuario <span class="requerido">*</span></label>
                    <input type="text" name="usuario" value="{{ old('usuario') }}"
                           placeholder="nombre.usuario" class="{{ $errors->has('usuario') ? 'input-error' : '' }}">
                    @error('usuario')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>Contraseña <span class="requerido">*</span></label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres"
                           class="{{ $errors->has('password') ? 'input-error' : '' }}">
                    @error('password')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>Confirmar contraseña <span class="requerido">*</span></label>
                    <input type="password" name="password_confirmation" placeholder="Repite la contraseña">
                </div>
                <div class="form-group">
                    <label>Rol <span class="requerido">*</span></label>
                    <select name="rol_id" class="{{ $errors->has('rol_id') ? 'input-error' : '' }}">
                        <option value="">— Seleccionar rol —</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" {{ old('rol_id') == $rol->id ? 'selected' : '' }}>
                                {{ ucfirst($rol->nombre_rol) }}
                            </option>
                        @endforeach
                    </select>
                    @error('rol_id')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>Género <span class="requerido">*</span></label>
                    <select name="genero" class="{{ $errors->has('genero') ? 'input-error' : '' }}">
                        <option value="">— Seleccionar —</option>
                        <option value="masculino" {{ old('genero') === 'masculino' ? 'selected' : '' }}>Masculino</option>
                        <option value="femenino"  {{ old('genero') === 'femenino'  ? 'selected' : '' }}>Femenino</option>
                        <option value="otro"      {{ old('genero') === 'otro'      ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('genero')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label>Edad <span class="requerido">*</span></label>
                    <input type="number" name="edad" value="{{ old('edad') }}"
                           placeholder="Edad" min="1" max="120"
                           class="{{ $errors->has('edad') ? 'input-error' : '' }}">
                    @error('edad')<p class="error-msg">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-acciones">
                <button type="button" onclick="toggleFormulario()" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario">&#10003; Guardar usuario</button>
            </div>
        </form>
    </div>
</div>

{{-- Tabla de usuarios --}}
<div class="tabla-wrapper">
    <div class="tabla-header">
        <h2>Usuarios registrados</h2>
        <span style="font-size:13px; color:#999;">{{ $usuarios->count() }} usuarios en total</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre completo</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Género</th>
                <th>Edad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $u)
            <tr>
                <td style="color:#999;">{{ $loop->iteration }}</td>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="avatar">{{ strtoupper(substr($u->nombre,0,1).substr($u->apellido,0,1)) }}</div>
                        <strong>{{ $u->nombre_completo }}</strong>
                    </div>
                </td>
                <td style="color:#555;">{{ $u->usuario }}</td>
                <td>
                    <span class="pill pill-pendiente">
                        {{ ucfirst($u->rol->nombre_rol ?? 'Sin rol') }}
                    </span>
                </td>
                <td>{{ ucfirst($u->genero) }}</td>
                <td>{{ $u->edad }} años</td>
                <td>
                    <span class="pill {{ $u->estado === 'activo' ? 'pill-activo' : 'pill-inactivo' }}">
                        {{ ucfirst($u->estado) }}
                    </span>
                </td>
                <td>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        {{-- Editar --}}
                        <button class="btn btn-primario" style="padding:5px 10px; font-size:12px;"
                            onclick="abrirEditar(
                                {{ $u->id }},
                                '{{ $u->nombre }}',
                                '{{ $u->apellido }}',
                                '{{ $u->usuario }}',
                                '{{ $u->genero }}',
                                {{ $u->edad }},
                                {{ $u->rol_id ?? 'null' }}
                            )">
                            Editar
                        </button>

                        {{-- Activar/Desactivar --}}
                        @if($u->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.usuarios.estado', $u->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn {{ $u->estado === 'activo' ? 'btn-secundario' : 'btn-amarillo' }}"
                                    style="padding:5px 10px; font-size:12px;">
                                {{ $u->estado === 'activo' ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>

                        {{-- Eliminar --}}
                        <form method="POST" action="{{ route('admin.usuarios.destroy', $u->id) }}"
                              onsubmit="return confirm('¿Eliminar al usuario {{ $u->nombre_completo }}? Esta acción no se puede deshacer.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-peligro" style="padding:5px 10px; font-size:12px;">
                                Eliminar
                            </button>
                        </form>
                        @else
                        <span style="font-size:12px; color:#999; padding:5px;">Tu cuenta</span>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; color:#999; padding:2rem;">
                    No hay usuarios registrados aún.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal de edición --}}
<div id="modal-editar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:2rem; width:100%; max-width:540px; border-top:4px solid #1A4FA8; max-height:90vh; overflow-y:auto;">
        <div class="form-card-header" style="margin-bottom:1.25rem;">
            <h2 style="font-size:16px; color:#0D2F6E;">Editar usuario</h2>
            <button onclick="cerrarEditar()" class="btn-cerrar">&#10005;</button>
        </div>
        <form method="POST" id="form-editar">
            @csrf @method('PUT')
            <div class="form-grid-3" style="margin-bottom:1rem;">
                <div class="form-group">
                    <label>Nombre <span class="requerido">*</span></label>
                    <input type="text" name="nombre" id="edit-nombre" placeholder="Nombre">
                </div>
                <div class="form-group">
                    <label>Apellido <span class="requerido">*</span></label>
                    <input type="text" name="apellido" id="edit-apellido" placeholder="Apellido">
                </div>
                <div class="form-group">
                    <label>Usuario <span class="requerido">*</span></label>
                    <input type="text" name="usuario" id="edit-usuario" placeholder="nombre.usuario">
                </div>
                <div class="form-group">
                    <label>Nueva contraseña <span style="color:#999; font-weight:400;">(opcional)</span></label>
                    <input type="password" name="password" placeholder="Dejar vacío para no cambiar">
                </div>
                <div class="form-group">
                    <label>Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" placeholder="Repetir nueva contraseña">
                </div>
                <div class="form-group">
                    <label>Rol <span class="requerido">*</span></label>
                    <select name="rol_id" id="edit-rol">
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre_rol) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Género <span class="requerido">*</span></label>
                    <select name="genero" id="edit-genero">
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Edad <span class="requerido">*</span></label>
                    <input type="number" name="edad" id="edit-edad" min="1" max="120">
                </div>
            </div>
            <div class="form-acciones">
                <button type="button" onclick="cerrarEditar()" class="btn btn-secundario">Cancelar</button>
                <button type="submit" class="btn btn-primario">&#10003; Actualizar usuario</button>
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
        display: flex; justify-content: space-between;
        align-items: center; margin-bottom: 1.25rem;
    }
    .form-card-header h2 { font-size: 15px; font-weight: 600; color: #0D2F6E; }
    .btn-cerrar {
        background: transparent; border: none;
        font-size: 16px; color: #999; cursor: pointer;
        padding: 4px 8px; border-radius: 4px;
    }
    .btn-cerrar:hover { background: #f4f6fa; color: #333; }
    .form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    label { font-size: 13px; font-weight: 600; color: #3a4255; }
    .requerido { color: #C0392B; }
    input[type="text"], input[type="password"],
    input[type="number"], select {
        padding: 9px 12px; border: 1px solid #D1DCF0;
        border-radius: 7px; font-size: 14px; color: #1a1a2e;
        outline: none; transition: border-color 0.2s; width: 100%;
    }
    input:focus, select:focus { border-color: #1A4FA8; }
    .input-error { border-color: #C0392B !important; }
    .error-msg { color: #C0392B; font-size: 12px; }
    .form-acciones { display: flex; justify-content: flex-end; gap: 10px; }
    .avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: #E8F0FB; color: #1A4FA8;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
    @media (max-width: 768px) {
        .form-grid-3 { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
        .form-grid-3 { grid-template-columns: 1fr; }
    }
</style>

<script>
    function toggleFormulario() {
        const form = document.getElementById('formulario-crear');
        const visible = form.style.display === 'block';
        form.style.display = visible ? 'none' : 'block';
        if (!visible) document.querySelector('[name="nombre"]').focus();
    }

    function abrirEditar(id, nombre, apellido, usuario, genero, edad, rolId) {
        document.getElementById('form-editar').action = '/admin/usuarios/' + id;
        document.getElementById('edit-nombre').value   = nombre;
        document.getElementById('edit-apellido').value = apellido;
        document.getElementById('edit-usuario').value  = usuario;
        document.getElementById('edit-genero').value   = genero;
        document.getElementById('edit-edad').value     = edad;
        if (rolId) document.getElementById('edit-rol').value = rolId;
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