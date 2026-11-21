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
                    'en_revision' => 'en_revision',
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

        <!-- Llamamos a la vista compartida -->
        <?= view('compartido/evidencias', [
            'asignacion' => $asignacion, 
            'evidencias' => $evidencias, 
            'esEmpleado' => false, 
            'soloLectura' => false
        ]) ?>

        <!-- Botones de Aprobación (Solo si está en revisión) -->
        <?php if ($asignacion['estatus'] === 'en_revision'): ?>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px dashed #cbd5e1; display: flex; gap: 15px;">
                <form id="form-rechazar" action="<?= site_url('cliente/asignaciones/rechazar/' . $asignacion['id']) ?>" method="post" style="flex: 1;">
                    <?= csrf_field() ?>
                    <button type="button" class="boton-delete" onclick="confirmarRechazo()" style="width: 100%; padding: 12px;">Rechazar Tarea</button>
                </form>
                
                <form id="form-aprobar" action="<?= site_url('cliente/asignaciones/aprobar/' . $asignacion['id']) ?>" method="post" style="flex: 1;">
                    <?= csrf_field() ?>
                    <button type="button" class="boton-crear" onclick="confirmarAprobacion()" style="width: 100%; padding: 12px; background-color: #15803d;">Aprobar y Completar</button>
                </form>
            </div>

            <script>
                function confirmarAprobacion() {
                    Swal.fire({
                        title: '¿Aprobar esta tarea?',
                        text: "La tarea se marcará como completada y el empleado será notificado.",
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonColor: '#15803d',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sí, aprobar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('form-aprobar').submit();
                        }
                    });
                }

                function confirmarRechazo() {
                    Swal.fire({
                        title: '¿Rechazar esta tarea?',
                        text: "El empleado tendrá que realizarla de nuevo. Esta acción quedará registrada.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sí, rechazar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('form-rechazar').submit();
                        }
                    });
                }
            </script>
        <?php endif; ?>

        <?php if (! empty($asignacion['tarea_estatus']) && $asignacion['tarea_estatus'] == 0): ?>
            <div class="login-error" style="margin-top: 20px;">
                Esta tarea ha sido desactivada en el sistema.
            </div>
        <?php endif; ?>
    </div>
<?= $this->endSection() ?>
