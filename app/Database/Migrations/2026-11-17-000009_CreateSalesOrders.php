<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateSalesOrders extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS sales_orders (
  id SERIAL PRIMARY KEY,
  so_number VARCHAR(30) NOT NULL,
  customer_id INT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
  qty INT NOT NULL CHECK (qty > 0),
  unit_price NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (unit_price >= 0),
  order_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','confirmed','delivered','cancelled')),
  notes TEXT NULL,
  created_by INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT uq_so_product UNIQUE (so_number, product_id)
);
CREATE INDEX IF NOT EXISTS idx_so_number ON sales_orders(so_number);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS sales_orders CASCADE");
    }
}
