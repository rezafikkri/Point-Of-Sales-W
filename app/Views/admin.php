<?= $this->extend('admin_layout') ?>

<?= $this->section('main') ?>
<header class="header">
<div class="container-xl">
    <h4 class="mb-0">Hi</h4>
    <h5>, <?= $_SESSION['sign_in_user_full_name'] ?></h5>
</div><!-- container-xl -->
</header>

<main class="main mb-4">
<div class="container-xl">
<div class="row">
    <div class="col-lg-7 pe-lg-1 mb-3 mb-lg-0">
    <div class="chart">
        <div class="chart__header">
            <h5>Transaksi</h5>
            <p>Dalam 2 bulan terakhir</p>
        </div>
        <div class="chart__body" id="chart-body"></div>
        <div class="d-flex justify-content-center" id="loading">
            <div class="loading"><div></div></div>
        </div>
    </div><!-- chart -->
    </div><!-- col-lg-7 -->

    <div class="col-lg-5">
    <div class="row mb-3">

        <div class="col-sm-4 col-lg-6 pe-sm-1 mb-3">
        <div class="info-box info-box--blue">
            <div class="info-box__data">
            <h4 class="mb-2"><?= number_to_amount($totalTransaction, 1) ?></h4>
                <p class="mb-0">Total Transaksi</p>
            </div>
            <div class="info-box__icon">
               <svg xmlns="http://www.w3.org/2000/svg" width="40" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zm0 3v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7H0zm3 2h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1z"/></svg>
            </div>
        </div><!-- infor-box__item -->
        </div>

        <div class="col-sm-4 col-lg-6 pe-sm-1 pe-lg-3 mb-3">
        <div class="info-box info-box--sky-blue">
            <div class="info-box__data">
                <h4 class="mb-0 mb-2"><?= $totalUser ?></h4>
                <p class="mb-0">Total Pengguna</p>
            </div>
            <div class="info-box__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" fill="currentColor" viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
            </div>
        </div><!-- infor-box__item -->
        </div>

        <div class="col-sm-4 col-lg-6">
        <div class="info-box info-box--green">
            <div class="info-box__data">
                <h4 class="mb-2"><?= number_to_amount($totalProduct, 1) ?></h4>
                <p class="mb-0">Total Produk</p>
            </div>
            <div class="info-box__icon">
               <svg xmlns="http://www.w3.org/2000/svg" width="35" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5z"/></svg>
            </div>
        </div><!-- info-box -->
        </div>

    </div><!-- row -->
    </div><!-- col-lg-5 -->

</div><!-- row -->
</div><!-- container-xl -->
</main>
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script type="module" src="<?= base_url('dist/js/admin.js') ?>"></script>
<?= $this->endSection() ?>
