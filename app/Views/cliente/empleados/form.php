<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <?php
        $activo = old('active', $empleado !== null ? (int) $empleado['estatus'] : 1);
    ?>

    <div class="formulario">
        <h1><?= esc($title) ?></h1>

        <?php if ($empresa !== null): ?>
            <p style="color: #64748b; margin-bottom: 20px;">Empresa: <strong><?= esc($empresa['nombre']) ?></strong></p>
        <?php endif; ?>

        <form action="<?= $empleado !== null ? site_url('cliente/empleados/update/' . $empleado['id']) : site_url('cliente/empleados/create') ?>" method="post">
            <?= csrf_field() ?>

            <div class="campo">
                <label for="nombre">Nombre *</label>
                <input type="text" name="nombre" id="nombre" value="<?= esc(old('nombre', $empleado['nombre'] ?? '')) ?>" maxlength="100" required>
            </div>

            <div class="campo">
                <label for="apellidos">Apellidos *</label>
                <input type="text" name="apellidos" id="apellidos" value="<?= esc(old('apellidos', $empleado['apellidos'] ?? '')) ?>" maxlength="100" required>
            </div>

            <div class="campo">
                <label for="username">Nombre de usuario *</label>
                <input type="text" name="username" id="username" value="<?= esc(old('username', $usuario?->username ?? '')) ?>" maxlength="30" required>
                <small>Solo letras, números y puntos.</small>
            </div>

            <div class="campo">
                <label for="email">Correo electrónico *</label>
                <input type="email" name="email" id="email" value="<?= esc(old('email', $usuario?->getEmail() ?? '')) ?>" maxlength="254" required>
            </div>

            <div class="campo">
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" value="<?= esc(old('telefono', $empleado['telefono'] ?? '')) ?>" maxlength="20">
            </div>

            <div class="campo">
                <label for="password">Contraseña <?= $empleado === null ? '*' : '(dejar vacía para conservarla)' ?></label>
                <input type="password" name="password" id="password" minlength="8" <?= $empleado === null ? 'required' : '' ?>>
            </div>

            <div class="campo">
                <label for="password_confirm">Confirmar contraseña <?= $empleado === null ? '*' : '' ?></label>
                <input type="password" name="password_confirm" id="password_confirm" minlength="8" <?= $empleado === null ? 'required' : '' ?>>
            </div>

            <div class="campo">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
                    <input type="checkbox" name="active" value="1" <?= (int) $activo === 1 ? 'checked' : '' ?>>
                    Cuenta activa
                </label>
            </div>

            <button class="boton-crear" type="submit"><?= $empleado === null ? 'Crear empleado' : 'Guardar cambios' ?></button>
            <a href="<?= site_url('cliente/empleados') ?>" style="margin-left: 10px;">Cancelar</a>
        </form>
    </div>
<?= $this->endSection() ?>
