<?php
namespace App\Models;
use CodeIgniter\Model;
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'email', 'password_hash', 'role_id', 'is_active'];
    protected array $casts = ['is_active' => 'boolean'];
}
