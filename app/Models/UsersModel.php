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
        'edited_at',
        'deleted_at'
    ];
    protected $useAutoIncrement = false;
    protected $useSoftDeletes = true;

    public function getSignIn(string $username): ?array
    {
        return $this->select('full_name, level, password, user_id')
                    ->where('deleted_at IS NULL', '', false)->getWhere([
                        'username' => $username
                    ])->getRowArray();
    }
    
    public function getAll(): array
    {
        return $this->select('user_id, full_name, level, last_sign_in, created_at, edited_at')
             ->orderBy('full_name', 'ASC')->where('deleted_at IS NULL', '', false)->get()->getResultArray();
    }

    public function getAllDeleted(): array
    {
         return $this->select('user_id, full_name, level, deleted_at')
             ->orderBy('full_name', 'ASC')->where('deleted_at IS NOT NULL', '', false)->get()->getResultArray();       
    }

    public function getTotal(): int
    {
        return $this->where('deleted_at IS NULL', '', false)->countAll();
    }

    public function getOne(string $userId, string $column): ?array
    {
        return $this->select($column)->where('deleted_at IS NULL', '', false)->getWhere(['user_id' => $userId])->getRowArray();
    }
}
