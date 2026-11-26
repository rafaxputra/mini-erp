<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddScheduledDoAndRestockAlerts extends Migration
{
    public function up()
    {
        $this->db->query(<<<'SQL'
ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'delivered';
ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS cancel_reason TEXT NULL;
ALTER TABLE deliveries ADD COLUMN IF NOT EXISTS cancelled_at TIMESTAMP NULL;
CREATE INDEX IF NOT EXISTS idx_deliveries_status ON deliveries(status);

CREATE TABLE IF NOT EXISTS restock_alerts (
    id SERIAL PRIMARY KEY,
    sales_order_number VARCHAR(50) NOT NULL,
    product_id INT NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    warehouse_id INT NOT NULL REFERENCES warehouses(id) ON DELETE CASCADE,
    requested_qty INT NOT NULL,
    available_qty INT NOT NULL,
    shortage_qty INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'open',
    created_by INT NULL REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX IF NOT EXISTS idx_restock_alerts_status ON restock_alerts(status);
CREATE INDEX IF NOT EXISTS idx_restock_alerts_so ON restock_alerts(sales_order_number);
SQL);
    }

    public function down()
    {
        $this->db->query(<<<'SQL'
DROP TABLE IF EXISTS restock_alerts CASCADE;
ALTER TABLE deliveries DROP COLUMN IF EXISTS cancelled_at;
ALTER TABLE deliveries DROP COLUMN IF EXISTS cancel_reason;
ALTER TABLE deliveries DROP COLUMN IF EXISTS status;
SQL);
    }
}
