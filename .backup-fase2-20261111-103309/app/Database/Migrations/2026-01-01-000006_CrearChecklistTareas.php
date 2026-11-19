<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearChecklistTareas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'asignacion_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'completada' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'fecha_completada' => ['type' => 'DATETIME', 'null' => true],
            'foto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'notas' => ['type' => 'TEXT', 'null' => true],
            'latitud' => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
            'longitud' => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('asignacion_id', 'asignaciones_tareas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('checklist_tareas');
    }

    public function down()
    {
        $this->forge->dropTable('checklist_tareas');
    }
}
