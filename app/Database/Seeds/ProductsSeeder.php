<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run()
    {
        $productIds = [
            '464050e3-cdeb-49c4-8524-ea22f0395d68',
            '4cc922be-b1e5-4146-be47-436a235f169c',
            'd7e3b0ee-01ee-4250-852e-37b055c19b5b',
            '10c5b53b-e35e-41e2-9695-1d892d8ae567',
            '02f5e887-b398-4c90-b807-a5968a3a038d',
            '3b72d138-4f8f-4130-9fe0-f9305e85ad53',
            '346eb9fd-85e2-4f5b-a2a0-1f8aa0e31bd4',
            'e6284448-fac6-443d-bc02-85b8a27b6c7c',
            'f2a5020b-7ce6-4f9a-a55b-8daa3f44c0ab',
            'd326f34d-97dd-4895-bcd7-313723cd50eb'
        ];

        $productBuilder = $this->db->table('products');
        $productData = [
            [
                'product_id' => $productIds[0],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Lemon Juice',
                'product_photo' => 'Lemon Juice.jpg',
                'product_status' => 'ada',
                'created_at' => date('Y-m-d') . ' 05:07:02',
                'edited_at' => date('Y-m-d') . ' 05:07:02'
            ],
            [
                'product_id' => $productIds[1],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Pomegranate Juice',
                'product_photo' => 'Pomegranate juice.jpg',
                'product_status' => 'tidak_ada',
                'created_at' => date('Y-m-d') . ' 05:17:00',
                'edited_at' => date('Y-m-d') . ' 05:17:00'
            ],
            [
                'product_id' => $productIds[2],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Strawberry Juice',
                'product_photo' => 'strawberry juice.jpg',
                'product_status' => 'ada',
                'created_at' => date('Y-m-d') . ' 05:27:00',
                'edited_at' => date('Y-m-d') . ' 05:27:00'
            ],
            [
                'product_id' => $productIds[3],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Orange Juice',
                'product_photo' => 'orange juice.jpg',
                'product_status' => 'ada',
                'created_at' => date('Y-m-d') . ' 05:10:00',
                'edited_at' => date('Y-m-d') . ' 05:10:00'
            ],
            [
                'product_id' => $productIds[4],
                'product_category_id' => 'd97e5f5d-9b1d-49a0-be4f-406148bfcea9',
                'product_name' => 'Milkshake',
                'product_photo' => 'Milkshake.jpg',
                'product_status' => 'ada',
                'created_at' => date('Y-m-d') . ' 05:30:00',
                'edited_at' => date('Y-m-d') . ' 05:30:00'
            ],
            [
                'product_id' => $productIds[5],
                'product_category_id' => '4db8a04e-627d-4729-a355-f6dc2a21fc07',
                'product_name' => 'Acer',
                'product_photo' => 'Acer.jpg',
                'product_status' => 'ada',
                'created_at' => date('Y-m-d') . ' 05:35:00',
                'edited_at' => date('Y-m-d') . ' 05:35:00'
            ],
            [
                'product_id' => $productIds[6],
                'product_category_id' => '4db8a04e-627d-4729-a355-f6dc2a21fc07',
                'product_name' => 'Asus',
                'product_photo' => 'Asus.jpg',
                'product_status' => 'ada',
                'created_at' => date('Y-m-d') . ' 05:33:00',
                'edited_at' => date('Y-m-d') . ' 05:33:00'
            ],
            [
                'product_id' => $productIds[7],
                'product_category_id' => '4db8a04e-627d-4729-a355-f6dc2a21fc07',
                'product_name' => 'HP',
                'product_photo' => 'HP.jpg',
                'product_status' => 'ada',
                'created_at' => date('Y-m-d') . ' 05:40:00',
                'edited_at' => date('Y-m-d') . ' 05:40:00'
            ],
            [
                'product_id' => $productIds[8],
                'product_category_id' => '4db8a04e-627d-4729-a355-f6dc2a21fc07',
                'product_name' => 'Lenovo Thinkpad',
                'product_photo' => 'Lenovo Thinkpad.jpg',
                'product_status' => 'ada',
                'created_at' => date('Y-m-d') . ' 05:38:00',
                'edited_at' => date('Y-m-d') . ' 05:38:00'
            ],
            [
                'product_id' => $productIds[9],
                'product_category_id' => '4db8a04e-627d-4729-a355-f6dc2a21fc07',
                'product_name' => 'Macbook Pro',
                'product_photo' => 'Macbook Pro.jpg',
                'product_status' => 'ada',
                'created_at' => date('Y-m-d') . ' 05:50:00',
                'edited_at' => date('Y-m-d') . ' 05:50:00'
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
                'product_price_id' => '51b5812c-e8ec-4215-9e41-c0e15139a9e6',
                'product_id' => $productIds[4],
                'product_magnitude' => '1 Gelas',
                'product_price' => '20000'
            ],
            [
                'product_price_id' => 'c5b668a1-7db7-4557-8f4b-c691886b0ea4',
                'product_id' => $productIds[5],
                'product_magnitude' => '1 Buah',
                'product_price' => '20000000'
            ],
            [
                'product_price_id' => 'c9e79cad-9769-4579-ace7-f0f6e08f5016',
                'product_id' => $productIds[6],
                'product_magnitude' => '1 Buah',
                'product_price' => '15000000'
            ],
            [
                'product_price_id' => '327435c5-28e0-4b20-a044-de5ed7dd24b8',
                'product_id' => $productIds[7],
                'product_magnitude' => '1 Buah',
                'product_price' => '10000000'
            ],
            [
                'product_price_id' => '3c7b6c1a-ed9d-4622-9dd4-01bd62d023ac',
                'product_id' => $productIds[8],
                'product_magnitude' => '1 Buah',
                'product_price' => '9000000'
            ],
            [
                'product_price_id' => '3d82376b-1612-4a6d-b9d9-4a30e55164dd',
                'product_id' => $productIds[9],
                'product_magnitude' => '1 Buah',
                'product_price' => '23000000'
            ],
        ];
        $priceBuilder->insertBatch($priceData);
    }
}
