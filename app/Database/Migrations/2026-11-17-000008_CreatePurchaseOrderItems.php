<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreatePurchaseOrderItems extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE purchase_order_items (
  id SERIAL PRIMARY KEY,
  purchase_order_id INT NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
  qty INT NOT NULL CHECK (qty > 0),
  unit_price NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (unit_price >= 0),
  subtotal NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (subtotal >= 0)
);
CREATE INDEX idx_poi_po ON purchase_order_items(purchase_order_id);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS purchase_order_items CASCADE");
    }
}
