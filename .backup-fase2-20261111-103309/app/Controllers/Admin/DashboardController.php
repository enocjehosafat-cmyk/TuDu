<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AsignacionTareaModel;
use App\Models\EmpresaModel;
use App\Models\TareaModel;
use App\Models\UsuariosShieldModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $usuarios = new UsuariosShieldModel();

        $totales = [
            'empresas'   => (new EmpresaModel())->countAllResults(),
            'usuarios'   => $usuarios->where('active', 1)->countAllResults(),
            'tareas'     => (new TareaModel())->countAllResults(),
            'asignaciones' => (new AsignacionTareaModel())->countAllResults(),
        ];

        return view('admin/dashboard', [
            'title'   => 'Panel de administración',
            'totales' => $totales,
        ]);
    }
}
