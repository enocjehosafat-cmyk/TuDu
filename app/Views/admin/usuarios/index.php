<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Gestión de usuarios</h1>
            <p>Administra los accesos y roles del sistema.</p>
        </div>
    </div>

    <form action="<?= site_url('admin/usuarios') ?>" method="get" style="margin-bottom: 20px; display: flex; gap: 10px; width: 100%;">
        <input
            type="search"
            name="q"
            value="<?= esc($busqueda) ?>"
            placeholder="Buscar por usuario, correo, nombre o empresa"
            style="flex: 1; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px;"
        >
        <button class="boton-crear" type="submit">Buscar</button>
        <?php if ($busqueda !== ''): ?>
            <a href="<?= site_url('admin/usuarios') ?>" class="boton-edit" style="display: inline-flex; align-items: center; padding: 0 16px;">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive"><table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Roles</th>
                <th>Empresa</th>
                <th>Estatus</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $fila): ?>
                <tr>
                    <td data-label="Usuario">
                        <strong><?= esc($fila['username']) ?></strong>
                    </td>
                    <td data-label="Nombre">
                        <?php if (! empty($fila['nombre'])): ?>
                            <?= esc(trim($fila['nombre'] . ' ' . ($fila['apellidos'] ?? ''))) ?>
                        <?php else: ?>
                            <span style="color: #94a3b8;">Sin perfil</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Correo"><?= esc($fila['email'] ?? '—') ?></td>
                    <td data-label="Roles">
                        <?php if ($fila['grupos'] === []): ?>
                            <span class="estado-empleado inactivo">Pendiente</span>
                        <?php else: ?>
                            <?php foreach ($fila['grupos'] as $grupo): ?>
                                <span class="estado-empleado activo" style="margin-right: 4px;">
                                    <?= esc($roles[$grupo]['title'] ?? $grupo) ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td data-label="Empresa"><?= esc($fila['empresa_nombre'] ?? '—') ?></td>
                    <td data-label="Estatus">
                        <span class="estado-empleado <?= (int) $fila['active'] === 1 ? 'activo' : 'inactivo' ?>">
                            <?= (int) $fila['active'] === 1 ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td data-label="Opciones" class="acciones">
                        <a class="boton-edit" href="<?= site_url('admin/usuarios/edit/' . $fila['id']) ?>">Editar</a>
                        <?php if ((int) $fila['id'] !== (int) auth()->id()): ?>
                            <form action="<?= site_url('admin/usuarios/delete/' . $fila['id']) ?>" method="post" class="form-confirm" data-title="¿Eliminar usuario?" data-message="Esta acción borrará al usuario del sistema." data-icon="warning" data-confirm="Sí, eliminar">
                                <?= csrf_field() ?>
                                <button class="boton-delete" type="submit">Eliminar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if ($usuarios === []): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b;">No se encontraron usuarios.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>
<?= $this->endSection() ?>

