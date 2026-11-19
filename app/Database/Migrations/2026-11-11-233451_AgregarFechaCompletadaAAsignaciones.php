<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AgregarFechaCompletadaAAsignaciones extends Migration
{
    public function up()
    {
        $this->forge->addColumn('asignaciones_tareas', [
            'fecha_completada' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'estatus',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('asignaciones_tareas', 'fecha_completada');
    }
}