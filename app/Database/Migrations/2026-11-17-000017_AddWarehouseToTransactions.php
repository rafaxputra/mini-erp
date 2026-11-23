<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class AddWarehouseToTransactions extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE purchase_orders ADD COLUMN IF NOT EXISTS warehouse_id INT NOT NULL DEFAULT 1 REFERENCES warehouses(id) ON DELETE RESTRICT;
ALTER TABLE goods_receipts ADD COLUMN IF NOT EXISTS warehouse_id INT NOT NULL DEFAULT 1 REFERENCES warehouses(id) ON DELETE RESTRICT;
ALTER TABLE sales_orders ADD COLUMN IF NOT EXISTS warehouse_id INT NOT NULL DEFAULT 1 REFERENCES warehouses(id) ON DELETE RESTRICT;
ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS warehouse_id INT NOT NULL DEFAULT 1 REFERENCES warehouses(id) ON DELETE RESTRICT;
CREATE INDEX IF NOT EXISTS idx_po_warehouse ON purchase_orders(warehouse_id);
CREATE INDEX IF NOT EXISTS idx_gr_warehouse ON goods_receipts(warehouse_id);
CREATE INDEX IF NOT EXISTS idx_so_warehouse ON sales_orders(warehouse_id);
CREATE INDEX IF NOT EXISTS idx_do_warehouse ON deliveries(warehouse_id);
SQL);
    }
    public function down()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE purchase_orders DROP COLUMN IF EXISTS warehouse_id;
ALTER TABLE goods_receipts DROP COLUMN IF EXISTS warehouse_id;
ALTER TABLE sales_orders DROP COLUMN IF EXISTS warehouse_id;
ALTER TABLE deliveries DROP COLUMN IF EXISTS warehouse_id;
SQL);
    }
}
