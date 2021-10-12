<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CashierPermission implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // if unsigned in
        if(!$session->has('posw_sign_in_status')) {
            return redirect()->to('/');
        }

        // if signed in, but posw_user_level is not kasir
        if($_SESSION['posw_user_level'] !== 'kasir') {
            return redirect()->to('/sign_out');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $reponse, $arguments = null)
    {

    }
}
