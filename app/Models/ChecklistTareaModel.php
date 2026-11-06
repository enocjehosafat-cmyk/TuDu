<?php

namespace App\Models;

use CodeIgniter\Model;

class ChecklistTareaModel extends Model
{
    protected $table         = 'checklist_tareas';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['asignacion_id', 'completada', 'fecha_completada', 'foto', 'notas', 'latitud', 'longitud'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function listarPorAsignacion(int $asignacionId): array
    {
        return $this->db->table('checklist_tareas c')
            ->select('c.*')
            ->where('c.asignacion_id', $asignacionId)
            ->orderBy('c.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function crearItems(int $asignacionId, array $items): void
    {
        $datos = [];

        foreach ($items as $item) {
            $item = trim((string) $item);

            if ($item === '') {
                continue;
            }

            $datos[] = [
                'asignacion_id' => $asignacionId,
                'notas'         => mb_substr($item, 0, 2000),
                'completada'    => 0,
                'created_at'    => date('Y-m-d H:i:s'),
            ];
        }

        if ($datos !== []) {
            $this->db->table('checklist_tareas')->insertBatch($datos);
        }
    }
}
