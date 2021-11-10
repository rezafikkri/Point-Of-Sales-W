<?php

namespace App\Controllers;

use App\Models\UsersModel;

class SignIn extends BaseController
{
    public function index()
    {
        helper('form');
        return view('sign_in');
    }

    public function signIn()
    {
        if (!$this->validate([
            'username' => [
                'label' => 'Username',
                'rules' => 'required',
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'required',
            ]
        ])) {
            // set validation error messages to flash session
            $this->session->setFlashData('errors', $this->addDelimiterMessages($this->validator->getErrors()));
            return redirect()->to('/')->withInput();
        }

        $username = $this->request->getPost('username', FILTER_SANITIZE_STRING);
        $password = $this->request->getPost('password', FILTER_SANITIZE_STRING);

        $usersModel = new UsersModel();
        $userSignIn = $usersModel->getUserSignIn($username);

        // if username is exist
        if($userSignIn) {
            // if password is valid
            if(password_verify($password, $userSignIn['password'])) {
                $this->session->set([
                    'sign_in_status' => true,
                    'sign_in_user_id' => $userSignIn['user_id'],
                    'sign_in_user_level' => $userSignIn['level'],
                    'sign_in_user_full_name' => $userSignIn['full_name']
                ]);

                // if user level is admin
                if($_SESSION['sign_in_user_level'] == 'admin') {
                    return redirect()->to('/admin');
                }

                return redirect()->to('/kasir');
            }

            // if password is wrong
            $this->session->setFlashData('errors', $this->addDelimiterMessages([
                'password' => 'Password salah.'
            ]));
            return redirect()->to('/');
        }

        // if username not found
        $this->session->setFlashData('errors', $this->addDelimiterMessages([
            'username' => 'Username tidak ditemukan.'
        ]));
        return redirect()->to('/');
    }
}
