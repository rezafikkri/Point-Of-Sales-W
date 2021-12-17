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
        'product_quantity'
    ];
    protected $useAutoIncrement = false;

    public function getAll(string $transactionId, string $columns): array
    {
        return $this->select($columns)
                    ->join('harga_produk', 'transaksi_detail.product_price_id = harga_produk.product_price_id', 'INNER')
                    ->join('produk', 'harga_produk.produk_id = produk.produk_id', 'INNER')
                    ->getWhere(['transaction_id' => $transactionId])
                    ->getResultArray();
    }

    public function updateProductQty(string $transaction_detail_id, int $product_qty_new, string $transactionId): bool
    {
        return $this->where('transaction_id', $transactionId)
                    ->update($transaction_detail_id, ['product_quantity'=>$product_qty_new]);
    }

    public function removeTransactionDetail(string $transaction_detail_id, string $transactionId): int
    {
        $this->where('transaction_id', $transactionId)
             ->delete($transaction_detail_id);
        return $this->db->affectedRows();
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
