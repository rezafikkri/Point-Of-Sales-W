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
                    ->orderBy('product_name', 'ASC')->limit($limit)
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

    public function getBestSellers(int $limit): array
    {
        $builder = $this->db->table("
            (SELECT p.product_name, p.product_photo, p.product_id,
            SUM(td.product_quantity) product_quantity
            FROM products p
            INNER JOIN product_prices pp USING(product_id)
            INNER JOIN transaction_details td USING(product_price_id)
            WHERE p.product_status='ada'
            GROUP BY p.product_id ORDER BY product_quantity DESC LIMIT ".$this->db->escape($limit)." ) p
        ");

        return $builder->select("
            p.product_id,
            p.product_name,
            p.product_photo,
            pp.product_price_id,
            pp.product_price,
            pp.product_magnitude
        ")
        ->join('product_prices pp', 'pp.product_id = p.product_id', 'INNER')
        ->orderBy('p.product_quantity', 'DESC')
        ->get()->getResultArray();
    }

    public function getRemainderForCashier(array $productIds, int $limit): array
    {
        $table = "
            (SELECT p.product_name, p.product_photo, p.product_id, p.edited_at
            FROM products p
            INNER JOIN product_prices pp USING(product_id)
            WHERE p.product_status='ada'
        ";

        // if exists product_ids
        if (count($productIds) > 0) {
            $table .= " AND p.product_id NOT IN ('" . implode("','", $productIds) . "')";
        }
    
        $table .= " GROUP BY p.product_id ORDER BY p.edited_at DESC LIMIT " . $this->db->escape($limit) . ") p";
        $builder = $this->db->table($table);

        return $builder->select("
            p.product_id,
            p.product_name,
            p.product_photo,
            pp.product_price_id,
            pp.product_price,
            pp.product_magnitude
        ")
        ->join('product_prices pp', 'pp.product_id = p.product_id', 'INNER')
        ->orderBy('p.edited_at', 'DESC')
        ->get()->getResultArray();
    }

    public function getTotalForCashier(): int
    {
        return $this->select('product_id')->where('product_status', 'ada')->get()->getNumRows();
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

    public function countAllProductSearchForCashier(string $match): int
    {
        return $this->select('produk_id')->where('status_produk', 'ada')->like('nama_produk',$match,'after')->get()->getNumRows();
    }
}
