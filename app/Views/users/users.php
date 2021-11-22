<?php

use CodeIgniter\I18n\Time;

$this->extend('admin_layout');

?>

<?= $this->section('main'); ?>
<div class="container-xl">
<header class="header d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start">
    <h4 class="mb-4 mb-sm-0 me-2">Pengguna</h4>
    
    <div class="d-flex flex-column-reverse flex-sm-row justify-content-start justify-content-sm-end align-items-sm-start flex-fill">
        <a href="/admin/pengguna/membuat" class="btn btn--gray-outline text-center me-0 me-sm-2" title="Kotak sampah"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"/><path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"/></svg></a>
        <a href="/admin/pengguna/membuat" class="btn btn--blue text-center mb-3 mb-sm-0">Membuat Pengguna</a>
    </div><!-- d-flex -->
</header>

<main class="main">
    <div class="main__box position-relative">

        <div class="table-responsive" id="table" data-csrf-name="<?= csrf_token(); ?>" data-csrf-value="<?= csrf_hash(); ?>">
         <table class="table table--auto-striped min-width-711">
            <thead>
                <tr>
                    <th class="text-center" colspan="2">Aksi</th>
                    <th>Nama Lengkap</th>
                    <th>Tingkat</th>
                    <th width="160">Terakhir Masuk</th>
                    <th width="160">Dibuat</th>
                    <th width="160">Diedit</th>
                </tr>
            </thead>
            <tbody>
            <?php
                foreach ($users as $u) :
                    $createdAt = Time::createFromFormat('Y-m-d H:i:s', $u['created_at']);
                    $editedAt = Time::createFromFormat('Y-m-d H:i:s', $u['edited_at']);
            ?>
                <tr id="user<?= $u['user_id']; ?>">
                    <?php
                        // if user id = user id sign in
                        if ($u['user_id'] !== $_SESSION['sign_in_user_id']) :
                    ?>
                    <td width="10"><a href="#" data-user-id="<?= $u['user_id']; ?>" data-full-name="<?= $u['full_name']; ?>" title="Mengapus pengguna" class="text-hover-red" id="delete-user"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"/><path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"/></svg></a></td>
                    <td width="10">
                    <?php else : ?>
                    <td width="10" colspan="2" class="text-center">
                    <?php endif; ?>
                        <a href="/admin/pengguna/edit/<?= $u['user_id']; ?>" title="Edit pengguna"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"/></svg></a>
                    </td>
                    <td><?= $u['full_name']; ?></td>
                    <td><?= ($u['level'] == 'admin') ? 'Admin' : 'Kasir'; ?></td>
                    <td><?= $u['last_sign_in']; ?></td>
                    <td><?= $createdAt->toLocalizedString('dd MMM yyyy HH:mm') ?></td>
                    <td><?= $editedAt->toLocalizedString('dd MMM yyyy HH:mm') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div><!-- table-responsive -->

    </div><!-- main__box  -->
</main>
</div><!-- container-xl -->

<div class="modal modal--red">
    <div class="modal__content">
        <a class="btn btn--light" id="btn-close" href=""><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg></a>
        <div class="modal__icon mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" fill="currentColor" viewBox="0 0 16 16"><path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.496 6.033a.237.237 0 0 1-.24-.247C5.35 4.091 6.737 3.5 8.005 3.5c1.396 0 2.672.73 2.672 2.24 0 1.08-.635 1.594-1.244 2.057-.737.559-1.01.768-1.01 1.486v.105a.25.25 0 0 1-.25.25h-.81a.25.25 0 0 1-.25-.246l-.004-.217c-.038-.927.495-1.498 1.168-1.987.59-.444.965-.736.965-1.371 0-.825-.628-1.168-1.314-1.168-.803 0-1.253.478-1.342 1.134-.018.137-.128.25-.266.25h-.825zm2.325 6.443c-.584 0-1.009-.394-1.009-.927 0-.552.425-.94 1.01-.94.609 0 1.028.388 1.028.94 0 .533-.42.927-1.029.927z"/></svg>
        </div>
        <div class="modal__body mb-4">
            <h4 class="mb-2">Konfirmasi Hapus Pengguna</h4>
            <p class="mb-4">Yakin mau menghapus <strong>Dian</strong>?</p>
            <input type="hidden" name="user_id">
            <div class="input-group">
                <input type="password" name="password" class="form-input form-input--focus-red" placeholder="Password mu...">
                <a class="btn btn--gray-outline" id="show-password" href=""><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg></a>
            </div>
        </div>
        <div class="position-relative d-inline-block">
            <a class="btn btn--red-outline" href="#" id="remove-user-in-db">Ya, Hapus</a>

            <div class="loading-bg rounded position-absolute top-0 bottom-0 end-0 start-0 d-flex justify-content-center align-items-center d-none">
                <div class="loading loading--red">
                    <div></div>
                </div>
            </div>
        </div><!-- position-relative -->
    </div>
</div><!-- modal -->
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script src="<?= base_url('dist/js/user.posw.min.js'); ?>"></script>
<?= $this->endSection(); ?>
