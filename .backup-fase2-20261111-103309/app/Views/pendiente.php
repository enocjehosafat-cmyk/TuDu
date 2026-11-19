<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <?php
    $usuario      = auth()->user();
    $sinVerificar = $usuario !== null && ! $usuario->isActivated();
    ?>
    <div class="tareas-encabezado">
        <div>
            <h1><?= $sinVerificar ? 'Verifica tu correo' : 'Cuenta pendiente' ?></h1>
            <p><?= $sinVerificar ? 'Tu cuenta todavía no está verificada.' : 'Tu cuenta está creada, pero todavía no tiene un rol asignado.' ?></p>
        </div>
    </div>

    <?php if ($sinVerificar): ?>
        <div class="detalle">
            <h3>Verificación pendiente</h3>
            <p>Enviamos un código de verificación a <strong><?= esc($usuario->email ?? '') ?></strong>.</p>
            <p>Si no lo recibiste, puedes solicitar un nuevo código.</p>
            <form method="post" action="<?= site_url('register/resend') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= esc($usuario->email ?? '') ?>">
                <button class="boton-crear" type="submit">Reenviar correo de verificación</button>
            </form>
        </div>
    <?php else: ?>
        <div class="detalle">
            <h3>¿Qué sigue?</h3>
            <p>Un administrador debe asignarte el rol de Cliente o Empleado para que puedas acceder a tu panel.</p>
            <p>Si crees que esto es un error, contacta al equipo de soporte de TuDu.</p>
        </div>
    <?php endif; ?>
<?= $this->endSection() ?>
