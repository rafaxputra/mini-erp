<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateWarehouses extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE warehouses (
  id SERIAL PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  location VARCHAR(150) NULL,
  is_default BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS warehouses CASCADE");
    }
}
