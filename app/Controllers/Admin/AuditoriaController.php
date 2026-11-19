<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;

class AuditoriaController extends BaseController
{
    public function index()
    {
        $model = new ActivityLogModel();
        
        // Traemos los últimos 100 registros ordenados por fecha descendente
        $logs = $model->orderBy('created_at', 'DESC')->limit(100)->findAll();

        return view('admin/auditoria/index', [
            'title' => 'Auditoría del sistema',
            'logs'  => $logs,
        ]);
    }
}