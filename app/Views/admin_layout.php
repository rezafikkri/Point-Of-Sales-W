<!doctype html>
<html data-base-url="<?= base_url() ?>">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= base_url('/dist/css/bootstrap-reboot.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/dist/css/bootstrap-grid.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/dist/css/bootstrap-utilities.min.css') ?>">

    <!-- POSW CSS -->
    <link rel="stylesheet" href="<?= base_url('/dist/css/posw.min.css') ?>">
    <?= $this->renderSection('style') ?>

    <title><?= $title ?></title>
</head>
<body>

<nav class="navbar">
<div class="container-xxl d-flex justify-content-between align-items-center">
    <ul class="navbar__left">
        <li><a href="/admin"><img src="<?= base_url('/dist/images/posw.svg') ?>" alt="posw logo" width="80"></a></li>
    </ul>

    <a class="btn btn--toggler" href="">
        <svg xmlns="http://www.w3.org/2000/svg" width="25" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 11.5A.5.5 0 0 1 3 11h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 7h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 3h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/></svg>
    </a>

    <ul class="navbar__right navbar__right--collapse">
        <li><a href="/admin" class="<?= active_menu(['admin']) ?>">Home</a></li>
        <li><a href="/admin/product-categories" class="<?= active_menu(['product-categories', 'product-category']) ?>">Kategori Produk</a></li>
        <li><a href="/admin/products" class="<?= active_menu(['products', 'product']) ?>">Produk</a></li>
        <li><a href="/admin/transactions" class="<?= active_menu(['transactions']) ?>">Transaksi</a></li>
        <li><a href="/admin/users" class="<?= active_menu(['users', 'user']) ?>">Pengguna</a></li>

        <li class="dropdown"><a href="" class="dropdown-toggle" target=".dropdown-menu"><?= $_SESSION['sign_in_user_full_name']  ?></a>
            <ul class="dropdown-menu dropdown-menu--end d-none">
                <li><a href="/admin/user/edit/<?= $_SESSION['sign_in_user_id'] ?>">Pengaturan</a></li>
                <li>
                    <hr>
                </li>
                <li><a href="/sign_out" class="text-hover-red">Keluar</a></li>
            </ul>
        </li>
    </ul>
</div>
</nav>

<?= $this->renderSection('main') ?>

<footer class="footer">
<div class="container-xl">
    <ul>
        <li>&copy; 2021 <a href="https://rezafikkri.github.io/" target="_blank" rel="noreferrer noopener">Reza Sariful Fikri</a>
    </li>
        <li>
            <a href="https://github.com/rezafikkri/Point-Of-Sales-W/wiki" target="_blank" rel="noreferrer noopener">Bantuan</a>
        </li>
    </ul>
</div>
</footer>

<script src="<?= base_url('dist/js/posw.js') ?>"></script>
<?= $this->renderSection('script') ?>

</body>
</html>
