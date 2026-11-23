<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateDeliveries extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS deliveries (
  id SERIAL PRIMARY KEY,
  delivery_number VARCHAR(30) NOT NULL,
  sales_order_id INT NOT NULL REFERENCES sales_orders(id) ON DELETE RESTRICT,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
  qty_delivered INT NOT NULL CHECK (qty_delivered > 0),
  delivery_date DATE NOT NULL,
  notes TEXT NULL,
  delivered_by INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT uq_do_so_row UNIQUE (delivery_number, sales_order_id)
);
CREATE INDEX IF NOT EXISTS idx_delivery_number ON deliveries(delivery_number);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS deliveries CASCADE");
    }
}
