<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropWarehouseIdFromPurchaseOrders extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE purchase_orders DROP COLUMN IF EXISTS warehouse_id CASCADE;
SQL);
    }

    public function down()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE purchase_orders ADD COLUMN IF NOT EXISTS warehouse_id INT NULL REFERENCES warehouses(id) ON DELETE RESTRICT;
SQL);
    }
}
