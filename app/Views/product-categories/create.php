<?= $this->extend('admin_layout') ?>

<?= $this->section('main') ?>
<div class="container-xl">
<header class="header d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start">
    <h4 class="mb-4 mb-sm-0 me-2">Membuat Kategori Produk</h4>
    <a href="/admin/product-categories" class="btn btn--gray-outline text-center">Batal</a>
</header>

<main class="main">
    <div class="row">
    <div class="col-md-8">
        <?= $_SESSION['errors']['create_product_category'] ?? null ?>
        <div class="main__box">
            <?= form_open('/admin/product-category/store') ?>
                <div class="mb-3">
                    <label class="form-label" for="product-category-name">Nama Kategori</label>
                    <input class="form-input" id="product-category-name" type="text" name="product_category_name" value="<?= old('product_category_name') ?>">
                    <?= $_SESSION['errors']['product_category_name'] ?? null ?>
                </div>
               <button class="btn btn--blue" type="submit">Simpan</button>
            </form>
        </div><!-- main__box -->
    </div>
    </div>
</main>
</div><!-- container-xl -->
<?= $this->endSection() ?>
