<?php

namespace App\Controllers;

use App\Models\{ProductCategoriesModel, ProductsModel, ProductPricesModel};
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\I18n\Time;

class Products extends BaseController
{
    private const PRODUCT_LIMIT = 5;

    public function __construct()
    {
        $this->productCategoriesModel = new ProductCategoriesModel();
        $this->productsModel = new ProductsModel();
        $this->productPricesModel = new ProductPricesModel();        
    }

    public function index()
    {
        helper('active_menu');

        $data['title'] = 'Produk . POSW';
        $data['page'] = 'produk';

        $data['totalProduct'] = $this->productsModel->getTotal();
        $data['products'] = $this->productsModel->getAll(static::PRODUCT_LIMIT);
        $data['productLimit'] = static::PRODUCT_LIMIT;

        return view('products/products', $data);
    }

    public function showDetails(string $productId)
    {
        $productId = filter_var($productId, FILTER_SANITIZE_STRING);
        $productPrices = $this->productPricesModel->getAll($productId, 'product_price, product_magnitude');
        $productPhoto = $this->productsModel->getOne($productId, 'product_photo')['product_photo'] ?? '';

        return json_encode([
            'product_prices' => $productPrices,
            'product_photo' => $productPhoto,
            'csrf_value' => csrf_hash()
        ]);
    }

    public function create()
    {
        helper(['active_menu', 'form']);

        $data['productCategories'] = $this->productCategoriesModel->getAll('product_category_id, product_category_name');
        $data['title'] = 'Membuat Produk . POSW';

        return view('products/create_product', $data);
    }

    private function generateProductPriceInsertBatchData(string $productId, array $productMagnitudes, array $productPrices): array
    {
        $insertData = [];
        $countProductMagnitude = count($productMagnitudes);
        for ($i = 0; $i < $countProductMagnitude; $i++) {
            $insertData[] = [
                'product_price_id' => generate_uuid(),
                'product_id' => $productId,
                'product_magnitude' => filter_var($productMagnitudes[$i], FILTER_SANITIZE_STRING),
                'product_price' => filter_var($productPrices[$i], FILTER_SANITIZE_STRING)
            ];
        }
        return $insertData;
    }

