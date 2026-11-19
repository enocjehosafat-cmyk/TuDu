<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Auditoría del sistema</h1>
            <p>Registro de las últimas 100 acciones realizadas en la plataforma.</p>
        </div>
    </div>

    <div class="table-responsive"><table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <?php
                    $claseAccion = match ($log['action']) {
                        'completar' => 'activo',   // Verde
                        'cancelar'  => 'inactivo', // Rojo
                        'eliminar'  => 'inactivo', // Rojo
                        'crear'     => 'pendiente',// Amarillo
                        default     => 'activo',
                    };
                ?>
                <tr>
                    <td data-label="Fecha"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                    <td data-label="Usuario"><strong><?= esc($log['user_name']) ?></strong></td>
                    <td data-label="Acción">
                        <span class="estado-empleado <?= esc($claseAccion) ?>">
                            <?= esc(ucfirst($log['action'])) ?>
                        </span>
                    </td>
                    <td data-label="Descripción"><?= esc($log['description']) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if ($logs === []): ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b;">No hay acciones registradas todavía.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>
<?= $this->endSection() ?>