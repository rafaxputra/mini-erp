<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateCategories extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE categories (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS categories CASCADE");
    }
}
