<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateStockMovements extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS stock_movements (
  id SERIAL PRIMARY KEY,
  product_id INT NOT NULL REFERENCES products(id) ON DELETE RESTRICT,
  warehouse_id INT NOT NULL REFERENCES warehouses(id) ON DELETE RESTRICT,
  movement_type VARCHAR(20) NOT NULL CHECK (movement_type IN ('IN','OUT','ADJUSTMENT')),
  reference_type VARCHAR(30) NOT NULL,
  reference_id INT NOT NULL,
  qty INT NOT NULL CHECK (qty > 0),
  qty_before INT NOT NULL CHECK (qty_before >= 0),
  qty_after INT NOT NULL CHECK (qty_after >= 0),
  notes TEXT NULL,
  created_by INT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  created_at TIMESTAMP NULL
);
CREATE INDEX IF NOT EXISTS idx_sm_product_created ON stock_movements(product_id, created_at);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS stock_movements CASCADE");
    }
}
