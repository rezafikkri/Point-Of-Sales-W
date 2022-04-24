<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionDetailsModel extends Model
{
	protected $table = 'transaction_details';
	protected $primaryKey = 'transaction_detail_id';
    protected $allowedFields = [
        'transaction_detail_id',
        'transaction_id',
        'product_price_id',
        'product_quantity',
        'product_name',
        'product_magnitude',
        'product_price'
    ];
    protected $useAutoIncrement = false;

    public function getAll(string $transactionId, string $columns): array
    {
        return $this->select($columns)->getWhere(['transaction_id' => $transactionId])->getResultArray();
    }

    public function getAllForCashier(string $transactionId, string $columns): array
    {
        return $this->select($columns)
                    ->join('product_prices pp', 'transaction_details.product_price_id = pp.product_price_id', 'INNER')
                    ->join('products p', 'p.product_id = pp.product_id', 'INNER')
                    ->getWhere(['transaction_id' => $transactionId])
                    ->getResultArray();
    }

    public function updateProductQty(string $transactionDetailId, int $newProductQty, string $transactionId): bool
    {
        return $this->where('transaction_id', $transactionId)
                    ->update($transactionDetailId, ['product_quantity' => $newProductQty]);
    }

    public function deleteOne(string $transactionDetailId, string $transactionId): bool
    {
        return $this->where('transaction_id', $transactionId)
             ->delete($transactionDetailId);
    }

    public function removeTransactionDetails(array $transaction_detail_ids, string $transactionId): int
    {
        $this->whereIn('transaction_detail_id', $transaction_detail_ids)
             ->where('transaction_id', $transactionId)
             ->delete();
        return $this->db->affectedRows();
    }

    /*
     |------------------------
     | save transaction detail
     |----------------------------
     | this method will be insert if transaction detail not exists or update if exists
     */

    private function generateQueryUpdateOnConflict(array $data): string
    {
        $query = 'UPDATE SET ';
        foreach ($data as $key => $value) {
            $query .= $key.' = EXCLUDED.'.$key.', ';
        }
        return rtrim($query, ', ');
    }

    private function generateQuestionMarksBatch(array $data): string
    {
        $values = '';
        foreach ($data as $d) {
            $count_d = count($d)-1;
            $values .= '('.str_repeat('?,', $count_d).'?),';
        }
        return rtrim($values, ',');
    }

    private function generateValuesBatch(array $data): array
    {
        $values = [];
        foreach ($data as $d) {
            foreach($d as $c) {
                $values[] = $c;
            }
        }
        return $values;
    }

    public function saveTransactionDetails(array $data_save): int
    {
        $query = "INSERT INTO transaksi_detail(".$this->generateColumns($data_save[0]).")
            VALUES ".$this->generateQuestionMarksBatch($data_save)."
            ON CONFLICT (transaction_detail_id) DO ".$this->generateQueryUpdateOnConflict($data_save[0]);
        $this->query($query, $this->generateValuesBatch($data_save));

        return $this->db->affectedRows();
    }
}
