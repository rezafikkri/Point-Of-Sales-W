<?php

use CodeIgniter\I18n\Time;

$this->extend('admin_layout');

?>

<?= $this->section('main') ?>
<div class="container-xl">
<header class="header d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start">
    <h4 class="mb-4 mb-sm-0 me-2">Transaksi</h4>

    <div class="d-flex flex-column-reverse flex-sm-row justify-content-start justify-content-sm-end align-items-sm-start flex-fill">
        <div class="input-group me-0 me-sm-2 mb-3 mb-sm-0">
           <input type="text" name="date_range" placeholder="Pilih Rentang Waktu...">
           <a class="btn btn--blue" href="#" id="search-transaction">
               <svg xmlns="http://www.w3.org/2000/svg" width="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10.442 10.442a1 1 0 0 1 1.415 0l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1 1 0 0 1 0-1.415z"/><path fill-rule="evenodd" d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"/></svg>
           </a>
       </div><!-- input-group -->

        <div class="position-relative d-inline-block">
            <a href="#" id="export-transaction-excel"  class="btn btn--blue" title="Ekspor ke excel"><svg xmlns="http://www.w3.org/2000/svg" width="17" fill="currentColor" viewBox="0 0 16 16"><path d="M6 12v-2h3v2H6z"/><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0zM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1zM3 9h10v1h-3v2h3v1h-3v2H9v-2H6v2H5v-2H3v-1h2v-2H3V9z"/></svg></a>

            <div class="loading-bg rounded position-absolute top-0 bottom-0 end-0 start-0 d-flex justify-content-center align-items-center d-none">
                <div class="loading">
                    <div></div>
                </div>
            </div>
        </div><!-- position relative -->
    </div><!-- d-flex -->
</header>

<main class="main mb-5">
    <div class="main__box position-relative">

        <div class="d-flex justify-content-between align-items-center mb-3" id="main-header">
            <div class="flex-fill">
                <a href="#" id="remove-transaction" class="btn btn--red-outline" title="Hapus transaksi"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"/><path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"/></svg></a>
            </div>
            <div>
            <?php
                // if exists transaction
                $countTransaction = count($transactions);
                if ($countTransaction > 0) :
            ?>
            <span class="text-muted me-1" id="result-status">1 - <?= $countTransaction ?> dari <?= $totalTransaction ?> Total transaksi</span>
            <?php else : ?>
                <span class="text-muted me-1" id="result-status">0 Total transaksi</span>
            <?php endif ?>
            </div>
        </div><!-- d-flex -->

        <div class="table-responsive" id="table" data-csrf-name="<?= csrf_token() ?>" data-csrf-value="<?= csrf_hash() ?>">
        <table class="table table--manual-striped min-width-711">
            <thead>
                <tr>
                    <th class="text-center" colspan="2">Aksi</th>
                    <th>Total Produk</th>
                    <th>Total Bayaran</th>
                    <th width="100">Status</th>
                    <th>Kasir</th>
                    <th width="160">Dibuat</th>
                    <th width="160">Diedit</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $fmt = new \NumberFormatter('id_ID', \NumberFormatter::CURRENCY);
                $fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);

                // if exists transaction
                if ($countTransaction > 0) :
                    $i = 1;
                foreach($transactions as $t) :
                    $createdAt = Time::createFromFormat('Y-m-d H:i:s', $t['created_at']);
                    $editedAt = Time::createFromFormat('Y-m-d H:i:s', $t['edited_at']);

                // if $i is prime number
                if (($i%2) != 0) :
            ?>
                <tr class="table__row-odd">
            <?php else: ?>
                <tr>
            <?php endif ?>
                    <td width="10">
                    <?php if (is_allowed_delete_transaction($t['edited_at'])) : ?>
                        <div class="form-check">
                            <input type="checkbox" name="transaction_id" data-edited-at="<?= $t['edited_at'] ?>"
                            class="form-check-input" value="<?= $t['transaction_id'] ?>">
                        </div>
                    <?php endif ?>
                    </td>
                        <td width="10"><a href="#" id="show-transaction-detail" data-transaction-id="<?= $t['transaction_id'] ?>" title="Lihat detail transaksi"><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg></a></td>

                    <td><?= $t['product_total'] ?? 0 ?></td>
                    <td><?= $fmt->formatCurrency($t['payment_total'] ?? 0, 'IDR') ?></td>

                    <?php if ($t['transaction_status'] == 'selesai') : ?>
                        <td><span class="text-green">Selesai</span></td>
                    <?php else : ?>
                        <td><span class="text-red">Berlangsung</span></td>
                    <?php endif ?>

                    <td><?= $t['full_name'] ?></td>
                    <td><?= $createdAt->toLocalizedString('dd MMM yyyy HH:mm') ?></td>
                    <td><?= $editedAt->toLocalizedString('dd MMM yyyy HH:mm') ?></td>
                </tr>
            <?php $i++; endforeach; else : ?>
                <tr class="table__row-odd">
                    <td colspan="6">Transaksi tidak ada.</td>
                </tr>
            <?php endif ?>
            </tbody>
        </table>
        </div><!-- table-reponsive -->
    <?php
        // if product show total = transaction limit
        if ($countTransaction == $transactionLimit) :
    ?>
        <span id="limit-message" class="text-muted d-block mt-3">Hanya <?= $transactionLimit ?> Transaksi terbaru yang ditampilkan, Pakai fitur
        <i>Pencarian</i> untuk hasil lebih spesifik!</span>
    <?php endif ?>

        <div class="loading-bg position-absolute top-0 end-0 bottom-0 start-0 d-flex justify-content-center d-none">
            <div class="loading mt-5">
                <div></div>
            </div>
        </div>

    </div><!-- main__box -->
