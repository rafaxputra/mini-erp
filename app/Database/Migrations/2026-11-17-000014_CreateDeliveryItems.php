<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateDeliveryItems extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE delivery_items (
  id SERIAL PRIMARY KEY,
  delivery_id INT NOT NULL REFERENCES deliveries(id) ON DELETE CASCADE,
  sales_order_item_id INT NOT NULL REFERENCES sales_order_items(id) ON DELETE RESTRICT,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
  qty_delivered INT NOT NULL CHECK (qty_delivered > 0)
);
CREATE INDEX idx_di_delivery ON delivery_items(delivery_id);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS delivery_items CASCADE");
    }
}