    public function store()
    {
        if (!$this->validate([
            'product_category' => [
                'label' => 'Kategori produk',
                'rules' => 'required',
            ],
            'product_name' => [
                'label' => 'Nama produk',
                'rules' => 'required|max_length[50]|is_unique[products.product_name]',
            ],
            'product_status' => [
                'label' => 'Status Produk',
                'rules' => 'in_list[ada,tidak_ada]',
            ],
            'product_photo' => 'product_photo',
            'product_magnitudes' => 'product_magnitude',
            'product_prices' => 'product_price'
        ])) {
            // set validation errors message to flash session
            $this->ignoreMessages = ['product_magnitudes', 'product_prices'];
            $this->session->setFlashData('errors', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->to('/admin/produk/membuat')->withInput();
        }

        helper('generate_uuid');

        // genearate new random name for product photo
        $productPhotoFile = $this->request->getFile('product_photo');
        $productPhotoName = $productPhotoFile->getRandomName();

        $createdAt = date('Y-m-d H:i:s');
        $productCategoryId = $this->request->getPost('product_category', FILTER_SANITIZE_STRING);
        $productName = $this->request->getPost('product_name', FILTER_SANITIZE_STRING);
        $productStatus = $this->request->getPost('product_status', FILTER_SANITIZE_STRING);

        $this->productsModel->transStart();

        // insert product
        $productId = $this->productsModel->insert([
            'product_id' => generate_uuid(),
            'product_category_id' => $productCategoryId,
            'product_name' => $productName,
            'product_photo' => $productPhotoName,
            'product_status' => $productStatus,
            'created_at' => $createdAt,
            'edited_at' => $createdAt
        ]);

        /**
         * in production and development,
         * if insert success, function insertBatch() will be return number of row inserted.
         * in production, if fail will be show oops page
         */
        $productPriceInsertBatchData = $this->generateProductPriceInsertBatchData(
            $productId,
            $this->request->getPost('product_magnitudes'),
            $this->request->getPost('product_prices')
        );
        $insertProductPriceBatch = $this->productPricesModel->insertBatch($productPriceInsertBatchData);

        $this->productsModel->transComplete();

        // if success create product 
        if ($productId == true && $insertProductPriceBatch == true) {
            // move product photo
            $productPhotoFile->move('dist/images/product-photos', $productPhotoName);
            return redirect()->to('/admin/produk');
        } else {
            // make error message
            $this->openDelimiterMessage = '<div class="alert alert--warning mb-3"><span class="alert__icon"></span><p>';
            $this->closeDelimiterMessage = '</p><a class="alert__close" href="#"></a></div>';
            $this->session->setFlashData('errors', $this->addDelimiterMessages([
                'create_product' => 'Produk gagal dibuat. Silahkan coba kembali!'
            ]));
            return redirect()->to('/admin/produk/membuat')->withInput();
        }
    }

    public function search(string $keyword)
    {
        $keyword = filter_var($keyword, FILTER_SANITIZE_STRING);

        // get total product search
        $totalProduct = $this->productsModel->getTotalSearch($keyword);
        $products = $this->productsModel->search(static::PRODUCT_LIMIT, $keyword);

        // convert timestamp
        foreach ($products as $key => $value) {
            $createdAt = Time::createFromFormat('Y-m-d H:i:s', $value['created_at']);
            $editedAt = Time::createFromFormat('Y-m-d H:i:s', $value['edited_at']);

            $products[$key]['created_at'] = $createdAt->toLocalizedString('dd MMM yyyy HH:mm');
            $products[$key]['indo_edited_at'] = $editedAt->toLocalizedString('dd MMM yyyy HH:mm');
        }

        return json_encode([
            'products' => $products,
            'total_product' => $totalProduct,
            'product_limit' => static::PRODUCT_LIMIT,
            'csrf_value' => csrf_hash()
        ]);
    }

    public function edit(string $productId)
    {
        helper(['active_menu', 'form']);

        $productId = filter_var($productId, FILTER_SANITIZE_STRING);

        $data['title'] = 'Edit Produk . POSW';
        $data['page'] = 'edit-produk';
        $data['productId'] = $productId;
        $data['productCategories'] = $this->productCategoriesModel->getAll('product_category_id, product_category_name');
        $data['product'] = $this->productsModel->getOne($productId);
        $data['productPrices'] = $this->productPricesModel->getAll($productId, 'product_price_id, product_magnitude, product_price');

        return view('products/edit_product', $data);
    }

    private function generateProductPriceUpsertBatchData(
        string $productId,
        array $productPriceIds,
        array $productMagnitudes,
        array $productPrices
    ): array {
        $updateData = [];
        $countProductMagnitude = count($productMagnitudes);
        for ($i = 0; $i < $countProductMagnitude; $i++) {
            // if product price id exist
            if (isset($productPriceIds[$i])) {
                $productPriceId = filter_var($productPriceIds[$i], FILTER_SANITIZE_STRING);
            } else {
                $productPriceId = generate_uuid();
            }

            $updateData[] = [
                'product_price_id' => $productPriceId,
                'product_id' => $productId,
                'product_magnitude' => filter_var($productMagnitudes[$i], FILTER_SANITIZE_STRING),
                'product_price' => filter_var($productPrices[$i], FILTER_SANITIZE_STRING)
            ];
        }
        return $updateData;
    }

    public function update()
    {
        $productId = $this->request->getPost('product_id', FILTER_SANITIZE_STRING);

        // generate validation data
        $validationData = [
            'product_category' => [
                'label' => 'Kategori produk',
                'rules' => 'required',
            ],
            'product_name' => [
                'label' => 'Nama produk',
                'rules' => "required|max_length[50]|is_unique[products.product_name,product_id,$productId]",
            ],
            'product_status' => [
                'label' => 'Status Produk',
                'rules' => 'in_list[ada,tidak_ada]',
            ],
            'product_magnitudes' => 'product_magnitude',
            'product_prices' => 'product_price'
        ];

        $productPhotoFile = $this->request->getFile('product_photo');
        // if product photo exist
        if ($productPhotoFile->getError() != 4) {
            $validationData['product_photo'] = 'product_photo';
        }

        // validate data
        if (!$this->validate($validationData)) {
            // set validation errors message to flash session
            $this->ignoreMessages = ['product_magnitudes', 'product_prices'];
            $this->session->setFlashData('errors', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->to('/admin/produk/edit/' . $productId)->withInput();
        }

        // generate product update data
        $productUpdateData = [
            'product_category_id' => $this->request->getPost('product_category', FILTER_SANITIZE_STRING),
            'product_name' => $this->request->getPost('product_name', FILTER_SANITIZE_STRING),
            'product_status' => $this->request->getPost('product_status', FILTER_SANITIZE_STRING),
            'edited_at' => date('Y-m-d H:i:s')
        ];

        // if product photo exist
        if ($productPhotoFile->getError() != 4) {
            $productPhotoName = $productPhotoFile->getRandomName();
            // add product photo to product update data array
            $productUpdateData['product_photo'] = $productPhotoName;
        }

        helper('generate_uuid');
        
        // generate product price upsert batch data
        $productPriceIds = $this->request->getPost('product_price_ids');
        $productPriceUpsertBatchData = $this->generateProductPriceUpsertBatchData(
            $productId,
            $productPriceIds,
            $this->request->getPost('product_magnitudes'),
            $this->request->getPost('product_prices')
        );
        
        $this->productsModel->transStart();

        // update product
        $updateProduct = $this->productsModel->update($productId, $productUpdateData);
        // update insert product price
        $upsertProductPriceBatch = $this->productPricesModel->upsertBatch($productPriceUpsertBatchData);

        $this->productsModel->transComplete();

        // if success edit product
        if ($updateProduct == true && $upsertProductPriceBatch == true) {
            // if product photo exist
            if ($productPhotoFile->getError() != 4) {
                // move product photo
                $productPhotoFile->move('dist/images/product-photos', $productPhotoName);
                // remove old product photo
                $oldProductPhoto = $this->request->getPost('old_product_photo', FILTER_SANITIZE_STRING);
                if (file_exists('dist/images/product-photos/'.$oldProductPhoto)) {
                    unlink('dist/images/product-photos/'.$oldProductPhoto);
                }
            }

            $message = 'Produk berhasil diedit.';
            $alertType = 'success';
            $flashMessageName = 'success';
        } else {
            $message = 'Produk gagal diedit. Silahkan coba kembali!';
            $alertType = 'warning';
            $flashMessageName = 'errors';
        }

        $this->openDelimiterMessage = "<div class=\"alert alert--$alertType mb-3\"><span class=\"alert__icon\"></span><p>";
        $this->closeDelimiterMessage = '</p><a class="alert__close" href="#"></a></div>';
        $this->session->setFlashData($flashMessageName, $this->addDelimiterMessages([
            'edit_product' => $message
        ]));
        return redirect()->to('/admin/produk/edit/' . $productId);
    }

    public function removeProductPrice()
    {
        $productPriceId = $this->request->getPost('product_price_id', FILTER_SANITIZE_STRING);
        if ($this->productPricesModel->delete($productPriceId)) {
            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }

        $errorMessage = 'Gagal menghapus harga produk, cek apakah masih ada data transaksi yang terhubung!';
        return json_encode([
            'status' => 'fail',
            'message' => $errorMessage,
            'csrf_value' => csrf_hash()
        ]);
    }

    public function removeProductsInDB()
    {
        $product_ids = explode(',',$this->request->getPost('product_ids', FILTER_SANITIZE_STRING));
        $photo_products = $this->productsModel->findProducts($product_ids, 'foto_produk');
        // remove product
        if ($this->productsModel->removeProducts($product_ids) > 0) {
            // remove photo product
            foreach($photo_products as $p) {
                if (file_exists('dist/images/product_photo/'.$p['foto_produk'])) {
                    unlink('dist/images/product_photo/'.$p['foto_produk']);
                }
            }

            $count_product_id = count($product_ids);
            $smallest_create_time = $this->request->getPost('smallest_create_time');
            $keyword = $this->request->getPost('keyword', FILTER_SANITIZE_STRING);

            // if keyword !== null
            if ($keyword !== null) {
                // product total
                $product_total = $this->productsModel->countAllProductSearch($keyword);
                // get longer product
                $longer_products = $this->productsModel->getLongerProductSearches($count_product_id, $smallest_create_time, $keyword);

            } else {
                // product total
                $product_total = $this->productsModel->countAllProduct();
                // get longer product
                $longer_products = $this->productsModel->getLongerProducts($count_product_id, $smallest_create_time);
            }

            // add array indo create time to longer products array
            $count_longer_products = count($longer_products);
            for ($i = 0; $i < $count_longer_products; $i++) {
                $longer_products[$i]['indo_create_time'] = $this->indo_time->toIndoLocalizedString($longer_products[$i]['waktu_buat']);
            }

            return json_encode([
                'status' => 'success',
                'longer_products' => $longer_products,
                'product_total' => $product_total,
                'product_limit' => static::PRODUCT_LIMIT,
                'csrf_value' => csrf_hash()
            ]);
        }

        $error_message = 'Gagal menghapus produk, cek apakah masih ada transaksi yang terhubung! <a href="https://github.com/rezafikkri/Point-Of-Sales-Warung/wiki/Produk#gagal-menghapus-produk" target="_blank" rel="noreferrer noopener">Pelajari lebih lanjut!</a>';
        return json_encode([
            'status' => 'fail',
            'message' => $error_message,
            'csrf_value'=>csrf_hash()
        ]);
    }
}
