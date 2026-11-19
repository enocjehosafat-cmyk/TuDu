<?php

namespace App\Models;

use CodeIgniter\Model;

class EmpresaModel extends Model
{
    protected $table         = 'empresas';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre', 'rfc', 'direccion', 'logo', 'estatus'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'nombre'    => 'required|min_length[2]|max_length[150]',
        'rfc'       => 'permit_empty|max_length[15]',
        'direccion' => 'permit_empty|max_length[255]',
        'estatus'   => 'permit_empty|in_list[0,1]',
    ];
}
