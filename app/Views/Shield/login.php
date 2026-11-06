<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <title>Iniciar sesión | TuDu</title>
</head>

<body class="pagina-login">

<main class="login-contenedor">

    <section class="login-izquierda">
        <div class="login-marca">
            <img
                src="<?= base_url('img/logo-horizontal.png') ?>"
                alt="Logo TuDu"
                class="login-logo-panel"
            >

            <p class="login-lema">
                Organiza · Planifica · Logra más
            </p>
        </div>
    </section>

    <section class="login-derecha">
        <div class="login-formulario">
            <h2>Iniciar sesión</h2>

            <p class="login-subtitulo">
                Ingresa a tu cuenta para continuar.
            </p>

            <?php if (session('error')): ?>
                <p class="login-error" role="alert">
                    <?= esc(session('error')) ?>
                </p>
            <?php endif; ?>

            <?php if (session('message')): ?>
                <p class="login-mensaje" role="status">
                    <?= esc(session('message')) ?>
                </p>
            <?php endif; ?>

            <form action="<?= site_url('login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="campo">
                    <label for="identity">Correo o nombre de usuario</label>

                    <div class="login-input">
                        <span class="login-icono" aria-hidden="true">✉</span>

                        <input
                            type="text"
                            name="identity"
                            id="identity"
                            placeholder="Correo o nombre de usuario"
                            autocomplete="username"
                            value="<?= esc(old('identity') ?? '') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="campo">
                    <label for="password">Contraseña</label>

                    <div class="login-input">
                        <span class="login-icono" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="10" width="14" height="11" rx="2"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                            </svg>
                        </span>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Contraseña"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="login-ver-password"
                            id="ver-password"
                            aria-label="Mostrar contraseña"
                            aria-pressed="false"
                        >
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button class="boton-crear" type="submit">
                    Iniciar sesión
                </button>

                <div style="text-align: center; margin-top: 20px;">
                    <p style="font-size: 14px; color: #64748b; margin-bottom: 10px;">¿No tienes cuenta?</p>
                    <a href="<?= site_url('register') ?>" class="boton-crear" style="display: inline-block; text-decoration: none; background-color: #1565c0; width: 100%; text-align: center;">
                        Crear cuenta de empresa
                    </a>
                </div>
            </form>
        </div>
    </section>

</main>

<script>
    const botonPassword = document.getElementById('ver-password');
    const campoPassword = document.getElementById('password');

    botonPassword.addEventListener('click', function () {
        const visible = campoPassword.type === 'text';

        campoPassword.type = visible ? 'password' : 'text';

        botonPassword.setAttribute('aria-pressed', String(!visible));
        botonPassword.setAttribute(
            'aria-label',
            visible ? 'Mostrar contraseña' : 'Ocultar contraseña'
        );
    });
</script>

</body>
</html>
