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
            // set validation error messages to flash session
            $this->session->setFlashData('errors', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->to('/admin/kategori-produk/membuat')->withInput();
        }

        helper('generate_uuid');

        $productCategoryName = $this->request->getPost('product_category_name', FILTER_SANITIZE_STRING);
        $createdAt = date('Y-m-d H:i:s');
        /**
         * in production and development,
         * if insert success, function insert() will be return id from new item.
         * in production, if fail will be return false
         */
        $insertProductCategory = $this->productCategoriesModel->insert([
            'product_category_id' => generate_uuid(),
            'product_category_name' => $productCategoryName,
            'created_at' => $createdAt,
            'edited_at' => $createdAt
        ]);

        // if success create product category
        if ($insertProductCategory == true) {
            return redirect()->to('/admin/kategori-produk');
        }

        // make error message
        $this->openDelimiterMessage = '<div class="alert alert--warning mb-3"><span class="alert__icon"></span><p>';
        $this->closeDelimiterMessage = '</p><a class="alert__close" href="#"></a></div>';
        $this->session->setFlashData('errors', $this->addDelimiterMessages([
            'create_product_category' => 'Kategori produk gagal dibuat. Silahkan coba kembali!'
        ]));
        return redirect()->to('/admin/kategori-produk/membuat');
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
        $productCategoryId = $this->request->getPost('product_category_id', FILTER_SANITIZE_STRING);

        if (!$this->validate([
            'product_category_name' => [
                'label' => 'Nama Kategori',
                'rules' => 'required|max_length[20]'
            ]
        ])) {
            // set validation error messages to flash session
            $this->session->setFlashData('errors', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->to('/admin/kategori-produk/edit/' . $productCategoryId);
        }

        $productCategoryName = $this->request->getPost('product_category_name', FILTER_SANITIZE_STRING);

        /**
         * in production and development,
         * if insert success, function update() will be return true.
         * in production, if fail will be return false
         */
        $updateProductCategory = $this->productCategoriesModel->update($productCategoryId, [
            'product_category_name' => $productCategoryName,
            'edited_at' => date('Y-m-d H:i:s')
        ]);
        
        // if success update product category
        if ($updateProductCategory == true) {
            $message = 'Kategori produk berhasil diedit.';
            $alertType = 'success';
            $flashMessageName = 'success';
        } else {
            $message = 'Kategori produk gagal diedit. Silahkan coba kembali!';
            $alertType = 'warning';
            $flashMessageName = 'errors';
        }

        $this->openDelimiterMessage = "<div class=\"alert alert--$alertType mb-3\"><span class=\"alert__icon\"></span><p>";
        $this->closeDelimiterMessage = '</p><a class="alert__close" href="#"></a></div>';
        $this->session->setFlashData($flashMessageName, $this->addDelimiterMessages([
            'edit_product_category' => $message
        ]));
        return redirect()->to('/admin/kategori-produk/edit/' . $productCategoryId);
    }

    public function delete()
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
