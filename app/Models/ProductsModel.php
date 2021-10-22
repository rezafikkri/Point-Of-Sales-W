<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductsModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $allowedFields = [
        'product_id',
        'product_category_id',
        'product_name',
        'product_photo',
        'product_status',
        'created_at',
        'updated_at'
    ];
    protected $useAutoIncrement = false;

    public function getProducts(int $limit): array
    {
        return $this->select('produk_id,nama_produk,nama_kategori_produk,status_produk,produk.waktu_buat')
                    ->join('kategori_produk', 'kategori_produk.kategori_produk_id = produk.kategori_produk_id', 'INNER')
                    ->orderBy('waktu_buat', 'DESC')->limit($limit)->get()->getResultArray();
    }

    public function getTotal(): int
    {
        return $this->countAll();
    }

    public function getProductSearches(int $limit, string $match): array
    {
        return $this->select('produk_id,nama_produk,nama_kategori_produk,status_produk,produk.waktu_buat')
                    ->join('kategori_produk', 'kategori_produk.kategori_produk_id = produk.kategori_produk_id', 'INNER')
                    ->orderBy('waktu_buat', 'DESC')->limit($limit)
                    ->like('nama_produk',$match,'after')->get()->getResultArray();
    }

    public function countAllProduct(): int
    {
        return $this->select('produk_id')->get()->getNumRows();
    }

    public function countAllProductSearch(string $match): int
    {
        return $this->select('produk_id')->like('nama_produk',$match,'after')->get()->getNumRows();
    }

    public function findProduct(string $product_id, string $column): ? array
    {
        return $this->select($column)->getWhere(['produk_id'=>$product_id])->getRowArray();
    }

    public function removeProducts(array $product_ids): int
    {
        try {
            $this->whereIn('produk_id', $product_ids)->delete();
            return $this->db->affectedRows();
        } catch (\ErrorException $e) {
            return 0;
        }
    }

    public function getLongerProducts(int $limit, string $smallest_create_time): array
    {
        return $this->select('produk_id,nama_produk,status_produk,produk.waktu_buat,nama_kategori_produk')
                    ->join('kategori_produk', 'kategori_produk.kategori_produk_id = produk.kategori_produk_id', 'INNER')
                    ->limit($limit)->orderBy('produk.waktu_buat', 'DESC')->getWhere(['produk.waktu_buat <' => $smallest_create_time])
                    ->getResultArray();
    }

    public function getLongerProductSearches(int $limit, string $smallest_create_time, string $match): array
    {
         return $this->select('produk_id,nama_produk,status_produk,produk.waktu_buat,nama_kategori_produk')
                     ->join('kategori_produk', 'kategori_produk.kategori_produk_id = produk.kategori_produk_id', 'INNER')
                     ->limit($limit)->orderBy('produk.waktu_buat', 'DESC')->like('nama_produk',$match,'after')
                     ->getWhere(['produk.waktu_buat <' => $smallest_create_time])->getResultArray();
    }

    public function findProducts(array $product_ids, string $column): array
    {
        return $this->select($column)->whereIn('produk_id', $product_ids)->get()->getResultArray();
    }

    public function getBestsellerProducts(int $limit): array
    {
        $builder = $this->db->table("
            (SELECT p.nama_produk, p.foto_produk, p.produk_id,
                    (SELECT kp.nama_kategori_produk FROM kategori_produk kp WHERE kp.kategori_produk_id=p.kategori_produk_id) nama_kategori_produk,
                    SUM(td.jumlah_produk) jumlah_produk
            FROM produk p
            INNER JOIN harga_produk hp USING(produk_id)
            LEFT JOIN transaksi_detail td USING(harga_produk_id)
            WHERE p.status_produk='ada' AND jumlah_produk IS NOT NULL AND p.produk_id IS NOT NULL
            GROUP BY p.produk_id ORDER BY jumlah_produk DESC LIMIT ".$this->db->escape($limit)." ) p
        ");

        return $builder->select("
            p.produk_id,
            p.nama_produk,
            p.foto_produk,
            p.nama_kategori_produk,
            hp.harga_produk_id,
            hp.harga_produk,
            hp.besaran_produk,
            p.jumlah_produk
        ")
        ->join('harga_produk hp', 'hp.produk_id = p.produk_id', 'INNER')
        ->orderBy('p.jumlah_produk', 'DESC')
        ->get()->getResultArray();

    /* Query
     *
     * SELECT p.nama_produk, sum(td.jumlah_produk) jumlah_produk, kp.nama_kategori_produk FROM produk p
     * INNER JOIN kategori_produk kp USING(kategori_produk_id)
     * INNER JOIN harga_produk hp USING(produk_id)
     * LEFT JOIN transaksi_detail td USING(harga_produk_id)
     * WHERE p.status_produk='ada' GROUP BY p.nama_produk, kp.nama_kategori_produk ORDER BY jumlah_produk DESC LIMIT 8;
    */
    }

    private function escapeArray(array $data): array
    {
        $data_escaped = [];
        foreach($data as $d) {
            $data_escaped[] = $this->db->escape($d);
        }
        return $data_escaped;
    }

    public function getProductsForCashier(array $product_ids, int $limit): array
    {
        $table = "(SELECT p.waktu_buat, p.nama_produk, p.foto_produk, p.produk_id,
                          (SELECT kp.nama_kategori_produk FROM kategori_produk kp WHERE kp.kategori_produk_id=p.kategori_produk_id) nama_kategori_produk,
                          SUM(td.jumlah_produk) jumlah_produk
                  FROM produk p
                  INNER JOIN harga_produk hp USING(produk_id)
                  LEFT JOIN transaksi_detail td USING(harga_produk_id)
                  WHERE p.status_produk='ada' AND p.produk_id IS NOT NULL";

        // if exists product_ids
        if (count($product_ids) > 0) {
            $table .= " AND p.produk_id NOT IN (".implode(',',$this->escapeArray($product_ids)).")";
        }

        $table .= " GROUP BY p.produk_id ORDER BY p.waktu_buat DESC LIMIT ".$this->db->escape($limit).") p";

        $builder = $this->db->table($table);
        return $builder->select("
            p.produk_id,
            p.waktu_buat,
            p.nama_produk,
            p.foto_produk,
            p.nama_kategori_produk,
            hp.harga_produk_id,
            hp.harga_produk,
            hp.besaran_produk,
            p.jumlah_produk
        ")
        ->join('harga_produk hp', 'hp.produk_id = p.produk_id', 'INNER')
        ->orderBy('p.waktu_buat', 'DESC')
        ->get()->getResultArray();
    }

    public function getProductSearchesForCashier(int $limit, string $keyword): array
    {
        $table = "(SELECT p.waktu_buat, p.nama_produk, p.foto_produk, p.produk_id,
                          (SELECT kp.nama_kategori_produk FROM kategori_produk kp WHERE kp.kategori_produk_id=p.kategori_produk_id) nama_kategori_produk,
                          SUM(td.jumlah_produk) jumlah_produk
                  FROM produk p
                  INNER JOIN harga_produk hp USING(produk_id)
                  LEFT JOIN transaksi_detail td USING(harga_produk_id)
                  WHERE p.status_produk='ada' AND p.produk_id IS NOT NULL AND p.nama_produk LIKE '".$this->db->escapeLikeString($keyword)."%'
                  GROUP BY p.produk_id ORDER BY p.waktu_buat DESC LIMIT ".$this->db->escape($limit).") p";

        $builder = $this->db->table($table);
        return $builder->select("
            p.produk_id,
            p.waktu_buat,
            p.nama_produk,
            p.foto_produk,
            p.nama_kategori_produk,
            hp.harga_produk_id,
            hp.harga_produk,
            hp.besaran_produk,
            p.jumlah_produk
        ")
        ->join('harga_produk hp', 'hp.produk_id = p.produk_id', 'INNER')
        ->orderBy('p.waktu_buat', 'DESC')
        ->get()->getResultArray();
    }

    public function countAllProductForCashier(): int
    {
        return $this->select('produk_id')->where('status_produk', 'ada')->get()->getNumRows();
    }

    public function countAllProductSearchForCashier(string $match): int
    {
        return $this->select('produk_id')->where('status_produk', 'ada')->like('nama_produk',$match,'after')->get()->getNumRows();
    }
}
