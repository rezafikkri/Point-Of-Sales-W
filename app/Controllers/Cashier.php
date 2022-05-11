<?php

namespace App\Controllers;

use App\Models\{ProductsModel, TransactionsModel, TransactionDetailsModel};
use CodeIgniter\I18n\Time;

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
        $remainderProducts = $this->remapProducts($this->productsModel->getRemainder($productIds, static::PRODUCT_LIMIT));

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
            [
                'transaction_id' => $transactionId
            ] = json_decode(file_get_contents(WRITEPATH . 'backup-transaction/data.json'), true);

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
        $deleteTransaction = $this->transactionsModel->delete($_SESSION['transaction_id']);

        if ($deleteTransaction) {
            // remove session transaction id
            $this->session->remove('transaction_id');
    
            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }
        return json_encode([
            'status' => 'success',
            'message' => 'Transaksi gagal dibatalkan',
            'csrf_value' => csrf_hash()
        ]);
    }

    public function finishTransaction()
    {
        if (!$this->validate([
            'customer_money' => [
                'label' => 'Uang Pembeli',
                'rules' => 'required|integer|max_length[10]'
            ]
        ])) {
            return json_encode([
                'status' => 'fail',
                'message' => $this->validator->getErrors()['customer_money'],
                'csrf_value' => csrf_hash()
            ]);
        }

        $customerMoney = $this->request->getPost('customer_money', FILTER_SANITIZE_NUMBER_INT);
        $productHistories = json_decode($this->request->getPost('product_histories'), true);
        $productHistoriesFiltered = [];
        foreach ($productHistories as $ph) {
            $productHistoriesFiltered[] = [
                'transaction_detail_id' => filter_var($ph['transactionDetailId'], FILTER_SANITIZE_STRING),
                'product_name' => filter_var($ph['productName'], FILTER_SANITIZE_STRING),
                'product_price' => filter_var($ph['productPrice'], FILTER_SANITIZE_STRING),
                'product_magnitude' => filter_var($ph['productMagnitude'], FILTER_SANITIZE_STRING)
            ];
        }
 
        $this->transactionsModel->transBegin();

        // update customer money in db and update status transaction
        $createdAt = date('Y-m-d H:i:s');
        $updateTransaction = $this->transactionsModel->update($_SESSION['transaction_id'], [
            'customer_money' => $customerMoney,
            'transaction_status' => 'selesai',
            'created_at' => $createdAt,
            'edited_at' => $createdAt
        ]);
        // update product name, product price and product magnitude in product details table
        $updateProductHistories = $this->transactionDetailsModel->updateProductHistories(
            $_SESSION['transaction_id'],
            $productHistoriesFiltered
        );

        if ($updateTransaction && $updateProductHistories) {
            $this->transactionsModel->transCommit();

            // remove transaction id session
            $this->session->remove('transaction_id');

            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }
        
        $this->transactionsModel->transRollback();
        return json_encode([
            'status' => 'fail',
            'message' => 'Transaksi gagal',
            'csrf_value' => csrf_hash()
        ]);
    }

    private function buyProductTransaction(int $productQty): bool
    {
        $productPriceId = $this->request->getPost('product_price_id', FILTER_SANITIZE_STRING);

        // if exists transaction id session
        if (isset($_SESSION['transaction_id'])) {
            // add product to transaction detail
            $transactionDetailId = $this->transactionDetailsModel->insert([
                'transaction_detail_id' => generate_uuid(),
                'transaction_id' => $_SESSION['transaction_id'],
                'product_price_id' => $productPriceId,
                'product_quantity' => $productQty
            ]);
        } else {
            // if exists not transaction yet
            $transactionId = $this->transactionsModel->getUnfinishedTransactionId();
            if ($transactionId != null) {
                // create session
                $this->session->set('transaction_id', $transactionId);

                // add product to transaction detail
                $transactionDetailId = $this->transactionDetailsModel->insert([
                    'transaction_detail_id' => generate_uuid(),
                    'transaction_id' => $transactionId,
                    'product_price_id' => $productPriceId,
                    'product_quantity' => $productQty
                ]);
            }
            // if not exists not transaction yet
            else {
                $this->productsModel->transStart();

                // insert transaction
                $transactionId = $this->transactionsModel->insert([
                    'transaction_id' => generate_uuid(),
                    'user_id' => $_SESSION['sign_in_user_id'],
                    'transaction_status' => 'belum'
                ]);

                // add product to transaction detail
                $transactionDetailId = $this->transactionDetailsModel->insert([
                    'transaction_detail_id' => generate_uuid(),
                    'transaction_id' => $transactionId,
                    'product_price_id' => $productPriceId,
                    'product_quantity' => $productQty
                ]);
                
                $this->productsModel->transComplete();
                
                if ($transactionDetailId) {
                    // create session
                    $this->session->set('transaction_id', $transactionId);
                }
            }
        }

        if ($transactionDetailId) {
            // add transaction detail id to property
            $this->transactionDetailIdBuyProduct = $transactionDetailId;
            return true;
        }
        return false;
    }

    private function buyProductRollbackTransaction(string $transactionId, int $productQty): bool
    {
        // add product to transaction detail
        $transactionDetailId = $this->transactionDetailsModel->insert([
            'transaction_detail_id' => generate_uuid(),
            'transaction_id' => $transactionId,
            'product_price_id' => $this->request->getPost('product_price_id', FILTER_SANITIZE_STRING),
            'product_quantity' => $productQty
        ]);
        
        if ($transactionDetailId) {
            // add transaction detail id to property
            $this->transactionDetailIdBuyProduct = $transactionDetailId;
            return true;
        }
        return false;
    }

    public function buyProduct()
    {
        helper('generate_uuid');

        $productQty = (int)$this->request->getPost('product_qty', FILTER_SANITIZE_STRING);
        // if product qty = 0
        if ($productQty <= 0) {
            return false;
        }

        // if file backup exists
        if (file_exists(WRITEPATH . 'backup-transaction/data.json')) {
            [
                'transaction_id' => $transactionId
            ] = json_decode(file_get_contents(WRITEPATH . 'backup-transaction/data.json'), true);
            $buyProduct = $this->buyProductRollbackTransaction($transactionId, $productQty);
        } else {
            $buyProduct = $this->buyProductTransaction($productQty);
        }

        // if buy product success
        if ($buyProduct) {
            return json_encode([
                'status' => 'success',
                'transaction_detail_id' => $this->transactionDetailIdBuyProduct,
                'csrf_value' => csrf_hash()
            ]);
        }

        return json_encode([
            'status' => 'fail',
            'csrf_value' => csrf_hash()
        ]);
    }

    public function deleteProduct()
    {
        $transactionDetailId = $this->request->getPost('transaction_detail_id', FILTER_SANITIZE_STRING);

        // if session transaction id exists
        if (isset($_SESSION['transaction_id'])) {
            $transactionId = $_SESSION['transaction_id'];
        }
        // else if backup file exists
        else if (file_exists(WRITEPATH . 'backup-transaction/data.json')) {
            [
                'transaction_id' => $transactionId
            ] = json_decode(file_get_contents(WRITEPATH . 'backup-transaction/data.json'), true);
        } else {
            return false;
        }

        // delete product
        $deleteProduct = $this->transactionDetailsModel->deleteOne($transactionDetailId, $transactionId);

        // if delete product success
        if ($deleteProduct) {
            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }

        return json_encode([
            'status' => 'fail',
            'csrf_value' => csrf_hash()
        ]);
    }

    public function updateProductQty()
    {
        $transactionDetailId = $this->request->getPost('transaction_detail_id', FILTER_SANITIZE_STRING);
        $newProductQty = (int) $this->request->getPost('new_product_qty', FILTER_SANITIZE_STRING);

        // if new product qty <= 0
        if ($newProductQty <= 0) {
            return false;
        }

        // if session transaction id exists
        if (isset($_SESSION['transaction_id'])) {
            $transactionId = $_SESSION['transaction_id'];
        }
        // if file backup exists
        else if (file_exists(WRITEPATH . 'backup-transaction/data.json')) {
            [
                'transaction_id' => $transactionId
            ] = json_decode(file_get_contents(WRITEPATH . 'backup-transaction/data.json'), true);
        } else {
            return false;
        }

        // update product qty
        $updateProductQty = $this->transactionDetailsModel->updateProductQty(
            $transactionDetailId,
            $newProductQty,
            $transactionId
        );
        
        // if update product qty success
        if ($updateProductQty) {
            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }

        return json_encode([
            'status' => 'fail',
            'csrf_value' => csrf_hash()
        ]);
    }

    public function showTransactionsFiveHoursAgo()
    {
        $timestampFiveHoursAgo = date(
            'Y-m-d H:i:s',
            mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')) - (60 * 60 * 5)
        );
        $transactionsFiveHoursAgo = $this->transactionsModel->getTransactionsFiveHoursAgo($timestampFiveHoursAgo);

        // convert timestamp
        foreach ($transactionsFiveHoursAgo as $key => $value) {
            $createdAt = Time::createFromFormat('Y-m-d H:i:s', $value['created_at']);
            $transactionsFiveHoursAgo[$key]['created_at'] = $createdAt->toLocalizedString('dd MMM y HH:mm');

            // if edited at is not null
            if ($value['edited_at']) {
                $editedAt = Time::createFromFormat('Y-m-d H:i:s', $value['edited_at']);
                $transactionsFiveHoursAgo[$key]['edited_at'] = $editedAt->toLocalizedString('dd MMM y HH:mm');
            }
        }

        return json_encode([
            'transactions_five_hours_ago' => $transactionsFiveHoursAgo,
            'csrf_value' => csrf_hash()
        ]);
    }

    public function showTransactionDetailsFiveHoursAgo()
    {
        $transactionId = $this->request->getGet('transaction_id', FILTER_SANITIZE_STRING);
        // change transaction status
        $this->transactionsModel->update($transactionId, [
            'transaction_status' => 'belum'
        ]);

        // get customer money and transaction detail
        $customerMoney = $this->transactionsModel->getOne($transactionId, 'customer_money')['customer_money'] ?? null;
        $transactionDetails = $this->transactionDetailsModel->getAllForCashier(
            $transactionId,
            'p.product_id, transaction_detail_id, p.product_name, pp.product_price, pp.product_magnitude, product_quantity'
        );

        // backup transaction and transaction details to json file
        $dataBackup = json_encode(['transaction_id' => $transactionId, 'transaction_details' => $transactionDetails]);
        file_put_contents(WRITEPATH . 'backup-transaction/data.json', $dataBackup);

        return json_encode([
            'customer_money' => $customerMoney,
            'transaction_details' => $transactionDetails,
            'csrf_value' => csrf_hash()
        ]);
    }

    public function finishRollbackTransaction()
    {
        if (!$this->validate([
            'customer_money' => [
                'label' => 'Uang Pembeli',
                'rules' => 'required|integer|max_length[10]'
            ]
        ])) {
            return json_encode([
                'status' => 'fail',
                'message' => $this->validator->getErrors()['customer_money'],
                'csrf_value' => csrf_hash()
            ]);
        }

        [
            'transaction_id' => $transactionId
        ] = json_decode(file_get_contents(WRITEPATH . 'backup-transaction/data.json'), true);
        $customerMoney = $this->request->getPost('customer_money', FILTER_SANITIZE_NUMBER_INT);
        $productHistories = json_decode($this->request->getPost('product_histories'), true);
        $productHistoriesFiltered = [];
        foreach ($productHistories as $ph) {
            $productHistoriesFiltered[] = [
                'transaction_detail_id' => filter_var($ph['transactionDetailId'], FILTER_SANITIZE_STRING),
                'product_name' => filter_var($ph['productName'], FILTER_SANITIZE_STRING),
                'product_price' => filter_var($ph['productPrice'], FILTER_SANITIZE_STRING),
                'product_magnitude' => filter_var($ph['productMagnitude'], FILTER_SANITIZE_STRING)
            ];
        }

        $this->transactionsModel->transBegin();

        // update customer money, edited at and update status transaction 
        $editedAt = date('Y-m-d H:i:s');
        $updateTransaction = $this->transactionsModel->update($transactionId, [
            'customer_money' => $customerMoney,
            'transaction_status' => 'selesai',
            'edited_at' => $editedAt
        ]);
        // update product name, product price and product magnitude in product details table
        $updateProductHistories = $this->transactionDetailsModel->updateProductHistories(
            $transactionId,
            $productHistoriesFiltered
        );
         
        if ($updateTransaction && $updateProductHistories) {
            $this->transactionsModel->transCommit();

            // if exists file backup
            if (file_exists(WRITEPATH . 'backup-transaction/data.json')) {
                // remove file backup
                unlink(WRITEPATH . 'backup-transaction/data.json');
            }
    
            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }

        $this->transactionsModel->transRollback();
        return json_encode([
            'status' => 'fail',
            'message' => 'Transaksi gagal',
            'csrf_value' => csrf_hash()
        ]);
    }
}
