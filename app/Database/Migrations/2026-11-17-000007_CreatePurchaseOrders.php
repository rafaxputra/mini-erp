<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreatePurchaseOrders extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE purchase_orders (
  id SERIAL PRIMARY KEY,
  po_number VARCHAR(30) NOT NULL UNIQUE,
  supplier_id INT NOT NULL REFERENCES suppliers(id) ON DELETE RESTRICT,
  order_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','approved','received','cancelled')),
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
        $this->db->query("DROP TABLE IF EXISTS purchase_orders CASCADE");
    }
}
