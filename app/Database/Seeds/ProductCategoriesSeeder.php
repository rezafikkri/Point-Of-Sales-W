<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductCategoriesSeeder extends Seeder
{
    public function run()
    {
        $dateTime = date('Y-m-d') . ' 05:00:20';
        $builder = $this->db->table('product_categories');

        $data = [
            [
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_category_name' => 'Minuman',
                'created_at' => $dateTime,
                'edited_at' => $dateTime
            ],
            [
                'product_category_id' => '4db8a04e-627d-4729-a355-f6dc2a21fc07',
                'product_category_name' => 'Laptop',
                'created_at' => $dateTime,
                'edited_at' => $dateTime
            ]
        ];
        $builder->insertBatch($data);
    }
}
