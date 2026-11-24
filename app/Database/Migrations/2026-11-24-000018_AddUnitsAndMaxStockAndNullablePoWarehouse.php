<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddUnitsAndMaxStockAndNullablePoWarehouse extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE IF NOT EXISTS units (
    id SERIAL PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE products ADD COLUMN IF NOT EXISTS unit_id INT REFERENCES units(id) ON DELETE RESTRICT;
CREATE INDEX IF NOT EXISTS idx_products_unit_id ON products(unit_id);

ALTER TABLE products ADD COLUMN IF NOT EXISTS max_stock INT NOT NULL DEFAULT 0 CHECK (max_stock >= 0);

ALTER TABLE purchase_orders ALTER COLUMN warehouse_id DROP NOT NULL;
ALTER TABLE purchase_orders ALTER COLUMN warehouse_id SET DEFAULT NULL;
SQL);
    }

    public function down()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE products DROP COLUMN IF EXISTS unit_id;
ALTER TABLE products DROP COLUMN IF EXISTS max_stock;
DROP TABLE IF EXISTS units CASCADE;
SQL);
    }
}
