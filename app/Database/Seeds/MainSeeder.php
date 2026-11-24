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

        // -------------------------------------------------------------
        // 1. DATA MASTER GUDANG (3 Gudang dengan karakteristik berbeda)
        // -------------------------------------------------------------
        $this->db->table('warehouses')->insertBatch([
            [
                'id' => 1,
                'code' => 'GUD-01',
                'name' => 'Gudang Utama',
                'location' => 'Kantor Pusat - Surabaya',
                'is_default' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            ],
            [
                'id' => 2,
                'code' => 'GUD-02',
                'name' => 'Gudang Cabang Sidoarjo',
                'location' => 'Kawasan Industri Krian, Sidoarjo',
                'is_default' => false,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            ],
            [
                'id' => 3,
                'code' => 'GUD-03',
                'name' => 'Gudang Hub Surabaya Timur',
                'location' => 'Rungkut Industri, Surabaya',
                'is_default' => false,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
            ],
        ]);
        $this->db->query("SELECT setval('warehouses_id_seq', (SELECT MAX(id) FROM warehouses))");

        // -------------------------------------------------------------
        // 2. PERAN (ROLES) & PENGGUNA (USERS)
        // -------------------------------------------------------------
        $this->db->table('roles')->insertBatch([
            ['id' => 1, 'name' => 'admin', 'description' => 'Admin (Super Admin)', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'purchasing', 'description' => 'Staff Purchasing', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'gudang', 'description' => 'Staff Gudang', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'sales', 'description' => 'Staff Sales', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'manajemen', 'description' => 'Manajemen Eksekutif', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->query("SELECT setval('roles_id_seq', (SELECT MAX(id) FROM roles))");

        $this->db->table('users')->insertBatch([
            ['id' => 1, 'name' => 'Administrator', 'email' => 'admin@nusantara.test', 'password_hash' => password_hash('admin123', PASSWORD_DEFAULT), 'role_id' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Budi Santoso (Purchasing)', 'email' => 'purchasing@nusantara.test', 'password_hash' => password_hash('purchasing123', PASSWORD_DEFAULT), 'role_id' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Agus Prasetyo (Kepala Gudang)', 'email' => 'gudang@nusantara.test', 'password_hash' => password_hash('gudang123', PASSWORD_DEFAULT), 'role_id' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Siti Aminah (Sales)', 'email' => 'sales@nusantara.test', 'password_hash' => password_hash('sales123', PASSWORD_DEFAULT), 'role_id' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Hendra Wijaya (Direktur)', 'email' => 'manajemen@nusantara.test', 'password_hash' => password_hash('manajemen123', PASSWORD_DEFAULT), 'role_id' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->query("SELECT setval('users_id_seq', (SELECT MAX(id) FROM users))");

        // -------------------------------------------------------------
        // 3. KATEGORI (CATEGORIES)
        // -------------------------------------------------------------
        $this->db->table('categories')->insertBatch([
            ['id' => 1, 'name' => 'Alat Tulis', 'description' => 'Pulpen, pensil, spidol, penggaris, dan alat tulis lain', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Kertas & Buku', 'description' => 'Kertas HVS A4, F4, kertas foto, dan buku ekspedisi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Perlengkapan Kantor', 'description' => 'Stapler, map, amplop, klip kertas, dan perlengkapan meja', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Tinta dan Toner', 'description' => 'Tinta printer dye universal dan toner cartridge laser', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Peralatan Komputer', 'description' => 'Mouse, flashdisk, dan periferal penunjang kantor', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'Fasilitas & Kebersihan', 'description' => 'Tisu, tempat sampah, dan sarana kebersihan kantor', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->query("SELECT setval('categories_id_seq', (SELECT MAX(id) FROM categories))");

        // -------------------------------------------------------------
        // 4. PEMASOK (SUPPLIERS)
        // -------------------------------------------------------------
        $this->db->table('suppliers')->insertBatch([
            ['id' => 1, 'code' => 'SUP-001', 'name' => 'PT Sinar Jaya Stationery', 'phone' => '031-5341200', 'email' => 'order@sinarjaya.test', 'address' => 'Jl. Basuki Rahmat No. 45, Surabaya', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'SUP-002', 'name' => 'CV Makmur Abadi Kertas', 'phone' => '031-8921450', 'email' => 'sales@makmurabadi.test', 'address' => 'Kawasan Industri Krian Blok C-3, Sidoarjo', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'SUP-003', 'name' => 'PT Graha Pena Distribusi', 'phone' => '031-8287700', 'email' => 'distribusi@grahapena.test', 'address' => 'Jl. Ahmad Yani No. 88, Surabaya', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'SUP-004', 'name' => 'CV Cahaya Office Logistik', 'phone' => '0341-492100', 'email' => 'kontak@cahayaoffice.test', 'address' => 'Jl. Soekarno Hatta No. 12, Malang', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'SUP-005', 'name' => 'PT Digital Print Star', 'phone' => '021-6543210', 'email' => 'supply@digitalprintstar.test', 'address' => 'Mangga Dua Square Lt. 2, Jakarta Pusat', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'code' => 'SUP-006', 'name' => 'Toko Sentral ATK Grosir', 'phone' => '0354-682300', 'email' => 'sentralatkgrosir@test.com', 'address' => 'Jl. Dhoho No. 70, Kediri', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->query("SELECT setval('suppliers_id_seq', (SELECT MAX(id) FROM suppliers))");

        // -------------------------------------------------------------
        // 5. PELANGGAN (CUSTOMERS)
        // -------------------------------------------------------------
        $this->db->table('customers')->insertBatch([
            ['id' => 1, 'code' => 'CUS-001', 'name' => 'Toko Buku Salemba Indah', 'phone' => '081234567890', 'email' => 'salembaindah@test.com', 'address' => 'Jl. Pahlawan No. 15, Sidoarjo', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'CUS-002', 'name' => 'PT Maju Bersama Logistik', 'phone' => '081234567891', 'email' => 'procurement@majubersama.test', 'address' => 'Kawasan SIER Rungkut, Surabaya', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'CUS-003', 'name' => 'Toko Sinar Abadi ATK', 'phone' => '081234567892', 'email' => 'sinarabadiatk@test.com', 'address' => 'Jl. Hayam Wuruk No. 8, Kediri', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'CUS-004', 'name' => 'PT Berkah Perkasa Teknik', 'phone' => '081234567893', 'email' => 'admin@berkahperkasa.test', 'address' => 'Jl. Mayjen Sungkono No. 102, Surabaya', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'CUS-005', 'name' => 'CV Pustaka Mandiri Edukasi', 'phone' => '081234567894', 'email' => 'info@pustakamandiri.test', 'address' => 'Jl. Ijen No. 24, Malang', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'code' => 'CUS-006', 'name' => 'Toko Mandiri Jaya Krian', 'phone' => '081234567895', 'email' => 'mandirijaya@test.com', 'address' => 'Pasar Krian Blok A-5, Sidoarjo', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'code' => 'CUS-007', 'name' => 'PT Cakra Buana Raya', 'phone' => '081234567896', 'email' => 'purchasing@cakrabuana.test', 'address' => 'Kawasan Industri Gresik, Gresik', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'code' => 'CUS-008', 'name' => 'Koperasi Karyawan Sejahtera', 'phone' => '081234567897', 'email' => 'kopkar@sejahtera.test', 'address' => 'Jl. Kartini No. 3, Tulungagung', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->query("SELECT setval('customers_id_seq', (SELECT MAX(id) FROM customers))");

        // -------------------------------------------------------------
        // 6. PRODUK & SALDO AWAL (Dengan 5 Produk Berstatus Stok Menipis)
        // -------------------------------------------------------------
        $catalog = [
            [
                'id' => 1, 'code' => 'PRD-001', 'name' => 'Pulpen Gel Hitam 0.5mm 12pcs',
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 15000, 'selling_price' => 25000, 'min_stock' => 15,
                'stocks' => [1 => 80, 2 => 40, 3 => 45],
            ],
            [
                'id' => 2, 'code' => 'PRD-002', 'name' => 'Pulpen Gel Biru 0.5mm 12pcs',
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 15000, 'selling_price' => 25000, 'min_stock' => 10,
                'stocks' => [1 => 45, 2 => 0, 3 => 30],
            ],
            [
                'id' => 3, 'code' => 'PRD-003', 'name' => 'Pensil 2B Ujian Komputer 12pcs',
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 12000, 'selling_price' => 20000, 'min_stock' => 10,
                'stocks' => [1 => 50, 2 => 40, 3 => 0],
            ],
            [
                'id' => 4, 'code' => 'PRD-004', 'name' => 'Spidol Whiteboard Hitam 12pcs',
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 25000, 'selling_price' => 40000, 'min_stock' => 10,
                'stocks' => [1 => 40, 2 => 30, 3 => 25],
            ],
            [
                'id' => 5, 'code' => 'PRD-005', 'name' => 'Penghapus Putih Lembut 20pcs',
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 10000, 'selling_price' => 18000, 'min_stock' => 15,
                'stocks' => [1 => 30, 2 => 35, 3 => 45],
            ],
            [
                'id' => 6, 'code' => 'PRD-006', 'name' => 'Penggaris Besi Stainless 30cm',
                'category_id' => 1, 'unit' => 'pcs', 'purchase_price' => 7000, 'selling_price' => 14000, 'min_stock' => 10,
                'stocks' => [1 => 20, 2 => 10, 3 => 30],
            ],
            // [STOK MENIPIS 1]: Sisa 7 set <= Batas Minimum 10
            [
                'id' => 7, 'code' => 'PRD-007', 'name' => 'Stabilo Boss Highlighter 4 Warna',
                'category_id' => 1, 'unit' => 'set', 'purchase_price' => 32000, 'selling_price' => 52000, 'min_stock' => 10,
                'stocks' => [1 => 4, 2 => 0, 3 => 3],
            ],
            [
                'id' => 8, 'code' => 'PRD-008', 'name' => 'Kertas HVS A4 70gsm PaperOne',
                'category_id' => 2, 'unit' => 'rim', 'purchase_price' => 32000, 'selling_price' => 46000, 'min_stock' => 25,
                'stocks' => [1 => 150, 2 => 80, 3 => 100],
            ],
            [
                'id' => 9, 'code' => 'PRD-009', 'name' => 'Kertas HVS A4 80gsm SiDU',
                'category_id' => 2, 'unit' => 'rim', 'purchase_price' => 36000, 'selling_price' => 52000, 'min_stock' => 25,
                'stocks' => [1 => 120, 2 => 80, 3 => 90],
            ],
            [
                'id' => 10, 'code' => 'PRD-010', 'name' => 'Kertas HVS F4/Folio 70gsm',
                'category_id' => 2, 'unit' => 'rim', 'purchase_price' => 35000, 'selling_price' => 50000, 'min_stock' => 20,
                'stocks' => [1 => 70, 2 => 35, 3 => 0],
            ],
            // [STOK MENIPIS 2]: Sisa 6 pack <= Batas Minimum 10
            [
                'id' => 11, 'code' => 'PRD-011', 'name' => 'Kertas Foto Glossy A4 20 Lembar',
                'category_id' => 2, 'unit' => 'pack', 'purchase_price' => 28000, 'selling_price' => 45000, 'min_stock' => 10,
                'stocks' => [1 => 6, 2 => 0, 3 => 0],
            ],
            [
                'id' => 12, 'code' => 'PRD-012', 'name' => 'Buku Kas / Ekspedisi Hardcover',
                'category_id' => 2, 'unit' => 'buku', 'purchase_price' => 18000, 'selling_price' => 30000, 'min_stock' => 10,
                'stocks' => [1 => 25, 2 => 10, 3 => 35],
            ],
            [
                'id' => 13, 'code' => 'PRD-013', 'name' => 'Stapler Besar HD-50 Kangaro',
                'category_id' => 3, 'unit' => 'pcs', 'purchase_price' => 45000, 'selling_price' => 75000, 'min_stock' => 5,
                'stocks' => [1 => 10, 2 => 30, 3 => 5],
            ],
            [
                'id' => 14, 'code' => 'PRD-014', 'name' => 'Isi Staples No. 10 (Box isi 20)',
                'category_id' => 3, 'unit' => 'box', 'purchase_price' => 14000, 'selling_price' => 24000, 'min_stock' => 15,
                'stocks' => [1 => 60, 2 => 25, 3 => 0],
            ],
            [
                'id' => 15, 'code' => 'PRD-015', 'name' => 'Map Plastik Zipper A4 12pcs',
                'category_id' => 3, 'unit' => 'pack', 'purchase_price' => 22000, 'selling_price' => 38000, 'min_stock' => 15,
                'stocks' => [1 => 80, 2 => 40, 3 => 60],
            ],
            [
                'id' => 16, 'code' => 'PRD-016', 'name' => 'Amplop Coklat Tali F4 50pcs',
                'category_id' => 3, 'unit' => 'pack', 'purchase_price' => 32000, 'selling_price' => 55000, 'min_stock' => 10,
                'stocks' => [1 => 40, 2 => 20, 3 => 0],
            ],
            [
                'id' => 17, 'code' => 'PRD-017', 'name' => 'Lakban Bening 48mm x 100 Yard',
                'category_id' => 3, 'unit' => 'roll', 'purchase_price' => 8500, 'selling_price' => 15000, 'min_stock' => 20,
                'stocks' => [1 => 100, 2 => 60, 3 => 60],
            ],
            [
                'id' => 18, 'code' => 'PRD-018', 'name' => 'Klip Kertas Paper Clip No. 3',
                'category_id' => 3, 'unit' => 'pak', 'purchase_price' => 16000, 'selling_price' => 26000, 'min_stock' => 15,
                'stocks' => [1 => 20, 2 => 50, 3 => 30],
            ],
            [
                'id' => 19, 'code' => 'PRD-019', 'name' => 'Gunting Kantor Stainless 21cm',
                'category_id' => 3, 'unit' => 'pcs', 'purchase_price' => 18000, 'selling_price' => 32000, 'min_stock' => 5,
                'stocks' => [1 => 15, 2 => 35, 3 => 10],
            ],
            // [STOK KRITIS 3]: Sisa 1 pcs <= Batas Minimum 3 (Perlu Restock Segera)
            [
                'id' => 20, 'code' => 'PRD-020', 'name' => 'Papan Tulis Whiteboard 60x90cm',
                'category_id' => 3, 'unit' => 'pcs', 'purchase_price' => 175000, 'selling_price' => 285000, 'min_stock' => 3,
                'stocks' => [1 => 1, 2 => 0, 3 => 0],
            ],
            [
                'id' => 21, 'code' => 'PRD-021', 'name' => 'Tinta Printer Hitam 100ml Universal',
                'category_id' => 4, 'unit' => 'botol', 'purchase_price' => 65000, 'selling_price' => 110000, 'min_stock' => 10,
                'stocks' => [1 => 25, 2 => 0, 3 => 15],
            ],
            // [STOK MENIPIS 4]: Sisa 4 set <= Batas Minimum 8
            [
                'id' => 22, 'code' => 'PRD-022', 'name' => 'Tinta Printer Warna (CMY) 100ml',
                'category_id' => 4, 'unit' => 'set', 'purchase_price' => 85000, 'selling_price' => 145000, 'min_stock' => 8,
                'stocks' => [1 => 2, 2 => 0, 3 => 2],
            ],
            // [STOK MENIPIS 5]: Sisa 2 pcs <= Batas Minimum 3
            [
                'id' => 23, 'code' => 'PRD-023', 'name' => 'Toner Cartridge Laser Hitam HP 85A',
                'category_id' => 4, 'unit' => 'pcs', 'purchase_price' => 260000, 'selling_price' => 395000, 'min_stock' => 3,
                'stocks' => [1 => 2, 2 => 0, 3 => 0],
            ],
            [
                'id' => 24, 'code' => 'PRD-024', 'name' => 'Mouse USB Optik Logitech B100',
                'category_id' => 5, 'unit' => 'pcs', 'purchase_price' => 58000, 'selling_price' => 95000, 'min_stock' => 8,
                'stocks' => [1 => 25, 2 => 0, 3 => 20],
            ],
        ];

        // Masukkan data produk & catat saldo awal per gudang dengan timestamp realistis
        $initialMovementDate = date('Y-m-d H:i:s', strtotime('-12 days 08:30:00'));

        foreach ($catalog as $item) {
            $totalStock = array_sum($item['stocks']);
            $this->db->table('products')->insert([
                'id' => $item['id'],
                'code' => $item['code'],
                'name' => $item['name'],
                'category_id' => $item['category_id'],
                'unit' => $item['unit'],
                'purchase_price' => $item['purchase_price'],
                'selling_price' => $item['selling_price'],
                'stock_qty' => $totalStock,
                'min_stock' => $item['min_stock'],
                'is_active' => true,
                'created_at' => $initialMovementDate,
                'updated_at' => $initialMovementDate,
            ]);

            foreach ($item['stocks'] as $whId => $qty) {
                if ($qty > 0) {
                    $this->db->table('stock_movements')->insert([
                        'product_id' => $item['id'],
                        'warehouse_id' => $whId,
                        'movement_type' => 'ADJUSTMENT',
                        'reference_type' => 'STOCK_AWAL',
                        'reference_id' => 0,
                        'qty' => $qty,
                        'qty_before' => 0,
                        'qty_after' => $qty,
                        'notes' => 'Saldo awal migrasi fisik produk baru',
                        'created_by' => 1,
                        'created_at' => $initialMovementDate,
                    ]);
                }
            }
        }
        $this->db->query("SELECT setval('products_id_seq', (SELECT MAX(id) FROM products))");

        // -------------------------------------------------------------
        // 7. TRANSAKSI REALISTIS: PESANAN PEMBELIAN & PENERIMAAN BARANG
        // -------------------------------------------------------------
        $d8 = date('Y-m-d', strtotime('-8 days'));
        $d7 = date('Y-m-d', strtotime('-7 days'));
        $d6 = date('Y-m-d', strtotime('-6 days'));
        $d5 = date('Y-m-d', strtotime('-5 days'));
        $d4 = date('Y-m-d', strtotime('-4 days'));
        $d3 = date('Y-m-d', strtotime('-3 days'));
        $d2 = date('Y-m-d', strtotime('-2 days'));
        $d1 = date('Y-m-d', strtotime('-1 day'));

        $n8 = date('Ymd', strtotime('-8 days'));
        $n7 = date('Ymd', strtotime('-7 days'));
        $n6 = date('Ymd', strtotime('-6 days'));
        $n5 = date('Ymd', strtotime('-5 days'));
        $n4 = date('Ymd', strtotime('-4 days'));
        $n3 = date('Ymd', strtotime('-3 days'));
        $n2 = date('Ymd', strtotime('-2 days'));
        $n1 = date('Ymd', strtotime('-1 day'));

        // PO 1: Diterima Penuh di GUDANG UTAMA (Pulpen Hitam x20 & Kertas A4 PaperOne x30)
        $po1Num = 'PO-' . $n8 . '-001';
        $this->db->table('purchase_orders')->insertBatch([
            ['po_number' => $po1Num, 'supplier_id' => 1, 'warehouse_id' => 1, 'product_id' => 1, 'qty' => 20, 'unit_price' => 15000, 'order_date' => $d8, 'status' => 'received', 'notes' => 'Pengadaan rutin kantor pusat', 'created_by' => 2, 'created_at' => $d8 . ' 09:15:00', 'updated_at' => $d7 . ' 10:30:00'],
            ['po_number' => $po1Num, 'supplier_id' => 1, 'warehouse_id' => 1, 'product_id' => 8, 'qty' => 30, 'unit_price' => 32000, 'order_date' => $d8, 'status' => 'received', 'notes' => 'Pengadaan rutin kantor pusat', 'created_by' => 2, 'created_at' => $d8 . ' 09:15:00', 'updated_at' => $d7 . ' 10:30:00'],
        ]);
        $po1Items = $this->db->table('purchase_orders')->where('po_number', $po1Num)->get()->getResultArray();
        $gr1Num = 'GR-' . date('Ymd', strtotime('-7 days')) . '-001';
        foreach ($po1Items as $item) {
            $this->db->table('goods_receipts')->insert([
                'gr_number' => $gr1Num, 'purchase_order_id' => $item['id'], 'warehouse_id' => 1,
                'product_id' => $item['product_id'], 'qty_received' => $item['qty'], 'receipt_date' => $d7,
                'notes' => 'Penerimaan PO ' . $po1Num . ' lengkap dan baik', 'received_by' => 3,
                'created_at' => $d7 . ' 10:30:00', 'updated_at' => $d7 . ' 10:30:00',
            ]);
            $grId = $this->db->insertID();
            $this->applyStockIn($item['product_id'], 1, $item['qty'], 'PURCHASE_RECEIPT', $grId, 'Terima barang PO ' . $po1Num, 3, $d7 . ' 10:30:00');
        }

        // PO 2: Diterima Penuh di GUDANG CABANG SIDOARJO (Kertas A4 SiDU x25 & Stapler Kangaro x10)
        $po2Num = 'PO-' . $n6 . '-001';
        $this->db->table('purchase_orders')->insertBatch([
            ['po_number' => $po2Num, 'supplier_id' => 2, 'warehouse_id' => 2, 'product_id' => 9, 'qty' => 25, 'unit_price' => 36000, 'order_date' => $d6, 'status' => 'received', 'notes' => 'Pasokan kebutuhan cabang Sidoarjo', 'created_by' => 2, 'created_at' => $d6 . ' 11:00:00', 'updated_at' => $d5 . ' 14:15:00'],
            ['po_number' => $po2Num, 'supplier_id' => 2, 'warehouse_id' => 2, 'product_id' => 13, 'qty' => 10, 'unit_price' => 45000, 'order_date' => $d6, 'status' => 'received', 'notes' => 'Pasokan kebutuhan cabang Sidoarjo', 'created_by' => 2, 'created_at' => $d6 . ' 11:00:00', 'updated_at' => $d5 . ' 14:15:00'],
        ]);
        $po2Items = $this->db->table('purchase_orders')->where('po_number', $po2Num)->get()->getResultArray();
        $gr2Num = 'GR-' . date('Ymd', strtotime('-5 days')) . '-001';
        foreach ($po2Items as $item) {
            $this->db->table('goods_receipts')->insert([
                'gr_number' => $gr2Num, 'purchase_order_id' => $item['id'], 'warehouse_id' => 2,
                'product_id' => $item['product_id'], 'qty_received' => $item['qty'], 'receipt_date' => $d5,
                'notes' => 'Penerimaan di Gudang Sidoarjo via truk supplier', 'received_by' => 3,
                'created_at' => $d5 . ' 14:15:00', 'updated_at' => $d5 . ' 14:15:00',
            ]);
            $grId = $this->db->insertID();
            $this->applyStockIn($item['product_id'], 2, $item['qty'], 'PURCHASE_RECEIPT', $grId, 'Terima barang PO ' . $po2Num, 3, $d5 . ' 14:15:00');
        }

        // PO 3: Status APPROVED (Menunggu Barang Datang di Gudang Surabaya Timur)
        $po3Num = 'PO-' . $n3 . '-001';
        $this->db->table('purchase_orders')->insertBatch([
            ['po_number' => $po3Num, 'supplier_id' => 5, 'warehouse_id' => 3, 'product_id' => 21, 'qty' => 15, 'unit_price' => 65000, 'order_date' => $d3, 'status' => 'approved', 'notes' => 'Restock tinta printer hub timur', 'created_by' => 2, 'created_at' => $d3 . ' 13:00:00', 'updated_at' => $d2 . ' 08:30:00'],
            ['po_number' => $po3Num, 'supplier_id' => 5, 'warehouse_id' => 3, 'product_id' => 24, 'qty' => 10, 'unit_price' => 58000, 'order_date' => $d3, 'status' => 'approved', 'notes' => 'Restock mouse kantor', 'created_by' => 2, 'created_at' => $d3 . ' 13:00:00', 'updated_at' => $d2 . ' 08:30:00'],
        ]);

        // PO 4: Status DRAFT (Menunggu Persetujuan Admin / Manajemen)
        $po4Num = 'PO-' . $n1 . '-001';
        $this->db->table('purchase_orders')->insertBatch([
            ['po_number' => $po4Num, 'supplier_id' => 3, 'warehouse_id' => 1, 'product_id' => 20, 'qty' => 5, 'unit_price' => 175000, 'order_date' => $d1, 'status' => 'draft', 'notes' => 'Rencana restock whiteboard besar kantor pusat', 'created_by' => 2, 'created_at' => $d1 . ' 15:45:00', 'updated_at' => $d1 . ' 15:45:00'],
            ['po_number' => $po4Num, 'supplier_id' => 3, 'warehouse_id' => 1, 'product_id' => 23, 'qty' => 4, 'unit_price' => 260000, 'order_date' => $d1, 'status' => 'draft', 'notes' => 'Rencana restock toner HP laser', 'created_by' => 2, 'created_at' => $d1 . ' 15:45:00', 'updated_at' => $d1 . ' 15:45:00'],
        ]);

        // PO 5: Status CANCELLED (Pembatalan Pesanan dengan Alasan Audit)
        $po5Num = 'PO-' . $n8 . '-002';
        $this->db->table('purchase_orders')->insertBatch([
            ['po_number' => $po5Num, 'supplier_id' => 6, 'warehouse_id' => 1, 'product_id' => 6, 'qty' => 50, 'unit_price' => 7000, 'order_date' => $d8, 'status' => 'cancelled', 'notes' => 'Dibatalkan oleh staf purchasing: Supplier Sentral ATK kehabisan stok fisik merek yang diminta.', 'created_by' => 2, 'created_at' => $d8 . ' 16:00:00', 'updated_at' => $d7 . ' 09:00:00'],
        ]);

        // -------------------------------------------------------------
        // 8. TRANSAKSI REALISTIS: PESANAN PENJUALAN & PRODUK TERLARIS
        // -------------------------------------------------------------
        // SO 1: Terkirim Penuh dari GUDANG UTAMA (Pelanggan: PT Maju Bersama Logistik)
        // Penjualan besar Kertas A4 (50 rim) & Pulpen Hitam (40 pack)
        $so1Num = 'SO-' . $n6 . '-001';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so1Num, 'customer_id' => 2, 'warehouse_id' => 1, 'product_id' => 8, 'qty' => 50, 'unit_price' => 46000, 'order_date' => $d6, 'status' => 'delivered', 'notes' => 'Pengadaan kertas kuartal akhir divisi operasional', 'created_by' => 4, 'created_at' => $d6 . ' 09:30:00', 'updated_at' => $d5 . ' 10:00:00'],
            ['so_number' => $so1Num, 'customer_id' => 2, 'warehouse_id' => 1, 'product_id' => 1, 'qty' => 40, 'unit_price' => 25000, 'order_date' => $d6, 'status' => 'delivered', 'notes' => 'Pengadaan pulpen kantor', 'created_by' => 4, 'created_at' => $d6 . ' 09:30:00', 'updated_at' => $d5 . ' 10:00:00'],
        ]);
        $so1Items = $this->db->table('sales_orders')->where('so_number', $so1Num)->get()->getResultArray();
        $do1Num = 'DO-' . date('Ymd', strtotime('-5 days')) . '-001';
        foreach ($so1Items as $item) {
            $this->db->table('deliveries')->insert([
                'delivery_number' => $do1Num, 'sales_order_id' => $item['id'], 'warehouse_id' => 1,
                'product_id' => $item['product_id'], 'qty_delivered' => $item['qty'], 'delivery_date' => $d5,
                'notes' => 'Diterima oleh Pak Slamet (Logistik SIER Surabaya)', 'delivered_by' => 3,
                'created_at' => $d5 . ' 10:00:00', 'updated_at' => $d5 . ' 10:00:00',
            ]);
            $doId = $this->db->insertID();
            $this->applyStockOut($item['product_id'], 1, $item['qty'], 'SALES_DELIVERY', $doId, 'Kirim pesanan SO ' . $so1Num, 3, $d5 . ' 10:00:00');
        }

        // SO 2: Terkirim Penuh dari GUDANG CABANG SIDOARJO (Pelanggan: Toko Buku Salemba Indah)
        // Penjualan Kertas SiDU (35 rim), Lakban Bening (30 roll), Spidol Whiteboard (20 pack)
        $so2Num = 'SO-' . $n5 . '-001';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so2Num, 'customer_id' => 1, 'warehouse_id' => 2, 'product_id' => 9, 'qty' => 35, 'unit_price' => 52000, 'order_date' => $d5, 'status' => 'delivered', 'notes' => 'Pesanan mingguan toko buku Sidoarjo', 'created_by' => 4, 'created_at' => $d5 . ' 11:00:00', 'updated_at' => $d4 . ' 13:30:00'],
            ['so_number' => $so2Num, 'customer_id' => 1, 'warehouse_id' => 2, 'product_id' => 17, 'qty' => 30, 'unit_price' => 15000, 'order_date' => $d5, 'status' => 'delivered', 'notes' => 'Pesanan lakban packing toko', 'created_by' => 4, 'created_at' => $d5 . ' 11:00:00', 'updated_at' => $d4 . ' 13:30:00'],
            ['so_number' => $so2Num, 'customer_id' => 1, 'warehouse_id' => 2, 'product_id' => 4, 'qty' => 20, 'unit_price' => 40000, 'order_date' => $d5, 'status' => 'delivered', 'notes' => 'Spidol display toko', 'created_by' => 4, 'created_at' => $d5 . ' 11:00:00', 'updated_at' => $d4 . ' 13:30:00'],
        ]);
        $so2Items = $this->db->table('sales_orders')->where('so_number', $so2Num)->get()->getResultArray();
        $do2Num = 'DO-' . date('Ymd', strtotime('-4 days')) . '-001';
        foreach ($so2Items as $item) {
            $this->db->table('deliveries')->insert([
                'delivery_number' => $do2Num, 'sales_order_id' => $item['id'], 'warehouse_id' => 2,
                'product_id' => $item['product_id'], 'qty_delivered' => $item['qty'], 'delivery_date' => $d4,
                'notes' => 'Diambil langsung menggunakan pick-up toko Salemba', 'delivered_by' => 3,
                'created_at' => $d4 . ' 13:30:00', 'updated_at' => $d4 . ' 13:30:00',
            ]);
            $doId = $this->db->insertID();
            $this->applyStockOut($item['product_id'], 2, $item['qty'], 'SALES_DELIVERY', $doId, 'Kirim pesanan SO ' . $so2Num, 3, $d4 . ' 13:30:00');
        }

        // SO 3: Terkirim Penuh dari GUDANG HUB SURABAYA TIMUR (Pelanggan: PT Berkah Perkasa Teknik)
        // Penjualan Map Plastik (25 pack), Klip Kertas (20 pak), Mouse USB (12 pcs)
        $so3Num = 'SO-' . $n4 . '-001';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so3Num, 'customer_id' => 4, 'warehouse_id' => 3, 'product_id' => 15, 'qty' => 25, 'unit_price' => 38000, 'order_date' => $d4, 'status' => 'delivered', 'notes' => 'Perlengkapan tender proyek', 'created_by' => 4, 'created_at' => $d4 . ' 10:15:00', 'updated_at' => $d3 . ' 11:00:00'],
            ['so_number' => $so3Num, 'customer_id' => 4, 'warehouse_id' => 3, 'product_id' => 18, 'qty' => 20, 'unit_price' => 26000, 'order_date' => $d4, 'status' => 'delivered', 'notes' => 'Klip kertas berkas', 'created_by' => 4, 'created_at' => $d4 . ' 10:15:00', 'updated_at' => $d3 . ' 11:00:00'],
            ['so_number' => $so3Num, 'customer_id' => 4, 'warehouse_id' => 3, 'product_id' => 24, 'qty' => 12, 'unit_price' => 95000, 'order_date' => $d4, 'status' => 'delivered', 'notes' => 'Peremajaan periferal laptop staf lapangan', 'created_by' => 4, 'created_at' => $d4 . ' 10:15:00', 'updated_at' => $d3 . ' 11:00:00'],
        ]);
        $so3Items = $this->db->table('sales_orders')->where('so_number', $so3Num)->get()->getResultArray();
        $do3Num = 'DO-' . date('Ymd', strtotime('-3 days')) . '-001';
        foreach ($so3Items as $item) {
            $this->db->table('deliveries')->insert([
                'delivery_number' => $do3Num, 'sales_order_id' => $item['id'], 'warehouse_id' => 3,
                'product_id' => $item['product_id'], 'qty_delivered' => $item['qty'], 'delivery_date' => $d3,
                'notes' => 'Dikirim kurir ekspedisi internal ke Mayjen Sungkono', 'delivered_by' => 3,
                'created_at' => $d3 . ' 11:00:00', 'updated_at' => $d3 . ' 11:00:00',
            ]);
            $doId = $this->db->insertID();
            $this->applyStockOut($item['product_id'], 3, $item['qty'], 'SALES_DELIVERY', $doId, 'Kirim pesanan SO ' . $so3Num, 3, $d3 . ' 11:00:00');
        }

        // SO 4: Terkirim Penuh dari GUDANG UTAMA (Pelanggan: PT Cakra Buana Raya)
        // Penjualan Kertas A4 (40 rim) & Kertas F4 Folio (25 rim)
        $so4Num = 'SO-' . $n3 . '-001';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so4Num, 'customer_id' => 7, 'warehouse_id' => 1, 'product_id' => 8, 'qty' => 40, 'unit_price' => 46000, 'order_date' => $d3, 'status' => 'delivered', 'notes' => 'Pengadaan kertas pabrik Gresik', 'created_by' => 4, 'created_at' => $d3 . ' 13:45:00', 'updated_at' => $d2 . ' 14:20:00'],
            ['so_number' => $so4Num, 'customer_id' => 7, 'warehouse_id' => 1, 'product_id' => 10, 'qty' => 25, 'unit_price' => 50000, 'order_date' => $d3, 'status' => 'delivered', 'notes' => 'Kertas folio dokumen legal', 'created_by' => 4, 'created_at' => $d3 . ' 13:45:00', 'updated_at' => $d2 . ' 14:20:00'],
        ]);
        $so4Items = $this->db->table('sales_orders')->where('so_number', $so4Num)->get()->getResultArray();
        $do4Num = 'DO-' . date('Ymd', strtotime('-2 days')) . '-001';
        foreach ($so4Items as $item) {
            $this->db->table('deliveries')->insert([
                'delivery_number' => $do4Num, 'sales_order_id' => $item['id'], 'warehouse_id' => 1,
                'product_id' => $item['product_id'], 'qty_delivered' => $item['qty'], 'delivery_date' => $d2,
                'notes' => 'Dikirim armada pick-up kantor ke Kawasan Industri Gresik', 'delivered_by' => 3,
                'created_at' => $d2 . ' 14:20:00', 'updated_at' => $d2 . ' 14:20:00',
            ]);
            $doId = $this->db->insertID();
            $this->applyStockOut($item['product_id'], 1, $item['qty'], 'SALES_DELIVERY', $doId, 'Kirim pesanan SO ' . $so4Num, 3, $d2 . ' 14:20:00');
        }

        // SO 5: Terkirim Penuh dari GUDANG CABANG SIDOARJO (Pelanggan: CV Pustaka Mandiri Edukasi)
        // Penjualan Pulpen Hitam (30 pack), Pensil 2B (25 pack), Penghapus (20 pack)
        $so5Num = 'SO-' . $n2 . '-001';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so5Num, 'customer_id' => 5, 'warehouse_id' => 2, 'product_id' => 1, 'qty' => 30, 'unit_price' => 25000, 'order_date' => $d2, 'status' => 'delivered', 'notes' => 'Kebutuhan paket ujian bimbingan belajar', 'created_by' => 4, 'created_at' => $d2 . ' 08:30:00', 'updated_at' => $d1 . ' 09:45:00'],
            ['so_number' => $so5Num, 'customer_id' => 5, 'warehouse_id' => 2, 'product_id' => 3, 'qty' => 25, 'unit_price' => 20000, 'order_date' => $d2, 'status' => 'delivered', 'notes' => 'Pensil 2B ujian', 'created_by' => 4, 'created_at' => $d2 . ' 08:30:00', 'updated_at' => $d1 . ' 09:45:00'],
            ['so_number' => $so5Num, 'customer_id' => 5, 'warehouse_id' => 2, 'product_id' => 5, 'qty' => 20, 'unit_price' => 18000, 'order_date' => $d2, 'status' => 'delivered', 'notes' => 'Penghapus ujian', 'created_by' => 4, 'created_at' => $d2 . ' 08:30:00', 'updated_at' => $d1 . ' 09:45:00'],
        ]);
        $so5Items = $this->db->table('sales_orders')->where('so_number', $so5Num)->get()->getResultArray();
        $do5Num = 'DO-' . date('Ymd', strtotime('-1 day')) . '-001';
        foreach ($so5Items as $item) {
            $this->db->table('deliveries')->insert([
                'delivery_number' => $do5Num, 'sales_order_id' => $item['id'], 'warehouse_id' => 2,
                'product_id' => $item['product_id'], 'qty_delivered' => $item['qty'], 'delivery_date' => $d1,
                'notes' => 'Diterima staf logistik Pustaka Mandiri Malang', 'delivered_by' => 3,
                'created_at' => $d1 . ' 09:45:00', 'updated_at' => $d1 . ' 09:45:00',
            ]);
            $doId = $this->db->insertID();
            $this->applyStockOut($item['product_id'], 2, $item['qty'], 'SALES_DELIVERY', $doId, 'Kirim pesanan SO ' . $so5Num, 3, $d1 . ' 09:45:00');
        }

        // SO 6: Status CONFIRMED (Siap Kirim dari Gudang Sidoarjo)
        // Lakban Bening (25 roll) & Stapler Kangaro (10 pcs)
        $so6Num = 'SO-' . $n1 . '-001';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so6Num, 'customer_id' => 6, 'warehouse_id' => 2, 'product_id' => 17, 'qty' => 25, 'unit_price' => 15000, 'order_date' => $d1, 'status' => 'confirmed', 'notes' => 'Toko Mandiri Jaya Krian - Menunggu jadwal pickup', 'created_by' => 4, 'created_at' => $d1 . ' 10:30:00', 'updated_at' => $d1 . ' 11:15:00'],
            ['so_number' => $so6Num, 'customer_id' => 6, 'warehouse_id' => 2, 'product_id' => 13, 'qty' => 10, 'unit_price' => 75000, 'order_date' => $d1, 'status' => 'confirmed', 'notes' => 'Stapler besar kebutuhan toko', 'created_by' => 4, 'created_at' => $d1 . ' 10:30:00', 'updated_at' => $d1 . ' 11:15:00'],
        ]);

        // SO 7: Status CONFIRMED (Siap Kirim dari Hub Surabaya Timur)
        // Kertas SiDU (15 rim) & Pulpen Biru (20 pack)
        $so7Num = 'SO-' . $n1 . '-002';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so7Num, 'customer_id' => 3, 'warehouse_id' => 3, 'product_id' => 9, 'qty' => 15, 'unit_price' => 52000, 'order_date' => $d1, 'status' => 'confirmed', 'notes' => 'Toko Sinar Abadi Kediri - Siap dikirim kargo', 'created_by' => 4, 'created_at' => $d1 . ' 14:00:00', 'updated_at' => $d1 . ' 15:00:00'],
            ['so_number' => $so7Num, 'customer_id' => 3, 'warehouse_id' => 3, 'product_id' => 2, 'qty' => 20, 'unit_price' => 25000, 'order_date' => $d1, 'status' => 'confirmed', 'notes' => 'Pulpen biru pesanan toko', 'created_by' => 4, 'created_at' => $d1 . ' 14:00:00', 'updated_at' => $d1 . ' 15:00:00'],
        ]);

        // SO 8: Status DRAFT (Draft Order Hari Ini)
        $so8Num = 'SO-' . date('Ymd') . '-001';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so8Num, 'customer_id' => 8, 'warehouse_id' => 1, 'product_id' => 12, 'qty' => 15, 'unit_price' => 30000, 'order_date' => date('Y-m-d'), 'status' => 'draft', 'notes' => 'Pesanan buku kas koperasi tahunan', 'created_by' => 4, 'created_at' => date('Y-m-d 08:30:00'), 'updated_at' => date('Y-m-d 08:30:00')],
            ['so_number' => $so8Num, 'customer_id' => 8, 'warehouse_id' => 1, 'product_id' => 14, 'qty' => 20, 'unit_price' => 24000, 'order_date' => date('Y-m-d'), 'status' => 'draft', 'notes' => 'Isi staples', 'created_by' => 4, 'created_at' => date('Y-m-d 08:30:00'), 'updated_at' => date('Y-m-d 08:30:00')],
            ['so_number' => $so8Num, 'customer_id' => 8, 'warehouse_id' => 1, 'product_id' => 19, 'qty' => 10, 'unit_price' => 32000, 'order_date' => date('Y-m-d'), 'status' => 'draft', 'notes' => 'Gunting kantor', 'created_by' => 4, 'created_at' => date('Y-m-d 08:30:00'), 'updated_at' => date('Y-m-d 08:30:00')],
        ]);

        // SO 9: Status CANCELLED (Pembatalan Pesanan dengan Alasan Audit)
        $so9Num = 'SO-' . $n4 . '-002';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so9Num, 'customer_id' => 2, 'warehouse_id' => 1, 'product_id' => 6, 'qty' => 30, 'unit_price' => 14000, 'order_date' => $d4, 'status' => 'cancelled', 'notes' => 'Dibatalkan oleh pelanggan: PT Maju Bersama membatalkan pesanan penggaris karena alokasi anggaran dialihkan ke kertas.', 'created_by' => 4, 'created_at' => $d4 . ' 16:30:00', 'updated_at' => $d3 . ' 08:15:00'],
        ]);

        // -------------------------------------------------------------
        // 9. PENYESUAIAN STOK OPERASIONAL (STOCK ADJUSTMENT)
        // -------------------------------------------------------------
        // Koreksi 1: Selisih minus karena lakban rusak kemasan saat bongkar di Gudang Sidoarjo
        $adjDate1 = date('Y-m-d H:i:s', strtotime('-4 days 16:20:00'));
        $this->applyStockAdjustment(17, 2, -2, 'Koreksi stok: 2 roll lakban robek/rusak terkena benda tajam saat bongkar muat', 3, $adjDate1);

        // Koreksi 2: Selisih lebih hasil stock opname fisik di Gudang Surabaya Timur
        $adjDate2 = date('Y-m-d H:i:s', strtotime('-2 days 17:00:00'));
        $this->applyStockAdjustment(5, 3, 5, 'Koreksi stock opname: Ditemukan 1 pack isi 5pcs belum tercatat di kartu stok', 3, $adjDate2);

        // -------------------------------------------------------------
        // 10. REKONSILIASI AKHIR SALDO PRODUK (STOCK RECONCILIATION)
        // -------------------------------------------------------------
        // Pastikan kolom products.stock_qty sama persis dengan total saldo fisik dari semua gudang
        $allProducts = $this->db->table('products')->get()->getResultArray();
        foreach ($allProducts as $p) {
            $row = $this->db->query('SELECT COALESCE(SUM(qa), 0) AS total FROM (SELECT DISTINCT ON (warehouse_id) qty_after AS qa FROM stock_movements WHERE product_id = ? ORDER BY warehouse_id, created_at DESC, id DESC) s', [$p['id']])->getRowArray();
            $realTotal = (int) ($row['total'] ?? 0);
            $this->db->table('products')->where('id', $p['id'])->update(['stock_qty' => $realTotal]);
        }
    }

    private function applyStockIn(int $productId, int $warehouseId, int $qty, string $refType, int $refId, string $notes, int $userId, string $timestamp): void
    {
        $lastMovement = $this->db->query('SELECT qty_after FROM stock_movements WHERE product_id = ? AND warehouse_id = ? ORDER BY created_at DESC, id DESC LIMIT 1', [$productId, $warehouseId])->getRowArray();
        $before = $lastMovement ? (int) $lastMovement['qty_after'] : 0;
        $after = $before + $qty;

        $this->db->table('stock_movements')->insert([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => 'IN',
            'reference_type' => $refType,
            'reference_id' => $refId,
            'qty' => $qty,
            'qty_before' => $before,
            'qty_after' => $after,
            'notes' => $notes,
            'created_by' => $userId,
            'created_at' => $timestamp,
        ]);
    }

    private function applyStockOut(int $productId, int $warehouseId, int $qty, string $refType, int $refId, string $notes, int $userId, string $timestamp): void
    {
        $lastMovement = $this->db->query('SELECT qty_after FROM stock_movements WHERE product_id = ? AND warehouse_id = ? ORDER BY created_at DESC, id DESC LIMIT 1', [$productId, $warehouseId])->getRowArray();
        $before = $lastMovement ? (int) $lastMovement['qty_after'] : 0;
        $after = max(0, $before - $qty);

        $this->db->table('stock_movements')->insert([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => 'OUT',
            'reference_type' => $refType,
            'reference_id' => $refId,
            'qty' => $qty,
            'qty_before' => $before,
            'qty_after' => $after,
            'notes' => $notes,
            'created_by' => $userId,
            'created_at' => $timestamp,
        ]);
    }

    private function applyStockAdjustment(int $productId, int $warehouseId, int $delta, string $notes, int $userId, string $timestamp): void
    {
        $lastMovement = $this->db->query('SELECT qty_after FROM stock_movements WHERE product_id = ? AND warehouse_id = ? ORDER BY created_at DESC, id DESC LIMIT 1', [$productId, $warehouseId])->getRowArray();
        $before = $lastMovement ? (int) $lastMovement['qty_after'] : 0;
        $after = max(0, $before + $delta);

        $this->db->table('stock_movements')->insert([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => 'ADJUSTMENT',
            'reference_type' => 'MANUAL_ADJUSTMENT',
            'reference_id' => 0,
            'qty' => abs($delta),
            'qty_before' => $before,
            'qty_after' => $after,
            'notes' => $notes,
            'created_by' => $userId,
            'created_at' => $timestamp,
        ]);
    }
}
