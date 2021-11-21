<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTransactionHistories extends Migration
{
    public function up()
    {
        $this->forge->addField('transaction_history_id UUID PRIMARY KEY');
        $this->forge->addField([
            'transaction_detail_id' => [
                'type' => 'UUID'
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
        $this->forge->addForeignKey('transaction_detail_id', 'transaction_details', 'transaction_detail_id', 'NO ACTION', 'CASCADE');
        $this->forge->createTable('transaction_histories');
    }

    public function down()
    {
		$this->forge->dropTable('transaction_histories');

    }
}
