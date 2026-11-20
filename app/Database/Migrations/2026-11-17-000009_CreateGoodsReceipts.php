<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateGoodsReceipts extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE goods_receipts (
  id SERIAL PRIMARY KEY,
  gr_number VARCHAR(30) NOT NULL UNIQUE,
  purchase_order_id INT NOT NULL REFERENCES purchase_orders(id) ON DELETE RESTRICT,
  receipt_date DATE NOT NULL,
  notes TEXT NULL,
  received_by INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS goods_receipts CASCADE");
    }
}
