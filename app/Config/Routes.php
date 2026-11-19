<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

service('auth')->routes($routes, ['except' => ['login', 'logout', 'register']]);

$routes->get('register', 'RegisterController::registerView', ['as' => 'register']);
$routes->post('register', 'RegisterController::registerAction', ['filter' => 'auth-rates']);
$routes->post('register/resend', 'RegisterController::resendVerification', ['filter' => 'auth-rates']);

$routes->get('login', 'LoginController::loginView', ['as' => 'login']);
$routes->post('login', 'LoginController::loginAction', ['filter' => 'auth-rates']);

$routes->post('logout', '\CodeIgniter\Shield\Controllers\LoginController::logoutAction', ['as' => 'logout']);

$routes->get('pendiente', 'Home::pendiente', ['filter' => 'session']);

$routes->group('admin', ['filter' => 'permission:admin.access'], static function ($routes): void {
    $routes->get('dashboard', 'Admin\DashboardController::index');
    $routes->get('auditoria', 'Admin\AuditoriaController::index');
    $routes->group('empresas', static function ($routes): void {
        $routes->get('/', 'Admin\EmpresaController::index');
        $routes->get('new', 'Admin\EmpresaController::new');
        $routes->post('create', 'Admin\EmpresaController::create');
        $routes->get('edit/(:num)', 'Admin\EmpresaController::edit/$1');
        $routes->post('update/(:num)', 'Admin\EmpresaController::update/$1');
        $routes->post('delete/(:num)', 'Admin\EmpresaController::delete/$1');
        $routes->post('activar/(:num)', 'Admin\EmpresaController::activar/$1');
        $routes->post('eliminar/(:num)', 'Admin\EmpresaController::eliminar/$1');
    });

    $routes->group('usuarios', static function ($routes): void {
        $routes->get('/', 'Admin\UsuarioController::index');
        $routes->get('edit/(:num)', 'Admin\UsuarioController::edit/$1');
        $routes->post('update/(:num)', 'Admin\UsuarioController::update/$1');
        $routes->post('delete/(:num)', 'Admin\UsuarioController::delete/$1');
    });

    $routes->group('roles', static function ($routes): void {
        $routes->get('/', 'Admin\RolController::index');
        $routes->get('new', 'Admin\RolController::new');
        $routes->post('create', 'Admin\RolController::create');
        $routes->get('show/(:segment)', 'Admin\RolController::show/$1');
        $routes->get('edit/(:segment)', 'Admin\RolController::edit/$1');
        $routes->post('update/(:segment)', 'Admin\RolController::update/$1');
        $routes->post('delete/(:segment)', 'Admin\RolController::delete/$1');
        $routes->post('add-user/(:segment)', 'Admin\RolController::addUser/$1');
        $routes->post('remove-user/(:segment)/(:num)', 'Admin\RolController::removeUser/$1/$2');
    });
});

$routes->group('cliente', ['filter' => ['permission:cliente.access', 'empresa-activa']], static function ($routes): void {
    $routes->get('dashboard', 'Cliente\DashboardController::index');

    $routes->group('empleados', static function ($routes): void {
        $routes->get('/', 'Cliente\EmpleadoController::index');
        $routes->get('new', 'Cliente\EmpleadoController::new');
        $routes->post('create', 'Cliente\EmpleadoController::create');
        $routes->get('edit/(:num)', 'Cliente\EmpleadoController::edit/$1');
        $routes->post('update/(:num)', 'Cliente\EmpleadoController::update/$1');
        $routes->post('delete/(:num)', 'Cliente\EmpleadoController::delete/$1');
    });

    $routes->group('tareas', static function ($routes): void {
        $routes->get('/', 'Cliente\TareaController::index');
        $routes->get('new', 'Cliente\TareaController::new');
        $routes->post('create', 'Cliente\TareaController::create');
        $routes->get('edit/(:num)', 'Cliente\TareaController::edit/$1');
        $routes->post('update/(:num)', 'Cliente\TareaController::update/$1');
        $routes->post('delete/(:num)', 'Cliente\TareaController::delete/$1');
    });

    $routes->group('asignaciones', static function ($routes): void {
        $routes->get('/', 'Cliente\AsignacionController::index');
        $routes->get('historial', 'Cliente\AsignacionController::historial');
        $routes->get('new', 'Cliente\AsignacionController::new');
        $routes->post('create', 'Cliente\AsignacionController::create');
        $routes->get('edit/(:num)', 'Cliente\AsignacionController::edit/$1');
        $routes->get('show/(:num)', 'Cliente\AsignacionController::show/$1');
        $routes->post('update/(:num)', 'Cliente\AsignacionController::update/$1');
        $routes->post('delete/(:num)', 'Cliente\AsignacionController::delete/$1');
        $routes->post('aprobar/(:num)', 'Cliente\AsignacionController::aprobar/$1');
        $routes->post('rechazar/(:num)', 'Cliente\AsignacionController::rechazar/$1');
    });
});

$routes->group('empleado', ['filter' => ['permission:empleado.access', 'empresa-activa']], static function ($routes): void {    $routes->get('dashboard', 'Empleado\DashboardController::index');

    $routes->get('asignaciones', 'Empleado\AsignacionController::index');
    $routes->get('asignaciones/(:num)', 'Empleado\AsignacionController::show/$1');
    $routes->post('asignaciones/(:num)/checklist', 'Empleado\AsignacionController::guardarChecklist/$1');
    $routes->post('asignaciones/(:num)/completar', 'Empleado\AsignacionController::completar/$1');
        $routes->post('asignaciones/(:num)/evidencia', 'Empleado\AsignacionController::guardarEvidenciaGeneral/$1');
});
