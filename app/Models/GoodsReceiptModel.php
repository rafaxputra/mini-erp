<?php
namespace App\Models;
use CodeIgniter\Model;
class GoodsReceiptModel extends Model
{
    protected $table = 'goods_receipts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['gr_number', 'purchase_order_id', 'warehouse_id', 'product_id', 'qty_received', 'receipt_date', 'notes', 'received_by'];

    public function receivedQtyForPoRow(int $purchaseOrderId): int
    {
        $row = $this->db->table('goods_receipts')->select('COALESCE(SUM(qty_received), 0) AS total')->where('purchase_order_id', $purchaseOrderId)->get()->getRowArray();
        return (int) ($row['total'] ?? 0);
    }
}
