<?php
namespace App\Models;
use CodeIgniter\Model;
class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['code', 'name', 'category_id', 'unit_id', 'unit', 'purchase_price', 'selling_price', 'stock_qty', 'min_stock', 'max_stock', 'is_active'];
}
