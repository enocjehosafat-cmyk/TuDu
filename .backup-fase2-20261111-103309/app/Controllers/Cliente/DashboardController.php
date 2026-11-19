<?php

namespace App\Controllers\Cliente;

use App\Controllers\BaseController;
use App\Models\AsignacionTareaModel;
use App\Models\EmpleadoModel;
use App\Models\EmpresaModel;
use App\Models\TareaModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $empresaId = $this->empresaId();

        $empleadoModel = new EmpleadoModel();
        $tareaModel    = new TareaModel();

        $empleados = $empleadoModel->listarPorEmpresa($empresaId);

        return view('cliente/dashboard', [
            'title'            => 'Panel de mi empresa',
            'empresa'          => (new EmpresaModel())->find($empresaId),
            'totalEmpleados'   => count($empleados),
            'empleadosActivos' => count(array_filter($empleados, static fn (array $empleado): bool => (int) $empleado['estatus'] === 1)),
            'totalTareas'      => $tareaModel->where('empresa_id', $empresaId)->countAllResults(),
            'totalAsignaciones' => (new AsignacionTareaModel())->contarPorEmpresa($empresaId),
            'empleados'        => array_slice($empleados, 0, 5),
        ]);
    }
}
