<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\{UsersModel, ProductsModel, TransactionsModel};

class Admin extends Controller
{
    public function __construct()
    {
        $this->transactionsModel = new TransactionsModel();
    }

    public function index()
    {
        helper('active_menu');
        helper('number');

        $usersModel = new UsersModel();
        $productsModel = new ProductsModel();

        $data['title'] = 'Home . POSW';
        $data['page'] = 'home';
        $data['totalUsers'] = $usersModel->getTotal();
        $data['totalProducts'] = $productsModel->getTotal();
        $data['totalTransactions'] = $this->transactionsModel->getTotal();

        return view('admin', $data);
    }

    public function getTransactionsTwoMonthsAgo()
    {
        $transactionsTwoMonthsAgo = $this->transactionsModel->getTwoMonthsAgo();
        $transactionsTwoMonthsAgoGrouped = [];
        // last day in transactions grouped
        $day = '0';
        // last index in transactions grouped
        $index = 0;

        foreach($transactionsTwoMonthsAgo as $key => $value) {
            $newDay = substr($value['updated_at'], 8, 2);

            if ($day == $newDay) {
                $transactionsTwoMonthsAgoGrouped['amount'][$index] += 1;
            } else {
                // if not first loop
                if ($key != 0) {
                    $index++;
                }

                $day = $newDay;
                $transactionsTwoMonthsAgoGrouped['amount'][$index] = 1;
            }
            $transactionsTwoMonthsAgoGrouped['updated_at'][$index] = strtotime($value['updated_at'])*1000;
        }
        return json_encode($transactionsTwoMonthsAgoGrouped);
    }
}
