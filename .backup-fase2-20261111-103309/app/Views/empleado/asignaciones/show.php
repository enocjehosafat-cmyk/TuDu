<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <?php
        $estatus     = $asignacion['estatus'] ?? 'pendiente';
        $claseEstado = match ($estatus) {
            'completada' => 'activo',
            'cancelada'  => 'inactivo',
            default      => 'pendiente',
        };
        $soloLectura = $soloLectura ?? ($estatus === 'cancelada');
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
                <?= esc(ucfirst($estatus)) ?>
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
            <fieldset <?= $soloLectura ? 'disabled' : '' ?> style="border: none; padding: 0; margin: 0;">
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
                                <tr <?= $soloLectura ? '' : 'onclick="this.querySelector(\'input\').click();" style="cursor: pointer;"' ?>>
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
            </fieldset>
            <?php if (! $soloLectura): ?>
                <button class="boton-crear" type="submit" style="margin-top: 10px;">Guardar checklist</button>
            <?php endif; ?>
        </form>
    <?php else: ?>
        <p style="color: #64748b;">Esta asignación no tiene checklist.</p>
    <?php endif; ?>

    <?php if (! $soloLectura && $estatus !== 'completada'): ?>
        <form action="<?= site_url('empleado/asignaciones/' . $asignacion['id'] . '/completar') ?>" method="post" style="margin-top: 20px;">
            <?= csrf_field() ?>
            <button class="boton-crear" type="submit">Marcar como completada</button>
        </form>
    <?php endif; ?>
<?= $this->endSection() ?>