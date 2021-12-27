<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionsModel extends Model
{
	protected $table = 'transactions';
	protected $primaryKey = 'transaction_id';
    protected $allowedFields = [
        'transaction_id',
        'user_id',
        'transaction_status',
        'customer_money',
        'created_at',
        'edited_at'
    ];
    protected $useAutoIncrement = false;

    public function getTotal(): int
    {
        return $this->countAll();
    }

    public function getAll(int $limit): array
    {
        return $this->select('
                        transactions.transaction_id,
                        transaction_status,
                        transactions.created_at,
                        transactions.edited_at,
                        full_name,
                        SUM(product_price*product_quantity) as total_payment
                    ', false)
                    ->selectSum('product_quantity', 'total_product')
                    ->join('transaction_details', 'transactions.transaction_id = transaction_details.transaction_id', 'LEFT')
                    ->join('users', 'transactions.user_id = users.user_id', 'INNER')
                    ->limit($limit)->groupBy(['transactions.transaction_id', 'full_name'])->orderBy('transactions.edited_at', 'DESC')
                    ->get()->getResultArray();
    }

    public function getTwoMonthsAgo(): array
    {
        $startDate = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m')-1, 1, date('Y')));
        $endDate = date('Y-m-d H:i:s');

        return $this->select('edited_at')
                    ->getWhere(['edited_at >=' => $startDate, 'edited_at <=' => $endDate])
                    ->getResultArray();
    }

    public function search(int $limit, string $dateStart, string $dateEnd): array
    {
        return $this->select('
                        transactions.transaction_id,
                        transaction_status,
                        transactions.created_at,
                        transactions.edited_at,
                        full_name,
                        SUM(product_price*product_quantity) as total_payment
                    ', false)
                    ->selectSum('product_quantity', 'total_product')
                    ->join('transaction_details', 'transactions.transaction_id = transaction_details.transaction_id', 'LEFT')
                    ->join('users', 'transactions.user_id = users.user_id', 'INNER')
                    ->where(['transactions.edited_at >=' => $dateStart, 'transactions.edited_at <=' => $dateEnd])
                    ->limit($limit)->groupBy(['transactions.transaction_id', 'full_name'])->orderBy('transactions.edited_at', 'ASC')
                    ->get()->getResultArray();
    }

    public function getTotalSearch(string $dateStart, string $dateEnd): int
    {
        return $this->select('transaction_id')
                    ->where(['transactions.edited_at >=' => $dateStart, 'transactions.edited_at <=' => $dateEnd])
                    ->countAllResults();
    }

    public function getAllLonger(int $limit, string $smallestEditedAt): array
    {
        return $this->select('
                        transactions.transaction_id,
                        transaction_status,
                        transactions.created_at,
                        transactions.edited_at,
                        full_name,
                        SUM(product_price*product_quantity) as total_payment
                    ', false)
                    ->selectSum('product_quantity', 'total_product')
                    ->join('transaction_details', 'transactions.transaction_id = transaction_details.transaction_id', 'LEFT')
                    ->join('users', 'transactions.user_id = users.user_id', 'INNER')
                    ->limit($limit)->groupBy(['transactions.transaction_id', 'full_name'])->orderBy('transactions.edited_at', 'DESC')
                    ->getWhere(['transactions.edited_at <' => $smallestEditedAt])->getResultArray();
    }

    public function searchLonger(int $limit, string $smallestEditedAt, string $dateStart, string $dateEnd): array
    {
        return $this->select('
                        transactions.transaction_id,
                        transaction_status,
                        transactions.created_at,
                        transactions.edited_at,
                        full_name,
                        SUM(product_price*product_quantity) as total_payment
                    ', false)
                    ->selectSum('product_quantity', 'total_product')
                    ->join('transaction_details', 'transactions.transaction_id = transaction_details.transaction_id', 'LEFT')
                    ->join('users', 'transactions.user_id = users.user_id', 'INNER')
                    ->where(['transactions.edited_at >=' => $dateStart, 'transactions.edited_at <=' => $dateEnd])
                    ->limit($limit)->groupBy(['transactions.transaction_id', 'full_name'])->orderBy('transactions.edited_at', 'ASC')
                    ->getWhere(['transactions.edited_at <' => $smallestEditedAt])->getResultArray();
    }

    public function getNotTransactionYetId(): ?string
    {
        return $this->select('transaksi_id')
                    ->getWhere(['status_transaksi' => 'belum', 'pengguna_id' => $_SESSION['posw_user_id']])
                    ->getRowArray()['transaksi_id'] ?? null;
    }

    public function getTransactionsThreeDaysAgo(string $timestamp_three_days_ago): array
    {
        return $this->select('transaksi_id, waktu_buat')
                    ->orderBy('waktu_buat', 'desc')
                    ->getWhere(['waktu_buat >=' => $timestamp_three_days_ago, 'pengguna_id' => $_SESSION['posw_user_id']])
                    ->getResultArray();
    }

    public function findTransaction(string $transaction_id, string $column): ?array
    {
        return $this->select($column)
                    ->getWhere(['transaksi_id' => $transaction_id])
                    ->getRowArray();
    }
}
