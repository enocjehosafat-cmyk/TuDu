<?php

namespace App\Models;

use CodeIgniter\Model;

class EvidenciaAsignacionModel extends Model
{
    protected $table         = 'evidencias_asignaciones';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['asignacion_id', 'foto', 'latitud', 'longitud'];
    protected $useTimestamps = true;
    protected $updatedField  = ''; // Solo usamos created_at

    public function listarPorAsignacion(int $asignacionId): array
    {
        return $this->where('asignacion_id', $asignacionId)->findAll();
    }
}