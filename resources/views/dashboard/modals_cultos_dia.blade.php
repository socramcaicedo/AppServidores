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
@media (max-width: 600px) {
    #modal-cultos-dia > div {
        margin: 0.75rem;
        max-width: calc(100% - 1.5rem) !important;
        max-height: 95vh !important;
    }
    #modal-cultos-dia h2 { font-size: 15px !important; }
}
</style>

<script>
document.getElementById('modal-cultos-dia').addEventListener('click', function(e) {
    if (e.target === this) {
        if (typeof cerrarModalCultos === 'function') cerrarModalCultos();
    }
});
</script>