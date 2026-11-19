<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Historial de asignaciones</h1>
            <p>Tareas completadas y canceladas de tu empresa.</p>
        </div>
        <a class="boton-edit" href="<?= site_url('cliente/asignaciones') ?>" style="background-color: #64748b; color: white; padding: 12px 18px; border-radius: 6px; display: inline-flex; align-items: center; font-weight: bold; font-size: 13px;">
            Volver a asignaciones
        </a>
    </div>

    <div class="table-responsive"><table>
        <thead>
            <tr>
                <th>Tarea</th>
                <th>Empleado</th>
                <th>Fecha</th>
                <th>Prioridad</th>
                <th>Estatus</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($asignaciones as $asignacion): ?>
                <?php
                    $estatus     = $asignacion['estatus'] ?? 'pendiente';
                    $claseEstado = match ($estatus) {
                        'completada' => 'activo',
                        'cancelada'  => 'inactivo',
                        default      => 'pendiente',
                    };
                ?>
                <tr>
                    <td data-label="Tarea">
                        <strong><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></strong>
                    </td>
                    <td data-label="Empleado"><?= esc($asignacion['empleado'] ?? '—') ?></td>
                    <td data-label="Fecha"><?= esc($asignacion['fecha'] ?? '—') ?></td>
                    <td data-label="Prioridad"><?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?></td>
                    <td data-label="Estatus">
                        <span class="estado-empleado <?= esc($claseEstado) ?>">
                            <?= esc(ucfirst($estatus)) ?>
                        </span>
                    </td>
                    <td data-label="Opciones" class="acciones">
                        <a class="boton-edit" href="<?= site_url('cliente/asignaciones/show/' . $asignacion['id']) ?>">Ver</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($asignaciones === []): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b;">No hay tareas en el historial.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>
<?= $this->endSection() ?>