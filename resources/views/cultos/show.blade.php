@extends('layouts.app')
@section('titulo', $culto->nombre_culto)

@section('sidebar')
<div class="sidebar-section">
    <p class="sidebar-title">Administración</p>
    <a href="{{ route('dashboard') }}">
        <span class="icono">&#127968;</span> Dashboard
    </a>
    <a href="{{ route('servidores.index') }}">
        <span class="icono">&#128101;</span> Servidores
    </a>
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
@php
    $estado = $culto->estado;
    $pillColor = match($estado) {
        'realizado'  => 'pill-inactivo',
        'hoy'        => 'pill-pendiente',
        'programado' => 'pill-activo',
    };
@endphp

{{-- Modal de Reemplazo de Servidor --}}
<div id="modal-reemplazar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45);
     z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; padding:2rem; width:100%;
                max-width:500px; border-top:4px solid #F5C518; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h2 style="font-size:18px; font-weight:600; color:#0D2F6E;">
                🔄 Reemplazar Servidor
            </h2>
            <button onclick="cerrarModalReemplazar()"
                    style="background:transparent; border:none; font-size:20px; cursor:pointer;">
                &#10005;
            </button>
        </div>

        <p style="font-size:14px; color:#555; margin-bottom:1rem; line-height:1.6;">
            Vas a reemplazar a <strong id="servidor-actual-nombre"></strong> en el rol <strong id="servidor-actual-rol"></strong>.
            Por favor, selecciona el nuevo servidor y el motivo del reemplazo.
        </p>

        <form method="POST" id="form-reemplazar">
            @csrf

            <div style="margin-bottom:1rem;">
                <label style="font-size:13px; font-weight:600; color:#3a4255; display:block; margin-bottom:5px;">
                    Nuevo servidor <span style="color:#C0392B;">*</span>
                </label>
                <select name="nuevo_servidor_id" required
                        style="width:100%; padding:10px 12px; border:1px solid #D1DCF0; border-radius:7px; font-size:14px; outline:none;">
                    <option value="">— Seleccionar servidor —</option>
                    @foreach($servidores as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:1rem;">
                <label style="font-size:13px; font-weight:600; color:#3a4255; display:block; margin-bottom:5px;">
                    Motivo del reemplazo <span style="color:#C0392B;">*</span>
                </label>
                <select name="motivo_reemplazo" required id="motivo-reemplazo-select"
                        style="width:100%; padding:10px 12px; border:1px solid #D1DCF0; border-radius:7px; font-size:14px; outline:none;">
                    <option value="">— Seleccionar motivo —</option>
                    @foreach(\App\Models\Asignacion::motivosReemplazo() as $key => $motivo)
                        <option value="{{ $key }}">{{ $motivo }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:1.5rem; display:none;" id="descripcion-container">
                <label style="font-size:13px; font-weight:600; color:#3a4255; display:block; margin-bottom:5px;">
                    Descripción adicional
                </label>
                <textarea name="motivo_descripcion" rows="3"
                          placeholder="Escribe más detalles sobre el motivo..."
                          style="width:100%; padding:10px 12px; border:1px solid #D1DCF0; border-radius:7px; font-size:13px; resize:vertical; font-family:inherit;"></textarea>
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="cerrarModalReemplazar()"
                        style="padding:10px 20px; background:#E8E8E8; border:none; border-radius:7px; cursor:pointer; font-size:14px;">
                    Cancelar
                </button>
                <button type="submit"
                        style="padding:10px 20px; background:#F5C518; border:none; border-radius:7px; cursor:pointer; font-size:14px; font-weight:600; color:#0D2F6E;">
                    🔄 Reemplazar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Encabezado --}}
