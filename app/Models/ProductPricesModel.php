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
    
    private function generateUpsertBatchQuery(array $data): string
    {
        // $columns contains columns for upsertBatch query
        $columns = '';
        // $values contains values for upsertBatch query
        $values = '';
        // Contains do update clause for upsertBatch query if happen conflict
        $doUpdate = 'DO UPDATE SET ';

        // generate columns, do update and values
        $columns .= '(';
        $values .= '(';
        foreach ($data[0] as $key => $value) {
            $columns .= $key . ',';
            $doUpdate .= "$key = EXCLUDED.$key,";
            $values .= $this->escape($value) . ',';
        }
        $columns = rtrim($columns, ',');
        $columns .= ')';
        $doUpdate = rtrim($doUpdate, ',');
        $values = rtrim($values, ',');
        $values .= '),';

        // generate next values
        $countData = count($data);
        for ($i = 1; $i < $countData; $i++) {
            $values .= '(';
            foreach ($data[$i] as $d) {
                $values .= $this->escape($d) . ',';
            }
            $values = rtrim($values, ',');
            $values .= '),';
        }
        $values = rtrim($values, ',');
        
        return "INSERT INTO $this->table $columns VALUES $values
            ON CONFLICT ($this->primaryKey) $doUpdate";
    }
    
    /**
     * Upsert Batch (Update or Insert Batch)
     *
     * This function is combination of Update or Insert data batch.
     * Update row if it already exists, otherwise it will be insert the new row
     *
     * @param array $data
     *
     * @return bool
     */
    public function upsertBatch(array $data): bool
    {
        $query = $this->generateUpsertBatchQuery($data);
        if ($this->simpleQuery($query)) {
            return true;
        }
        return false;
    }
}
