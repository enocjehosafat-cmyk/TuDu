<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></h1>
            <?php if (! empty($asignacion['descripcion'])): ?>
                <p><?= esc($asignacion['descripcion']) ?></p>
            <?php endif; ?>
        </div>
        <a href="<?= site_url('empleado/asignaciones') ?>">Volver a mis asignaciones</a>
    </div>

    <div class="detalle">
        <p><strong>Fecha:</strong> <?= esc($asignacion['fecha'] ?? '—') ?></p>
        <p><strong>Vencimiento:</strong> <?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></p>
        <p><strong>Prioridad:</strong> <?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?></p>
        <p>
            <strong>Estatus:</strong>
            <span class="estado-empleado <?= $asignacion['estatus'] === 'completada' ? 'activo' : 'inactivo' ?>">
                <?= esc(ucfirst((string) $asignacion['estatus'])) ?>
            </span>
        </p>
        <?php if (! empty($asignacion['notas'])): ?>
            <p><strong>Notas:</strong> <?= esc($asignacion['notas']) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($checklist !== []): ?>
        <h3>Checklist</h3>
        <form action="<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/checklist') ?>" method="post">
            <?= csrf_field() ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">OK</th>
                            <th>Paso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checklist as $item): ?>
                            <tr onclick="this.querySelector('input').click();" style="cursor: pointer;">
                                <td style="text-align: center;" onclick="event.stopPropagation();">
                                    <input type="checkbox" name="completada[]" value="<?= (int) $item['id'] ?>" <?= (int) $item['completada'] === 1 ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <?= esc($item['notas'] ?? '') ?>
                                    <?php if (! empty($item['fecha_completada'])): ?>
                                        <br><small style="color: #64748b;">Completado el <?= esc($item['fecha_completada']) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button class="boton-crear" type="submit" style="margin-top: 10px;">Guardar checklist</button>
        </form>
    <?php else: ?>
        <p style="color: #64748b;">Esta asignación no tiene checklist.</p>
    <?php endif; ?>

    <?php if ($asignacion['estatus'] !== 'completada'): ?>
        <form action="<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/completar') ?>" method="post" style="margin-top: 20px;">
            <?= csrf_field() ?>
            <button class="boton-crear" type="submit">Marcar como completada</button>
        </form>
    <?php endif; ?>
<?= $this->endSection() ?>
