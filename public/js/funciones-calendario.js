// Variables globales
let formularioModificado = false;
let cultosDelDiaActual = [];

// URLs base inyectadas desde la vista (window.APP_URLS).
// Fallback a rutas absolutas para servir con `php artisan serve` desde la raíz.
const APP_URLS = window.APP_URLS || {
    cultosDia: '/cultos/dia',
    cultosBase: '/cultos',
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
        alert(`Ya existe un culto programado para esta hora:\n\n${horarioOcupado.nombre_culto}\n${horarioOcupado.tipo} - ${horarioOcupado.hora}\n\nPor favor, selecciona una hora diferente.`);
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

                // Construir HTML de servidores asignados
                let servidoresHtml = '';
                if (culto.asignaciones && culto.asignaciones.length > 0) {
                    servidoresHtml = `
                        <div style="margin-top:1rem; margin-bottom:0.5rem;">
                            <p style="font-size:12px; font-weight:600; color:#0D2F6E; margin:0 0 0.5rem 0;">
                                &#128101; Servidores asignados (${culto.asignaciones.length})
                            </p>
                            <div style="display:flex; flex-wrap:wrap; gap:6px;">`;

                    culto.asignaciones.forEach((asig) => {
                        const initials = asig.servidor_nombre.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                        const confirmadoBadge = asig.confirmado
                            ? '<span style="font-size:9px; color:#1A7A4A; margin-left:4px;">&#10003;</span>'
                            : '<span style="font-size:9px; color:#F5C518; margin-left:4px;">&#9203;</span>';

                        servidoresHtml += `
                                <div style="display:inline-flex; align-items:center; gap:6px; background:#F4F6FA; border:1px solid #D1DCF0; border-radius:20px; padding:4px 10px 4px 4px;">
                                    <div style="width:24px; height:24px; border-radius:50%; background:#E8F0FB; color:#1A4FA8; display:flex; align-items:center; justify-content:center; font-size:9px; font-weight:700;">${initials}</div>
                                    <div>
                                        <span style="font-size:11px; font-weight:600; color:#2a2a3e;">${asig.servidor_nombre}${confirmadoBadge}</span>
                                        <span style="font-size:10px; color:#777; display:block; line-height:1.2;">${asig.rol}</span>
                                    </div>
                                </div>`;
                    });

                    servidoresHtml += `
                            </div>
                        </div>`;
                } else {
                    servidoresHtml = `
                        <div style="margin-top:1rem; padding:0.75rem; background:#F8F9FA; border-radius:6px; text-align:center;">
                            <p style="font-size:12px; color:#999; margin:0;">Sin servidores asignados</p>
                        </div>`;
                }

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

                            ${servidoresHtml}

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

// Event listeners para cerrar modales con tecla ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        confirmarCerrarModalCrear();
        cerrarModalCultos();
    }
});

// Event listeners para cerrar modales al hacer clic fuera del contenido
document.addEventListener('DOMContentLoaded', function() {
    const modalCrear = document.getElementById('modal-crear-culto');
    const modalCultos = document.getElementById('modal-cultos-dia');

    if (modalCrear) {
        modalCrear.addEventListener('click', function(e) {
            if (e.target === this) {
                confirmarCerrarModalCrear();
            }
        });
    }

    if (modalCultos) {
        modalCultos.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalCultos();
            }
        });
    }
});
