<?php
namespace App\Models;
use CodeIgniter\Model;
class WarehouseModel extends Model
{
    protected $table = 'warehouses';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['code', 'name', 'location', 'is_default'];
}
