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
        'edited_at'
    ];
    protected $useAutoIncrement = false;

    public function getAll(int $limit): array
    {
        return $this->select('product_id, product_name, product_category_name, product_status, products.created_at, products.edited_at')
                    ->join('product_categories', 'product_categories.product_category_id = products.product_category_id', 'INNER')
                    ->orderBy('edited_at', 'DESC')->limit($limit)->get()->getResultArray();
    }

    public function getTotal(): int
    {
        return $this->countAll();
    }

    public function getOne(string $productId): ?array
    {
        return $this->select('product_category_id, product_name, product_status, product_photo')->getWhere([
            'product_id' => $productId
        ])->getRowArray();
    }

    public function search(int $limit, string $keyword): array
    {
        return $this->select('product_id, product_name, product_category_name, product_status, products.created_at, products.edited_at')
                    ->join('product_categories', 'product_categories.product_category_id = products.product_category_id', 'INNER')
                    ->orderBy('edited_at', 'DESC')->limit($limit)
                    ->like('product_name', $keyword)->get()->getResultArray();
    }

    public function getTotalSearch(string $keyword): int
    {
        return $this->select('product_id')
                    ->like('product_name', $keyword)
                    ->countAllResults();
    }

    public function finds(array $productIds, string $column): array
    {
        return $this->select($column)->find($productIds);
    }
    
    /**
     * Get all longer product
     * 
     * This method use for get all longer product when delete product success
     */
    public function getAllLonger(int $limit, string $smallestEditedAt): array
    {
        return $this->select('product_id, product_name, product_status, products.created_at, products.edited_at, product_category_name')
                    ->join('product_categories', 'product_categories.product_category_id = products.product_category_id', 'INNER')
                    ->limit($limit)->orderBy('products.edited_at', 'DESC')
                    ->getWhere([
                        'products.edited_at <' => $smallestEditedAt
                    ])->getResultArray();
    }

    /**
     * Search all longer product
     * 
     * This method use for search longer product when delete product success
     */
    public function searchLonger(int $limit, string $smallestEditedAt, string $keyword): array
    {
         return $this->select('product_id, product_name, product_status, products.created_at, products.edited_at, product_category_name')
                     ->join('product_categories', 'product_categories.product_category_id = products.product_category_id', 'INNER')
                     ->limit($limit)->orderBy('products.edited_at', 'DESC')
                     ->like('product_name', $keyword)->getWhere([
                         'products.edited_at <' => $smallestEditedAt
                     ])->getResultArray();
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
