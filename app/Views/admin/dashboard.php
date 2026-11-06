<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Panel de administración</h1>
            <p>Bienvenido de nuevo, <?= esc(auth()->user()->username) ?>. Este es el resumen del sistema.</p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('admin/empresas/new') ?>">
            + Nueva empresa
        </a>
    </div>

    <div class="personal-estadisticas">
        <div class="estadistica-tarjeta">
            <span>Total de empresas</span>
            <h2><?= esc($totales['empresas']) ?></h2>
        </div>
        <div class="estadistica-tarjeta">
            <span>Usuarios activos</span>
            <h2 class="numero-activos"><?= esc($totales['usuarios']) ?></h2>
        </div>
        <div class="estadistica-tarjeta">
            <span>Tareas registradas</span>
            <h2><?= esc($totales['tareas']) ?></h2>
        </div>
    </div>

    <div class="detalle">
        <h3>Acciones rápidas</h3>
        <p>Administra empresas, usuarios y roles desde un solo lugar.</p>
        <p>
            <a class="boton-crear" href="<?= site_url('admin/empresas') ?>">Gestionar empresas</a>
            <a class="boton-crear" href="<?= site_url('admin/usuarios') ?>" style="margin-left: 10px;">Gestionar usuarios</a>
            <a class="boton-crear" href="<?= site_url('admin/roles') ?>" style="margin-left: 10px;">Ver roles</a>
        </p>
    </div>
<?= $this->endSection() ?>