<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem;">
    <div>
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
            <a href="{{ route('cultos.index') }}"
               style="color:#1A4FA8; font-size:13px; text-decoration:none;">
                &#8592; Volver a cultos
            </a>
        </div>
        <h1 style="font-size:22px; font-weight:600; color:#0D2F6E;">
            {{ $culto->nombre_culto }}
        </h1>
        <p style="color:#555; font-size:14px; margin-top:4px;">
            {{ $culto->fecha->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
            &mdash; {{ $culto->fecha->format('g:i A') }}
            &nbsp;<span class="pill {{ $pillColor }}">{{ ucfirst($estado) }}</span>
        </p>
        @if($culto->descripcion)
        <p style="color:#777; font-size:13px; margin-top:6px;">{{ $culto->descripcion }}</p>
        @endif
    </div>
    <a href="{{ route('cultos.pdf', $culto->id) }}"
       class="btn btn-primario"
       target="_blank"
       style="background:#C0392B; color:#fff; text-decoration:none;"
       title="Ver PDF del orden del culto">
        &#128196; Ver PDF
    </a>
</div>

<div style="display:grid; grid-template-columns: 1fr 360px; gap:1.5rem;" class="grid-cultos-detalle">

    {{-- Columna principal: Asignaciones --}}
    <div>
        {{-- Tabla de servidores asignados --}}
        <div class="tabla-wrapper">
            <div class="tabla-header">
                <h2>Servidores asignados</h2>
                <span style="font-size:13px; color:#999;">
                    {{ $culto->asignaciones->count() }} asignaciones
                </span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:90px;">N°</th>
                        <th>Servidor</th>
                        <th>Rol de servicio</th>
                        <th>Confirmado</th>
                        <th>WhatsApp</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($culto->asignaciones as $asignacion)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; justify-content:center; gap:4px;">
                                @if($culto->fecha->addHours(12)->isFuture())
                                @if(!$loop->first)
                                <form method="POST" action="{{ route('cultos.asignaciones.mover', [$culto->id, $asignacion->id]) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="direccion" value="arriba">
                                    <button type="submit" title="Subir en el orden"
                                            style="padding:2px 7px; font-size:11px; background:#E8F0FB; color:#1A4FA8; border:1px solid #D1DCF0; border-radius:5px; cursor:pointer; line-height:1;"
                                            onmouseover="this.style.background='#d8e6f8'"
                                            onmouseout="this.style.background='#E8F0FB'">&#9650;</button>
                                </form>
                                @else
                                <span style="display:inline-block; width:26px;"></span>
                                @endif
                                @endif

                                <span style="font-size:13px; font-weight:700; color:#0D2F6E;">{{ $loop->iteration }}</span>

                                @if($culto->fecha->addHours(12)->isFuture())
                                @if(!$loop->last)
                                <form method="POST" action="{{ route('cultos.asignaciones.mover', [$culto->id, $asignacion->id]) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="direccion" value="abajo">
                                    <button type="submit" title="Bajar en el orden"
                                            style="padding:2px 7px; font-size:11px; background:#E8F0FB; color:#1A4FA8; border:1px solid #D1DCF0; border-radius:5px; cursor:pointer; line-height:1;"
                                            onmouseover="this.style.background='#d8e6f8'"
                                            onmouseout="this.style.background='#E8F0FB'">&#9660;</button>
                                </form>
                                @else
                                <span style="display:inline-block; width:26px;"></span>
                                @endif
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div class="avatar">
                                    {{ strtoupper(substr($asignacion->servidor->nombre_completo ?? 'S', 0, 2)) }}
                                </div>
                                {{ $asignacion->servidor->nombre_completo ?? '—' }}
                            </div>
                        </td>
                        <td>{{ $asignacion->rol_servicio }}</td>
                        <td>
                            <span class="pill {{ $asignacion->confirmado ? 'pill-activo' : 'pill-pendiente' }}">
                                {{ $asignacion->confirmado ? 'Confirmado' : 'Pendiente' }}
                            </span>
                        </td>
                        <td>
                            @if($asignacion->servidor)
                            <a href="{{ $asignacion->servidor->link_whatsapp }}"
                               target="_blank"
                               style="color:#1A7A4A; font-size:13px; text-decoration:none; font-weight:500;">
                                &#128222; Contactar
                            </a>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                {{-- Reemplazar (permitido hasta 12 horas después del culto) --}}
                                @if($culto->fecha->addHours(12)->isFuture() && $asignacion->estado === 'asignado')
                                <button class="btn"
                                        style="padding:8px 12px; font-size:12px; background:#F39C12; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:500; min-width:90px; white-space:nowrap;"
                                        onmouseover="this.style.background='#E67E22'"
                                        onmouseout="this.style.background='#F39C12'"
                                        onclick="abrirModalReemplazar(
                                            {{ $asignacion->id }},
                                            '{{ addslashes(e($asignacion->servidor->nombre_completo)) }}',
                                            '{{ addslashes(e($asignacion->rol_servicio)) }}'
                                        )">
                                    🔄 Reemplazar
                                </button>
                                @endif

                                {{-- Confirmar/Desconfirmar (permitido hasta 12 horas después del culto) --}}
                                @if($culto->fecha->addHours(12)->isFuture() && $asignacion->estado === 'asignado')
                                <form method="POST" action="{{ route('cultos.asignaciones.confirmar', [$culto->id, $asignacion->id]) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            style="padding:8px 12px; font-size:12px; {{ $asignacion->confirmado ? 'background:#7F8C8D; color:#fff;' : 'background:#1A7A4A; color:#fff;' }} border:none; border-radius:6px; cursor:pointer; font-weight:500; min-width:90px; white-space:nowrap;"
                                            onmouseover="this.style.background='{{ $asignacion->confirmado ? '#6C757D' : '#169B56' }}'"
                                            onmouseout="this.style.background='{{ $asignacion->confirmado ? '#7F8C8D' : '#1A7A4A' }}'">
                                        {{ $asignacion->confirmado ? '❌ Desconfirmar' : '✓ Confirmar' }}
                                    </button>
                                </form>
                                @endif

                                {{-- Eliminar (permitido hasta 12 horas después del culto) --}}
                                @if($culto->fecha->addHours(12)->isFuture())
                                <form method="POST" action="{{ route('cultos.asignaciones.destroy', [$culto->id, $asignacion->id]) }}"
                                      onsubmit="return confirm('¿Eliminar asignación de {{ addslashes(e($asignacion->servidor->nombre_completo)) }}?')"
                                      style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            style="padding:8px 12px; font-size:12px; background:#C0392B; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:500; min-width:90px; white-space:nowrap;"
                                            onmouseover="this.style.background='#E74C3C'"
                                            onmouseout="this.style.background='#C0392B'">
                                        🗑️ Eliminar
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; color:#999; padding:2rem;">
                            No hay servidores asignados a este culto aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Lista de servidores para asignar (permitido hasta 12 horas después del culto) --}}
        @if($culto->fecha->addHours(12)->isFuture())
        <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; padding:1.25rem; margin-bottom:1rem;">
            <h3 style="font-size:15px; font-weight:600; color:#0D2F6E; margin:0 0 1rem 0;">
                &#128101; Asignar servidor al culto
            </h3>

            {{-- Filtros --}}
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1rem; padding:1rem; background:#F8F9FA; border-radius:7px;">
                {{-- Búsqueda por nombre --}}
                <div>
                    <label style="font-size:12px; font-weight:600; color:#3a4255; display:block; margin-bottom:4px;">
                        &#128269; Buscar por nombre
                    </label>
                    <input type="text" id="buscar-servidor" placeholder="Escribe el nombre..."
                           style="width:100%; padding:8px 12px; border:1px solid #D1DCF0; border-radius:7px; font-size:13px; outline:none;">
                </div>

                {{-- Filtro por género --}}
                <div>
                    <label style="font-size:12px; font-weight:600; color:#3a4255; display:block; margin-bottom:4px;">
                        &#127979; Género
                    </label>
                    <select id="filtro-genero" style="width:100%; padding:8px 12px; border:1px solid #D1DCF0; border-radius:7px; font-size:13px; outline:none;">
                        <option value="">Todos los géneros</option>
                        @foreach($generos as $genero)
                            <option value="{{ $genero->idgenero }}">{{ $genero->denominacion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Tabla de servidores --}}
            <div style="max-height:400px; overflow-y:auto; border:1px solid #D1DCF0; border-radius:7px;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead style="background:#F4F6FA; position:sticky; top:0; z-index:1;">
                        <tr>
                            <th style="padding:10px; text-align:left; font-size:12px; color:#555; border-bottom:2px solid #D1DCF0;">Servidor</th>
                            <th style="padding:10px; text-align:center; font-size:12px; color:#555; border-bottom:2px solid #D1DCF0; width:120px;">Participaciones</th>
                            <th style="padding:10px; text-align:center; font-size:12px; color:#555; border-bottom:2px solid #D1DCF0; width:100px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-servidores">
                        @php
                            $idsAsignados = $culto->asignaciones->pluck('servidor_id')->toArray();
                        @endphp
                        @foreach($servidores as $servidor)
                        @php
                            $yaAsignado = in_array($servidor->id, $idsAsignados);
                        @endphp
                        <tr class="fila-servidor"
                            data-nombre="{{ strtolower($servidor->nombre_completo) }}"
                            data-genero="{{ $servidor->idgenero }}"
                            @if($yaAsignado) data-asignado="1" @endif
                            style="border-bottom:1px solid #eee; {{ $yaAsignado ? 'opacity:0.5; background:#F8F9FA;' : '' }}">
                            <td style="padding:10px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div class="avatar">
                                        {{ strtoupper(substr($servidor->nombre_completo, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="font-size:13px; font-weight:600; color:#2a2a3e;">{{ $servidor->nombre_completo }}
                                            @if($yaAsignado)
                                            <span style="font-size:10px; padding:2px 6px; background:#1A7A4A; color:#fff; border-radius:4px; margin-left:4px;">Ya asignado</span>
                                            @endif
                                        </div>
                                        <div style="font-size:11px; color:#777;">
                                            {{ $servidor->cargo }}
                                            @if($servidor->genero)
                                            <span style="margin-left:6px; padding:2px 6px; background:#E8F0FB; color:#1A4FA8; border-radius:4px; font-size:10px;">
                                                {{ $servidor->genero->denominacion }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:10px; text-align:center;">
                                <span class="pill pill-pendiente" style="font-size:11px;">
                                    {{ $servidor->asignaciones_activas_count }} participacion{{ $servidor->asignaciones_activas_count != 1 ? 'es' : '' }}
                                </span>
                            </td>
                            <td style="padding:10px; text-align:center;">
                                @if($yaAsignado)
                                <span style="font-size:12px; color:#999;">—</span>
                                @else
                                <button type="button" class="btn btn-amarillo btn-asignar"
                                        style="padding:6px 12px; font-size:12px; width:100%;"
                                        onclick="mostrarCampoRol({{ $servidor->id }})">
                                    &#43; Asignar
                                </button>
                                @endif
                            </td>
                        </tr>
                        {{-- Fila oculta para el formulario de rol --}}
                        <tr id="fila-rol-{{ $servidor->id }}" style="display:none; background:#F8F9FA;">
                            <td colspan="3" style="padding:12px;">
                                <form method="POST" action="{{ route('cultos.asignaciones.store', $culto->id) }}" class="form-asignar">
                                    @csrf
                                    <input type="hidden" name="servidor_id" value="{{ $servidor->id }}">
                                    <div style="display:flex; gap:10px; align-items:center;">
                                        <div style="flex:1;">
                                            <label style="font-size:12px; font-weight:600; color:#3a4255; display:block; margin-bottom:4px;">
                                                Rol de servicio para {{ $servidor->nombre_completo }} <span style="color:#C0392B;">*</span>
                                            </label>
                                            <select name="rol_servicio" required
                                                    style="width:100%; padding:8px 12px; border:1px solid #D1DCF0; border-radius:7px; font-size:13px; outline:none;">
                                                <option value="">— Seleccionar rol —</option>
                                                @foreach(\App\Models\Asignacion::rolesServicio() as $key => $nombre)
                                                    <option value="{{ $nombre }}">{{ $nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primario" style="padding:8px 16px; font-size:13px; align-self:flex-end;">
                                            &#10003; Guardar
                                        </button>
                                        <button type="button" class="btn btn-secundario" onclick="ocultarCampoRol({{ $servidor->id }})"
                                                style="padding:8px 16px; font-size:13px; align-self:flex-end;">
                                            &#10005; Cancelar
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Columna lateral: Mensaje --}}
    <div>
        <div style="background:#fff; border:1px solid #D1DCF0; border-radius:10px; overflow:hidden;">
            <div style="background:#0D2F6E; padding:1rem 1.25rem;">
                <h2 style="font-size:14px; font-weight:600; color:#fff;">
                    &#128172; Mensaje pastoral
                </h2>
                <p style="font-size:12px; color:#93aad4; margin-top:2px;">
                    Visible para todos los roles
                </p>
            </div>

            {{-- Mensaje existente --}}
            @if($culto->mensaje)
            <div style="padding:1.25rem; border-bottom:1px solid #D1DCF0;">
                <p style="font-size:14px; color:#2a2a3e; line-height:1.7;">
                    {{ $culto->mensaje }}
                </p>
                <p style="font-size:12px; color:#999; margin-top:8px;">
                    — {{ $culto->mensajeAutor?->nombre_completo ?? 'Desconocido' }}
                    &middot; {{ $culto->updated_at->isoFormat('D MMM YYYY, h:mm A') }}
                </p>
            </div>
            @else
            <div style="padding:1.25rem; border-bottom:1px solid #D1DCF0;">
                <p style="font-size:13px; color:#999; text-align:center; padding:1rem 0;">
                    No hay mensaje registrado aún.
                </p>
            </div>
            @endif

            {{-- Formulario para dejar mensaje --}}
            @if(auth()->user()->tieneRol('secretario_general') || auth()->user()->tieneRol('pastor'))
            <div style="padding:1.25rem;">
                <form method="POST" action="{{ route('cultos.mensaje', $culto->id) }}">
                    @csrf
                    <div style="margin-bottom:10px;">
                        <label style="font-size:13px; font-weight:600; color:#3a4255; display:block; margin-bottom:5px;">
                            {{ $culto->mensaje ? 'Actualizar mensaje' : 'Dejar un mensaje' }}
                        </label>
                        <textarea name="mensaje" rows="4"
                                  placeholder="Escribe una sugerencia, instrucción o mensaje para este culto..."
                                  style="width:100%; padding:9px 12px; border:1px solid #D1DCF0;
                                         border-radius:7px; font-size:13px; resize:vertical;
                                         font-family:inherit; outline:none; color:#1a1a2e;">{{ old('mensaje', $culto->mensaje) }}</textarea>
                        @error('mensaje')
                            <p style="color:#C0392B; font-size:12px; margin-top:4px;">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primario" style="width:100%; justify-content:center;">
                        &#10003; {{ $culto->mensaje ? 'Actualizar mensaje' : 'Guardar mensaje' }}
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

</div>

<style>
    .avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: #E8F0FB; color: #1A4FA8;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
    .btn-verde { background: #1A7A4A; color: #fff; }

    @media (max-width: 900px) {
        .grid-cultos-detalle {
            grid-template-columns: 1fr !important;
        }
    }

    @media (max-width: 600px) {
        #modal-reemplazar > div {
            margin: 0.75rem;
            max-width: calc(100% - 1.5rem) !important;
            padding: 1.25rem !important;
        }
        #modal-reemplazar select,
        #modal-reemplazar textarea {
            font-size: 16px !important;
        }
        #modal-reemplazar > div > div:last-of-type {
            flex-direction: column;
        }
        #modal-reemplazar > div > div:last-of-type button {
            width: 100%;
            text-align: center;
        }
    }
</style>

<script>
// Función para abrir el modal de reemplazo
function abrirModalReemplazar(asignacionId, servidorNombre, rolServicio) {
    // Mostrar información del servidor actual
    document.getElementById('servidor-actual-nombre').textContent = servidorNombre;
    document.getElementById('servidor-actual-rol').textContent = rolServicio;

    // Configurar la acción del formulario
    const form = document.getElementById('form-reemplazar');
    form.action = '/cultos/{{ $culto->id }}/asignaciones/' + asignacionId + '/reemplazar';

    // Mostrar el modal
    document.getElementById('modal-reemplazar').style.display = 'flex';
}

// Función para cerrar el modal
function cerrarModalReemplazar() {
    document.getElementById('modal-reemplazar').style.display = 'none';

    // Limpiar el formulario
    document.getElementById('form-reemplazar').reset();
    document.getElementById('descripcion-container').style.display = 'none';
}

// Mostrar campo de descripción si se selecciona "otro"
document.addEventListener('DOMContentLoaded', function() {
    const motivoSelect = document.getElementById('motivo-reemplazo-select');
    const descripcionContainer = document.getElementById('descripcion-container');

    if (motivoSelect) {
        motivoSelect.addEventListener('change', function() {
            if (this.value === 'otro') {
                descripcionContainer.style.display = 'block';
            } else {
                descripcionContainer.style.display = 'none';
            }
        });
    }
});

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModalReemplazar();
        // Cerrar todas las filas de rol si se presiona ESC
        document.querySelectorAll('[id^="fila-rol-"]').forEach(fila => {
            fila.style.display = 'none';
        });
    }
});

