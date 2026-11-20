<?php
namespace App\Models;
use CodeIgniter\Model;
class SalesOrderModel extends Model
{
    protected $table = 'sales_orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['so_number', 'customer_id', 'order_date', 'status', 'notes', 'total_amount', 'created_by'];
}
