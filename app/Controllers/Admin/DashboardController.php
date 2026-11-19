<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmpresaModel;
use App\Models\UsuariosShieldModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $usuarios = new UsuariosShieldModel();

        $totales = [
            'empresas' => (new EmpresaModel())->countAllResults(),
            'usuarios' => $usuarios->where('active', 1)->countAllResults(),
            'roles'    => count(service('rol')->roles()), // Contamos los roles en su lugar
        ];

        return view('admin/dashboard', [
            'title'   => 'Panel de administración',
            'totales' => $totales,
        ]);
    }
}