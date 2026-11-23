<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateGoodsReceipts extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS goods_receipts (
  id SERIAL PRIMARY KEY,
  gr_number VARCHAR(30) NOT NULL,
  purchase_order_id INT NOT NULL REFERENCES purchase_orders(id) ON DELETE RESTRICT,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
  qty_received INT NOT NULL CHECK (qty_received > 0),
  receipt_date DATE NOT NULL,
  notes TEXT NULL,
  received_by INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT uq_gr_po_row UNIQUE (gr_number, purchase_order_id)
);
CREATE INDEX IF NOT EXISTS idx_gr_number ON goods_receipts(gr_number);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS goods_receipts CASCADE");
    }
}
