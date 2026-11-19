<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1><?= esc($rol['title']) ?></h1>
            <p><?= esc($rol['description'] ?? '') ?></p>
        </div>
        <div class="acciones">
            <a class="boton-edit" href="<?= site_url('admin/roles/edit/' . $slug) ?>">Editar rol</a>
            <?php if ($puedeEliminar): ?>
                <form action="<?= site_url('admin/roles/delete/' . $slug) ?>" method="post" onsubmit="return confirm('¿Eliminar este rol?');">
                    <?= csrf_field() ?>
                    <button class="boton-delete" type="submit">Eliminar rol</button>
                </form>
            <?php endif; ?>
            <a class="boton-edit" href="<?= site_url('admin/roles') ?>">Volver a roles</a>
        </div>
    </div>

    <div class="detalle" style="margin-bottom: 25px;">
        <h3>Permisos del rol</h3>
        <?php if ($permisos === []): ?>
            <p>Este rol no tiene permisos asignados.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($permisos as $permiso): ?>
                    <li>
                        <strong><?= esc($permiso) ?></strong>
                        — <?= esc($permissions[$permiso]['description'] ?? '') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <h3>Miembros del rol</h3>
    <table style="margin-bottom: 25px;">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Empresa</th>
                <th>Estatus</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td data-label="Usuario">
                        <strong><?= esc($usuario['username']) ?></strong>
                    </td>
                    <td data-label="Nombre">
                        <?php if (! empty($usuario['nombre'])): ?>
                            <?= esc(trim($usuario['nombre'] . ' ' . ($usuario['apellidos'] ?? ''))) ?>
                        <?php else: ?>
                            <span style="color: #94a3b8;">Sin perfil</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Empresa"><?= esc($usuario['empresa_nombre'] ?? '—') ?></td>
                    <td data-label="Estatus">
                        <span class="estado-empleado <?= (int) $usuario['active'] === 1 ? 'activo' : 'inactivo' ?>">
                            <?= (int) $usuario['active'] === 1 ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td data-label="Opciones" class="acciones">
                        <form action="<?= site_url('admin/roles/remove-user/' . $slug . '/' . $usuario['id']) ?>" method="post" onsubmit="return confirm('¿Retirar este rol al usuario?');">
                            <?= csrf_field() ?>
                            <button class="boton-delete" type="submit">Retirar rol</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($usuarios === []): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">Este rol todavía no tiene miembros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>

    <div class="formulario">
        <h3>Agregar usuario al rol</h3>

        <?php if ($disponibles === []): ?>
            <p>No hay usuarios disponibles para asignar a este rol.</p>
        <?php else: ?>
            <form action="<?= site_url('admin/roles/add-user/' . $slug) ?>" method="post">
                <?= csrf_field() ?>

                <div class="campo">
                    <label for="user_id">Usuario</label>
                    <select name="user_id" id="user_id" required>
                        <option value="">Selecciona un usuario</option>
                        <?php foreach ($disponibles as $usuario): ?>
                            <option value="<?= esc($usuario['id']) ?>">
                                <?= esc($usuario['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button class="boton-crear" type="submit">Asignar rol</button>
            </form>
        <?php endif; ?>
    </div>
<?= $this->endSection() ?>
