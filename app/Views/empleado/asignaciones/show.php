<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <?php
        $estatus     = $asignacion['estatus'] ?? 'pendiente';
        $claseEstado = match ($estatus) {
            'completada'  => 'activo',
            'cancelada'   => 'inactivo',
            'en_revision' => 'en_revision',
            default       => 'pendiente',
        };
        $soloLectura = $soloLectura ?? ($estatus === 'cancelada');
        $totalFotos  = count($evidencias);
    ?>

    <div class="tareas-encabezado">
        <div>
            <h1><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></h1>
            <?php if (! empty($asignacion['descripcion'])): ?>
                <p><?= esc($asignacion['descripcion']) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= site_url('empleado/asignaciones') ?>">Volver a mis asignaciones</a>
    </div>

    <?php if ($soloLectura): ?>
        <div class="login-error" style="margin-bottom: 20px;">
            Esta asignación fue cancelada. Solo puedes consultar el historial.
        </div>
    <?php endif; ?>

    <div class="detalle">
        <p><strong>Fecha:</strong> <?= esc($asignacion['fecha'] ?? '—') ?></p>
        <p><strong>Vencimiento:</strong> <?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></p>
        <p><strong>Prioridad:</strong> <?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?></p>
        <p>
            <strong>Estatus:</strong>
            <span class="estado-empleado <?= esc($claseEstado) ?>">
                <?= esc(ucfirst(str_replace('_', ' ', $estatus))) ?>
            </span>
        </p>
        <?php if (! empty($asignacion['fecha_completada'])): ?>
            <p><strong>Completada / Enviada el:</strong> <?= date('d/m/Y H:i', strtotime($asignacion['fecha_completada'])) ?></p>
        <?php endif; ?>
        <?php if (! empty($asignacion['notas'])): ?>
            <p><strong>Notas:</strong> <?= esc($asignacion['notas']) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($checklist !== []): ?>
        <h3>Checklist</h3>
        <form action="<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/checklist') ?>" method="post">
            <?= csrf_field() ?>
            <fieldset <?= $soloLectura ? 'disabled' : '' ?> style="border: none; padding: 0; margin: 0;">
                <div class="table-responsive">
                    <table class="checklist-table">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">OK</th>
                                <th>Paso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($checklist as $item): ?>
                                <tr <?= $soloLectura ? '' : 'onclick="this.querySelector(\'input\').click();" style="cursor: pointer;"' ?>>
                                    <td style="text-align: center;" onclick="event.stopPropagation();">
                                        <input type="checkbox" name="completada[]" value="<?= (int) $item['id'] ?>" <?= (int) $item['completada'] === 1 ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <?= esc($item['notas'] ?? '') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </fieldset>
            <?php if (! $soloLectura): ?>
                <button class="boton-crear" type="submit" style="margin-top: 10px;">Guardar checklist</button>
            <?php endif; ?>
        </form>
    <?php else: ?>
        <p style="color: #64748b;">Esta asignación no tiene checklist.</p>
    <?php endif; ?>

    <!-- Galería de Evidencias Generales -->
    <div style="margin-top: 30px; background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <h3 style="margin-top: 0; color: #1e293b;">Evidencias Fotográficas</h3>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 15px;">
            Sube hasta 5 fotos que demuestren que realizaste el trabajo. 
            <?php if ($totalFotos >= 5): ?>
                <strong style="color: #15803d;">¡Has alcanzado el límite de 5 fotos!</strong>
            <?php endif; ?>
        </p>

        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
            <?php foreach ($evidencias as $ev): ?>
                <div style="position: relative;">
                    <a href="<?= base_url('uploads/evidencias/' . $ev['foto']) ?>" target="_blank">
                        <img src="<?= base_url('uploads/evidencias/' . $ev['foto']) ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;">
                    </a>
                    <?php if (!empty($ev['latitud'])): ?>
                        <div style="text-align: center; font-size: 10px; color: #15803d;">📍 GPS</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$soloLectura && $totalFotos < 5 && $estatus !== 'completada' && $estatus !== 'en_revision'): ?>
            <button type="button" class="boton-crear" onclick="abrirModalEvidencia()">
                + Agregar Foto
            </button>
        <?php endif; ?>
    </div>

    <?php if (! $soloLectura && $estatus !== 'completada' && $estatus !== 'en_revision'): ?>
        <form action="<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/completar') ?>" method="post" style="margin-top: 20px;">
            <?= csrf_field() ?>
            <button class="boton-crear" type="submit" style="width: 100%; padding: 15px; font-size: 16px; background-color: #15803d;">Enviar a Revisión</button>
        </form>
    <?php endif; ?>

    <!-- Modal para subir foto -->
    <div id="modal-evidencia" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 20px; border-radius: 12px; width: 90%; max-width: 400px;">
            <h3 style="margin-top: 0;" id="modal-titulo">Subir Evidencia</h3>
            
            <form id="form-evidencia" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <div class="campo">
                    <label>Foto de la tarea</label>
                    <input type="file" name="foto" id="input-foto" accept="image/*" required>
                </div>
                
                <input type="hidden" name="latitud" id="lat-input">
                <input type="hidden" name="longitud" id="lng-input">

                <p id="gps-status" style="font-size: 12px; color: #64748b; margin-bottom: 5px;">Obteniendo ubicación...</p>

                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="button" class="boton-delete" style="flex: 1;" onclick="cerrarModal()">Cerrar</button>
                    <button type="submit" class="boton-crear" style="flex: 1;" id="btn-guardar-evidencia">Guardar Foto</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        let totalFotosJS = <?= $totalFotos ?>;

        function abrirModalEvidencia() {
            document.getElementById('modal-evidencia').style.display = 'flex';
            
            const inputFoto = document.getElementById('input-foto');
            const gpsStatus = document.getElementById('gps-status');

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
            document.getElementById('modal-evidencia').style.display = 'none';
            if (totalFotosJS > <?= $totalFotos ?>) {
                window.location.reload();
            }
        }

        document.getElementById('form-evidencia').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnGuardar = document.getElementById('btn-guardar-evidencia');
            btnGuardar.innerText = 'Subiendo...';
            btnGuardar.disabled = true;

            const formData = new FormData(this);

            fetch('<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/evidencia') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    totalFotosJS = data.total_fotos;
                    
                    if (data.total_fotos !== undefined) {
                        document.getElementById('gps-status').innerText = '✅ Foto ' + data.total_fotos + ' guardada.';
                    } else {
                        document.getElementById('gps-status').innerText = '✅ Foto guardada.';
                    }
                    document.getElementById('gps-status').style.color = '#15803d';
                    
                    document.querySelector('input[name="foto"]').value = '';

                    // 👇 NUEVA LOGICA: GUARDAR Y SALIR 👇
                    if (totalFotosJS >= 5) {
                        btnGuardar.innerText = 'Guardar y Salir';
                        btnGuardar.style.backgroundColor = '#64748b'; // Color gris
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                        
                    } else {
                        btnGuardar.innerText = 'Guardar Siguiente Foto';
                        btnGuardar.disabled = false;
                    }
                } else {
                    alert('Error: ' + data.message);
                    btnGuardar.innerText = 'Guardar Foto';
                    btnGuardar.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión al subir la foto.');
                btnGuardar.innerText = 'Guardar Foto';
                btnGuardar.disabled = false;
            });
        });
    </script>
<?= $this->endSection() ?>