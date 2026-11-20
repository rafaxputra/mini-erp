<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateProducts extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE products (
  id SERIAL PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  category_id INT NOT NULL REFERENCES categories(id) ON DELETE RESTRICT,
  unit VARCHAR(20) NOT NULL DEFAULT 'pcs',
  purchase_price NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (purchase_price >= 0),
  selling_price NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (selling_price >= 0),
  stock_qty INT NOT NULL DEFAULT 0 CHECK (stock_qty >= 0),
  min_stock INT NOT NULL DEFAULT 0 CHECK (min_stock >= 0),
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS products CASCADE");
    }
}
