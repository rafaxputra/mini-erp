<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateDeliveries extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE deliveries (
  id SERIAL PRIMARY KEY,
  delivery_number VARCHAR(30) NOT NULL UNIQUE,
  sales_order_id INT NOT NULL REFERENCES sales_orders(id) ON DELETE RESTRICT,
  delivery_date DATE NOT NULL,
  notes TEXT NULL,
  delivered_by INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS deliveries CASCADE");
    }
}
