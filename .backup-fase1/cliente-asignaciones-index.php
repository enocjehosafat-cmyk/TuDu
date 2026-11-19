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

    <div class="table-responsive"><table>
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
                    <td data-label="Tarea">
                        <strong><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></strong>
                    </td>
                    <td data-label="Empleado"><?= esc($asignacion['empleado'] ?? '—') ?></td>
                    <td data-label="Fecha"><?= esc($asignacion['fecha'] ?? '—') ?></td>
                    <td data-label="Vencimiento"><?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></td>
                    <td data-label="Prioridad">
                        <span class="estado-empleado <?= (string) $asignacion['prioridad'] === 'alta' ? 'inactivo' : 'activo' ?>">
                            <?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?>
                        </span>
                    </td>
                    <td data-label="Estatus">
                        <?php
                            $estatus     = $asignacion['estatus'] ?? 'pendiente';
                            $claseEstado = match ($estatus) {
                                'completada' => 'activo',
                                'cancelada'  => 'inactivo',
                                default      => 'pendiente',
                            };
                            $tareaDesactivada = (int) ($asignacion['tarea_estatus'] ?? 1) === 0;
                        ?>
                        <span class="estado-empleado <?= esc($claseEstado) ?>">
                        <?= esc(ucfirst($estatus)) ?>
                        </span>
    <?php if ($tareaDesactivada): ?>
        <br><small style="color: #94a3b8;">(Tarea desactivada)</small>
    <?php endif; ?>
</td>
<td data-label="Opciones" class="acciones">
    <?php if ($estatus === 'cancelada'): ?>
        <a class="boton-edit" href="<?= site_url('cliente/asignaciones/edit/' . $asignacion['id']) ?>">Ver</a>
    <?php else: ?>
        <a class="boton-edit" href="<?= site_url('cliente/asignaciones/edit/' . $asignacion['id']) ?>">Editar</a>
        <form action="<?= site_url('cliente/asignaciones/delete/' . $asignacion['id']) ?>" method="post" onsubmit="return confirm('¿Cancelar esta asignación? El historial y el checklist se conservarán.');">
            <?= csrf_field() ?>
            <button class="boton-delete" type="submit">Cancelar</button>
        </form>
    <?php endif; ?>
</td>
            <?php endforeach; ?>

            <?php if ($asignaciones === []): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b;">Aún no hay asignaciones registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>

    <?php if (isset($pager)): ?>
        <div class="paginacion-contenedor">
            <?= $pager->links('default', 'tudu') ?>
        </div>
    <?php endif; ?>
<?= $this->endSection() ?>