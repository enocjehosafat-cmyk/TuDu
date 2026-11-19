<?php

namespace App\Services;

use App\Models\ActivityLogModel;

class AuditService
{
    public function log(string $action, string $entityType, ?int $entityId, string $description): void
    {
        $usuario = auth()->user();
        $userName = $usuario !== null ? $usuario->username : 'Sistema';

        $model = new ActivityLogModel();
        $model->insert([
            'user_id'     => $usuario?->id,
            'user_name'   => $userName,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'description' => $description,
        ]);
    }
}