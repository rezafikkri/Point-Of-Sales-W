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
        $data['productCategories'] = $this->productCategoriesModel->getAll('*');

        return view('product-categories/product_categories', $data);
    }

    public function create()
    {
        helper(['active_menu', 'form']);

        $data['title'] = 'Membuat Kategori Produk . POSW';
        $data['page'] = 'membuat-kategori-produk';

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
            'edited_at' => $createdAt
        ]);
        return redirect()->to('/admin/kategori-produk');
    }

    public function edit(string $productCategoryId)
    {
        helper(['active_menu', 'form']);

        $productCategoryId = filter_var($productCategoryId, FILTER_SANITIZE_STRING);

        $data['title'] = 'Edit Kategori Produk . POSW';
        $data['page'] = 'edit-kategori-produk';
        $data['productCategoryId'] = $productCategoryId;
        $data['productCategoryDB'] = $this->productCategoriesModel->getOne($productCategoryId);

        return view('product-categories/edit_product_category', $data);
    }

    public function update()
    {
        if (!$this->validate([
            'product_category_name' => [
                'label' => 'Nama Kategori',
                'rules' => 'required|max_length[20]'
            ]
        ])) {
            // set validation error messages to flash session
            $this->session->setFlashData('errors', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->back();
        }

        $productCategoryId = $this->request->getPost('product_category_id', FILTER_SANITIZE_STRING);
        $productCategoryName = $this->request->getPost('product_category_name', FILTER_SANITIZE_STRING);

        /**
         * in production and development,
         * if insert success, function update() will be return true.
         * in production, if fail will be return false
         */
        $this->productCategoriesModel->update($productCategoryId, [
            'product_category_name' => $productCategoryName,
            'edited_at' => date('Y-m-d H:i:s')
        ]);

        // make success messages
        $this->openDelimiterMessages = '<div class="alert alert--success mb-3"><span class="alert__icon"></span><p>';
        $this->closeDelimiterMessages = '</p><a class="alert__close" href="#"></a></div>';
        $this->session->setFlashData('success', $this->addDelimiterMessages([
            'update_product_category' => 'Kategori produk telah diperbaharui.'
        ]));

        return redirect()->back();
    }

    public function remove()
    {
        $productCategoryId = $this->request->getPost('product_category_id', FILTER_SANITIZE_STRING);
        /**
         * in production and development,
         * if insert success, function update() will be return true.
         * in production, if fail will be return false
         */
        if ($this->productCategoriesModel->delete($productCategoryId)) {
            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }

        $errorMessage = 'Gagal menghapus kategori produk, cek apakah masih ada data produk yang terhubung!';
        return json_encode([
            'status' => 'fail',
            'message' => $errorMessage,
            'csrf_value' => csrf_hash()
        ]);
    }
}
