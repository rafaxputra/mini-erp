<?php
namespace App\Database\Seeds;
use CodeIgniter\Database\Seeder;
class MainSeeder extends Seeder
{
    public function run()
    {
        $tables = [
            'stock_movements', 'deliveries', 'sales_orders', 'goods_receipts',
            'purchase_orders', 'products', 'customers', 'suppliers',
            'categories', 'users', 'roles', 'warehouses',
        ];
        foreach ($tables as $t) {
            if ($this->db->tableExists($t)) {
                $this->db->query("TRUNCATE TABLE {$t} RESTART IDENTITY CASCADE");
            }
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('warehouses')->insert([
            'code' => 'GUD-01', 'name' => 'Gudang Utama', 'location' => 'Kantor Pusat',
            'is_default' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->db->table('warehouses')->insert([
            'code' => 'GUD-02', 'name' => 'Gudang Cabang', 'location' => 'Sidoarjo',
            'is_default' => false, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->db->table('roles')->insertBatch([
            ['id' => 1, 'name' => 'admin', 'description' => 'Admin (Super Admin)', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'purchasing', 'description' => 'Staff Purchasing', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'gudang', 'description' => 'Staff Gudang', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'sales', 'description' => 'Staff Sales', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'manajemen', 'description' => 'Manajemen', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->query("SELECT setval('roles_id_seq', (SELECT MAX(id) FROM roles))");
        $this->db->table('users')->insertBatch([
            ['name' => 'Administrator', 'email' => 'admin@nusantara.test', 'password_hash' => password_hash('admin123', PASSWORD_DEFAULT), 'role_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Staff Purchasing', 'email' => 'purchasing@nusantara.test', 'password_hash' => password_hash('purchasing123', PASSWORD_DEFAULT), 'role_id' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Staff Gudang', 'email' => 'gudang@nusantara.test', 'password_hash' => password_hash('gudang123', PASSWORD_DEFAULT), 'role_id' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Staff Sales', 'email' => 'sales@nusantara.test', 'password_hash' => password_hash('sales123', PASSWORD_DEFAULT), 'role_id' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Manajemen', 'email' => 'manajemen@nusantara.test', 'password_hash' => password_hash('manajemen123', PASSWORD_DEFAULT), 'role_id' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->table('categories')->insertBatch([
            ['name' => 'Alat Tulis', 'description' => 'Pulpen, pensil, spidol, dan alat tulis lain', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kertas', 'description' => 'Kertas A4, F4, dan kertas cetak', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Perlengkapan Kantor', 'description' => 'Stapler, map, amplop, klip, dan perlengkapan kantor lain', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Tinta dan Toner', 'description' => 'Tinta printer dan toner cartridge', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->table('suppliers')->insertBatch([
            ['code' => 'SUP-001', 'name' => 'PT Sinar Jaya', 'phone' => '031-111111', 'email' => 'info@sinarjaya.test', 'address' => 'Surabaya', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'SUP-002', 'name' => 'CV Makmur Abadi', 'phone' => '031-222222', 'email' => 'info@makmurabadi.test', 'address' => 'Sidoarjo', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->table('customers')->insertBatch([
            ['code' => 'CUS-001', 'name' => 'Toko Berkah', 'phone' => '081234567890', 'email' => 'tokoberkah@test.com', 'address' => 'Sidoarjo', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'CUS-002', 'name' => 'PT Maju Bersama', 'phone' => '081234567891', 'email' => 'info@majubersama.test', 'address' => 'Surabaya', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $products = [
            ['code' => 'PRD-001', 'name' => 'Pulpen Gel Hitam 12pcs', 'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 15000, 'selling_price' => 25000, 'stock_qty' => 20, 'min_stock' => 5],
            ['code' => 'PRD-002', 'name' => 'Tinta Printer Hitam 100ml', 'category_id' => 4, 'unit' => 'pcs', 'purchase_price' => 75000, 'selling_price' => 120000, 'stock_qty' => 15, 'min_stock' => 5],
            ['code' => 'PRD-003', 'name' => 'Kertas A4 80gsm', 'category_id' => 2, 'unit' => 'rim', 'purchase_price' => 35000, 'selling_price' => 50000, 'stock_qty' => 50, 'min_stock' => 10],
            ['code' => 'PRD-004', 'name' => 'Stapler Besar HD-50', 'category_id' => 3, 'unit' => 'pcs', 'purchase_price' => 45000, 'selling_price' => 75000, 'stock_qty' => 12, 'min_stock' => 3],
            ['code' => 'PRD-005', 'name' => 'Map Plastik A4 100pcs', 'category_id' => 3, 'unit' => 'pack', 'purchase_price' => 20000, 'selling_price' => 35000, 'stock_qty' => 100, 'min_stock' => 20],
        ];
        foreach ($products as $p) {
            $p['is_active'] = true;
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
            $this->db->table('products')->insert($p);
            $id = $this->db->insertID();
            $this->db->table('stock_movements')->insert([
                'product_id' => $id, 'warehouse_id' => 1, 'movement_type' => 'ADJUSTMENT',
                'reference_type' => 'STOCK_AWAL', 'reference_id' => 0, 'qty' => $p['stock_qty'],
                'qty_before' => 0, 'qty_after' => $p['stock_qty'],
                'notes' => 'Stok awal', 'created_by' => 1, 'created_at' => $now,
            ]);
        }

        // Contoh pesanan beli, 3 PO dengan status beda-beda biar pas demo langsung kelihatan.
        // Tanggalnya 3 hari ke belakang supaya nomor PO hari ini tetap mulai dari 001.
        $d3 = date('Y-m-d', strtotime('-3 days'));
        $d2 = date('Y-m-d', strtotime('-2 days'));
        $d1 = date('Y-m-d', strtotime('-1 day'));
        $n3 = date('Ymd', strtotime('-3 days'));
        $n2 = date('Ymd', strtotime('-2 days'));
        $n1 = date('Ymd', strtotime('-1 day'));

        // PO 1 sudah diterima penuh: Stapler x2 dan Map Plastik x10.
        $po1Rows = [
            ['po_number' => 'PO-' . $n3 . '-001', 'supplier_id' => 1, 'warehouse_id' => 1, 'product_id' => 4, 'qty' => 2, 'unit_price' => 45000, 'order_date' => $d3, 'status' => 'received', 'notes' => 'Pesanan contoh, sudah diterima penuh', 'created_by' => 2, 'created_at' => $d3 . ' 09:00:00', 'updated_at' => $d2 . ' 09:00:00'],
            ['po_number' => 'PO-' . $n3 . '-001', 'supplier_id' => 1, 'warehouse_id' => 1, 'product_id' => 5, 'qty' => 10, 'unit_price' => 20000, 'order_date' => $d3, 'status' => 'received', 'notes' => 'Pesanan contoh, sudah diterima penuh', 'created_by' => 2, 'created_at' => $d3 . ' 09:00:00', 'updated_at' => $d2 . ' 09:00:00'],
        ];
        $po1RowIds = [];
        foreach ($po1Rows as $row) {
            $this->db->table('purchase_orders')->insert($row);
            $po1RowIds[] = (int) $this->db->insertID();
        }
        // Barang masuk untuk PO 1, stok naik sesuai jumlah yang diterima.
        $gr1Rows = [
            ['gr_number' => 'GR-' . $n2 . '-001', 'purchase_order_id' => $po1RowIds[0], 'warehouse_id' => 1, 'product_id' => 4, 'qty_received' => 2, 'receipt_date' => $d2, 'notes' => 'Terima PO PO-' . $n3 . '-001', 'received_by' => 3, 'created_at' => $d2 . ' 09:00:00', 'updated_at' => $d2 . ' 09:00:00'],
            ['gr_number' => 'GR-' . $n2 . '-001', 'purchase_order_id' => $po1RowIds[1], 'warehouse_id' => 1, 'product_id' => 5, 'qty_received' => 10, 'receipt_date' => $d2, 'notes' => 'Terima PO PO-' . $n3 . '-001', 'received_by' => 3, 'created_at' => $d2 . ' 09:00:00', 'updated_at' => $d2 . ' 09:00:00'],
        ];
        foreach ($gr1Rows as $row) {
            $this->db->table('goods_receipts')->insert($row);
            $grId = (int) $this->db->insertID();
            $prod = $this->db->table('products')->where('id', $row['product_id'])->get()->getRowArray();
            $before = (int) $prod['stock_qty'];
            $after = $before + $row['qty_received'];
            $this->db->table('products')->where('id', $row['product_id'])->update(['stock_qty' => $after, 'updated_at' => $d2 . ' 09:00:00']);
            $this->db->table('stock_movements')->insert([
                'product_id' => $row['product_id'], 'warehouse_id' => 1, 'movement_type' => 'IN',
                'reference_type' => 'PURCHASE_RECEIPT', 'reference_id' => $grId, 'qty' => $row['qty_received'],
                'qty_before' => $before, 'qty_after' => $after,
                'notes' => 'Terima PO PO-' . $n3 . '-001', 'created_by' => 3, 'created_at' => $d2 . ' 09:00:00',
            ]);
        }
        // PO 2 sudah disetujui, tinggal tunggu barang masuk. PO 3 masih draf, menunggu persetujuan admin.
        $this->db->table('purchase_orders')->insertBatch([
            ['po_number' => 'PO-' . $n2 . '-001', 'supplier_id' => 2, 'warehouse_id' => 2, 'product_id' => 2, 'qty' => 3, 'unit_price' => 75000, 'order_date' => $d2, 'status' => 'approved', 'notes' => 'Pesanan contoh, menunggu barang masuk', 'created_by' => 2, 'created_at' => $d2 . ' 09:00:00', 'updated_at' => $d2 . ' 09:00:00'],
            ['po_number' => 'PO-' . $n1 . '-001', 'supplier_id' => 1, 'warehouse_id' => 1, 'product_id' => 3, 'qty' => 5, 'unit_price' => 35000, 'order_date' => $d1, 'status' => 'draft', 'notes' => 'Pesanan contoh, menunggu persetujuan admin', 'created_by' => 2, 'created_at' => $d1 . ' 09:00:00', 'updated_at' => $d1 . ' 09:00:00'],
        ]);
    }
}
