<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistroPendienteModel extends Model
{
    protected $table         = 'registros_pendientes';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['usuario_id', 'nombre_empresa', 'nombre', 'apellidos', 'telefono'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'usuario_id'     => 'required|is_natural_no_zero',
        'nombre_empresa' => 'required|min_length[2]|max_length[150]',
        'nombre'         => 'required|min_length[2]|max_length[100]',
        'apellidos'      => 'required|min_length[2]|max_length[100]',
        'telefono'       => 'permit_empty|max_length[20]',
    ];
}
