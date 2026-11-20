<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateSalesOrders extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE sales_orders (
  id SERIAL PRIMARY KEY,
  so_number VARCHAR(30) NOT NULL UNIQUE,
  customer_id INT NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
  order_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','confirmed','delivered','cancelled')),
  notes TEXT NULL,
  total_amount NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (total_amount >= 0),
  created_by INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS sales_orders CASCADE");
    }
}
