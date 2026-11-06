<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Tareas</h1>
            <p>Las tareas que podrás asignar a tus empleados.</p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('cliente/tareas/new') ?>">
            + Nueva tarea
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Tiempo estimado</th>
                <th>Estatus</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tareas as $tarea): ?>
                <tr>
                    <td>
                        <strong><?= esc($tarea['nombre']) ?></strong>
                    </td>
                    <td>
                        <?= esc(mb_strimwidth((string) ($tarea['descripcion'] ?? ''), 0, 60, '…')) ?>
                    </td>
                    <td>
                        <?= $tarea['tiempo_estimado'] !== null ? esc($tarea['tiempo_estimado'] . ' min') : '—' ?>
                    </td>
                    <td>
                        <span class="estado-empleado <?= (int) $tarea['estatus'] === 1 ? 'activo' : 'inactivo' ?>">
                            <?= (int) $tarea['estatus'] === 1 ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a class="boton-edit" href="<?= site_url('cliente/tareas/edit/' . $tarea['id']) ?>">Editar</a>
                        <form action="<?= site_url('cliente/tareas/delete/' . $tarea['id']) ?>" method="post" onsubmit="return confirm('¿Eliminar esta tarea?');">
                            <?= csrf_field() ?>
                            <button class="boton-delete" type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($tareas === []): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">Aún no hay tareas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?= $this->endSection() ?>
