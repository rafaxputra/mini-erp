<?php
namespace App\Models;
use CodeIgniter\Model;
class DeliveryModel extends Model
{
    protected $table = 'deliveries';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['delivery_number', 'sales_order_id', 'warehouse_id', 'product_id', 'qty_delivered', 'delivery_date', 'notes', 'delivered_by'];

    public function deliveredQtyForSoRow(int $salesOrderId): int
    {
        $row = $this->db->table('deliveries')->select('COALESCE(SUM(qty_delivered), 0) AS total')->where('sales_order_id', $salesOrderId)->get()->getRowArray();
        return (int) ($row['total'] ?? 0);
    }
}
