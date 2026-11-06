<?php

namespace App\Models;

use CodeIgniter\Model;

class TareaModel extends Model
{
    protected $table         = 'tareas';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'empresa_id', 'nombre', 'descripcion', 'actividades',
        'tiempo_estimado', 'es_diaria', 'fecha_inicio', 'fecha_fin', 'estatus'
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'empresa_id'      => 'required|is_natural_no_zero',
        'nombre'          => 'required|min_length[2]|max_length[100]',
        'descripcion'     => 'permit_empty|max_length[2000]',
        'tiempo_estimado' => 'permit_empty|is_natural',
        'estatus'         => 'permit_empty|in_list[0,1]',
    ];

    public function guardarTarea(array $datos, array $actividades = [])
    {
        $datos['actividades'] = empty($actividades) ? null : json_encode(array_values(array_filter($actividades)));
        if (isset($datos['id'])) {
            return $this->update($datos['id'], $datos);
        }
        return $this->insert($datos);
    }

    public function getActividades(int $tareaId): array
    {
        $tarea = $this->find($tareaId);
        if (!$tarea || empty($tarea['actividades'])) {
            return [];
        }
        return json_decode($tarea['actividades'], true) ?? [];
    }

    public function listarPorEmpresa(int $empresaId): array
    {
        return $this->where('empresa_id', $empresaId)->orderBy('nombre', 'ASC')->findAll();
    }

    public function listarActivasPorEmpresa(int $empresaId): array
    {
        return $this->where('empresa_id', $empresaId)->where('estatus', 1)->orderBy('nombre', 'ASC')->findAll();
    }
}
