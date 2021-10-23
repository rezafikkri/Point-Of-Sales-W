<?php

namespace App\Controllers;

use App\Models\ProductCategoriesModel;

class ProductCategories extends BaseController
{
    public function __construct()
    {
        $this->productCategoriesModel = new ProductCategoriesModel();
    }

    public function index()
    {
        helper('active_menu');

        $data['title'] = 'Kategori Produk . POSW';
        $data['page'] = 'kategori-produk';
        $data['productCategories'] = $this->productCategoriesModel->getAll();

        return view('product-categories/product_categories', $data);
    }

    public function create()
    {
        helper(['active_menu', 'form']);

        $data['title'] = 'Membuat Kategori Produk . POSW';
        $data['page'] = 'buat-kategori-produk';

        return view('product-categories/create_product_category', $data);
    }

    public function store()
    {
        if (!$this->validate([
            'product_category_name' => [
                'label' => 'Nama Kategori',
                'rules' => 'required|max_length[20]'
            ]
        ])) {
            // set validation errors message to flash session
            $this->session->setFlashData('errors', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        helper('generate_uuid');

        $productCategoryName = $this->request->getPost('product_category_name', FILTER_SANITIZE_STRING);
        $createdAt = date('Y-m-d H:i:s');
        /**
         * in production and development,
         * if insert success, function insert() will be return id from new item.
         * in production, if fail will be return false
         */
        $this->productCategoriesModel->insert([
            'product_category_id' => generate_uuid(),
            'product_category_name' => $productCategoryName,
            'created_at' => $createdAt,
            'updated_at' => $createdAt
        ]);
        return redirect()->to('/admin/kategori-produk');
    }

    public function updateProductCategory(string $productCategoryId = null)
    {
        $method = $this->request->getMethod();

        // if method = post and error not exists
        if ($method === 'post' && $this->validate([
            'product_category_name' => [
                'label' => 'Nama Kategori Produk',
                'rules' => 'required|max_length[20]',
                'errors' => $this->createIndoErrorMessages([
                    'required',
                    'max_length'
                ])
            ]
        ])) {
            $productCategoryId = $this->request->getPost('product_category_id', FILTER_SANITIZE_STRING);
            $productCategoryName = $this->request->getPost('product_category_name', FILTER_SANITIZE_STRING);

            if ($this->updateProductCategoryInDB($productCategoryId, $productCategoryName)) {
                // make success messages
                $this->session->setFlashData('success_messages', $this->addDelimiterMessages([
                    'update_product_category' => 'Kategori produk telah diperbaharui.'
                ]));
            }
            return redirect()->back();
        }

        // if method = post and error exists
        if ($method === 'post' && $this->validator->hasError('product_category_name')) {
            // set validation error messages to flash session
            $this->session->setFlashData('error_messages', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->back()->withInput();
        }

        $productCategoryId = filter_var($productCategoryId, FILTER_SANITIZE_STRING);

        $data['title'] = 'Perbaharui Kategori Produk . POSW';
        $data['page'] = 'perbaharui_kategori_produk';
        $data['productCategoryId'] = $productCategoryId;
        $data['productCategoryDB'] = $this->model->findProductCategory($productCategoryId);

        return view('product-categories/update_product_category', $data);
    }

    private function updateProductCategoryInDB(string $productCategoryId, string $productCategoryName): bool
    {
        /*
         * in production and development,
         * if insert success, function update() will be return true.
         * in production, if fail will be return false
         */
        return $this->model->update($productCategoryId, [
            'product_category_name' => $productCategoryName,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function removeProductCategoryInDB()
    {
        $product_category_id = $this->request->getPost('product_category_id', FILTER_SANITIZE_STRING);
        if($this->model->removeProductCategory($product_category_id) > 0) {
            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }

        $error_message = 'Gagal menghapus kategori produk, cek apakah masih ada produk yang terhubung! <a href="https://github.com/rezafikkri/Point-Of-Sales-Warung/wiki/Kategori-Produk#gagal-menghapus-kategori" target="_blank" rel="noreferrer noopener">Pelajari lebih lanjut!</a>';
        return json_encode([
            'status' => 'fail',
            'message' => $error_message,
            'csrf_value' => csrf_hash()
        ]);
    }
}
