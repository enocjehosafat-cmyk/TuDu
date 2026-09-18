<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Tudu extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' =>[
                'type' => 'INT',
                'constraint'=> 5,
                'unsigned' => TRUE,
                'auto_increment' =>TRUE,
            ],
             'titulo' =>[
                'type' => 'VARCHAR',
                'constraint'=> 255,
            ],
            'descripcion' =>[
                'type' => 'TEXT',
                'null'=> TRUE
            ],
        ]);

        $this->forge->addKey('id',TRUE);
        $this->forge->createTable('TuDu');
    }

    public function down()
    {
        $this->forge->dropTable('TuDu');
    }
}
