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

    <div class="personal-estadisticas" style="grid-template-columns: repeat(3, 1fr);">
        <div class="estadistica-tarjeta">
            <span>Total de empresas</span>
            <h2><?= esc($totales['empresas']) ?></h2>
        </div>
        <div class="estadistica-tarjeta">
            <span>Usuarios activos</span>
            <h2 class="numero-activos"><?= esc($totales['usuarios']) ?></h2>
        </div>
        <div class="estadistica-tarjeta">
            <span>Roles del sistema</span>
            <h2><?= esc($totales['roles']) ?></h2>
        </div>
    </div>

<?= $this->endSection() ?>