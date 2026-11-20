<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateGoodsReceiptItems extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE goods_receipt_items (
  id SERIAL PRIMARY KEY,
  goods_receipt_id INT NOT NULL REFERENCES goods_receipts(id) ON DELETE CASCADE,
  purchase_order_item_id INT NOT NULL REFERENCES purchase_order_items(id) ON DELETE RESTRICT,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
  qty_received INT NOT NULL CHECK (qty_received > 0)
);
CREATE INDEX idx_gri_gr ON goods_receipt_items(goods_receipt_id);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS goods_receipt_items CASCADE");
    }
}
