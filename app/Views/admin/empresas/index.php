<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Gestión de empresas</h1>
            <p>Administra los clientes registrados en TuDu.</p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('admin/empresas/new') ?>">
            + Nueva empresa
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>RFC</th>
                <th>Dirección</th>
                <th>Estatus</th>
                <th>Opciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empresas as $empresa): ?>
                <tr>
                    <td><?= esc($empresa['id']) ?></td>
                    <td><?= esc($empresa['nombre']) ?></td>
                    <td><?= esc($empresa['rfc'] ?? '—') ?></td>
                    <td><?= esc($empresa['direccion'] ?? '—') ?></td>
                    <td>
                        <span class="estado-empleado <?= (int) $empresa['estatus'] === 1 ? 'activo' : 'inactivo' ?>">
                            <?= (int) $empresa['estatus'] === 1 ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </td>
                    <td class="acciones">
                        <a class="boton-edit" href="<?= site_url('admin/empresas/edit/' . $empresa['id']) ?>">Editar</a>
                        <?php if ((int) $empresa['estatus'] === 1): ?>
                            <form action="<?= site_url('admin/empresas/delete/' . $empresa['id']) ?>" method="post" onsubmit="return confirm('¿Desactivar esta empresa?');">
                                <?= csrf_field() ?>
                                <button class="boton-delete" type="submit">Desactivar</button>
                            </form>
                        <?php else: ?>
                            <form action="<?= site_url('admin/empresas/activar/' . $empresa['id']) ?>" method="post">
                                <?= csrf_field() ?>
                                <button class="boton-edit" type="submit">Activar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?= $this->endSection() ?>
