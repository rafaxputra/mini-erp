<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateCustomers extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE customers (
  id SERIAL PRIMARY KEY,
  code VARCHAR(20) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NULL,
  email VARCHAR(150) NULL,
  address TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS customers CASCADE");
    }
}
