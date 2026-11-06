<?php

namespace App\Controllers\Empleado;

use App\Controllers\BaseController;
use App\Models\EmpleadoModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $perfil = (new EmpleadoModel())
            ->where('usuario_id', auth()->id())
            ->first();

        if ($perfil === null) {
            return view('empleado/dashboard', [
                'title'         => 'Panel de empleado',
                'perfil'        => null,
                'asignaciones'  => [],
                'pendientes'    => 0,
                'completadas'   => 0,
            ]);
        }

        $asignaciones = db_connect()->table('asignaciones_tareas a')
            ->select('a.*, t.nombre as tarea, t.descripcion')
            ->join('tareas t', 't.id = a.tarea_id', 'left')
            ->where('a.empleado_id', $perfil['id'])
            ->orderBy('a.fecha', 'DESC')
            ->get()
            ->getResultArray();

        $completadas = count(array_filter(
            $asignaciones,
            static fn (array $asignacion): bool => $asignacion['estatus'] === 'completada',
        ));

        return view('empleado/dashboard', [
            'title'        => 'Panel de empleado',
            'perfil'       => $perfil,
            'asignaciones' => $asignaciones,
            'pendientes'   => count($asignaciones) - $completadas,
            'completadas'  => $completadas,
        ]);
    }
}
