<?= $this->extend('admin_layout'); ?>

<?= $this->section('main'); ?>
<div class="container-xl">
<header class="header d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start">
    <h4 class="mb-4 mb-sm-0 me-2">Membuat Produk</h4>
    <a href="/admin/produk" class="btn btn--gray-outline text-center">Batal</a>
</header>

<main class="main mb-5">
    <div class="row">
    <div class="col-md-8">
        <?= $_SESSION['errors']['create_product'] ?? null; ?>
        <div class="main__box">
            <?= form_open_multipart('/admin/produk/menyimpan'); ?>
                <div class="mb-3">
                    <label class="form-label" for="category-product">Kategori Produk</label>
                    <select class="form-select" name="product_category" id="category-product">
                    <?php foreach ($productCategories as $pc) : ?>
                        <option value="<?= $pc['product_category_id']; ?>"
                        <?= $pc['product_category_id'] == old('product_category') ? 'selected' : ''; ?>>
                            <?= $pc['product_category_name']; ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                    <?= $_SESSION['errors']['product_category'] ?? null; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="product_name">Nama Produk</label>
                    <input class="form-input" type="text" name="product_name" value="<?= old('product_name'); ?>">
                    <?= $_SESSION['errors']['product_name'] ?? null; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="product-photo">Foto Produk</label>
                    <div class="form-file">
                        <input type="file" name="product_photo" id="product-photo" accept="image/jpeg">
                        <label for="product-photo">Pilih file...</label>
                    </div>
                    <?= $_SESSION['errors']['product_photo'] ?? '<small class="form-message form-message--info">
                    Ukuran file maksimal 1 MB dan ekstensi file harus .jpg atau .jpeg.</small>'; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="product-status">Status Produk</label>
                    <select class="form-select" name="product_status" id="product-status">
                    <?php
                        $productStatuses = ['ada' => 'Ada', 'tidak_ada' => 'Tidak Ada'];
                        foreach ($productStatuses as $key => $value) :
                    ?>
                        <option value="<?= $key; ?>"
                        <?= $key === old('product_status') ? 'selected' : ''; ?>>
                            <?= $value; ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                    <?= $_SESSION['errors']['product_status'] ?? null; ?>
                </div>
                <div id="magnitude-price">
                    <label class="form-label">Harga Produk</label>
                <?php
                    $countOldProductMagnitude = count(old('product_magnitudes', []));

                    // if not exists old product magnitude
                    if ($countOldProductMagnitude <= 0) $countOldProductMagnitude = 1;
                    for ($i = 0; $i < $countOldProductMagnitude; $i++) :

                    // if not first looping
                    if ($i != 0) :
               ?>
                    <div class="mt-3">
                <?php else : ?>
                    <div>
                <?php endif; ?>
                        <div class="input-group">
                            <input class="form-input" type="text" placeholder="Besaran..."
                            name="product_magnitudes[]" value="<?= old('product_magnitudes')[$i] ?? null; ?>">
                            <input class="form-input" type="number" placeholder="Harga..."
                            name="product_prices[]" value="<?= old('product_prices')[$i] ?? null; ?>">
                    <?php
                        // if not first looping
                        if ($i != 0) :
                    ?>
                           <a class="btn btn--gray-outline" id="remove-form-input-magnitude-price" href="">Hapus</a>
                    <?php endif; ?>
                        </div>
                        <small class="form-message form-message--danger"><?= $_SESSION['errors']['product_magnitudes'][$i] ?? null; ?></small>
                        <small class="form-message form-message--danger"><?= $_SESSION['errors']['product_prices'][$i] ?? null; ?></small>
                    </div>

                <?php endfor; ?>
                </div><!-- magnitude-price -->
                <small class="form-message form-message--info
                mb-3">Pelajari lebih lanjut <a href="https://github.com/rezafikkri/Point-Of-Sales-W/wiki/Produk#harga-produk"
                target="_blank" rel="noreferrer noopener">Harga Produk</a>.</small>

                <a class="btn btn--gray-outline me-2" id="add-form-input-magnitude-price" href="">
                Tambah Form Harga Produk</a><button class="btn btn--blue" type="submit">Simpan</button>
            </form>
        </div><!-- main__box -->
    </div>
    </div>
</main>
</div>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script type="module" src="<?= base_url('dist/js/create_product.js'); ?>"></script>
<?= $this->endSection(); ?>
