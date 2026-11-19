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
                    <td data-label="Opciones" class="acciones" style="display: flex; gap: 8px; align-items: center;">
                        <a class="boton-edit" href="<?= site_url('admin/empresas/edit/' . $empresa['id']) ?>">Editar</a>
                        
                        <?php if ((int) $empresa['estatus'] === 1): ?>
                            <form action="<?= site_url('admin/empresas/delete/' . $empresa['id']) ?>" method="post" class="form-confirm" data-title="¿Desactivar empresa?" data-message="La empresa quedará inactiva pero sus datos se conservarán." data-icon="warning" data-confirm="Sí, desactivar" style="margin: 0;">
                                <?= csrf_field() ?>
                                <button class="boton-delete" type="submit" style="background-color: #f59e0b; border: none; color: white; padding: 8px 12px; border-radius: 5px; cursor: pointer;">Desactivar</button>
                            </form>
                        <?php else: ?>
                            <form action="<?= site_url('admin/empresas/activar/' . $empresa['id']) ?>" method="post" class="form-confirm" data-title="¿Activar empresa?" data-message="La empresa volverá a estar operativa." data-icon="question" data-confirm="Sí, activar" style="margin: 0;">
                                <?= csrf_field() ?>
                                <button class="boton-edit" type="submit">Activar</button>
                            </form>
                            
                            <!-- Botón de Eliminar Total -->
                            <button type="button" class="boton-delete" onclick="confirmarBorradoTotal(<?= $empresa['id'] ?>, '<?= esc($empresa['nombre']) ?>')">Eliminar</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <?php if ($empresas === []): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">Aún no hay empresas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table></div>

    <script>
        function confirmarBorradoTotal(id, nombre) {
            Swal.fire({
                title: '¡Atención!',
                html: `Estás a punto de borrar la empresa <strong>${nombre}</strong>.<br><br>Si borras esta empresa, <strong>ya no se podrá recuperar</strong> y se perderán todos sus datos asociados.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d71920',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, estoy seguro',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: '¿Seguro seguro?',
                        text: "Esta acción es irreversible. La empresa desaparecerá de la base de datos.",
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonColor: '#d71920',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Sí, eliminar definitivamente',
                        cancelButtonText: 'No, detener'
                    }).then((finalResult) => {
                        if (finalResult.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '<?= site_url('admin/empresas/eliminar/') ?>' + id;
                            
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '<?= csrf_token() ?>';
                            csrfInput.value = '<?= csrf_hash() ?>';
                            
                            form.appendChild(csrfInput);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            });
        }
    </script>
<?= $this->endSection() ?>