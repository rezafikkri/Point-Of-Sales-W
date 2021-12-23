<?php

namespace App\Controllers;

use App\Models\UsersModel;

class Users extends BaseController
{
    /**
     * User sign in error message
     * 
     * Contains error message from validate user sign in password
     *
     * @var string $signInPassErrorMessage
     */
    private $signInPassErrorMessage = '';

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    public function index()
    {
        helper('active_menu');

        $data['title'] = 'Pengguna . POSW';
        $data['users'] = $this->usersModel->getAll();

        return view('users/users', $data);
    }

    public function create()
    {
        helper(['active_menu', 'form']);

        $data['title'] = 'Membuat Pengguna . POSW';

        return view('users/create', $data);
    }

    public function store()
    {
        if (!$this->validate([
            'full_name' => [
                'label' => 'Nama Lengkap',
                'rules' => 'required|min_length[4]|max_length[32]'
            ],
            'username' => [
                'label' => 'Username',
                'rules' => 'required|min_length[4]|max_length[32]|is_unique[users.username]'
            ],
            'level' => [
                'label' => 'Tingkat',
                'rules' => 'in_list[admin,cashier]'
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required|min_length[8]'
            ],
            'user_sign_in_password' => [
                'label' => 'Password Mu',
                'rules' => 'required'
            ]
        ])) {
            // set validation error messages to flash session
            $this->session->setFlashData('errors', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->to('/admin/user/create')->withInput();
        }

        // check user sign in password
        $userSignInPassword = $this->request->getPost('user_sign_in_password', FILTER_SANITIZE_STRING);
        $passwordHash = $this->usersModel->getOne($_SESSION['sign_in_user_id'], 'password')['password'];
        
        if (!password_verify($userSignInPassword, $passwordHash)) {
            // set validation error messages to flash session
            $this->session->setFlashData('errors', $this->addDelimiterMessages([
                'user_sign_in_password' => 'Password salah.'
            ]));
            return redirect()->to('/admin/user/create')->withInput();
        }
        
        helper('generate_uuid');

        $createdAt = date('Y-m-d H:i:s');
        $insertUser = $this->usersModel->insert([
            'user_id' => generate_uuid(),
            'full_name' => $this->request->getPost('full_name', FILTER_SANITIZE_STRING),
            'username' => $this->request->getPost('username', FILTER_SANITIZE_STRING),
            'level' => $this->request->getPost('level', FILTER_SANITIZE_STRING),
            'password' => password_hash($this->request->getPost('password', FILTER_SANITIZE_STRING), PASSWORD_DEFAULT),
            'created_at' => $createdAt,
            'edited_at' => $createdAt
        ]);

        // if success create user
        if ($insertUser) {
            return redirect()->to('/admin/users');
        }

        // make error message
        $this->openDelimiterMessage = '<div class="alert alert--warning mb-3"><span class="alert__icon"></span><p>';
        $this->closeDelimiterMessage = '</p><a class="alert__close" href="#"></a></div>';
        $this->session->setFlashData('errors', $this->addDelimiterMessages([
            'create_user' => 'User gagal dibuat. Silahkan coba kembali!'
        ]));
        return redirect()->to('/admin/user/create');
    }

    public function edit(string $userId)
    {
        helper(['active_menu', 'form']);

        $userId = filter_var($userId, FILTER_SANITIZE_STRING);

        $data['title'] = 'Edit Pengguna . POSW';
        $data['userId'] = $userId;
        $data['user'] = $this->usersModel->getOne($userId, 'full_name, username, level');

        return view('users/edit', $data);
    }

    public function update()
    {
        $userId = $this->request->getPost('user_id', FILTER_SANITIZE_STRING);

        // generate validation data
        $validationData = [
            'full_name' => [
                'label' => 'Nama Lengkap',
                'rules' => 'required|min_length[4]|max_length[32]'
            ],
            'username' => [
                'label' => 'Username',
                'rules' => "required|min_length[4]|max_length[32]|is_unique[users.username,user_id,$userId]"
            ],
            'level' => [
                'label' => 'Tingkat',
                'rules' => 'in_list[admin,cashier]'
            ],
            'user_sign_in_password' => [
                'label' => 'Password Mu',
                'rules' => 'required'
            ]
        ];

        $password = $this->request->getPost('password', FILTER_SANITIZE_STRING);
        if (!empty(trim($password))) {
            $validationData['password'] = [
                'label' => 'Password',
                'rules' => 'min_length[8]'
            ];
        }

        // validate data
        if (!$this->validate($validationData)) {
            // set validation error messages to flash session
            $this->session->setFlashData('errors', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->to('/admin/user/edit/' . $userId)->withInput();
        }

        // check user sign in password
        $userSignInPassword = $this->request->getPost('user_sign_in_password', FILTER_SANITIZE_STRING);
        $passwordHash = $this->usersModel->getOne($_SESSION['sign_in_user_id'], 'password')['password'];
        
        if (!password_verify($userSignInPassword, $passwordHash)) {
            // set validation error messages to flash session
            $this->session->setFlashData('errors', $this->addDelimiterMessages([
                'user_sign_in_password' => 'Password salah.'
            ]));
            return redirect()->to('/admin/user/edit/' . $userId)->withInput();
        }

        // generate user update data
        $userUpdateData = [
            'full_name' => $this->request->getPost('full_name', FILTER_SANITIZE_STRING),
            'username' => $this->request->getPost('username', FILTER_SANITIZE_STRING),
            'level' => $this->request->getPost('level', FILTER_SANITIZE_STRING)
        ];

        if (!empty(trim($password))) {
            $userUpdateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // if success update product category
        if ($this->usersModel->update($userId, $userUpdateData)) {
            $message = 'User berhasil diedit.';
            $alertType = 'success';
            $flashMessageName = 'success';
        } else {
            $message = 'User gagal diedit. Silahkan coba kembali!';
            $alertType = 'warning';
            $flashMessageName = 'errors';
        }

        $this->openDelimiterMessage = "<div class=\"alert alert--$alertType mb-3\"><span class=\"alert__icon\"></span><p>";
        $this->closeDelimiterMessage = '</p><a class="alert__close" href="#"></a></div>';
        $this->session->setFlashData($flashMessageName, $this->addDelimiterMessages([
            'edit_user' => $message
        ]));
        return redirect()->to('/admin/user/edit/' . $userId);
    }

    public function delete(string $type)
    {
        // check user sign in password
        $userSignInPassword = $this->request->getPost('user_sign_in_password', FILTER_SANITIZE_STRING);        
        if (!$this->validateUserSignInPassword($userSignInPassword)) {
            return json_encode([
                'status' => 'wrong_password',
                'message' => $this->userSignInPasswordErrorMessage,
                'csrf_value' => csrf_hash()
            ]);
        }

        // $purge Allows overriding the soft deletes setting
        if ($type == 'hard') {
            $purge = true;
        } else {
            $purge = false;
        }

        $userId = $this->request->getPost('user_id', FILTER_SANITIZE_STRING);
        if ($this->usersModel->delete($userId, $purge) > 0) {
            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }

        return json_encode([
            'status' => 'fail',
            'message' => 'Gagal menghapus pengguna.',
            'csrf_value' => csrf_hash()
        ]);
    }

    public function trash()
    {
        helper('active_menu');

        $data['title'] = 'Kotak Sampah Pengguna . POSW';
        $data['users'] = $this->usersModel->getAllDeleted();

        return view('users/trash', $data);
    }

    public function restore()
    {
        // check user sign in password
        $userSignInPassword = $this->request->getPost('user_sign_in_password', FILTER_SANITIZE_STRING);        
        if (!$this->validateUserSignInPassword($userSignInPassword)) {
            return json_encode([
                'status' => 'wrong_password',
                'message' => $this->userSignInPasswordErrorMessage,
                'csrf_value' => csrf_hash()
            ]);
        }

        $userId = $this->request->getPost('user_id', FILTER_SANITIZE_STRING);
        $updateUser = $this->usersModel->update($userId, [
            'deleted_at' => null
        ]);

        // if success update user
        if ($updateUser > 0) {
            return json_encode([
                'status' => 'success',
                'csrf_value' => csrf_hash()
            ]);
        }

        return json_encode([
            'status' => 'fail',
            'message' => 'Gagal memulihkan pengguna.',
            'csrf_value' => csrf_hash()
        ]);
    }
}
