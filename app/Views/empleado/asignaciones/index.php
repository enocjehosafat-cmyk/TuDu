<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Mis asignaciones</h1>
            <p>Tareas que te han asignado.</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tarea</th>
                <th>Fecha</th>
                <th>Vencimiento</th>
                <th>Prioridad</th>
                <th>Estatus</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($asignaciones as $asignacion): ?>
                <tr>
                    <td>
                        <strong><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></strong>
                        <?php if (! empty($asignacion['descripcion'])): ?>
                            <br><small><?= esc(mb_strimwidth((string) $asignacion['descripcion'], 0, 60, '…')) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($asignacion['fecha'] ?? '—') ?></td>
                    <td><?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></td>
                    <td><?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?></td>
                    <td>
                        <span class="estado-empleado <?= $asignacion['estatus'] === 'completada' ? 'activo' : 'inactivo' ?>">
                            <?= esc(ucfirst((string) $asignacion['estatus'])) ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a class="boton-edit" href="<?= site_url('empleado/asignaciones/' . $asignacion['id']) ?>">Ver detalle</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($asignaciones === []): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b;">No tienes tareas asignadas por el momento.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?= $this->endSection() ?>
