<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Cashier extends Controller
{
    public function index()
    {
        helper('active_menu');

        $data['title'] = 'Home . POSW';

        return view('cashier', $data);
    }
}
