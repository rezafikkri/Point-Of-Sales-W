<?php

use CodeIgniter\I18n\Time;

$this->extend('admin_layout');

?>

<?= $this->section('main') ?>
<div class="container-xl">
<header class="header d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start">
    <h4 class="mb-4 mb-sm-0 me-2">Kotak Sampah Pengguna</h4>
    
    <div class="d-flex flex-column-reverse flex-sm-row justify-content-start justify-content-sm-end align-items-sm-start flex-fill">
        <a href="/admin/users" class="btn btn--gray-outline text-center mb-3 mb-sm-0">Kembali</a>
    </div><!-- d-flex -->
</header>

<main class="main mb-5">
    <div class="main__box position-relative">

        <div class="table-responsive" id="table" data-csrf-name="<?= csrf_token() ?>" data-csrf-value="<?= csrf_hash() ?>">
         <table class="table table--auto-striped min-width-711">
            <thead>
                <tr>
                    <th class="text-center" colspan="2">Aksi</th>
                    <th>Nama Lengkap</th>
                    <th>Tingkat</th>
                    <th width="160">Dihapus</th>
                </tr>
            </thead>
            <tbody>
            <?php
                // if exists users
                if (count($users) > 0) :
                foreach ($users as $u) :
                    $deletedAt = Time::createFromFormat('Y-m-d H:i:s', $u['deleted_at']);
            ?>
                <tr id="user<?= $u['user_id'] ?>">
                <?php
                    // if user id = user id sign in
                    if ($u['user_id'] != $_SESSION['sign_in_user_id']) :
                ?>
                    <td width="10"><a href="#" data-user-id="<?= $u['user_id'] ?>" data-full-name="<?= $u['full_name'] ?>" title="Mengapus pengguna" class="text-hover-red" id="show-modal-delete"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"/><path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"/></svg></a></td>
                    <td width="10">
                <?php else : ?>
                    <td width="10" colspan="2" class="text-center">
                <?php endif ?>
                        <a href="#" data-user-id="<?= $u['user_id'] ?>" data-full-name="<?= $u['full_name'] ?>" title="Memulihkan pengguna" id="show-modal-restore"><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" class="bi bi-arrow-return-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.5 1.5A.5.5 0 0 0 1 2v4.8a2.5 2.5 0 0 0 2.5 2.5h9.793l-3.347 3.346a.5.5 0 0 0 .708.708l4.2-4.2a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 8.3H3.5A1.5 1.5 0 0 1 2 6.8V2a.5.5 0 0 0-.5-.5z"/></svg></a>
                    </td>
                    <td><?= $u['full_name'] ?></td>
                    <td><?= $u['level'] == 'admin' ? 'Admin' : 'Kasir' ?></td>
                    <td><?= $deletedAt->toLocalizedString('dd MMM yyyy HH:mm') ?></td>
                </tr>
            <?php endforeach; else : ?>
                <tr>
                    <td colspan="5">Pengguna tidak ada.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div><!-- table-responsive -->

    </div><!-- main__box  -->
</main>
</div><!-- container-xl -->

<div id="modals">
<div class="modal modal--red" id="permanently-delete-modal">
    <div class="modal__content">
        <a class="btn btn--light" id="btn-close" href=""><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg></a>
        <div class="modal__icon mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" fill="currentColor" viewBox="0 0 16 16"><path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.496 6.033a.237.237 0 0 1-.24-.247C5.35 4.091 6.737 3.5 8.005 3.5c1.396 0 2.672.73 2.672 2.24 0 1.08-.635 1.594-1.244 2.057-.737.559-1.01.768-1.01 1.486v.105a.25.25 0 0 1-.25.25h-.81a.25.25 0 0 1-.25-.246l-.004-.217c-.038-.927.495-1.498 1.168-1.987.59-.444.965-.736.965-1.371 0-.825-.628-1.168-1.314-1.168-.803 0-1.253.478-1.342 1.134-.018.137-.128.25-.266.25h-.825zm2.325 6.443c-.584 0-1.009-.394-1.009-.927 0-.552.425-.94 1.01-.94.609 0 1.028.388 1.028.94 0 .533-.42.927-1.029.927z"/></svg>
        </div>
        <div class="modal__body mb-4">
            <h4 class="mb-2">Konfirmasi Menghapus Pengguna</h4>
            <p class="mb-4">Pengguna akan dihapus permanen. Yakin mau menghapus <strong></strong>?</p>
            <input type="hidden" name="user_id">
            <div class="input-group">
                <input type="password" name="user_sign_in_password" class="form-input form-input--focus-red" placeholder="Password Mu...">
                <a class="btn btn--gray-outline" id="show-password" href=""><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg></a>
            </div>
        </div>
        <div class="position-relative d-inline-block">
            <a class="btn btn--red-outline" href="#" id="delete">Ya, Hapus</a>

            <div class="loading-bg rounded position-absolute top-0 bottom-0 end-0 start-0 d-flex justify-content-center align-items-center d-none" id="delete-loading">
                <div class="loading loading--red">
                    <div></div>
                </div>
            </div>
        </div><!-- position-relative -->
    </div>
</div><!-- delete user permanently modal -->

<div class="modal modal--blue" id="restore-modal">
    <div class="modal__content">
        <a class="btn btn--light" id="btn-close" href=""><svg xmlns="http://www.w3.org/2000/svg" width="21" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg></a>
        <div class="modal__icon mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" fill="currentColor" viewBox="0 0 16 16"><path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm3.496 6.033a.237.237 0 0 1-.24-.247C5.35 4.091 6.737 3.5 8.005 3.5c1.396 0 2.672.73 2.672 2.24 0 1.08-.635 1.594-1.244 2.057-.737.559-1.01.768-1.01 1.486v.105a.25.25 0 0 1-.25.25h-.81a.25.25 0 0 1-.25-.246l-.004-.217c-.038-.927.495-1.498 1.168-1.987.59-.444.965-.736.965-1.371 0-.825-.628-1.168-1.314-1.168-.803 0-1.253.478-1.342 1.134-.018.137-.128.25-.266.25h-.825zm2.325 6.443c-.584 0-1.009-.394-1.009-.927 0-.552.425-.94 1.01-.94.609 0 1.028.388 1.028.94 0 .533-.42.927-1.029.927z"/></svg>
        </div>
        <div class="modal__body mb-4">
            <h4 class="mb-2">Konfirmasi Memulihkan Pengguna</h4>
            <p class="mb-4">Yakin mau memulihkan <strong></strong>?</p>
            <input type="hidden" name="user_id">
            <div class="input-group">
                <input type="password" name="user_sign_in_password" class="form-input" placeholder="Password Mu...">
                <a class="btn btn--gray-outline" id="show-password" href=""><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg></a>
            </div>
        </div>
        <div class="position-relative d-inline-block">
            <a class="btn btn--blue-outline" href="#" id="restore">Ya, Pulihkan</a>

            <div class="loading-bg rounded position-absolute top-0 bottom-0 end-0 start-0 d-flex justify-content-center align-items-center d-none" id="restore-loading">
                <div class="loading loading--blue">
                    <div></div>
                </div>
            </div>
        </div><!-- position-relative -->
    </div>
</div><!-- restore user modal -->
</div><!-- modals -->
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script type="module" src="<?= base_url('dist/js/users/trash.js') ?>"></script>
<?= $this->endSection() ?>
