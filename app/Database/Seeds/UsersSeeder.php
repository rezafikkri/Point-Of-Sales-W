<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        helper('generate_uuid');
        $builder = $this->db->table('users');

        $data = [
            [
                'user_id' => generate_uuid(),
                'full_name' => 'Reza Sariful Fikri',
                'username' => 'reza',
                'level' => 'admin',
                'password' => password_hash('reza', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'user_id' => generate_uuid(),
                'full_name' => 'Dian Pranata',
                'username' => 'dian',
                'level' => 'cashier',
                'password' => password_hash('dian', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        $builder->insertBatch($data);
    }
}
