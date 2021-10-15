<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminPermission implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // if unsigned in
        if(!$session->has('posw_sign_in_status')) {
            return redirect()->to('/');
        }

        // if signed in, but posw_user_level is not admin
        if($_SESSION['posw_user_level'] !== 'admin') {
            return redirect()->to('/sign_out');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $reponse, $arguments = null)
    {

    }
}