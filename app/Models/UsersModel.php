<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $allowedFields = [
        'user_id',
        'full_name',
        'username',
        'level',
        'password',
        'last_sign_in',
        'created_at',
        'edited_at'
    ];
    protected $useAutoIncrement = false;

    public function getSignIn(string $username): ?array
    {
        return $this->select('full_name, level, password, user_id')->getWhere([
            'username' => $username
        ])->getRowArray();
    }

    public function getAll(): array
    {
        return $this->select('user_id, full_name, level, last_sign_in, created_at, edited_at')
                    ->orderBy('full_name', 'ASC')->get()->getResultArray();
    }

    public function getTotal(): int
    {
        return $this->countAll();
    }

    public function getOne(string $userId, string $column): ?array
    {
        return $this->select($column)->getWhere(['user_id' => $userId])->getRowArray();
    }
}
