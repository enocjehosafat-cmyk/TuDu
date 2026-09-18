<?php

namespace App\Models;

use CodeIgniter\Model;

class TuduModel extends Model
{
    protected $table = 'TuDu';
    protected $primaryKey = 'id';
    protected $allowedFields = ['titulo', 'descripcion'];
}
