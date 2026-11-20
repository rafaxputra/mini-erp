<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;
class CreateUsers extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
CREATE TABLE roles (
  id SERIAL PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE,
  description VARCHAR(255),
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role_id INT NOT NULL REFERENCES roles(id),
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
);
SQL);
    }
    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS users CASCADE");
        $this->db->query("DROP TABLE IF EXISTS roles CASCADE");
    }
}
