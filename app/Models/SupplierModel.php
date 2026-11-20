<?php
namespace App\Models;
use CodeIgniter\Model;
class SupplierModel extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['code', 'name', 'phone', 'email', 'address'];
}
