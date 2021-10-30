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
        helper(['active_menu', 'number']);

        $usersModel = new UsersModel();
        $productsModel = new ProductsModel();

        $data['title'] = 'Home . POSW';
        $data['page'] = 'home';
        $data['totalUser'] = $usersModel->getTotal();
        $data['totalProduct'] = $productsModel->getTotal();
        $data['totalTransaction'] = $this->transactionsModel->getTotal();

        return view('admin', $data);
    }

    public function showTransactionsTwoMonthsAgo()
    {
        $transactionsTwoMonthsAgo = $this->transactionsModel->getTwoMonthsAgo();
        $transactionsTwoMonthsAgoGrouped = [];
        // last day in transactions grouped
        $day = '0';
        // last index in transactions grouped
        $index = 0;

        foreach ($transactionsTwoMonthsAgo as $key => $value) {
            $newDay = substr($value['edited_at'], 8, 2);

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
            $transactionsTwoMonthsAgoGrouped['edited_at'][$index] = strtotime($value['edited_at'])*1000;
        }
        return json_encode($transactionsTwoMonthsAgoGrouped);
    }
}
