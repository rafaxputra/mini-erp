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
  po_number VARCHAR(30) NOT NULL,
  supplier_id INT NOT NULL REFERENCES suppliers(id) ON DELETE RESTRICT,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
  qty INT NOT NULL CHECK (qty > 0),
  unit_price NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (unit_price >= 0),
  order_date DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','approved','received','cancelled')),
  notes TEXT NULL,
  created_by INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT uq_po_product UNIQUE (po_number, product_id)
);
CREATE INDEX idx_po_number ON purchase_orders(po_number);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS purchase_orders CASCADE");
    }
}
