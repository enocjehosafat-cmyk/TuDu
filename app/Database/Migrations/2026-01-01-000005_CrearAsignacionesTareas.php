<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearAsignacionesTareas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'tarea_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'empleado_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'fecha' => ['type' => 'DATE'],
            'fecha_vencimiento' => ['type' => 'DATE', 'null' => true],
            'prioridad' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'media'],
            'notas' => ['type' => 'TEXT', 'null' => true],
            'estatus' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pendiente'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tarea_id', 'tareas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('empleado_id', 'empleados', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('asignaciones_tareas');
    }

    public function down()
    {
        $this->forge->dropTable('asignaciones_tareas');
    }
}
