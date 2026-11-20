<?php
namespace App\Models;
use CodeIgniter\Model;
class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['code', 'name', 'phone', 'email', 'address'];
}
