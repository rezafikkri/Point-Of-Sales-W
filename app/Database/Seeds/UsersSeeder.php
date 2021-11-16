<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $dateTime = date('Y-m-d') . ' 05:40:10';
        $builder = $this->db->table('users');

        $data = [
            [
                'user_id' => 'e09a58bd-f61a-4d63-8a03-d65d23b914f5',
                'full_name' => 'Reza Sariful Fikri',
                'username' => 'reza',
                'level' => 'admin',
                'password' => password_hash('reza', PASSWORD_DEFAULT),
                'created_at' => $dateTime,
                'edited_at' => $dateTime
            ],
            [
                'user_id' => '90b86b53-4bc8-436f-8919-c709d8026471',
                'full_name' => 'Dian Pranata',
                'username' => 'dian',
                'level' => 'cashier',
                'password' => password_hash('dian', PASSWORD_DEFAULT),
                'created_at' => $dateTime,
                'edited_at' => $dateTime
            ],
            [
                'user_id' => '8ca354cb-f0fc-47dd-8b5e-8d88e460c6c7',
                'full_name' => 'Adelina Damayanti',
                'username' => 'dea',
                'level' => 'cashier',
                'password' => password_hash('dea', PASSWORD_DEFAULT),
                'created_at' => $dateTime,
                'edited_at' => $dateTime
            ]
        ];
        $builder->insertBatch($data);
    }
}
