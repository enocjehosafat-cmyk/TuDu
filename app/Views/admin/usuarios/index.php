<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Gestión de usuarios</h1>
            <p>Crea cuentas, asigna roles y administra el acceso al sistema.</p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('admin/usuarios/new') ?>">
            + Nuevo usuario
        </a>
    </div>

    <form action="<?= site_url('admin/usuarios') ?>" method="get" style="margin-bottom: 20px; display: flex; gap: 10px;">
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

    <table>
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
                    <td><?= esc($fila['username']) ?></td>
                    <td>
                        <?php if (! empty($fila['nombre'])): ?>
                            <?= esc(trim($fila['nombre'] . ' ' . ($fila['apellidos'] ?? ''))) ?>
                        <?php else: ?>
                            <span style="color: #94a3b8;">Sin perfil</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($fila['email'] ?? '—') ?></td>
                    <td>
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
                    <td><?= esc($fila['empresa_nombre'] ?? '—') ?></td>
                    <td>
                        <span class="estado-empleado <?= (int) $fila['active'] === 1 ? 'activo' : 'inactivo' ?>">
                            <?= (int) $fila['active'] === 1 ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a class="boton-edit" href="<?= site_url('admin/usuarios/edit/' . $fila['id']) ?>">Editar</a>
                        <?php if ((int) $fila['id'] !== (int) auth()->id()): ?>
                            <form action="<?= site_url('admin/usuarios/delete/' . $fila['id']) ?>" method="post" onsubmit="return confirm('¿Eliminar este usuario?');">
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
    </table>
<?= $this->endSection() ?>
