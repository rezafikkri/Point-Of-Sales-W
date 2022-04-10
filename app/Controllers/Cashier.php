<?php

namespace App\Controllers;

use App\Models\{ProductsModel, TransactionsModel, TransactionDetailsModel};

class Cashier extends BaseController
{
    private const BESTSELLER_PRODUCT_LIMIT = 4;
    private const PRODUCT_LIMIT = 4;

    public function __construct()
    {
        $this->productsModel = new ProductsModel();
        $this->transactionsModel = new TransactionsModel();
        $this->transactionDetailsModel = new TransactionDetailsModel();
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
        ] = $this->remapProducts($this->productsModel->getBestSeller(static::BESTSELLER_PRODUCT_LIMIT), true);
        $remainderProducts = $this->remapProducts($this->productsModel->getRemainderForCashier($productIds, static::PRODUCT_LIMIT));

        $data['title'] = 'Home . POSW';
        $data['totalProduct'] = $this->productsModel->getTotalForCashier();
        $data['bestSellerProducts'] = $bestSellerProducts;
        $data['remainderProducts'] = $remainderProducts;
        $data['bestSellerProductLimit'] = static::BESTSELLER_PRODUCT_LIMIT;
        $data['remainderProductLimit'] = static::PRODUCT_LIMIT;

        return view('cashier', $data);
    }

    public function searchProducts(string $keyword)
    {
        $keyword = filter_var($keyword, FILTER_SANITIZE_STRING);
        $totalProduct = $this->productsModel->getTotalSearchForCashier($keyword);
        $products = $this->remapProducts($this->productsModel->searchForCashier(static::PRODUCT_LIMIT, $keyword));

        return json_encode([
            'products' => $products,
            'total_product' => $totalProduct,
            'product_limit' => static::PRODUCT_LIMIT
        ]);
    }

    /**
     * Get transaction details 
     * 
     * This method use for get transcation details for normal transaction process
     */
    private function getTransactionDetailsTransaction(): array
    {
        $transactionDetails = [];
        // if exists session transaction id
        if (isset($_SESSION['transaction_id'])) {
            $transactionDetails = $this->transactionDetailsModel->getAllForCashier(
                $_SESSION['transaction_id'],
                'p.product_id, transaction_detail_id, p.product_name, pp.product_price, pp.product_magnitude, product_quantity'
            );
        }

        // get transaction id from unfinished transaction
        $transactionId = $this->transactionsModel->getUnfinishedTransactionId();
        // if exists unfinised transaction id
        if ($transactionId) {
            $transactionDetails = $this->transactionDetailsModel->getAllForCashier(
                $transactionId,
                'p.product_id, transaction_detail_id, p.product_name, pp.product_price, pp.product_magnitude, product_quantity'
            );

            // create session
            $this->session->set([
                'transaction_id' => $transactionId
            ]);

        }
        return $transactionDetails;
    }

    private function getTransactionDetailsRollbackTransaction(string $transactionId): array
    {
        // get customer money
        $customerMoney = $this->transactionsModel->getOne($transactionId, 'customer_money')['customer_money'] ?? null;
        // get transaction details
        $transactionDetails = $this->transactionDetailsModel->getAllForCashier(
            $transactionId,
            'p.product_id, transaction_detail_id, p.product_name, pp.product_price, pp.product_magnitude, product_quantity'
        );

        return ['customer_money' => $customerMoney, 'transaction_details' => $transactionDetails];
    }

    public function showTransactionDetails()
    {
        // if file backup exists
        if (file_exists(WRITEPATH . 'backup-transaction/data.json')) {
            // get transaction details rollback transaction
            ['transaction_id' => $transactionId] = json_decode(file_get_contents(WRITEPATH . 'backup-transaction/data.json'), true);
            [
                'customer_money' => $customerMoney,
                'transaction_details' => $transactionDetails
            ] =  $this->getTransactionDetailsRollbackTransaction($transactionId);

            return json_encode([
                'transaction_id' => $transactionId,
                'customer_money' => $customerMoney,
                'transaction_details' => $transactionDetails,
                'type' => 'rollback-transaction'
            ]);
        }

        // get transaction details normal transaction
        $transactionDetails = $this->getTransactionDetailsTransaction();

        return json_encode([
            'transaction_id' => $_SESSION['transaction_id'] ?? null,
            'transaction_details' => $transactionDetails,
            'type' => 'transaction'
        ]);
    }

    public function cancelTransaction()
    {
        // remove transaction and will automatic remove transaction detail related to transaction
        $this->transactionsModel->delete($_SESSION['transaction_id']);
        // remove session transaction id
        $this->session->remove('transaction_id');

        return json_encode([
            'status' => 'success',
            'csrf_value' => csrf_hash()
        ]);
    }
}
