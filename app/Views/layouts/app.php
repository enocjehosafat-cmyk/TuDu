<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= esc($title ?? 'TuDu | Panel') ?></title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <?php $usuarioActual = auth()->user(); ?>

    <button class="mobile-menu-toggle" id="menuToggle" aria-label="Abrir menú">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

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
            <a href="<?= site_url('admin/auditoria') ?>" class="<?= url_is('admin/auditoria*') ? 'activo' : '' ?>"><span>Auditoría</span></a>
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
        // Menú Móvil
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

        // SweetAlert2 Toasts
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                showCloseButton: true,
                timer: 4500,
                timerProgressBar: true,
                customClass: {
                    popup: 'colored-toast'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            <?php if (session()->getFlashdata('success')): ?>
                Toast.fire({
                    iconHtml: '<div class="toast-icon toast-icon-success">✓</div>',
                    title: <?= json_encode(session()->getFlashdata('success')) ?>
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                Toast.fire({
                    iconHtml: '<div class="toast-icon toast-icon-warning">!</div>',
                    title: <?= json_encode(session()->getFlashdata('warning')) ?>
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                Toast.fire({
                    iconHtml: '<div class="toast-icon toast-icon-error">×</div>',
                    title: <?= json_encode(session()->getFlashdata('error')) ?>
                });
            <?php endif; ?>
        });

        // SweetAlert2 Confirmaciones (Formularios)
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.form-confirm');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: this.dataset.title,
                        text: this.dataset.message,
                        icon: this.dataset.icon || 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1565c0',
                        cancelButtonColor: '#d71920',
                        confirmButtonText: this.dataset.confirm || 'Sí, continuar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
