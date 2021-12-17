<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransactionDetails extends Migration
{
    public function up()
    {
        $this->forge->addField('transaction_detail_id UUID PRIMARY KEY');
        $this->forge->addField([
            'transaction_id' => [
                'type' => 'UUID'
            ],
            'product_price_id' => [
                'type' => 'UUID',
                'null' => true
            ],
            'product_quantity' => [
                'type' => 'NUMERIC',
                'constraint' => 4
            ],
            'product_name' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],
            'product_magnitude' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],
            'product_price' => [
                'type' => 'NUMERIC',
                'constraint' => 10
            ]
        ]);
        $this->forge->addForeignKey('transaction_id', 'transactions', 'transaction_id', 'NO ACTION', 'CASCADE');
        $this->forge->addForeignKey('product_price_id', 'product_prices', 'product_price_id', 'NO ACTION', 'SET NULL');
        $this->forge->createTable('transaction_details');
    }

    public function down()
    {
		$this->forge->dropTable('transaction_details');
    }
}
