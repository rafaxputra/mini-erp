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
    protected $allowedFields = ['so_number', 'customer_id', 'warehouse_id', 'product_id', 'qty', 'unit_price', 'order_date', 'status', 'notes', 'created_by'];

    public function totalAmount(string $soNumber): float
    {
        $row = $this->db->table('sales_orders')->select('COALESCE(SUM(qty * unit_price), 0) AS total')->where('so_number', $soNumber)->get()->getRowArray();
        return (float) ($row['total'] ?? 0);
    }
}
