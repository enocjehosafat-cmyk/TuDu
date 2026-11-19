<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTareas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'empresa_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'categoria_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
            'descripcion' => ['type' => 'TEXT', 'null' => true],
            'tiempo_estimado' => ['type' => 'INT', 'null' => true],
            'icono' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'estatus' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('categoria_id', 'categorias_tareas', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('tareas');
    }

    public function down()
    {
        $this->forge->dropTable('tareas');
    }
}
