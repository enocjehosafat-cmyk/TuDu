<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    public string $defaultGroup = 'pendiente';

    /**
     * @var array<string, array<string, string>>
     */
    public array $groups = [
        'pendiente' => [
            'title'       => 'Pendiente',
            'description' => 'Cuenta registrada a la espera de aprobación o asignación de rol.',
        ],
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Control total del sistema, empresas, usuarios y roles.',
        ],
        'cliente' => [
            'title'       => 'Cliente',
            'description' => 'Administra su empresa y a los empleados de la misma.',
        ],
        'empleado' => [
            'title'       => 'Empleado',
            'description' => 'Ejecuta las tareas que le asigna su empresa.',
        ],
    ];

    /**
     * @var array<string, string>
     */
    public array $permissions = [
        'admin.access'       => 'Puede acceder al panel de administración.',
        'empresas.manage'    => 'Puede crear, editar y desactivar empresas.',
        'usuarios.manage'    => 'Puede crear, editar y eliminar usuarios.',
        'roles.manage'       => 'Puede consultar y asignar roles.',
        'cliente.access'     => 'Puede acceder al panel de cliente.',
        'empleados.manage'   => 'Puede gestionar los empleados de su empresa.',
        'empleado.access'    => 'Puede acceder al panel de empleado.',
    ];

    /**
     * @var array<string, list<string>>
     */
    public array $matrix = [
        'pendiente' => [],
        'superadmin' => [
            'admin.*',
            'empresas.*',
            'usuarios.*',
            'roles.*',
        ],
        'cliente' => [
            'cliente.access',
            'empleados.manage',
        ],
        'empleado' => [
            'empleado.access',
        ],
    ];
}
