<?php

namespace App\Controllers;

use App\Models\{ProductsModel};

class Cashier extends BaseController
{
    private const BESTSELLER_PRODUCT_LIMIT = 8;
    private const PRODUCT_LIMIT = 50;

    public function __construct()
    {
        $this->productsModel = new ProductsModel();
    }

    /**
     * remap products
     * 
     * This method use for remap products to be displayed in cashier page
     */
    private function remapProducts(array $products, bool $returnProductIds = false): ? array
    {
        $fmt = new \NumberFormatter('id_ID', \NumberFormatter::CURRENCY);
        $fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);

        $productIds = [];
        $productsRemapped = [];
        foreach ($products as $val) {
            // last index for products remapped
            $lastIndex = count($productIds) - 1;

            // if current product id equal to previous product id
            if ($lastIndex >= 0 && $val['product_id'] == $productIds[$lastIndex]) {
                // only add product price to previous product in products remapped
                $productsRemapped[$lastIndex]['product_prices'][] = [
                    'product_price_id' => $val['product_price_id'],
                    'product_magnitude' => $val['product_magnitude'],
                    'product_price_formatted' => $fmt->formatCurrency($val['product_price'], 'IDR'),
                    'product_price' => $val['product_price']
                ];

            } else {
                // note product id to product_ids variabel, for fast check is product exists in products array
                $productIds[] = $val['product_id'];

                // add new data product
                $productsRemapped[] = [
                    'product_id' => $val['product_id'],
                    'product_name' => $val['product_name'],
                    'product_photo' => $val['product_photo'],
                    'product_prices' => [
                        [
                            'product_price_id' => $val['product_price_id'],
                            'product_magnitude' => $val['product_magnitude'],
                            'product_price_formatted' => $fmt->formatCurrency($val['product_price'], 'IDR'),
                            'product_price' => $val['product_price']   
                        ],
                    ],
                ];
            }
        }

        if ($returnProductIds == true) {
            return ['products' => $productsRemapped, 'product_ids' => $productIds];
        }
        return $productsRemapped;
    }

    public function index()
    {
        helper('active_menu');
        
        // get best seller product and remainder product
        [
            'products' => $bestSellerProducts,
            'product_ids' => $productIds
        ] = $this->remapProducts($this->productsModel->getBestsellers(static::BESTSELLER_PRODUCT_LIMIT), true);
        $remainderProducts = $this->remapProducts($this->productsModel->getRemainderForCashier($productIds, static::PRODUCT_LIMIT));

        $data['title'] = 'Home . POSW';
        $data['totalProduct'] = $this->productsModel->getTotalForCashier();
        $data['bestSellerProducts'] = $bestSellerProducts;
        $data['remainderProducts'] = $remainderProducts;

        return view('cashier', $data);
    }
}
