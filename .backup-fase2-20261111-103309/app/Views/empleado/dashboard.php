<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Mi panel</h1>
            <p>
                Bienvenido, <?= esc(auth()->user()->username) ?>.
                <?php if ($perfil !== null): ?>
                    <?= esc(trim($perfil['nombre'] . ' ' . $perfil['apellidos'])) ?>
                <?php endif; ?>
            </p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('empleado/asignaciones') ?>">
            Ver mis asignaciones
        </a>
    </div>

    <?php if ($perfil === null): ?>
        <div class="detalle">
            <h3>Perfil sin configurar</h3>
            <p>Tu cuenta no tiene un perfil de empleado asociado. Contacta a tu empresa o al administrador.</p>
        </div>
    <?php else: ?>
        <div class="personal-estadisticas">
            <div class="estadistica-tarjeta">
                <span>Tareas pendientes</span>
                <h2 class="numero-inactivos"><?= esc($pendientes) ?></h2>
            </div>
            <div class="estadistica-tarjeta">
                <span>Tareas completadas</span>
                <h2 class="numero-activos"><?= esc($completadas) ?></h2>
            </div>
        </div>

        <h3>Mis asignaciones</h3>
        <div class="table-responsive"><table>
            <thead>
                <tr>
                    <th>Tarea</th>
                    <th>Fecha</th>
                    <th>Vencimiento</th>
                    <th>Prioridad</th>
                    <th>Estatus</th>
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
                            <?php if (! empty($asignacion['descripcion'])): ?>
                                <br><small><?= esc($asignacion['descripcion']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td data-label="Fecha"><?= esc($asignacion['fecha'] ?? '—') ?></td>
                        <td data-label="Vencimiento"><?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></td>
                        <td data-label="Prioridad"><?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?></td>
                        <td data-label="Estatus">
                            <span class="estado-empleado <?= esc($claseEstado) ?>">
                                <?= esc(ucfirst($estatus)) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if ($asignaciones === []): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b;">No tienes tareas asignadas por el momento.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
<?= $this->endSection() ?>