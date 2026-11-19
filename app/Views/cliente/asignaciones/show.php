<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Detalle de asignación</h1>
            <p>Información de la tarea asignada.</p>
        </div>
        <a class="boton-edit" href="<?= site_url('cliente/asignaciones') ?>" style="background-color: #64748b; color: white; padding: 12px 18px; border-radius: 6px; display: inline-flex; align-items: center; font-weight: bold; font-size: 13px;">
            Volver a asignaciones
        </a>
    </div>

    <div class="formulario" style="max-width: 600px;">
        <div class="campo">
            <label>Tarea</label>
            <p style="font-size: 16px; font-weight: bold; color: #1f2937; margin: 0;"><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></p>
        </div>

        <div class="campo">
            <label>Empleado</label>
            <p style="font-size: 16px; margin: 0;"><?= esc($asignacion['empleado'] ?? '—') ?></p>
        </div>

        <div class="campo">
            <label>Fecha</label>
            <p style="font-size: 16px; margin: 0;"><?= esc($asignacion['fecha'] ?? '—') ?></p>
        </div>

        <div class="campo">
            <label>Fecha de vencimiento</label>
            <p style="font-size: 16px; margin: 0;"><?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></p>
        </div>

        <div class="campo">
            <label>Prioridad</label>
            <p style="font-size: 16px; margin: 0;"><?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?></p>
        </div>

        <div class="campo">
            <label>Estatus</label>
            <?php
                $estatus = $asignacion['estatus'] ?? 'pendiente';
                $clase = match ($estatus) {
                    'completada'  => 'activo',
                    'cancelada'   => 'inactivo',
                    'en_revision' => 'en_revision', // <-- NUEVO COLOR
                    default       => 'pendiente',
                };
            ?>
            <p style="margin: 0;">
                <span class="estado-empleado <?= esc($clase) ?>">
                    <?= esc(ucfirst(str_replace('_', ' ', $estatus))) ?>
                </span>
            </p>
        </div>

        <?php if (! empty($asignacion['fecha_completada'])): ?>
            <div class="campo">
                <label>Completada / Enviada el</label>
                <p style="font-size: 16px; margin: 0;"><?= date('d/m/Y H:i', strtotime($asignacion['fecha_completada'])) ?></p>
            </div>
        <?php endif; ?>

        <?php if (! empty($asignacion['notas'])): ?>
            <div class="campo">
                <label>Notas</label>
                <p style="font-size: 14px; margin: 0; color: #4b5563;"><?= nl2br(esc($asignacion['notas'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Galería de Evidencias -->
        <?php if (!empty($evidencias)): ?>
            <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <label>Evidencias del Empleado (<?= count($evidencias) ?>/5)</label>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                    <?php foreach ($evidencias as $ev): ?>
                        <div style="position: relative;">
                            <a href="<?= base_url('uploads/evidencias/' . $ev['foto']) ?>" target="_blank">
                                <img src="<?= base_url('uploads/evidencias/' . $ev['foto']) ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                            </a>
                            <?php if (!empty($ev['latitud'])): ?>
                                <div style="text-align: center; font-size: 11px; margin-top: 4px;">
                                    <a href="https://www.google.com/maps?q=<?= $ev['latitud'] ?>,<?= $ev['longitud'] ?>" target="_blank" style="color: #15803d; text-decoration: none; font-weight: bold;">📍 Maps</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <label>Evidencias del Empleado</label>
                <p style="font-size: 13px; color: #94a3b8; margin-top: 5px;">El empleado aún no ha subido fotos.</p>
            </div>
        <?php endif; ?>

        <!-- Botones de Aprobación (Solo si está en revisión) -->
        <?php if ($asignacion['estatus'] === 'en_revision'): ?>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px dashed #cbd5e1; display: flex; gap: 15px;">
                <form action="<?= site_url('cliente/asignaciones/rechazar/' . $asignacion['id']) ?>" method="post" style="flex: 1;">
                    <?= csrf_field() ?>
                    <button class="boton-delete" type="submit" style="width: 100%; padding: 12px;" onclick="return confirm('¿Rechazar esta tarea? El empleado tendrá que hacerla de nuevo.');">Rechazar Tarea</button>
                </form>
                <form action="<?= site_url('cliente/asignaciones/aprobar/' . $asignacion['id']) ?>" method="post" style="flex: 1;">
                    <?= csrf_field() ?>
                    <button class="boton-crear" type="submit" style="width: 100%; padding: 12px; background-color: #15803d;">Aprobar y Completar</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (! empty($asignacion['tarea_estatus']) && $asignacion['tarea_estatus'] == 0): ?>
            <div class="login-error" style="margin-top: 20px;">
                Esta tarea ha sido desactivada en el sistema.
            </div>
        <?php endif; ?>
    </div>
<?= $this->endSection() ?>