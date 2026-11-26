<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRevisionStatusToSalesOrders extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE sales_orders DROP CONSTRAINT IF EXISTS sales_orders_status_check;
ALTER TABLE sales_orders ADD CONSTRAINT sales_orders_status_check CHECK (status IN ('draft','confirmed','revision','delivered','cancelled'));
SQL);
    }

    public function down()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE sales_orders DROP CONSTRAINT IF EXISTS sales_orders_status_check;
ALTER TABLE sales_orders ADD CONSTRAINT sales_orders_status_check CHECK (status IN ('draft','confirmed','delivered','cancelled'));
SQL);
    }
}
