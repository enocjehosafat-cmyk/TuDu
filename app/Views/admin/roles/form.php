<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <?php
        $permisosMarcados = old('permisos');
        if ($permisosMarcados === null) {
            $permisosMarcados = $permisosActuales;
        }
    ?>

    <div class="formulario">
        <h1><?= esc($title) ?></h1>

        <form action="<?= $slug !== null ? site_url('admin/roles/update/' . $slug) : site_url('admin/roles/create') ?>" method="post">
            <?= csrf_field() ?>

            <div class="campo">
                <label for="slug">Identificador *</label>
                <?php if ($slug === null): ?>
                    <input type="text" name="slug" id="slug" value="<?= esc(old('slug', '')) ?>" maxlength="30" pattern="[a-z][a-z0-9_]{2,29}" required>
                    <small>Solo minúsculas, números y guiones bajos. No se puede cambiar después.</small>
                <?php else: ?>
                    <input type="text" value="<?= esc($slug) ?>" disabled>
                    <small>El identificador del rol no se puede cambiar.</small>
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="title">Nombre *</label>
                <input type="text" name="title" id="title" value="<?= esc(old('title', $rol['title'] ?? '')) ?>" maxlength="100" required>
            </div>

            <div class="campo">
                <label for="description">Descripción</label>
                <textarea name="description" id="description" maxlength="255" rows="3"><?= esc(old('description', $rol['description'] ?? '')) ?></textarea>
            </div>

            <div class="campo">
                <label>Permisos</label>
                <?php if ($puedeEditarPermisos): ?>
                    <p><small>Estos permisos son informativos y no cambian el acceso actual al panel.</small></p>
                    <?php foreach ($permissions as $clave => $permiso): ?>
                        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-weight: normal;">
                            <input
                                type="checkbox"
                                name="permisos[]"
                                value="<?= esc($clave) ?>"
                                <?= in_array($clave, (array) $permisosMarcados, true) ? 'checked' : '' ?>
                            >
                            <span>
                                <strong><?= esc($clave) ?></strong>
                                — <?= esc($permiso['description'] ?? '') ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Los permisos de este rol son fijos y no se pueden modificar.</p>
                    <ul>
                        <?php foreach ($permisosActuales as $permiso): ?>
                            <li><strong><?= esc($permiso) ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <button class="boton-crear" type="submit">Guardar rol</button>
            <a href="<?= site_url('admin/roles') ?>" style="margin-left: 10px;">Cancelar</a>
        </form>
    </div>
<?= $this->endSection() ?>
