<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table         = 'activity_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'user_name', 'action', 'entity_type', 'entity_id', 'description'];
    protected $useTimestamps = true;
    protected $updatedField  = ''; 
}