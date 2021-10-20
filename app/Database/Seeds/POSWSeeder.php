<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class POSWSeeder extends Seeder
{
    public function run()
    {
        $this->call('UsersSeeder');
        $this->call('ProductCategoriesSeeder');
        $this->call('ProductsSeeder');
        $this->call('TransactionsSeeder');
    }
}
