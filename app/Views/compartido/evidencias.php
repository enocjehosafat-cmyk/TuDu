<?php
    $estatus = $asignacion['estatus'] ?? 'pendiente';
    $totalFotos = count($evidencias);
    $puedeEditar = $esEmpleado && !$soloLectura && $estatus !== 'completada' && $estatus !== 'en_revision';
?>

<style>
    .foto-marco-redondeado {
        border-radius: 12px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
        object-fit: contain !important;
        max-height: 70vh !important;
    }
    .galeria-fotos { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 15px; }
    .foto-contenedor {
        position: relative; width: 100px; height: 100px; border-radius: 8px;
        overflow: hidden; border: 2px solid transparent; transition: border-color 0.2s, transform 0.2s;
    }
    .foto-contenedor:hover { border-color: #15803d; transform: scale(1.02); }
    .foto-contenedor img { width: 100%; height: 100%; object-fit: cover; cursor: pointer; }
    .btn-borrar-foto {
        position: absolute; top: 4px; right: 4px; background: #dc2626; color: white; border: none;
        border-radius: 50%; width: 24px; height: 24px; font-size: 14px; cursor: pointer;
        display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); z-index: 10;
    }
    .btn-borrar-foto:hover { background: #b91c1c; }
    .modal-content-responsive {
        background: white; padding: 20px; border-radius: 12px; width: 95%; max-width: 400px; box-sizing: border-box;
    }
</style>

<div style="margin-top: 30px; background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
    <h3 style="margin-top: 0; color: #1e293b;">Evidencias Fotográficas</h3>
    
    <?php if ($esEmpleado): ?>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">
            Sube hasta 5 fotos que demuestren que realizaste el trabajo. 
            <?php if ($totalFotos >= 5): ?>
                <strong style="color: #15803d;">¡Has alcanzado el límite de 5 fotos!</strong>
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <div class="galeria-fotos">
        <?php foreach ($evidencias as $ev): ?>
            <div id="contenedor-foto-<?= $ev['id'] ?>" class="foto-contenedor">
                <a href="javascript:void(0);" onclick="abrirLightbox('<?= base_url('uploads/evidencias/' . $ev['foto']) ?>')">
                    <img src="<?= base_url('uploads/evidencias/' . $ev['foto']) ?>" alt="Evidencia">
                </a>
                
                <?php if ($puedeEditar): ?>
                    <form id="form-borrar-foto-<?= $ev['id'] ?>" action="<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/evidencia/eliminar/' . $ev['id']) ?>" method="POST" style="display:none;"><?= csrf_field() ?></form>
                    <button type="button" class="btn-borrar-foto" onclick="confirmarBorrarFoto(<?= $ev['id'] ?>)" title="Eliminar foto">&times;</button>
                <?php endif; ?>

                <?php if (!empty($ev['latitud'])): ?>
                    <div style="position: absolute; bottom: 0; left: 0; width: 100%; background: rgba(255,255,255,0.8); text-align: center; font-size: 10px; color: #15803d; font-weight: bold; padding: 2px 0;">📍 GPS</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($puedeEditar && $totalFotos < 5): ?>
        <button type="button" class="boton-crear" onclick="abrirModalEvidencia()" id="btn-agregar-foto">+ Agregar Foto</button>
    <?php endif; ?>
</div>

<?php if ($esEmpleado): ?>
    <div id="modal-evidencia" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center;">
        <div class="modal-content-responsive" style="padding: 25px;">
            <h3 style="margin-top: 0; color: #1e293b; text-align: center;">Subir Evidencia</h3>
            <p style="font-size: 13px; color: #64748b; text-align: center; margin-bottom: 20px;">Toma una foto clara de la tarea realizada.</p>
            
            <form id="form-evidencia" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <input type="file" name="foto" id="input-foto" accept="image/*" capture="environment" required style="display: none;">
                
                <div onclick="document.getElementById('input-foto').click()" style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 25px 15px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 140px;" onmouseover="this.style.borderColor='#15803d'; this.style.background='#f0fdf4';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    <div style="font-size: 14px; color: #15803d; font-weight: bold; line-height: 1.4;" id="texto-archivo">
                        Toca aquí para abrir la cámara<br>o seleccionar foto
                    </div>
                </div>
                
                <input type="hidden" name="latitud" id="lat-input">
                <input type="hidden" name="longitud" id="lng-input">

                <p id="gps-status" style="font-size: 12px; color: #64748b; margin-top: 15px; text-align: center;">Obteniendo ubicación...</p>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="boton-delete" style="flex: 1; padding: 14px; border-radius: 8px; font-weight: bold;" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="boton-crear" style="flex: 1; padding: 14px; border-radius: 8px; font-weight: bold; background-color: #15803d;" id="btn-guardar-evidencia" disabled>Guardar Foto</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!$soloLectura && $estatus !== 'completada' && $estatus !== 'en_revision'): ?>
        <form action="<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/completar') ?>" method="post" style="margin-top: 20px;">
            <?= csrf_field() ?>
            <?php if ($totalFotos >= 5): ?>
                <button class="boton-crear" type="submit" style="width: 100%; padding: 15px; font-size: 16px; background-color: #15803d;">Enviar a Revisión</button>
            <?php else: ?>
                <button type="button" class="boton-crear" style="width: 100%; padding: 15px; font-size: 16px; background-color: #94a3b8; cursor: not-allowed;" onclick="Swal.fire('Faltan evidencias', 'Debes subir exactamente 5 fotos para enviar la tarea a revisión. Llevas <?= $totalFotos ?> de 5.', 'warning')">
                    Enviar a Revisión (Faltan fotos)
                </button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
