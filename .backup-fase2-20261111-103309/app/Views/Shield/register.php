<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <title>Crear cuenta | TuDu</title>
</head>
<body class="pagina-login">
<main class="login-contenedor">
    <section class="login-izquierda">
        <div class="login-marca">
            <img src="<?= base_url('img/logo-horizontal.png') ?>" alt="Logo TuDu" class="login-logo-panel">
            <p class="login-lema">Organiza · Planifica · Logra más</p>
        </div>
    </section>

    <section class="login-derecha">
        <div class="login-formulario">
            <h2>Crear cuenta de empresa</h2>
            <p class="login-subtitulo">Registra tu negocio y empieza a organizar tu equipo.</p>

            <?php if (session('errors')): ?>
                <div class="login-error" style="margin-bottom: 20px;">
                    <ul>
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (session('error')): ?>
                <div class="login-error" style="margin-bottom: 20px;">
                    <?= esc(session('error')) ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('register') ?>" method="post">
                <?= csrf_field() ?>

                <div class="campo">
                    <label for="nombre_empresa">Nombre de tu empresa o familia *</label>
                    <input type="text" name="nombre_empresa" id="nombre_empresa" value="<?= esc(old('nombre_empresa')) ?>" maxlength="150" required>
                </div>

                <div class="campo">
                    <label for="nombre">Tu nombre *</label>
                    <input type="text" name="nombre" id="nombre" value="<?= esc(old('nombre')) ?>" maxlength="100" required>
                </div>

                <div class="campo">
                    <label for="apellidos">Tus apellidos *</label>
                    <input type="text" name="apellidos" id="apellidos" value="<?= esc(old('apellidos')) ?>" maxlength="100" required>
                </div>

                <div class="campo">
                    <label for="username">Nombre de usuario *</label>
                    <input type="text" name="username" id="username" value="<?= esc(old('username')) ?>" maxlength="30" required>
                    <small>Solo letras, números y puntos.</small>
                </div>

                <div class="campo">
                    <label for="email">Correo electrónico *</label>
                    <input type="email" name="email" id="email" value="<?= esc(old('email')) ?>" maxlength="254" required>
                </div>

                <div class="campo">
                    <label for="telefono">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" value="<?= esc(old('telefono')) ?>" maxlength="20">
                </div>

                <div class="campo">
                    <label for="password">Contraseña *</label>
                    <input type="password" name="password" id="password" minlength="8" required>
                </div>

                <div class="campo">
                    <label for="password_confirm">Confirmar contraseña *</label>
                    <input type="password" name="password_confirm" id="password_confirm" minlength="8" required>
                </div>

                <button class="boton-crear" type="submit">Registrarme</button>
                <p style="margin-top: 12px; font-size: 0.9rem; color: #555; text-align: center;">
                    Te enviaremos un correo para verificar tu cuenta antes de crear la empresa.
                </p>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <a href="<?= site_url('login') ?>" style="color: #1565c0; font-weight: bold; text-decoration: none;">
                    Ya tengo cuenta, iniciar sesión
                </a>
            </div>
        </div>
    </section>
</main>
</body>
</html>
