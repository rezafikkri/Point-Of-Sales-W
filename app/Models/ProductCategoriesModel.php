<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductCategoriesModel extends Model
{
    protected $table = 'product_categories';
    protected $primaryKey = 'product_category_id';
    protected $allowedFields = [
        'product_category_id',
        'product_category_name',
        'created_at',
        'edited_at'
    ];
    protected $useAutoIncrement = false;

    public function getAll(): array
    {
        return $this->orderBy('edited_at', 'DESC')->findAll();
    }

    public function getOne(string $productCategoryId): ? array
    {
        return $this->select('product_category_name')->getWhere(['product_category_id' => $productCategoryId])->getRowArray();
    }

    public function removeProductCategory(string $product_category_id): int
    {
        try {
            $this->delete($product_category_id);
            return $this->db->affectedRows();
        } catch(\ErrorException $e) {
            return 0;
        }
    }

    public function getProductCategoriesForFormSelect(): array
    {
        return $this->select('kategori_produk_id, nama_kategori_produk')->get()->getResultArray();
    }
}
