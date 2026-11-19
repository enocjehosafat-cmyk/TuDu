<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Mis empleados</h1>
            <p>Gestiona al personal de tu empresa.</p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('cliente/empleados/new') ?>">
            + Nuevo empleado
        </a>
    </div>

    <div class="table-responsive"><table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Estatus</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empleados as $empleado): ?>
                <tr>
                    <td data-label="Nombre">
                        <strong><?= esc(trim($empleado['nombre'] . ' ' . $empleado['apellidos'])) ?></strong>
                    </td>
                    <td data-label="Usuario"><?= esc($empleado['username'] ?? '—') ?></td>
                    <td data-label="Correo"><?= esc($empleado['email'] ?? '—') ?></td>
                    <td data-label="Teléfono"><?= esc($empleado['telefono'] ?? '—') ?></td>
                    <td data-label="Estatus">
                        <span class="estado-empleado <?= (int) $empleado['estatus'] === 1 ? 'activo' : 'inactivo' ?>">
                            <?= (int) $empleado['estatus'] === 1 ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td data-label="Opciones" class="acciones">
                        <a class="boton-edit" href="<?= site_url('cliente/empleados/edit/' . $empleado['id']) ?>">Editar</a>
                        <?php if ((int) $empleado['usuario_id'] !== (int) auth()->id()): ?>
                            <form action="<?= site_url('cliente/empleados/delete/' . $empleado['id']) ?>" method="post" onsubmit="return confirm('¿Eliminar este empleado?');">
                                <?= csrf_field() ?>
                                <button class="boton-delete" type="submit">Eliminar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($empleados === []): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b;">Aún no hay empleados registrados.</td>
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