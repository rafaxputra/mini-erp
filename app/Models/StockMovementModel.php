<?php
namespace App\Models;
use CodeIgniter\Model;
class StockMovementModel extends Model
{
    protected $table = 'stock_movements';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $allowedFields = ['product_id', 'warehouse_id', 'movement_type', 'reference_type', 'reference_id', 'qty', 'qty_before', 'qty_after', 'notes', 'created_by'];
}