<?php endif; ?>

<script>
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    let totalFotosJS = <?= $totalFotos ?>;
    let fotosSubidasEnEstaSesion = 0;

    function abrirLightbox(urlImagen) {
        Swal.fire({
            imageUrl: urlImagen, imageAlt: 'Evidencia fotográfica', imageWidth: '100%', imageHeight: 'auto',
            showConfirmButton: false, showCloseButton: true, width: 'auto', maxWidth: '95%', padding: '15px', 
            background: '#ffffff', customClass: { image: 'foto-marco-redondeado' }, backdrop: 'rgba(0,0,0,0.85)',
            showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
            hideClass: { popup: 'animate__animated animate__fadeOutUp animate__faster' }
        });
    }

    <?php if ($esEmpleado): ?>
    function abrirModalEvidencia() {
        document.getElementById('modal-evidencia').style.display = 'flex';
        const inputFoto = document.getElementById('input-foto');
        const gpsStatus = document.getElementById('gps-status');
        const btnGuardar = document.getElementById('btn-guardar-evidencia');
        const textoArchivo = document.getElementById('texto-archivo');
        
        btnGuardar.innerText = 'Guardar Foto';
        btnGuardar.style.backgroundColor = ''; 
        btnGuardar.disabled = true; 
        inputFoto.value = '';
        textoArchivo.innerText = 'Toca aquí para abrir la cámara o seleccionar foto';
        textoArchivo.style.color = '#15803d';

        if (!isMobile) {
            inputFoto.removeAttribute('capture');
            gpsStatus.innerText = '📍 GPS no disponible en PC. La foto se guardará sin ubicación.';
            gpsStatus.style.color = '#b45309';
            return; 
        }

        inputFoto.setAttribute('capture', 'environment');
        gpsStatus.innerText = 'Obteniendo ubicación GPS...';
        gpsStatus.style.color = '#64748b';

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    document.getElementById('lat-input').value = position.coords.latitude;
                    document.getElementById('lng-input').value = position.coords.longitude;
                    gpsStatus.innerText = '📍 Ubicación obtenida correctamente.';
                    gpsStatus.style.color = '#15803d';
                },
                (error) => {
                    gpsStatus.innerText = '⚠️ Error de GPS. Puedes guardar sin ubicación.';
                    gpsStatus.style.color = '#b91c1c';
                }
            );
        } else {
            gpsStatus.innerText = 'Dispositivo sin GPS.';
            gpsStatus.style.color = '#b91c1c';
        }
    }

    function cerrarModal() {
        if (fotosSubidasEnEstaSesion > 0) { window.location.reload(); } else { document.getElementById('modal-evidencia').style.display = 'none'; }
    }

    document.getElementById('input-foto').addEventListener('change', function() {
        const btnGuardar = document.getElementById('btn-guardar-evidencia');
        const textoArchivo = document.getElementById('texto-archivo');
        if (this.files && this.files.length > 0) {
            btnGuardar.disabled = false;
            textoArchivo.innerText = '✅ Foto seleccionada';
        } else {
            btnGuardar.disabled = true;
            textoArchivo.innerText = 'Toca aquí para abrir la cámara o seleccionar foto';
        }
    });

    document.getElementById('form-evidencia')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const btnGuardar = document.getElementById('btn-guardar-evidencia');
        btnGuardar.innerText = 'Subiendo...';
        btnGuardar.disabled = true;

        const file = this.querySelector('input[type="file"]').files[0];
        const formData = new FormData();
        
        const enviarPeticion = (datos) => {
            fetch('<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/evidencia') ?>', {
                method: 'POST', body: datos, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    totalFotosJS = data.total_fotos !== undefined ? data.total_fotos : totalFotosJS + 1;
                    fotosSubidasEnEstaSesion++; 
                    document.getElementById('gps-status').innerText = '✅ Foto ' + totalFotosJS + ' guardada.';
                    document.getElementById('gps-status').style.color = '#15803d';
                    document.getElementById('input-foto').value = '';
                    document.getElementById('texto-archivo').innerText = 'Toca aquí para abrir la cámara o seleccionar foto';
                    document.getElementById('texto-archivo').style.color = '#15803d';

                    if (totalFotosJS >= 5) {
                        btnGuardar.innerText = 'Límite alcanzado. Recargando...';
                        btnGuardar.style.backgroundColor = '#64748b';
                        setTimeout(() => { window.location.reload(); }, 1000);
                    } else {
                        btnGuardar.innerText = 'Guardar Siguiente Foto';
                        btnGuardar.disabled = true; 
                    }
                } else {
                    Swal.fire('Error', data.message, 'error');
                    btnGuardar.innerText = 'Guardar Foto';
                    btnGuardar.disabled = false;
                }
            })
            .catch(error => {
                Swal.fire('Error', 'No se pudo conectar. Revisa tu internet.', 'error');
                btnGuardar.innerText = 'Guardar Foto';
                btnGuardar.disabled = false;
            });
        };

        if (file) {
            if (file.size > 1024 * 1024) { // Si pesa más de 1MB, comprimir
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                const img = new Image();
                img.onload = function() {
                    const maxWidth = 1200;
                    const scale = maxWidth / img.width;
                    canvas.width = maxWidth;
                    canvas.height = img.height * scale;
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob((blob) => {
                        formData.append('foto', blob, 'foto_comprimida.jpg');
                        formData.append('latitud', document.getElementById('lat-input').value);
                        formData.append('longitud', document.getElementById('lng-input').value);
                        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                        enviarPeticion(formData);
                    }, 'image/jpeg', 0.7);
                };
                img.src = URL.createObjectURL(file);
            } else {
                formData.append('foto', file);
                formData.append('latitud', document.getElementById('lat-input').value);
                formData.append('longitud', document.getElementById('lng-input').value);
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                enviarPeticion(formData);
            }
        }
    });

    function confirmarBorrarFoto(evidenciaId) {
        Swal.fire({
            title: '¿Borrar esta foto?', text: "Esta acción no se puede deshacer y liberará un espacio.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, borrar', cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) { document.getElementById('form-borrar-foto-' + evidenciaId).submit(); }
        });
    }
    <?php endif; ?>
</script>
