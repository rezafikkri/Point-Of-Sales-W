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

    public function getAll(string $productId, string $columns): array
    {
        return $this->select($columns)
                    ->getWhere([
                        'product_id' => $productId
                    ])->getResultArray();
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
