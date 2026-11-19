<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Asignaciones</h1>
            <p>Tareas asignadas a los empleados de tu empresa.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a class="boton-edit" href="<?= site_url('cliente/asignaciones/historial') ?>" style="background-color: #64748b; padding: 12px 18px; border-radius: 6px; display: inline-flex; align-items: center; font-weight: bold; font-size: 13px;">
                Ver historial
            </a>
            <a class="crear tareas-boton-crear" href="<?= site_url('cliente/asignaciones/new') ?>">
                + Nueva asignación
            </a>
        </div>
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
                <?php
                    $estatus          = $asignacion['estatus'] ?? 'pendiente';
                    $claseEstado      = match ($estatus) {
                        'completada' => 'activo',
                        'cancelada'  => 'inactivo',
                        default      => 'pendiente',
                    };
                    $tareaDesactivada = (int) ($asignacion['tarea_estatus'] ?? 1) === 0;
                ?>
                <tr>
                    <td data-label="Tarea">
                        <strong><?= esc($asignacion['tarea'] ?? 'Tarea eliminada') ?></strong>
                    </td>
                    <td data-label="Empleado"><?= esc($asignacion['empleado'] ?? '—') ?></td>
                    <td data-label="Fecha"><?= esc($asignacion['fecha'] ?? '—') ?></td>
                    <td data-label="Vencimiento"><?= esc($asignacion['fecha_vencimiento'] ?? '—') ?></td>
                    <td data-label="Prioridad">
                        <span class="estado-empleado <?= (string) ($asignacion['prioridad'] ?? 'media') === 'alta' ? 'inactivo' : 'activo' ?>">
                            <?= esc(ucfirst((string) ($asignacion['prioridad'] ?? '—'))) ?>
                        </span>
                    </td>
                    <td data-label="Estatus">
                        <?php
                            $estatus = $asignacion['estatus'] ?? 'pendiente';
                            $claseEstado = match ($estatus) {
                                'completada'  => 'activo',
                                'cancelada'   => 'inactivo',
                                'en_revision' => 'en_revision', // <-- NUEVO
                                default       => 'pendiente',
                            };
                            // Formatear el texto para que diga "En Revisión" en vez de "En_revision"
                            $textoEstatus = ucfirst(str_replace('_', ' ', $estatus));
                        ?>
                        <span class="estado-empleado <?= esc($claseEstado) ?>">
                            <?= esc($textoEstatus) ?>
                        </span>
                        <?php if ($tareaDesactivada): ?>
                            <br><small style="color: #94a3b8;">(Tarea desactivada)</small>
                        <?php endif; ?>
                    </td>                    
                    <td data-label="Opciones" class="acciones">
                        <?php if ($estatus === 'en_revision'): ?>
                            <!-- Si está en revisión, el botón dice "Revisar" y lleva al detalle -->
                            <a class="boton-edit" href="<?= site_url('cliente/asignaciones/show/' . $asignacion['id']) ?>" style="background-color: #0284c7;">Revisar</a>
                        <?php elseif ($estatus === 'completada' || $estatus === 'cancelada'): ?>
                            <!-- Si ya se completó o canceló, solo se puede ver -->
                            <a class="boton-edit" href="<?= site_url('cliente/asignaciones/show/' . $asignacion['id']) ?>">Ver</a>
                        <?php else: ?>
                            <!-- Si está pendiente, se puede editar o cancelar -->
                            <a class="boton-edit" href="<?= site_url('cliente/asignaciones/edit/' . $asignacion['id']) ?>">Editar</a>
                            <form action="<?= site_url('cliente/asignaciones/delete/' . $asignacion['id']) ?>" method="post" class="form-confirm" data-title="¿Cancelar asignación?" data-message="El historial y el checklist se conservarán." data-icon="warning" data-confirm="Sí, cancelar">
                                <?= csrf_field() ?>
                                <button class="boton-delete" type="submit">Cancelar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($asignaciones === []): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b;">Aún no hay asignaciones registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>
<?= $this->endSection() ?>