</main>
</div><!-- container-xl -->

<div class="modal modal--red">
    <div class="modal__content">
        <a class="btn btn--light" id="btn-close" href=""><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg></a>
        <div class="modal__icon mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" fill="currentColor" viewBox="0 0 16 16"><path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.496 6.033a.237.237 0 0 1-.24-.247C5.35 4.091 6.737 3.5 8.005 3.5c1.396 0 2.672.73 2.672 2.24 0 1.08-.635 1.594-1.244 2.057-.737.559-1.01.768-1.01 1.486v.105a.25.25 0 0 1-.25.25h-.81a.25.25 0 0 1-.25-.246l-.004-.217c-.038-.927.495-1.498 1.168-1.987.59-.444.965-.736.965-1.371 0-.825-.628-1.168-1.314-1.168-.803 0-1.253.478-1.342 1.134-.018.137-.128.25-.266.25h-.825zm2.325 6.443c-.584 0-1.009-.394-1.009-.927 0-.552.425-.94 1.01-.94.609 0 1.028.388 1.028.94 0 .533-.42.927-1.029.927z"/></svg>
        </div>
        <div class="modal__body mb-4">
            <h4 class="mb-2">Konfirmasi Hapus Transaksi</h4>
            <p class="mb-4">Yakin mau menghapus transaksi?</p>
            <div class="input-group">
                <input type="password" name="password" class="form-input form-input--focus-red" placeholder="Password mu...">
                <a class="btn btn--gray-outline" id="show-password" href=""><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg></a>
            </div>
        </div>
        <div class="position-relative d-inline-block">
            <a class="btn btn--red-outline" href="#" id="remove-transaction-in-db">Ya, Hapus</a>

            <div class="loading-bg rounded position-absolute top-0 bottom-0 end-0 start-0 d-flex justify-content-center align-items-center d-none">
                <div class="loading loading--red">
                    <div></div>
                </div>
            </div>
        </div><!-- position-relative -->
    </div>
</div><!-- modal -->
<?= $this->endSection() ?>

<?= $this->section('style') ?>
<link rel="stylesheet" href="<?= base_url('/dist/css/flatpickr.min.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="<?= base_url('dist/plugins/flatpickr/flatpickr.min.js') ?>"></script>
<script type="module" src="<?= base_url('dist/js/transaction.js') ?>"></script>
<?= $this->endSection() ?>
