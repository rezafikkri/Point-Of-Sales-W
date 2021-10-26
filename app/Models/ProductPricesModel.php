<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductPricesModel extends Model
{
    protected $table = 'product_prices';
    protected $primaryKey = 'product_price_id';
    protected $allowedFields = [
        'product_price_id',
        'product_id',
        'product_magnitude',
        'product_price'
    ];
    protected $useAutoIncrement = false;

    public function getProductPrices(string $product_id, string $column): array
    {
        return $this->select($column)->getWhere(['product_id'=>$product_id])->getResultArray();
    }

    public function removeProductPrice(string $product_price_id): int
    {
        try {
            $this->delete($product_price_id);
            return $this->db->affectedRows();
        } catch (\ErrorException $e) {
            return 0;
        }
    }
}
