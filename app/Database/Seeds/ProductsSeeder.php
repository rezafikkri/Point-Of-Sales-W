<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run()
    {
        $dateTime = date('Y-m-d H:i:s');
        $productIds = [
            '464050e3-cdeb-49c4-8524-ea22f0395d68',
            '4cc922be-b1e5-4146-be47-436a235f169c',
            'd7e3b0ee-01ee-4250-852e-37b055c19b5b',
            '10c5b53b-e35e-41e2-9695-1d892d8ae567',
            '02f5e887-b398-4c90-b807-a5968a3a038d'
        ];

        $productBuilder = $this->db->table('products');
        $productData = [
            [
                'product_id' => $productIds[0],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Lemon Juice',
                'product_photo' => 'Lemon Juice.jpg',
                'product_status' => 'ada',
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ],
            [
                'product_id' => $productIds[1],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Pomegranate Juice',
                'product_photo' => 'Pomegranate juice.jpg',
                'product_status' => 'tidak_ada',
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ],
            [
                'product_id' => $productIds[2],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Strawberry Juice',
                'product_photo' => 'strawberry juice.jpg',
                'product_status' => 'ada',
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ],
            [
                'product_id' => $productIds[3],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Orange Juice',
                'product_photo' => 'orange juice.jpg',
                'product_status' => 'ada',
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ],
            [
                'product_id' => $productIds[4],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Milkshake',
                'product_photo' => 'Milkshake.jpg',
                'product_status' => 'ada',
                'created_at' => $dateTime,
                'updated_at' => $dateTime
            ],
        ];
        $productBuilder->insertBatch($productData);

        $priceBuilder = $this->db->table('product_prices');
        $priceData = [
            [
                'product_price_id' => 'ce966275-fa43-4181-a4f6-729892fad007',
                'product_id' => $productIds[0],
                'product_magnitude' => '1 Gelas',
                'product_price' => '30000'
            ],
            [
                'product_price_id' => 'a1e5c85c-6a67-4697-aea1-2bd0a69f1a65',
                'product_id' => $productIds[0],
                'product_magnitude' => '3 Gelas',
                'product_price' => '80000'
            ],
            [
                'product_price_id' => '9114068c-8607-47ca-ab0e-fad8704e46f7',
                'product_id' => $productIds[1],
                'product_magnitude' => '1 Gelas',
                'product_price' => '25000'
            ],
            [
                'product_price_id' => '204bb38c-ee3f-4bc2-a1aa-9251491692a1',
                'product_id' => $productIds[2],
                'product_magnitude' => '1 Gelas',
                'product_price' => '20000'
            ],
            [
                'product_price_id' => '2afbba6d-2a40-4660-b620-2026e91cc17f',
                'product_id' => $productIds[3],
                'product_magnitude' => '1 Gelas',
                'product_price' => '20000'
            ],
            [
                'product_price_id' => '204bb38c-ee3f-4bc2-a1aa-9251491692a1',
                'product_id' => $productIds[4],
                'product_magnitude' => '1 Gelas',
                'product_price' => '20000'
            ],
        ];
        $priceBuilder->insertBatch($priceData);
    }
}
