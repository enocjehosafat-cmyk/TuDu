<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= esc($title ?? 'TuDu | Panel') ?></title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>

    <?php $usuarioActual = auth()->user(); ?>

    <!-- Botón Hamburguesa para Mobile -->
    <button class="mobile-menu-toggle" id="menuToggle" aria-label="Abrir menú">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

    <!-- Overlay oscuro al abrir el menú en mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="<?= base_url('img/logo-horizontal.png') ?>" alt="Logo TuDu">
        </div>

        <div class="sidebar-seccion">
            <span>MENÚ</span>
        </div>

        <?php if ($usuarioActual !== null && $usuarioActual->inGroup('superadmin')): ?>
            <a href="<?= site_url('admin/dashboard') ?>" class="<?= url_is('admin/dashboard*') ? 'activo' : '' ?>"><span>Inicio global</span></a>
            <a href="<?= site_url('admin/empresas') ?>" class="<?= url_is('admin/empresas*') ? 'activo' : '' ?>"><span>Empresas</span></a>
            <a href="<?= site_url('admin/usuarios') ?>" class="<?= url_is('admin/usuarios*') ? 'activo' : '' ?>"><span>Usuarios</span></a>
            <a href="<?= site_url('admin/roles') ?>" class="<?= url_is('admin/roles*') ? 'activo' : '' ?>"><span>Roles y permisos</span></a>
        <?php endif; ?>

        <?php if ($usuarioActual !== null && $usuarioActual->inGroup('cliente')): ?>
            <a href="<?= site_url('cliente/dashboard') ?>" class="<?= url_is('cliente/dashboard*') ? 'activo' : '' ?>"><span>Mi empresa</span></a>
            <a href="<?= site_url('cliente/empleados') ?>" class="<?= url_is('cliente/empleados*') ? 'activo' : '' ?>"><span>Empleados</span></a>
            <a href="<?= site_url('cliente/tareas') ?>" class="<?= url_is('cliente/tareas*') ? 'activo' : '' ?>"><span>Tareas</span></a>
            <a href="<?= site_url('cliente/asignaciones') ?>" class="<?= url_is('cliente/asignaciones*') ? 'activo' : '' ?>"><span>Asignaciones</span></a>
        <?php endif; ?>

        <?php if ($usuarioActual !== null && $usuarioActual->inGroup('empleado')): ?>
            <a href="<?= site_url('empleado/dashboard') ?>" class="<?= url_is('empleado/dashboard*') ? 'activo' : '' ?>"><span>Mi panel</span></a>
            <a href="<?= site_url('empleado/asignaciones') ?>" class="<?= url_is('empleado/asignaciones*') ? 'activo' : '' ?>"><span>Mis asignaciones</span></a>
        <?php endif; ?>

        <?php if ($usuarioActual !== null && ! $usuarioActual->inGroup('superadmin') && ! $usuarioActual->inGroup('cliente') && ! $usuarioActual->inGroup('empleado')): ?>
            <a href="<?= site_url('pendiente') ?>" class="<?= url_is('pendiente*') ? 'activo' : '' ?>"><span>Cuenta pendiente</span></a>
        <?php endif; ?>

        <div class="sidebar-cerrar-sesion">
            <form action="<?= site_url('logout') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit"><span>Cerrar sesión</span></button>
            </form>
        </div>
    </nav>

    <main class="main-content">
        <?php if (session()->getFlashdata('mensaje')): ?>
            <div class="login-mensaje"><?= esc(session()->getFlashdata('mensaje')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="login-error"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php $errores = session()->getFlashdata('errors'); ?>
        <?php if (is_array($errores) && $errores !== []): ?>
            <div class="login-error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleMenu() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        if (menuToggle) {
            menuToggle.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);
        }
    </script>
</body>
</html>
