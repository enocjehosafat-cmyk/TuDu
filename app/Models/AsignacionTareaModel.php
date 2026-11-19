<?php

namespace App\Models;

use CodeIgniter\Model;

class AsignacionTareaModel extends Model
{
    protected $table         = 'asignaciones_tareas';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tarea_id', 'empleado_id', 'fecha', 'fecha_vencimiento', 'prioridad', 'notas', 'estatus', 'fecha_completada'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'tarea_id'          => 'required|is_natural_no_zero',
        'empleado_id'       => 'required|is_natural_no_zero',
        'fecha'             => 'required|valid_date[Y-m-d]',
        'fecha_vencimiento' => 'permit_empty|valid_date[Y-m-d]',
        'prioridad'         => 'required|in_list[baja,media,alta]',
        'notas'             => 'permit_empty|max_length[2000]',
        'estatus'           => 'permit_empty|in_list[pendiente,en_revision,completada,cancelada]',
    ];

    public function contarPorEmpresa(int $empresaId): int
    {
        return $this->db->table('asignaciones_tareas a')
            ->join('empleados e', 'e.id = a.empleado_id')
            ->where('e.empresa_id', $empresaId)
            ->countAllResults();
    }

    public function listarPorEmpresa(int $empresaId): array
    {
        return $this->db->table('asignaciones_tareas a')
            ->select('a.*, t.nombre as tarea, concat(e.nombre, " ", e.apellidos) as empleado')
            ->join('tareas t', 't.id = a.tarea_id', 'left')
            ->join('empleados e', 'e.id = a.empleado_id', 'left')
            ->where('e.empresa_id', $empresaId)
            ->orderBy('a.fecha', 'DESC')
            ->get()
            ->getResultArray();
    }

public function buscarDeEmpresa(int $id, int $empresaId): ?array
{
    return $this->db->table('asignaciones_tareas a')
        ->select('a.*, t.nombre as tarea, t.descripcion, t.estatus as tarea_estatus')
        ->join('tareas t', 't.id = a.tarea_id', 'left')
        ->join('empleados e', 'e.id = a.empleado_id', 'left')
        ->where('a.id', $id)
        ->where('e.empresa_id', $empresaId)
        ->get()
        ->getRowArray();
}

    public function listarDeEmpleado(int $empleadoId): array
    {
        return $this->db->table('asignaciones_tareas a')
            ->select('a.*, t.nombre as tarea, t.descripcion, t.estatus as tarea_estatus')
            ->join('tareas t', 't.id = a.tarea_id', 'left')
            ->where('a.empleado_id', $empleadoId)
            ->where('a.estatus !=', 'cancelada')
            ->groupStart()
                ->where('a.estatus !=', 'completada')
                ->orGroupStart()
                    ->where('a.estatus', 'completada')
                    ->where('a.fecha_completada >=', date('Y-m-d H:i:s', strtotime('-15 days')))
                ->groupEnd()
            ->groupEnd()
            ->orderBy('a.fecha', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function listarPaginado(int $empresaId, int $porPagina = 15): array
    {
        $builder = $this->db->table('asignaciones_tareas a')
            ->select('a.*, t.nombre as tarea, t.estatus as tarea_estatus, concat(e.nombre, " ", e.apellidos) as empleado')
            ->join('tareas t', 't.id = a.tarea_id', 'left')
            ->join('empleados e', 'e.id = a.empleado_id', 'left')
            ->where('e.empresa_id', $empresaId)
            // Ocultar completadas con más de 15 días
            ->groupStart()
                ->where('a.estatus !=', 'completada')
                ->orGroupStart()
                    ->where('a.estatus', 'completada')
                    ->where('a.fecha_completada >=', date('Y-m-d H:i:s', strtotime('-15 days')))
                ->groupEnd()
            ->groupEnd()            
            ->where('a.estatus !=', 'cancelada')
            ->orderBy('a.fecha', 'DESC');

        $total = $builder->countAllResults(false);
        $page  = service('request')->getGet('page_asignaciones') ?? 1;
        $items = $builder->limit($porPagina, max(0, ((int) $page - 1) * $porPagina))
            ->get()
            ->getResultArray();

        foreach ($items as &$asignacion) {
            if ($asignacion['tarea'] === null || (int) ($asignacion['tarea_estatus'] ?? 0) === 0) {
                $asignacion['tarea'] = $asignacion['tarea'] === null
                    ? 'Tarea eliminada'
                    : $asignacion['tarea'] . ' (eliminada)';
            }
        }
        unset($asignacion);

        return [
            'items' => $items,
            'total' => $total,
            'page'  => (int) $page,
            'per'   => $porPagina,
        ];
    }

    public function listarHistorialPaginado(int $empresaId, int $porPagina = 15): array
    {
        $builder = $this->db->table('asignaciones_tareas a')
            ->select('a.*, t.nombre as tarea, t.estatus as tarea_estatus, concat(e.nombre, " ", e.apellidos) as empleado')
            ->join('tareas t', 't.id = a.tarea_id', 'left')
            ->join('empleados e', 'e.id = a.empleado_id', 'left')
            ->where('e.empresa_id', $empresaId)
            ->groupStart()
                ->where('a.estatus', 'cancelada')
                ->orWhere('a.estatus', 'completada')
            ->groupEnd()
            ->orderBy('a.fecha', 'DESC');

        $total = $builder->countAllResults(false);
        $page  = service('request')->getGet('page_asignaciones') ?? 1;
        $items = $builder->limit($porPagina, max(0, ((int) $page - 1) * $porPagina))
            ->get()
            ->getResultArray();

        foreach ($items as &$asignacion) {
            if ($asignacion['tarea'] === null || (int) ($asignacion['tarea_estatus'] ?? 0) === 0) {
                $asignacion['tarea'] = $asignacion['tarea'] === null
                    ? 'Tarea eliminada'
                    : $asignacion['tarea'] . ' (eliminada)';
            }
        }
        unset($asignacion);

        return [
            'items' => $items,
            'total' => $total,
            'page'  => (int) $page,
            'per'   => $porPagina,
        ];
    }
        public function buscarDeEmpleado(int $id, int $empleadoId): ?array
    {
        return $this->db->table('asignaciones_tareas a')
            ->select('a.*, t.nombre as tarea, t.descripcion, t.estatus as tarea_estatus')
            ->join('tareas t', 't.id = a.tarea_id', 'left')
            ->where('a.id', $id)
            ->where('a.empleado_id', $empleadoId)
            ->get()
            ->getRowArray();
    }
}