<?= $this->extend('admin_layout'); ?>

<?= $this->section('main'); ?>
<div class="container-xl">
<header class="header d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start">
    <h4 class="mb-4 mb-sm-0 me-2">Edit Produk</h4>
    <a href="/admin/produk" class="btn btn--gray-outline text-center">Kembali</a>
</header>

<main class="main mb-5" data-csrf-name="<?= csrf_token(); ?>">
    <div class="row">
    <div class="col-md-8">
        <?= $_SESSION['errors']['edit_product'] ?? null; ?>
        <?= $_SESSION['success']['edit_product'] ?? null; ?>
        <div class="main__box position-relative">
            <?= form_open_multipart('/admin/produk/memperbaharui'); ?>
                <input type="hidden" name="product_id" value="<?= $productId; ?>">
                <div class="mb-3">
                    <label class="form-label" for="product-category">Kategori Produk</label>
                    <select class="form-select" name="product_category" id="product-category">
                    <?php foreach ($productCategories as $cp) : ?>
                        <option value="<?= $cp['product_category_id']; ?>"
                        <?= $cp['product_category_id'] == ($product['product_category_id'] ?? null) ? 'selected' : ''; ?>>
                            <?= $cp['product_category_name']; ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                    <?= $_SESSION['errors']['product_category'] ?? null; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="product-name">Nama Produk</label>
                    <input class="form-input" type="text" name="product_name" value="<?= $product['product_name'] ?? null; ?>" id="product-name">
                    <?= $_SESSION['errors']['product_name'] ?? null; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="product-photo">Foto Produk</label>
                    <input type="hidden" value="<?= $product['product_photo'] ?>" name="old_product_photo">
                    <div class="form-file">
                        <input type="file" name="product_photo" id="product-photo" accept="image/jpeg">
                        <label for="product-photo">Pilih file...</label>
                    </div>
                    <?= $_SESSION['errors']['product_photo'] ?? '<small class="form-message form-message--info">
                    Pilih file jika ingin perbaharui foto produk, ukuran file maksimal 1 MB dan ekstensi file harus .jpg atau .jpeg.</small>'; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="product-status">Status Produk</label>
                    <select class="form-select" name="product_status" id="product-status">
                    <?php
                        $productStatuses = ['ada' => 'Ada', 'tidak_ada' => 'Tidak Ada'];
                        foreach ($productStatuses as $key => $value) :
                    ?>
                        <option value="<?= $key; ?>"
                        <?= $key == ($product['product_status'] ?? null) ? 'selected' : ''; ?>>
                            <?= $value; ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                    <?= $_SESSION['errors']['product_status'] ?? null; ?>
                </div>
                <div id="magnitude-price">
                    <label class="form-label">Harga Produk</label>
                <?php
                    $countProductPrice = count($productPrices);
                    $countOldProductMagnitude = count(old('product_magnitudes', []));

                    if ($countProductPrice > 0) :
                    for ($i = 0; $i < $countProductPrice; $i++) :

                    // if not first loop
                    if ($i != 0) :
                ?>
                    <div class="mt-3">
                <?php else: ?>
                    <div>
                <?php endif; ?>
                        <input type="hidden" name="product_price_ids[]" value="<?= $productPrices[$i]['product_price_id']; ?>">
                        <div class="input-group">
                            <input class="form-input" type="text" placeholder="Besaran..."
                            name="product_magnitudes[]" value="<?= $productPrices[$i]['product_magnitude'] ?? null; ?>">
                            <input class="form-input" type="number" placeholder="Harga..."
                            name="product_prices[]" value="<?= $productPrices[$i]['product_price'] ?? null; ?>">
                    <?php
                        // if not first looping
                        if ($i > 0) : ?>
                            <a class="btn btn--gray-outline" data-product-price-id="<?= $productPrices[$i]['product_price_id']; ?>"
                               id="remove-form-input-magnitude-price" href="">Hapus</a>
                    <?php endif; ?>
                        </div>
                        <small class="form-message form-message--danger"><?= $_SESSION['errors']['product_magnitudes'][$i] ?? null; ?></small>
                        <small class="form-message form-message--danger"><?= $_SESSION['errors']['product_prices'][$i] ?? null; ?></small>
                    </div><!-- mb-3 -->

                <?php
                    endfor; endif;

                    // if product magnitude old > product price
                    if ($countOldProductMagnitude > $countProductPrice) :
                    for ($j = $i; $j < $countOldProductMagnitude; $j++) :
                ?>
                    <div class="mt-3">
                        <div class="input-group">
                            <input class="form-input" type="text" placeholder="Besaran..."
                            name="product_magnitudes[]" value="<?= old('product_magnitudes')[$j] ?? null; ?>"s>
                            <input class="form-input" type="number" placeholder="Harga..."
                            name="product_prices[]" value="<?= old('product_prices')[$j] ?? null; ?>">
                            <a class="btn btn--gray-outline" id="remove-form-input-magnitude-price" href="">Hapus</a>
                        </div>
                        <small class="form-message form-message--danger"><?= $_SESSION['errors']['product_magnitudes'][$j] ?? null; ?></small>
                        <small class="form-message form-message--danger"><?= $_SESSION['errors']['product_prices'][$j] ?? null; ?></small>
                    </div><!-- mb-3 -->
                <?php endfor; endif; ?>
                </div><!-- magnitude-price -->
                <small class="form-message form-message--info
                mb-3">Pelajari lebih lanjut <a href="https://github.com/rezafikkri/Point-Of-Sales-W/wiki/Produk#harga-produk"
                target="_blank" rel="noreferrer noopener">Harga Produk.</a></small>

                <a class="btn btn--gray-outline me-2" id="add-form-input-magnitude-price" href="">
                Tambah Form Harga Produk</a><button class="btn btn--blue" type="submit">Simpan</button>
            </form>

            <div class="loading-bg position-absolute top-0 end-0 bottom-0 start-0 d-flex justify-content-center align-items-center d-none" id="loading">
                <div class="loading">
                    <div></div>
                </div>
            </div>
        </div><!-- main__box -->
    </div>
    </div>
</div>
</main>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script type="module" src="<?= base_url('dist/js/edit_product.js'); ?>"></script>
<?= $this->endSection(); ?>
