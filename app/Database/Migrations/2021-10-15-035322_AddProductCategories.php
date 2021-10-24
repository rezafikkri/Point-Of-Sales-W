<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductCategories extends Migration
{
    public function up()
    {
        $this->forge->addField('product_category_id UUID PRIMARY KEY');
        $this->forge->addField([
            'product_category_name' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],
            'created_at' => [
                'type' => 'TIMESTAMP'
            ],
            'edited_at' => [
                'type' => 'TIMESTAMP'
            ]
        ]);
        $this->forge->createTable('product_categories');
    }

    public function down()
    {
		$this->forge->dropTable('product_categories');
    }
}
