<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TransactionsSeeder extends Seeder
{
    private $dataTransaction;
    private $dataTransactionDetail;

    private function generateFakeData(): bool {
        helper('generate_uuid');

        $dataTransaction = [];
        $dataTransactionDetail = [];

        $startTimestamp = mktime(0, 0, 0, date('m')-1, 0, date('Y'));
        $endTimestamp = mktime(23, 59, 59, date('m')+1, 0, date('Y'));
        $productPriceIds = [
            'ce966275-fa43-4181-a4f6-729892fad007',
            'a1e5c85c-6a67-4697-aea1-2bd0a69f1a65',
            '9114068c-8607-47ca-ab0e-fad8704e46f7',
            '204bb38c-ee3f-4bc2-a1aa-9251491692a1',
            '2afbba6d-2a40-4660-b620-2026e91cc17f',
            '51b5812c-e8ec-4215-9e41-c0e15139a9e6'
        ];
        $userIds = [
            '90b86b53-4bc8-436f-8919-c709d8026471',
            '8ca354cb-f0fc-47dd-8b5e-8d88e460c6c7'
        ];

        for ($i = $startTimestamp; $i <= $endTimestamp; $i += (3600*24)) {
            $maxTransaction = rand(1, 50);

            for ($n = 1; $n <= $maxTransaction; $n++) {
                $dateTime = date('Y-m-d', $i) . ' ' . '06:' . rand(10, 23) . ':00';
                $transactionId = generate_uuid();

                $dataTransaction[] = [
                    'transaction_id' => $transactionId,
                    'user_id' => $userIds[rand(0, 1)],
                    'transaction_status' => 'finished',
                    'customer_money' => 80000,
                    'created_at' => $dateTime,
                    'edited_at' => $dateTime
                ];
                $dataTransactionDetail[] = [
                    'transaction_detail_id' => generate_uuid(),
                    'transaction_id' => $transactionId,
                    'product_price_id' => $productPriceIds[rand(0, 5)],
                    'product_quantity' => 1
                ];
            }
        }

        $this->dataTransaction = $dataTransaction;
        $this->dataTransactionDetail = $dataTransactionDetail;

        return true;
    }

    public function run()
    {
        $this->generateFakeData();

        $transactionBuilder = $this->db->table('transactions');
        $transactionBuilder->insertBatch($this->dataTransaction);
        $transactionDetailBuilder = $this->db->table('transaction_details');
        $transactionDetailBuilder->insertBatch($this->dataTransactionDetail);
    }
}
