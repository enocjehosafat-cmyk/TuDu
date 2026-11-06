<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ActualizarTareasYAsignaciones extends Migration
{
    public function up()
    {
        try { $this->forge->dropColumn('tareas', 'icono'); } catch (\Throwable $e) {}

        if (! $this->db->fieldExists('actividades', 'tareas')) {
            $this->forge->addColumn('tareas', [
                'actividades'  => ['type' => 'TEXT', 'null' => true, 'after' => 'descripcion'],
                'es_diaria'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'tiempo_estimado'],
                'fecha_inicio' => ['type' => 'DATE', 'null' => true, 'after' => 'es_diaria'],
                'fecha_fin'    => ['type' => 'DATE', 'null' => true, 'after' => 'fecha_inicio'],
            ]);
        }

        $this->forge->modifyColumn('asignaciones_tareas', [
            'tarea_id'          => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'fecha_vencimiento' => ['type' => 'DATE', 'null' => true],
        ]);
    }

    public function down()
    {
        if (! $this->db->fieldExists('icono', 'tareas')) {
            $this->forge->addColumn('tareas', [
                'icono' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            ]);
        }
        foreach (['actividades', 'es_diaria', 'fecha_inicio', 'fecha_fin'] as $col) {
            if ($this->db->fieldExists($col, 'tareas')) {
                $this->forge->dropColumn('tareas', $col);
            }
        }
    }
}
