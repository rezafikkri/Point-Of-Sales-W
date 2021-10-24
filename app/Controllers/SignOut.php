<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UsersModel;

class SignOut extends Controller
{
    public function index()
    {
        $session = session();

        // if has signed in
        if($session->has('sign_in_status')) {
            // update last sign in
            $usersModel = new UsersModel;
            $usersModel->update($_SESSION['sign_in_user_id'], [
                'last_sign_in' => date('Y-m-d H:i:s')
            ]);

            // destroy session
            $session->destroy();
        }

        return redirect()->to('/');
    }
}
