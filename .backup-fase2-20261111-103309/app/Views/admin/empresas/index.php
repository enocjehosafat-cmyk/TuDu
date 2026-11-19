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

    <div class="table-responsive"><table>
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
                    <td data-label="ID" style="display:none;"><?= esc($empresa['id']) ?></td>
                    <td data-label="Nombre">
                        <strong>#<?= esc($empresa['id']) ?> - <?= esc($empresa['nombre']) ?></strong>
                    </td>
                    <td data-label="RFC"><?= esc($empresa['rfc'] ?? '—') ?></td>
                    <td data-label="Dirección"><?= esc($empresa['direccion'] ?? '—') ?></td>
                    <td data-label="Estatus">
                        <span class="estado-empleado <?= (int) $empresa['estatus'] === 1 ? 'activo' : 'inactivo' ?>">
                            <?= (int) $empresa['estatus'] === 1 ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </td>
                    <td data-label="Opciones" class="acciones">
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
            
            <?php if ($empresas === []): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b;">Aún no hay empresas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>
<?= $this->endSection() ?>
