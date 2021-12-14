<?= $this->extend('admin_layout') ?>

<?= $this->section('main') ?>
<div class="container-xl">
<header class="header d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-start">
    <h4 class="mb-4 mb-sm-0 me-2">Membuat Pengguna</h4>
    <a href="/admin/users" class="btn btn--gray-outline text-center">Batal</a>
</header>

<main class="main mb-5">
    <div class="row">
    <div class="col-md-8">
        <?= $_SESSION['errors']['create_user'] ?? null ?>
        <div class="main__box">
            <?= form_open('/admin/user/store') ?>
                <div class="mb-3">
                    <label class="form-label" for="full-name">Nama Lengkap</label>
                    <input class="form-input" id="full-name" type="text" name="full_name" value="<?= old('full_name') ?>">
                    <?= $_SESSION['errors']['full_name'] ?? null ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-input" type="text" id="username" name="username" value="<?= old('username') ?>">
                    <?= $_SESSION['errors']['username'] ?? null ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="level">Tingkat</label>
                    <select class="form-select" name="level" id="level">
                    <?php
                        $levels = ['cashier', 'admin'];
                        for ($i = 0; $i < 2; $i++) :
                    ?>
                        <option value="<?= $levels[$i] ?>"
                        <?= $levels[$i] == old('level') ? 'selected' : '' ?>>
                            <?= $levels[$i] == 'cashier' ? 'Kasir' : 'Admin' ?>
                        </option>
                    <?php endfor ?>
                    </select>
                    <?= $_SESSION['errors']['level'] ?? null ?>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                     <div class="input-group">
                        <input class="form-input" type="password" id="password" name="password">
                        <a class="btn btn--gray-outline" id="show-password" href=""><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg></a>
                        <a class="btn btn--gray-outline" id="generate-password" href="">Hasilkan</a>
                    </div>
                    <?= $_SESSION['errors']['password'] ?? null ?>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="user-sign-in-password">Password Mu</label>
                     <div class="input-group">
                        <input class="form-input" type="password" id="user-sign-in-password" name="user_sign_in_password">
                        <a class="btn btn--gray-outline" id="show-password" href=""><svg xmlns="http://www.w3.org/2000/svg" width="19" fill="currentColor" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg></a>
                    </div>
                    <?= $_SESSION['errors']['user_sign_in_password'] ?? null ?>
                </div>

                <button class="btn btn--blue" type="submit">Simpan</button>
            </form>
        </div><!-- main__box -->
    </div>
    </div>
</main>
</div><!-- container-xl -->
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script type="module" src="<?= base_url('dist/js/users/create.js') ?>"></script>
<?= $this->endSection() ?>
