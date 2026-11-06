<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class QuitarCategoriaDeTareas extends Migration
{
    public function up()
    {
        $this->forge->dropForeignKey('tareas', 'tareas_categoria_id_foreign');
        $this->forge->dropColumn('tareas', 'categoria_id');
        $this->forge->dropTable('categorias_tareas', true);
    }

    public function down()
    {
        $this->forge->addColumn('tareas', [
            'categoria_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'empresa_id'],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'empresa_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
            'icono' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'color' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'estatus' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('categorias_tareas');

        $this->forge->addForeignKey('categoria_id', 'categorias_tareas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('tareas');
    }
}
