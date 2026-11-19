<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <?php
        $rolesMarcados = old('roles');
        if ($rolesMarcados === null) {
            $rolesMarcados = $rolesActuales;
        }
        $empresaSeleccionada = old('empresa_id', $perfil['empresa_id'] ?? '');
        $activo = old('active', $usuario !== null ? (int) $usuario->active : 1);
    ?>

    <div class="formulario">
        <h1><?= esc($title) ?></h1>

        <form action="<?= $usuario !== null ? site_url('admin/usuarios/update/' . $usuario->id) : site_url('admin/usuarios/create') ?>" method="post">
            <?= csrf_field() ?>

            <div class="campo">
                <label for="username">Nombre de usuario *</label>
                <input type="text" name="username" id="username" value="<?= esc(old('username', $usuario->username ?? '')) ?>" maxlength="30" required>
                <small>Solo letras, números y puntos.</small>
            </div>

            <div class="campo">
                <label for="email">Correo electrónico *</label>
                <input type="email" name="email" id="email" value="<?= esc(old('email', $usuario?->getEmail() ?? '')) ?>" maxlength="254" required>
            </div>

            <div class="campo">
                <label for="nombre">Nombre *</label>
                <input type="text" name="nombre" id="nombre" value="<?= esc(old('nombre', $perfil['nombre'] ?? '')) ?>" maxlength="100" required>
            </div>

            <div class="campo">
                <label for="apellidos">Apellidos *</label>
                <input type="text" name="apellidos" id="apellidos" value="<?= esc(old('apellidos', $perfil['apellidos'] ?? '')) ?>" maxlength="100" required>
            </div>

            <div class="campo">
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" value="<?= esc(old('telefono', $perfil['telefono'] ?? '')) ?>" maxlength="20">
            </div>

            <div class="campo">
                <label for="empresa_id">Empresa</label>
                <select name="empresa_id" id="empresa_id">
                    <option value="">Sin empresa (no aplica a Cliente ni Empleado)</option>
                    <?php foreach ($empresas as $empresa): ?>
                        <option value="<?= esc($empresa['id']) ?>" <?= (int) $empresaSeleccionada === (int) $empresa['id'] ? 'selected' : '' ?>>
                            <?= esc($empresa['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Obligatoria para los roles Cliente y Empleado. El Super Admin nunca pertenece a una empresa.</small>
            </div>

            <div class="campo">
                <label>Roles *</label>
                <?php foreach ($rolesDisponibles as $slug => $rol): ?>
                    <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-weight: normal;">
                        <input
                            type="checkbox"
                            name="roles[]"
                            value="<?= esc($slug) ?>"
                            data-rol="<?= esc($slug) ?>"
                            <?= in_array($slug, (array) $rolesMarcados, true) ? 'checked' : '' ?>
                        >
                        <?= esc($rol['title']) ?>
                        <?php if (! empty($rol['description'])): ?>
                            <small>— <?= esc($rol['description']) ?></small>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="campo">
                <label for="password">Contraseña <?= $usuario === null ? '*' : '(dejar vacía para conservarla)' ?></label>
                <input type="password" name="password" id="password" minlength="8" <?= $usuario === null ? 'required' : '' ?>>
            </div>

            <div class="campo">
                <label for="password_confirm">Confirmar contraseña <?= $usuario === null ? '*' : '' ?></label>
                <input type="password" name="password_confirm" id="password_confirm" minlength="8" <?= $usuario === null ? 'required' : '' ?>>
            </div>

            <div class="campo">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
                    <input type="checkbox" name="active" value="1" <?= (int) $activo === 1 ? 'checked' : '' ?>>
                    Cuenta activa
                </label>
            </div>

            <button class="boton-crear" type="submit">Guardar usuario</button>
            <a href="<?= site_url('admin/usuarios') ?>" style="margin-left: 10px;">Cancelar</a>
        </form>
    </div>

    <script>
        const roles = document.querySelectorAll('input[name="roles[]"]');
        const superadmin = document.querySelector('input[name="roles[]"][value="superadmin"]');
        const empresa = document.querySelector('#empresa_id');

        function ajustarSuperadmin() {
            const exclusivo = superadmin !== null && superadmin.checked;

            roles.forEach((rol) => {
                if (rol !== superadmin) {
                    rol.disabled = exclusivo;
                    if (exclusivo) {
                        rol.checked = false;
                    }
                }
            });

            if (empresa !== null) {
                empresa.disabled = exclusivo;
                if (exclusivo) {
                    empresa.value = '';
                }
            }
        }

        roles.forEach((rol) => rol.addEventListener('change', ajustarSuperadmin));
        ajustarSuperadmin();
    </script>
<?= $this->endSection() ?>
