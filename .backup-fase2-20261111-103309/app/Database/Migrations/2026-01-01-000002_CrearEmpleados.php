<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearEmpleados extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'empresa_id' => ['type' => 'BIGINT', 'unsigned' => true],
            // Quitamos el BIGINT estricto y usamos INT para que coincida con Shield
            // Pero como puede variar, lo dejamos sin FK forzada
            'usuario_id' => ['type' => 'INT', 'unsigned' => true, 'comment' => 'FK logica a users de Shield'],
            'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
            'apellidos' => ['type' => 'VARCHAR', 'constraint' => 100],
            'telefono' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'foto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'estatus' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['empresa_id', 'usuario_id']);
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        // NO agregamos la FK a users para evitar el error de tipos
        
        $this->forge->createTable('empleados');
    }

    public function down()
    {
        $this->forge->dropTable('empleados');
    }
}
