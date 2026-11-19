<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearRegistrosPendientes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'usuario_id' => ['type' => 'INT', 'unsigned' => true],
            'nombre_empresa' => ['type' => 'VARCHAR', 'constraint' => 150],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
            'apellidos' => ['type' => 'VARCHAR', 'constraint' => 100],
            'telefono' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('usuario_id');
        $this->forge->createTable('registros_pendientes');
    }

    public function down()
    {
        $this->forge->dropTable('registros_pendientes');
    }
}
