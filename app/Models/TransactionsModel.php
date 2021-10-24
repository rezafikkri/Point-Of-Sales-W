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

    public function getTransactions(int $limit): array
    {
        return $this->select('
                        transaksi.transaksi_id,
                        status_transaksi,
                        transaksi.waktu_buat,
                        nama_lengkap,
                        SUM(harga_produk*jumlah_produk) as payment_total'
                    , false)
                    ->selectSum('jumlah_produk', 'product_total')
                    ->join('transaksi_detail', 'transaksi.transaksi_id=transaksi_detail.transaksi_id', 'LEFT')
                    ->join('harga_produk', 'transaksi_detail.harga_produk_id=harga_produk.harga_produk_id', 'lEFT')
                    ->join('pengguna', 'transaksi.pengguna_id=pengguna.pengguna_id', 'INNER')
                    ->limit($limit)->groupBy(['transaksi.transaksi_id', 'nama_lengkap'])->orderBy('transaksi.waktu_buat', 'DESC')
                    ->get()->getResultArray();
    }

    public function getTwoMonthsAgo(): array {
        $startDate = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m')-1, 1, date('Y')));
        $endDate = date('Y-m-d H:i:s');

        return $this->select('edited_at')
                    ->getWhere(['edited_at >=' => $startDate, 'edited_at <=' => $endDate])
                    ->getResultArray();
    }

    public function getTransactionSearches(int $limit, string $date_start, string $date_end): array
    {
        return $this->select('
                        transaksi.transaksi_id,
                        status_transaksi,
                        transaksi.waktu_buat,
                        nama_lengkap,
                        SUM(harga_produk*jumlah_produk) as payment_total'
                    , false)
                    ->selectSum('jumlah_produk', 'product_total')
                    ->join('transaksi_detail', 'transaksi.transaksi_id=transaksi_detail.transaksi_id', 'LEFT')
                    ->join('harga_produk', 'transaksi_detail.harga_produk_id=harga_produk.harga_produk_id', 'lEFT')
                    ->join('pengguna', 'transaksi.pengguna_id=pengguna.pengguna_id', 'INNER')
                    ->where('transaksi.waktu_buat >=', $date_start)->where('transaksi.waktu_buat <=', $date_end)
                    ->limit($limit)->groupBy(['transaksi.transaksi_id', 'nama_lengkap'])->orderBy('transaksi.waktu_buat', 'DESC')
                    ->get()->getResultArray();
    }

    public function countAllTransactionSearch(string $date_start, string $date_end): int
    {
        return $this->select('transaksi_id')
                    ->where('transaksi.waktu_buat >=', $date_start)
                    ->where('transaksi.waktu_buat <=', $date_end)
                    ->get()->getNumRows();
    }

    public function getNotTransactionYetId(): ? string
    {
        return $this->select('transaksi_id')
                    ->getWhere(['status_transaksi' => 'belum', 'pengguna_id' => $_SESSION['posw_user_id']])
                    ->getRowArray()['transaksi_id']??null;
    }

    public function getTransactionsThreeDaysAgo(string $timestamp_three_days_ago): array
    {
        return $this->select('transaksi_id, waktu_buat')
                    ->orderBy('waktu_buat', 'desc')
                    ->getWhere(['waktu_buat >=' => $timestamp_three_days_ago, 'pengguna_id' => $_SESSION['posw_user_id']])
                    ->getResultArray();
    }

    public function findTransaction(string $transaction_id, string $column): ? array
    {
        return $this->select($column)
                    ->getWhere(['transaksi_id' => $transaction_id])
                    ->getRowArray();
    }

    public function removeTransactions(array $transaction_ids): int
    {
        $timestamp_three_days_ago = date('Y-m-d H:i:s', mktime(00, 00, 00, date('m'), date('d'), date('Y')) - (60 * 60 * 24 * 3));

        try {
            $this->whereIn('transaksi_id', $transaction_ids)
                 ->where(['status_transaksi' => 'selesai', 'waktu_buat <' => $timestamp_three_days_ago])->delete();
            return $this->db->affectedRows();
        } catch (\ErrorException $e) {
            return 0;
        }
    }

    public function getLongerTransactions(int $limit, string $smallest_create_time): array
    {
        return $this->select('
                        transaksi.transaksi_id,
                        status_transaksi,
                        transaksi.waktu_buat,
                        nama_lengkap,
                        SUM(harga_produk*jumlah_produk) as payment_total'
                    , false)
                    ->selectSum('jumlah_produk', 'product_total')
                    ->join('transaksi_detail', 'transaksi.transaksi_id=transaksi_detail.transaksi_id', 'LEFT')
                    ->join('harga_produk', 'transaksi_detail.harga_produk_id=harga_produk.harga_produk_id', 'lEFT')
                    ->join('pengguna', 'transaksi.pengguna_id=pengguna.pengguna_id', 'INNER')
                    ->limit($limit)->groupBy(['transaksi.transaksi_id', 'nama_lengkap'])->orderBy('transaksi.waktu_buat', 'DESC')
                    ->getWhere(['waktu_buat <' => $smallest_create_time])->getResultArray();
    }

    public function getLongerTransactionSearches(int $limit, string $smallest_create_time, string $date_start, string $date_end): array
    {
        return $this->select('
                        transaksi.transaksi_id,
                        status_transaksi,
                        transaksi.waktu_buat,
                        nama_lengkap,
                        SUM(harga_produk*jumlah_produk) as payment_total'
                    , false)
                    ->selectSum('jumlah_produk', 'product_total')
                    ->join('transaksi_detail', 'transaksi.transaksi_id=transaksi_detail.transaksi_id', 'LEFT')
                    ->join('harga_produk', 'transaksi_detail.harga_produk_id=harga_produk.harga_produk_id', 'lEFT')
                    ->join('pengguna', 'transaksi.pengguna_id=pengguna.pengguna_id', 'INNER')
                    ->where('transaksi.waktu_buat >=', $date_start)->where('transaksi.waktu_buat <=', $date_end)
                    ->limit($limit)->groupBy(['transaksi.transaksi_id', 'nama_lengkap'])->orderBy('transaksi.waktu_buat', 'DESC')
                    ->getWhere(['waktu_buat <' => $smallest_create_time])->getResultArray();
    }
}
