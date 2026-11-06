<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Asignaciones</h1>
            <p>Tareas asignadas a los empleados de tu empresa.</p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('cliente/asignaciones/new') ?>">
            + Nueva asignación
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tarea</th>
                <th>Empleado</th>
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
                    <td><strong><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></strong></td>
                    <td><?= esc($asignacion['empleado'] ?? '—') ?></td>
                    <td><?= esc($asignacion['fecha'] ?? '—') ?></td>
                    <td><?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></td>
                    <td>
                        <span class="estado-empleado <?= (string) $asignacion['prioridad'] === 'alta' ? 'inactivo' : 'activo' ?>">
                            <?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?>
                        </span>
                    </td>
                    <td>
                        <span class="estado-empleado <?= $asignacion['estatus'] === 'completada' ? 'activo' : 'inactivo' ?>">
                            <?= esc(ucfirst((string) $asignacion['estatus'])) ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a class="boton-edit" href="<?= site_url('cliente/asignaciones/edit/' . $asignacion['id']) ?>">Editar</a>
                        <form action="<?= site_url('cliente/asignaciones/delete/' . $asignacion['id']) ?>" method="post" onsubmit="return confirm('¿Eliminar esta asignación? Se borrará también su checklist.');">
                            <?= csrf_field() ?>
                            <button class="boton-delete" type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($asignaciones === []): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b;">Aún no hay asignaciones registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?= $this->endSection() ?>