// Cerrar modal al tocar fuera
document.getElementById('modal-reemplazar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalReemplazar();
});

// Funciones para la tabla de servidores
function mostrarCampoRol(servidorId) {
    // Ocultar todas las filas de rol primero
    document.querySelectorAll('[id^="fila-rol-"]').forEach(fila => {
        fila.style.display = 'none';
    });

    // Mostrar la fila de rol del servidor seleccionado
    const filaRol = document.getElementById('fila-rol-' + servidorId);
    if (filaRol) {
        filaRol.style.display = 'table-row';
        // Enfocar el campo de texto
        setTimeout(() => {
            filaRol.querySelector('[name="rol_servicio"]').focus();
        }, 100);
    }
}

function ocultarCampoRol(servidorId) {
    const filaRol = document.getElementById('fila-rol-' + servidorId);
    if (filaRol) {
        filaRol.style.display = 'none';
        // Limpiar el select
        filaRol.querySelector('[name="rol_servicio"]').selectedIndex = 0;
    }
}

// Filtros de servidores
document.addEventListener('DOMContentLoaded', function() {
    const buscarServidor = document.getElementById('buscar-servidor');
    const filtroGenero = document.getElementById('filtro-genero');

    function filtrarServidores() {
        const termino = buscarServidor ? buscarServidor.value.toLowerCase().trim() : '';
        const generoSeleccionado = filtroGenero ? filtroGenero.value : '';
        const filas = document.querySelectorAll('.fila-servidor');

        filas.forEach(fila => {
            const nombreServidor = fila.getAttribute('data-nombre');
            const generoServidor = fila.getAttribute('data-genero');

            // Verificar si coincide con la búsqueda por nombre
            const coincideNombre = !termino || nombreServidor.includes(termino);

            // Verificar si coincide con el género
            const coincideGenero = !generoSeleccionado || generoServidor === generoSeleccionado;

            // Mostrar u ocultar la fila según los filtros
            if (coincideNombre && coincideGenero) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
                // También ocultar la fila de rol correspondiente
                const servidorId = fila.querySelector('.btn-asignar')?.getAttribute('onclick')?.match(/\d+/)?.[0];
                if (servidorId) {
                    const filaRol = document.getElementById('fila-rol-' + servidorId);
                    if (filaRol) filaRol.style.display = 'none';
                }
            }
        });
    }

    // Event listeners para los filtros
    if (buscarServidor) {
        buscarServidor.addEventListener('input', filtrarServidores);
    }
    if (filtroGenero) {
        filtroGenero.addEventListener('change', filtrarServidores);
    }
});
</script>
@endsection