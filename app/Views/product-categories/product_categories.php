<?php

use CodeIgniter\I18n\Time;

$this->extend('admin_layout');

?>

<?= $this->section('main') ?>
<div class="container-xl">
<header class="header d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start">
    <h4 class="mb-4 mb-sm-0 me-2">Kategori Produk</h4>
    <a href="/admin/product-category/create" class="btn btn--blue text-center">Membuat Kategori</a>
</header>

<main class="main">
    <div class="main__box position-relative">

        <div class="table-responsive" id="table" data-csrf-name="<?= csrf_token() ?>" data-csrf-value="<?= csrf_hash() ?>">
        <table class="table table--auto-striped min-width-711">
            <thead>
                <tr>
                    <th colspan="2" class="text-center">Aksi</th>
                    <th>Nama Kategori</th>
                    <th width="160">Dibuat</th>
                    <th width="160">Diedit</th>
                </tr>
            </thead>
            <tbody>
            <?php
                // if exists product categories
                if (count($productCategories) > 0) :
                foreach ($productCategories as $pc) :
                    $createdAt = Time::createFromFormat('Y-m-d H:i:s', $pc['created_at']);
                    $editedAt = Time::createFromFormat('Y-m-d H:i:s', $pc['edited_at']);
            ?>
                <tr>
                    <td width="10"><a href="#" data-product-category-id="<?= $pc['product_category_id'] ?>" title="Menghapus kategori produk" class="text-hover-red" id="delete"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"/><path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"/></svg></a></td>
                    <td width="10"><a href="/admin/product-category/edit/<?= $pc['product_category_id'] ?>" title="Edit kategori produk"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"/></svg></a></td>

                    <td><?= $pc['product_category_name'] ?></td>
                    <td><?= $createdAt->toLocalizedString('dd MMM yyyy HH:mm') ?></td>
                    <td><?= $editedAt->toLocalizedString('dd MMM yyyy HH:mm') ?></td>
                </tr>
            <?php endforeach; else : ?>
                <tr>
                    <td colspan="5">Kategori produk tidak ada.</td>
                </tr>
            <?php endif ?>
            </tbody>
        </table>
        </div><!-- table-responsive -->

        <div class="loading-bg position-absolute top-0 end-0 bottom-0 start-0 d-flex justify-content-center d-none" id="loading">
            <div class="loading mt-5">
                <div></div>
            </div>
        </div>

    </div><!-- main__box -->
</main>
</div><!-- container-xl -->
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script type="module" src="<?= base_url('dist/js/product-categories/product_categories.js') ?>"></script>
<?= $this->endSection() ?>
