<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveAdjustmentsAndAddDeliveryTime extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS delivery_time VARCHAR(5) NOT NULL DEFAULT '09:00';

DELETE FROM stock_movements WHERE movement_type = 'ADJUSTMENT' AND reference_type <> 'STOCK_AWAL';
SQL);
    }

    public function down()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE deliveries DROP COLUMN IF EXISTS delivery_time;
SQL);
    }
}
