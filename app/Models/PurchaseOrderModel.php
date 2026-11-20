<?php
namespace App\Models;
use CodeIgniter\Model;
class PurchaseOrderModel extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['po_number', 'supplier_id', 'order_date', 'status', 'notes', 'total_amount', 'created_by'];
}
