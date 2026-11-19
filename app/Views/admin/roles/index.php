<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
    <div class="tareas-encabezado">
        <div>
            <h1>Roles y permisos</h1>
            <p>Crea y edita roles, sus permisos y la membresía de cada uno.</p>
            <p><small>Los permisos son informativos; el acceso al panel se controla por los roles del sistema.</small></p>
        </div>
        <a class="crear tareas-boton-crear" href="<?= site_url('admin/roles/new') ?>">
            + Nuevo rol
        </a>
    </div>

    <div class="personal-estadisticas">
        <?php foreach ($roles as $slug => $rol): ?>
            <div class="estadistica-tarjeta">
                <span><?= esc($rol['title']) ?></span>
                <h2><?= esc($usuariosPorRol[$slug] ?? 0) ?></h2>
                <p style="color: #64748b; font-size: 13px; margin: 8px 0 14px;">
                    <?= esc($rol['description'] ?? '') ?>
                </p>
                <a class="boton-edit" href="<?= site_url('admin/roles/show/' . $slug) ?>">Ver miembros</a>
                <a class="boton-edit" href="<?= site_url('admin/roles/edit/' . $slug) ?>" style="margin-left: 6px;">Editar rol</a>
            </div>
        <?php endforeach; ?>
    </div>

<?= $this->endSection() ?>
