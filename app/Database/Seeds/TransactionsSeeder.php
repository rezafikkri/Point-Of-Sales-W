<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TransactionsSeeder extends Seeder
{
    private $transactionData;
    private $transactionDetailData;
    private $transactionHistoryData;

    private function generateFakeData(): bool
    {
        helper('generate_uuid');

        $transactionData = [];
        $transactionDetailData = [];
        $transactionHistoryData = [];

        $startTimestamp = mktime(0, 0, 0, date('m') - 2, 0, date('Y'));
        $endTimestamp = mktime(05, 00, 00, date('m'), date('d'), date('Y'));
        $products = [
            [
                'product_name' => 'Lemon Juice',
                'product_price_id' => 'ce966275-fa43-4181-a4f6-729892fad007',
                'product_magnitude' => '1 Gelas',
                'product_price' => '10000'
            ],
            [
                'product_name' => 'Lemon Juice',
                'product_price_id' => 'a1e5c85c-6a67-4697-aea1-2bd0a69f1a65',
                'product_magnitude' => '3 Gelas',
                'product_price' => '20500'
            ],
            [
                'product_name' => 'Pomegranate Juice',
                'product_price_id' => '9114068c-8607-47ca-ab0e-fad8704e46f7',
                'product_magnitude' => '1 Gelas',
                'product_price' => '25000'
            ],
            [
                'product_name' => 'Strawberry Juice',
                'product_price_id' => '204bb38c-ee3f-4bc2-a1aa-9251491692a1',
                'product_magnitude' => '1 Gelas',
                'product_price' => '20000'
            ],
            [
                'product_name' => 'Orange Juice',
                'product_price_id' => '2afbba6d-2a40-4660-b620-2026e91cc17f',
                'product_magnitude' => '1 Gelas',
                'product_price' => '20000'
            ],
            [
                'product_name' => 'Milkshake',
                'product_price_id' => '51b5812c-e8ec-4215-9e41-c0e15139a9e6',
                'product_magnitude' => '1 Gelas',
                'product_price' => '20000'
            ] 
        ];
        $userIds = [
            '90b86b53-4bc8-436f-8919-c709d8026471',
            '8ca354cb-f0fc-47dd-8b5e-8d88e460c6c7'
        ];
        $customerMonies = [ 50000, 60000, 80000 ];

        // set variable $transactionStatus = true, for indicate transaction status = belum for first loop
        $transactionStatus = true;
        for ($i = $startTimestamp; $i <= $endTimestamp; $i += (3600 * 24)) {
            $maxTransaction = rand(1, 50);

            for ($n = 1; $n <= $maxTransaction; $n++) {
                $dateTime = date('Y-m-d', $i) . ' ' . rand(1, 8) . ':' . rand(0, 23) . ':' . rand(0, 23);
                $transactionId = generate_uuid();
                $productRandomInt1 = rand(0, 5);
                $productRandomInt2 = rand(0, 5);

                $transactionData[] = [
                    'transaction_id' => $transactionId,
                    'user_id' => $userIds[rand(0, 1)],
                    'transaction_status' => ($transactionStatus) ? 'belum' : 'selesai',
                    'customer_money' => ($transactionStatus) ? null : $customerMonies[rand(0, 2)],
                    'created_at' => ($transactionStatus) ? null : $dateTime,
                    'edited_at' => ($transactionStatus) ? null : $dateTime
                ];
                
                $transactionDetailData[] = [
                    'transaction_detail_id' => generate_uuid(),
                    'transaction_id' => $transactionId,
                    'product_price_id' => $products[$productRandomInt1]['product_price_id'],
                    'product_quantity' => 1,
                    'product_name' => ($transactionStatus) ? null : $products[$productRandomInt1]['product_name'],
                    'product_magnitude' => ($transactionStatus) ? null : $products[$productRandomInt1]['product_magnitude'],
                    'product_price' => ($transactionStatus) ? null : $products[$productRandomInt1]['product_price']
                ];
                if ($productRandomInt1 != $productRandomInt2) {
                    $transactionDetailData[] = [
                        'transaction_detail_id' => generate_uuid(),
                        'transaction_id' => $transactionId,
                        'product_price_id' => $products[$productRandomInt2]['product_price_id'],
                        'product_quantity' => 1,
                        'product_name' => ($transactionStatus) ? null : $products[$productRandomInt2]['product_name'],
                        'product_magnitude' => ($transactionStatus) ? null : $products[$productRandomInt2]['product_magnitude'],
                        'product_price' => ($transactionStatus) ? null : $products[$productRandomInt2]['product_price']
                    ];
                }

                $transactionStatus = false;
            }
        }

        $this->transactionData = $transactionData;
        $this->transactionDetailData = $transactionDetailData;

        return true;
    }

    public function run()
    {
        $this->generateFakeData();

        $transactionBuilder = $this->db->table('transactions');
        $transactionBuilder->insertBatch($this->transactionData);
        $transactionDetailBuilder = $this->db->table('transaction_details');
        $transactionDetailBuilder->insertBatch($this->transactionDetailData);
    }
}
