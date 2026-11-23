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
    protected $allowedFields = ['po_number', 'supplier_id', 'warehouse_id', 'product_id', 'qty', 'unit_price', 'order_date', 'status', 'notes', 'created_by'];

    public function totalAmount(string $poNumber): float
    {
        $row = $this->db->table('purchase_orders')->select('COALESCE(SUM(qty * unit_price), 0) AS total')->where('po_number', $poNumber)->get()->getRowArray();
        return (float) ($row['total'] ?? 0);
    }
}
