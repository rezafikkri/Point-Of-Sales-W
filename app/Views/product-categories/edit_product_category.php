<?= $this->extend('admin_layout') ?>

<?= $this->section('main') ?>
<div class="container-xl">
<header class="header d-flex flex-column flex-sm-row justify-content-sm-between align-items-start">
    <h4 class="mb-4 mb-sm-0 me-2">Edit Kategori Produk</h4>
    <a href="/admin/kategori-produk" class="btn btn--gray-outline">Kembali</a>
</header>

<main class="main">
    <div class="row">
    <div class="col-md-8">
        <?= $_SESSION['success']['edit_product_category'] ?? null ?>
        <?= $_SESSION['errors']['edit_product_category'] ?? null ?>
        <div class="main__box">
            <?= form_open('/admin/kategori-produk/memperbaharui') ?>
                <input type="hidden" value="<?= $productCategoryId ?>" name="product_category_id">
                <div class="mb-3">
                    <label class="form-label" for="product_category_name">Nama Kategori</label>
                    <input class="form-input" id="product_category_name" type="text"
                            name="product_category_name" value="<?= $productCategoryDB['product_category_name'] ?? null ?>">
                    <?= $_SESSION['errors']['product_category_name'] ?? null ?>
                </div>
               <button class="btn btn--blue" type="submit">Simpan</button>
            </form>
        </div><!-- main__box -->
    </div>
    </div>
</main>
</div>
<?= $this->endSection() ?>
