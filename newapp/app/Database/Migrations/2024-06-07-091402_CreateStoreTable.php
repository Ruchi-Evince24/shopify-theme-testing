<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStoreTable extends Migration
{
    public function up()
    {
        this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 255,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'shop_id' => [
                'type' => 'BIGINT',
                'constraint' => 255,
                'unsigned' => true,
                'null' => false
            ],
            'shop' => [
                'type' => 'VARCHAR',
                'unique' => true,
                'constraint' => '255',
            ],
            'access_token' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('eapp1_store',true);
    }

    public function down()
    {
        $this->forge->dropTable('eapp1_store');
    }
}
