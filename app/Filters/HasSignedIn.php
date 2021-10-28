<?php

namespace App\Filters;

use CodeIgniter\HTTP\{RequestInterface, ResponseInterface};
use CodeIgniter\Filters\FilterInterface;

class HasSignedIn implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // if has signed in
        if ($session->has('sign_in_status')) {
            // if sign_in_user_level is admin
            if ($_SESSION['sign_in_user_level'] == 'admin') {
                return redirect()->to('/admin');
            }

            return redirect()->to('/kasir');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $reponse, $arguments = null)
    {

    }
}
