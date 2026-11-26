<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        $tables = [
            'restock_alerts', 'stock_movements', 'deliveries', 'sales_orders', 'goods_receipts',
            'purchase_orders', 'products', 'units', 'customers', 'suppliers',
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
        ]);
        $this->db->query("SELECT setval('categories_id_seq', (SELECT MAX(id) FROM categories))");

        // -------------------------------------------------------------
        // 3B. SATUAN PRODUK (UNITS)
        // -------------------------------------------------------------
        $this->db->table('units')->insertBatch([
            ['id' => 1, 'code' => 'pcs', 'name' => 'Pieces (Buah)', 'description' => 'Satuan hitung barang tunggal / eceran', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'rim', 'name' => 'Rim (500 Lembar)', 'description' => 'Satuan kertas cetak dan fotokopi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'box', 'name' => 'Box (Kardus / Dus)', 'description' => 'Satuan kemasan kotak kardus', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'pack', 'name' => 'Pack (Paket / Pak)', 'description' => 'Kemasan bungkusan isi beberapa unit', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'set', 'name' => 'Set (Paket Lengkap)', 'description' => 'Gabungan beberapa barang dalam 1 set', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'code' => 'roll', 'name' => 'Roll (Gulungan)', 'description' => 'Satuan lakban, tali, pita gulung', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'code' => 'buku', 'name' => 'Buku', 'description' => 'Satuan buku tulis / ekspedisi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'code' => 'botol', 'name' => 'Botol', 'description' => 'Kemasan botol cairan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'code' => 'kg', 'name' => 'Kilogram (Kg)', 'description' => 'Satuan berat / bobot barang', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'code' => 'liter', 'name' => 'Liter (Ltr)', 'description' => 'Satuan volume cairan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'code' => 'meter', 'name' => 'Meter (M)', 'description' => 'Satuan panjang / kabel', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'code' => 'lusin', 'name' => 'Lusin (12 Pcs)', 'description' => 'Satuan grosir 12 unit', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->db->query("SELECT setval('units_id_seq', (SELECT MAX(id) FROM units))");

        // -------------------------------------------------------------
        // 4. PEMASOK (SUPPLIERS)
        // -------------------------------------------------------------
        $this->db->table('suppliers')->insertBatch([
            ['id' => 1, 'code' => 'SUP-001', 'name' => 'PT Sinar Jaya Stationery', 'phone' => '031-5341200', 'email' => 'order@sinarjaya.test', 'address' => 'Jl. Basuki Rahmat No. 45, Surabaya', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'SUP-002', 'name' => 'CV Makmur Abadi Kertas', 'phone' => '031-8921450', 'email' => 'sales@makmurabadi.test', 'address' => 'Kawasan Industri Krian Blok C-3, Sidoarjo', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'SUP-003', 'name' => 'PT Graha Pena Distribusi', 'phone' => '031-8287700', 'email' => 'distribusi@grahapena.test', 'address' => 'Jl. Ahmad Yani No. 88, Surabaya', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'SUP-004', 'name' => 'CV Cahaya Office Logistik', 'phone' => '0341-492100', 'email' => 'kontak@cahayaoffice.test', 'address' => 'Jl. Soekarno Hatta No. 12, Malang', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'SUP-005', 'name' => 'PT Digital Print Star', 'phone' => '021-6543210', 'email' => 'supply@digitalprintstar.test', 'address' => 'Mangga Dua Square Lt. 2, Jakarta Pusat', 'created_at' => $now, 'updated_at' => $now],
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
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 15000, 'selling_price' => 25000, 'min_stock' => 15, 'max_stock' => 250,
                'stocks' => [1 => 80, 2 => 40, 3 => 45],
            ],
            [
                'id' => 2, 'code' => 'PRD-002', 'name' => 'Pulpen Gel Biru 0.5mm 12pcs',
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 15000, 'selling_price' => 25000, 'min_stock' => 10, 'max_stock' => 200,
                'stocks' => [1 => 45, 2 => 0, 3 => 30],
            ],
            [
                'id' => 3, 'code' => 'PRD-003', 'name' => 'Pensil 2B Ujian Komputer 12pcs',
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 12000, 'selling_price' => 20000, 'min_stock' => 10, 'max_stock' => 200,
                'stocks' => [1 => 50, 2 => 40, 3 => 0],
            ],
            [
                'id' => 4, 'code' => 'PRD-004', 'name' => 'Spidol Whiteboard Hitam 12pcs',
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 25000, 'selling_price' => 40000, 'min_stock' => 10, 'max_stock' => 150,
                'stocks' => [1 => 40, 2 => 30, 3 => 25],
            ],
            [
                'id' => 5, 'code' => 'PRD-005', 'name' => 'Penghapus Putih Lembut 20pcs',
                'category_id' => 1, 'unit' => 'pack', 'purchase_price' => 10000, 'selling_price' => 18000, 'min_stock' => 15, 'max_stock' => 200,
                'stocks' => [1 => 30, 2 => 35, 3 => 45],
            ],
            [
                'id' => 6, 'code' => 'PRD-006', 'name' => 'Penggaris Besi Stainless 30cm',
                'category_id' => 1, 'unit' => 'pcs', 'purchase_price' => 7000, 'selling_price' => 14000, 'min_stock' => 10, 'max_stock' => 100,
                'stocks' => [1 => 20, 2 => 10, 3 => 30],
            ],
            // [STOK MENIPIS 1]: Sisa 7 set <= Batas Minimum 10
            [
                'id' => 7, 'code' => 'PRD-007', 'name' => 'Stabilo Boss Highlighter 4 Warna',
                'category_id' => 1, 'unit' => 'set', 'purchase_price' => 32000, 'selling_price' => 52000, 'min_stock' => 10, 'max_stock' => 80,
                'stocks' => [1 => 4, 2 => 0, 3 => 3],
            ],
            [
                'id' => 8, 'code' => 'PRD-008', 'name' => 'Kertas HVS A4 70gsm PaperOne',
                'category_id' => 2, 'unit' => 'rim', 'purchase_price' => 32000, 'selling_price' => 46000, 'min_stock' => 25, 'max_stock' => 500,
                'stocks' => [1 => 150, 2 => 80, 3 => 100],
            ],
            [
                'id' => 9, 'code' => 'PRD-009', 'name' => 'Kertas HVS A4 80gsm SiDU',
                'category_id' => 2, 'unit' => 'rim', 'purchase_price' => 36000, 'selling_price' => 52000, 'min_stock' => 25, 'max_stock' => 450,
                'stocks' => [1 => 120, 2 => 80, 3 => 90],
            ],
            [
                'id' => 10, 'code' => 'PRD-010', 'name' => 'Kertas HVS F4/Folio 70gsm',
                'category_id' => 2, 'unit' => 'rim', 'purchase_price' => 35000, 'selling_price' => 50000, 'min_stock' => 20, 'max_stock' => 350,
                'stocks' => [1 => 70, 2 => 35, 3 => 0],
            ],
            [
                'id' => 11, 'code' => 'PRD-011', 'name' => 'Mouse USB Optik Logitech B100',
                'category_id' => 5, 'unit' => 'pcs', 'purchase_price' => 58000, 'selling_price' => 95000, 'min_stock' => 8, 'max_stock' => 100,
                'stocks' => [1 => 25, 2 => 0, 3 => 20],
            ],
            [
                'id' => 12, 'code' => 'PRD-012', 'name' => 'Buku Kas / Ekspedisi Hardcover',
                'category_id' => 2, 'unit' => 'buku', 'purchase_price' => 18000, 'selling_price' => 30000, 'min_stock' => 10, 'max_stock' => 150,
                'stocks' => [1 => 25, 2 => 10, 3 => 35],
            ],
            [
                'id' => 13, 'code' => 'PRD-013', 'name' => 'Stapler Besar HD-50 Kangaro',
                'category_id' => 3, 'unit' => 'pcs', 'purchase_price' => 45000, 'selling_price' => 75000, 'min_stock' => 5, 'max_stock' => 80,
                'stocks' => [1 => 10, 2 => 30, 3 => 5],
            ],
            [
                'id' => 14, 'code' => 'PRD-014', 'name' => 'Isi Staples No. 10 (Box isi 20)',
                'category_id' => 3, 'unit' => 'box', 'purchase_price' => 14000, 'selling_price' => 24000, 'min_stock' => 15, 'max_stock' => 150,
                'stocks' => [1 => 60, 2 => 25, 3 => 0],
            ],
            [
                'id' => 15, 'code' => 'PRD-015', 'name' => 'Map Plastik Zipper A4 12pcs',
                'category_id' => 3, 'unit' => 'pack', 'purchase_price' => 22000, 'selling_price' => 38000, 'min_stock' => 15, 'max_stock' => 250,
                'stocks' => [1 => 80, 2 => 40, 3 => 60],
            ],
            [
                'id' => 16, 'code' => 'PRD-016', 'name' => 'Amplop Coklat Tali F4 50pcs',
                'category_id' => 3, 'unit' => 'pack', 'purchase_price' => 32000, 'selling_price' => 55000, 'min_stock' => 10, 'max_stock' => 120,
                'stocks' => [1 => 40, 2 => 20, 3 => 0],
            ],
            [
                'id' => 17, 'code' => 'PRD-017', 'name' => 'Lakban Bening 48mm x 100 Yard',
                'category_id' => 3, 'unit' => 'roll', 'purchase_price' => 8500, 'selling_price' => 15000, 'min_stock' => 20, 'max_stock' => 300,
                'stocks' => [1 => 100, 2 => 60, 3 => 60],
            ],
            [
                'id' => 18, 'code' => 'PRD-018', 'name' => 'Klip Kertas Paper Clip No. 3',
                'category_id' => 3, 'unit' => 'pack', 'purchase_price' => 16000, 'selling_price' => 26000, 'min_stock' => 15, 'max_stock' => 180,
                'stocks' => [1 => 20, 2 => 50, 3 => 30],
            ],
            [
                'id' => 19, 'code' => 'PRD-019', 'name' => 'Gunting Kantor Stainless 21cm',
                'category_id' => 3, 'unit' => 'pcs', 'purchase_price' => 18000, 'selling_price' => 32000, 'min_stock' => 5, 'max_stock' => 100,
                'stocks' => [1 => 15, 2 => 35, 3 => 10],
            ],
            // [STOK KRITIS 3]: Sisa 1 pcs <= Batas Minimum 3 (Perlu Restock Segera)
            [
                'id' => 20, 'code' => 'PRD-020', 'name' => 'Papan Tulis Whiteboard 60x90cm',
                'category_id' => 3, 'unit' => 'pcs', 'purchase_price' => 175000, 'selling_price' => 285000, 'min_stock' => 3, 'max_stock' => 20,
                'stocks' => [1 => 1, 2 => 0, 3 => 0],
            ],
            [
                'id' => 21, 'code' => 'PRD-021', 'name' => 'Tinta Printer Hitam 100ml Universal',
                'category_id' => 4, 'unit' => 'botol', 'purchase_price' => 65000, 'selling_price' => 110000, 'min_stock' => 10, 'max_stock' => 100,
                'stocks' => [1 => 25, 2 => 0, 3 => 15],
            ],
            // [STOK MENIPIS 4]: Sisa 4 set <= Batas Minimum 8
            [
                'id' => 22, 'code' => 'PRD-022', 'name' => 'Tinta Printer Warna (CMY) 100ml',
                'category_id' => 4, 'unit' => 'set', 'purchase_price' => 85000, 'selling_price' => 145000, 'min_stock' => 8, 'max_stock' => 60,
                'stocks' => [1 => 2, 2 => 0, 3 => 2],
            ],
            // [STOK MENIPIS 5]: Sisa 2 pcs <= Batas Minimum 3
            [
                'id' => 23, 'code' => 'PRD-023', 'name' => 'Toner Cartridge Laser Hitam HP 85A',
                'category_id' => 4, 'unit' => 'pcs', 'purchase_price' => 260000, 'selling_price' => 395000, 'min_stock' => 3, 'max_stock' => 25,
                'stocks' => [1 => 2, 2 => 0, 3 => 0],
            ],
        ];

        // Masukkan data produk (stok awal 0, seluruh mutasi masuk berasal dari PO -> GR)
        $initialMovementDate = date('Y-m-d H:i:s', strtotime('first day of -2 months 08:00:00'));

        $unitMap = [
            'pcs' => 1, 'rim' => 2, 'box' => 3, 'pack' => 4, 'set' => 5,
            'roll' => 6, 'buku' => 7, 'botol' => 8, 'kg' => 9, 'liter' => 10,
            'meter' => 11, 'lusin' => 12, 'lembar' => 13, 'pak' => 4,
        ];

        foreach ($catalog as $item) {
            $unitCode = $item['unit'] === 'pak' ? 'pack' : $item['unit'];
            $unitId = $unitMap[$item['unit']] ?? 1;

            $this->db->table('products')->insert([
                'id' => $item['id'],
                'code' => $item['code'],
                'name' => $item['name'],
                'category_id' => $item['category_id'],
                'unit_id' => $unitId,
                'unit' => $unitCode,
                'purchase_price' => $item['purchase_price'],
                'selling_price' => $item['selling_price'],
                'stock_qty' => 0,
                'min_stock' => $item['min_stock'],
                'max_stock' => $item['max_stock'] ?? ($item['min_stock'] * 10),
                'is_active' => true,
                'created_at' => $initialMovementDate,
                'updated_at' => $initialMovementDate,
            ]);
        }
        $this->db->query("SELECT setval('products_id_seq', (SELECT MAX(id) FROM products))");

        // -------------------------------------------------------------
        // 7. TRANSAKSI REALISTIS: PESANAN PEMBELIAN & PENERIMAAN BARANG
        //    (Seluruh stok awal masuk murni dari PO -> GR, tanpa koreksi)
        // -------------------------------------------------------------
        $d14 = date('Y-m-d', strtotime('-14 days'));
        $d13 = date('Y-m-d', strtotime('-13 days'));
        $d12 = date('Y-m-d', strtotime('-12 days'));
        $d11 = date('Y-m-d', strtotime('-11 days'));
        $d10 = date('Y-m-d', strtotime('-10 days'));
        $d9  = date('Y-m-d', strtotime('-9 days'));
        $d8  = date('Y-m-d', strtotime('-8 days'));
        $d7  = date('Y-m-d', strtotime('-7 days'));
        $d6  = date('Y-m-d', strtotime('-6 days'));
        $d5  = date('Y-m-d', strtotime('-5 days'));
        $d4  = date('Y-m-d', strtotime('-4 days'));
        $d3  = date('Y-m-d', strtotime('-3 days'));
        $d2  = date('Y-m-d', strtotime('-2 days'));
        $d1  = date('Y-m-d', strtotime('-1 day'));

        $n14 = date('Ymd', strtotime('-14 days'));
        $n13 = date('Ymd', strtotime('-13 days'));
        $n12 = date('Ymd', strtotime('-12 days'));
        $n11 = date('Ymd', strtotime('-11 days'));
        $n10 = date('Ymd', strtotime('-10 days'));
        $n9  = date('Ymd', strtotime('-9 days'));
        $n8  = date('Ymd', strtotime('-8 days'));
        $n7  = date('Ymd', strtotime('-7 days'));
        $n6  = date('Ymd', strtotime('-6 days'));
        $n5  = date('Ymd', strtotime('-5 days'));
        $n4  = date('Ymd', strtotime('-4 days'));
        $n3  = date('Ymd', strtotime('-3 days'));
        $n2  = date('Ymd', strtotime('-2 days'));
        $n1  = date('Ymd', strtotime('-1 day'));

        $seedPoReceived = function (string $poNum, int $supplierId, int $warehouseId, string $orderDate, string $recDate, string $poNotes, string $grNotes, array $lines) {
            $batch = [];
            foreach ($lines as [$pid, $qty, $price]) {
                $batch[] = [
                    'po_number' => $poNum, 'supplier_id' => $supplierId, 'product_id' => $pid,
                    'qty' => $qty, 'unit_price' => $price, 'order_date' => $orderDate,
                    'status' => 'received', 'notes' => $poNotes, 'created_by' => 2,
                    'created_at' => $orderDate . ' 09:15:00', 'updated_at' => $recDate . ' 10:30:00',
                ];
            }
            $this->db->table('purchase_orders')->insertBatch($batch);
            $poItems = $this->db->table('purchase_orders')->where('po_number', $poNum)->get()->getResultArray();
            $grNum = 'GR-' . str_replace('-', '', $recDate) . '-' . substr($poNum, -3);
            foreach ($poItems as $item) {
                $this->db->table('goods_receipts')->insert([
                    'gr_number' => $grNum, 'purchase_order_id' => $item['id'], 'warehouse_id' => $warehouseId,
                    'product_id' => $item['product_id'], 'qty_received' => $item['qty'], 'receipt_date' => $recDate,
                    'notes' => $grNotes, 'received_by' => 3,
                    'created_at' => $recDate . ' 10:30:00', 'updated_at' => $recDate . ' 10:30:00',
                ]);
                $grId = $this->db->insertID();
                $this->applyStockIn($item['product_id'], $warehouseId, $item['qty'], 'PURCHASE_RECEIPT', $grId, $grNotes . ' (' . $poNum . ')', 3, $recDate . ' 10:30:00');
            }
        };

        // PO 1..6: Pengadaan Lengkap ke 3 Gudang (~Rp 32,9 jt)
        $seedPoReceived('PO-' . $n14 . '-001', 1, 1, $d14, $d13, 'Pengadaan alat tulis kantor pusat', 'Penerimaan ATK PT Sinar Jaya di Gudang Utama', [
            [1, 70, 15000], [2, 35, 15000], [3, 45, 12000], [4, 30, 25000], [5, 40, 10000], [6, 25, 7000], [7, 14, 32000],
        ]);
        $seedPoReceived('PO-' . $n13 . '-001', 2, 1, $d13, $d12, 'Pengadaan kertas & buku Gudang Utama', 'Penerimaan kertas CV Makmur Abadi di Gudang Utama', [
            [8, 140, 32000], [9, 100, 36000], [10, 65, 35000], [12, 35, 18000],
        ]);
        $seedPoReceived('PO-' . $n11 . '-001', 3, 1, $d11, $d10, 'Pengadaan perlengkapan kantor & arsip', 'Penerimaan perlengkapan PT Graha Pena di Gudang Utama', [
            [13, 18, 45000], [14, 50, 14000], [15, 60, 22000], [16, 35, 32000], [17, 70, 8500], [18, 35, 16000], [19, 25, 18000], [20, 6, 175000],
        ]);
        $seedPoReceived('PO-' . $n10 . '-001', 5, 1, $d10, $d9, 'Pengadaan tinta printer & perangkat komputer', 'Penerimaan perangkat PT Digital Print Star di Gudang Utama', [
            [11, 20, 58000], [21, 22, 65000], [22, 12, 85000], [23, 6, 260000],
        ]);
        $seedPoReceived('PO-' . $n8 . '-001', 2, 2, $d8, $d7, 'Pasokan stok Cabang Sidoarjo', 'Penerimaan stok di Gudang Cabang Sidoarjo', [
            [8, 45, 32000], [9, 40, 36000], [17, 45, 8500], [1, 40, 15000], [4, 25, 25000], [13, 15, 45000],
        ]);
        $seedPoReceived('PO-' . $n7 . '-001', 1, 3, $d7, $d6, 'Pasokan Hub Surabaya Timur', 'Penerimaan stok di Hub Surabaya Timur', [
            [9, 30, 36000], [2, 30, 15000], [15, 35, 22000], [18, 30, 16000], [11, 15, 58000],
        ]);

        // PO 7: Status APPROVED (Menunggu Barang Datang)
        $po7Num = 'PO-' . $n3 . '-001';
        $this->db->table('purchase_orders')->insertBatch([
            ['po_number' => $po7Num, 'supplier_id' => 5, 'product_id' => 21, 'qty' => 15, 'unit_price' => 65000, 'order_date' => $d3, 'status' => 'approved', 'notes' => 'Restock tinta printer hub timur', 'created_by' => 2, 'created_at' => $d3 . ' 13:00:00', 'updated_at' => $d2 . ' 08:30:00'],
            ['po_number' => $po7Num, 'supplier_id' => 5, 'product_id' => 11, 'qty' => 10, 'unit_price' => 58000, 'order_date' => $d3, 'status' => 'approved', 'notes' => 'Restock mouse kantor', 'created_by' => 2, 'created_at' => $d3 . ' 13:00:00', 'updated_at' => $d2 . ' 08:30:00'],
        ]);

        // PO 8: Status DRAFT (Menunggu Persetujuan Manajemen)
        $po8Num = 'PO-' . $n1 . '-001';
        $this->db->table('purchase_orders')->insertBatch([
            ['po_number' => $po8Num, 'supplier_id' => 3, 'product_id' => 20, 'qty' => 5, 'unit_price' => 175000, 'order_date' => $d1, 'status' => 'draft', 'notes' => 'Rencana restock whiteboard kantor pusat', 'created_by' => 2, 'created_at' => $d1 . ' 15:45:00', 'updated_at' => $d1 . ' 15:45:00'],
            ['po_number' => $po8Num, 'supplier_id' => 3, 'product_id' => 23, 'qty' => 4, 'unit_price' => 260000, 'order_date' => $d1, 'status' => 'draft', 'notes' => 'Rencana restock toner HP laser', 'created_by' => 2, 'created_at' => $d1 . ' 15:45:00', 'updated_at' => $d1 . ' 15:45:00'],
        ]);

        // PO 9: Status CANCELLED
        $po9Num = 'PO-' . $n8 . '-002';
        $this->db->table('purchase_orders')->insertBatch([
            ['po_number' => $po9Num, 'supplier_id' => 4, 'product_id' => 6, 'qty' => 50, 'unit_price' => 7000, 'order_date' => $d8, 'status' => 'cancelled', 'notes' => 'Dibatalkan oleh staf purchasing: Supplier kehabisan stok fisik merek yang diminta.', 'created_by' => 2, 'created_at' => $d8 . ' 16:00:00', 'updated_at' => $d7 . ' 09:00:00'],
        ]);

        // -------------------------------------------------------------
        // 8. TRANSAKSI REALISTIS: PESANAN PENJUALAN & PENGIRIMAN (~Rp 43,2 jt)
        // -------------------------------------------------------------
        $seedSoDelivered = function (string $soNum, int $customerId, int $warehouseId, string $orderDate, string $delDate, string $soNotes, string $doNotes, array $lines) {
            $batch = [];
            foreach ($lines as [$pid, $qty, $price]) {
                $batch[] = [
                    'so_number' => $soNum, 'customer_id' => $customerId, 'warehouse_id' => $warehouseId,
                    'product_id' => $pid, 'qty' => $qty, 'unit_price' => $price, 'order_date' => $orderDate,
                    'status' => 'delivered', 'notes' => $soNotes, 'created_by' => 4,
                    'created_at' => $orderDate . ' 09:30:00', 'updated_at' => $delDate . ' 10:30:00',
                ];
            }
            $this->db->table('sales_orders')->insertBatch($batch);
            $soItems = $this->db->table('sales_orders')->where('so_number', $soNum)->get()->getResultArray();
            $doNum = 'DO-' . str_replace('-', '', $delDate) . '-' . substr($soNum, -3);
            foreach ($soItems as $item) {
                $this->db->table('deliveries')->insert([
                    'delivery_number' => $doNum, 'sales_order_id' => $item['id'], 'warehouse_id' => $warehouseId,
                    'product_id' => $item['product_id'], 'qty_delivered' => $item['qty'], 'delivery_date' => $delDate,
                    'delivery_time' => '10:30', 'status' => 'delivered',
                    'notes' => $doNotes, 'delivered_by' => 3,
                    'created_at' => $delDate . ' 10:30:00', 'updated_at' => $delDate . ' 10:30:00',
                ]);
                $this->applyStockOut($item['product_id'], $warehouseId, $item['qty'], 'SALES_ORDER', $item['id'], $doNotes . ' (' . $soNum . ')', 4, $orderDate . ' 09:30:00');
            }
        };

        // -------------------------------------------------------------
        // 7B. RIWAYAT 2 BULAN SEBELUMNYA (SEPTEMBER & OKTOBER) + AWAL NOVEMBER
        //     Disebar merata tiap 2-3 hari agar grafik tren bulanan realistis
        // -------------------------------------------------------------
        $m2Start = strtotime('first day of -2 months');
        $septPos = [
            ['02', 1, 1, 'Pengadaan awal bulan ATK kantor pusat', [[8, 45, 32000], [1, 35, 15000], [15, 25, 22000]]],
            ['06', 2, 2, 'Pasokan kertas Cabang Sidoarjo', [[9, 40, 36000], [10, 25, 35000], [17, 40, 8500]]],
            ['11', 3, 1, 'Pengadaan perlengkapan arsip & tinta', [[14, 30, 14000], [21, 12, 65000], [11, 8, 58000]]],
            ['16', 1, 3, 'Pasokan Hub Surabaya Timur', [[8, 35, 32000], [9, 30, 36000], [4, 20, 25000]]],
            ['21', 5, 1, 'Restock tengah-akhir bulan Gudang Utama', [[1, 30, 15000], [15, 20, 22000], [13, 10, 45000]]],
            ['25', 2, 2, 'Pengadaan akhir bulan Cabang Sidoarjo', [[10, 20, 35000], [17, 30, 8500], [18, 25, 16000]]],
        ];
        foreach ($septPos as $idx => [$day, $supId, $whId, $note, $lines]) {
            $od = date('Y-m-', $m2Start) . $day;
            $gd = date('Y-m-d', strtotime($od . ' +1 day'));
            $seedPoReceived('PO-' . str_replace('-', '', $od) . '-00' . ($idx + 1), $supId, $whId, $od, $gd, $note, 'Penerimaan ' . $note, $lines);
        }

        $septSos = [
            ['04', 2, 1, 'Order rutin PT Maju Bersama', [[8, 35, 46000], [1, 25, 25000], [15, 18, 38000]]],
            ['08', 1, 2, 'Order mingguan Toko Salemba', [[9, 30, 52000], [10, 18, 50000], [17, 30, 15000]]],
            ['13', 4, 1, 'Pengadaan proyek PT Berkah Perkasa', [[14, 24, 24000], [21, 10, 110000], [11, 6, 95000]]],
            ['18', 7, 3, 'Order kertas PT Cakra Buana', [[8, 30, 46000], [9, 25, 52000], [4, 15, 40000]]],
            ['23', 5, 1, 'Pesanan ATK Pustaka Mandiri', [[1, 25, 25000], [15, 18, 38000], [13, 8, 75000]]],
            ['27', 6, 2, 'Order akhir bulan Toko Mandiri Jaya', [[10, 18, 50000], [17, 25, 15000], [18, 20, 26000]]],
        ];
        foreach ($septSos as $idx => [$day, $custId, $whId, $note, $lines]) {
            $od = date('Y-m-', $m2Start) . $day;
            $dd = date('Y-m-d', strtotime($od . ' +1 day'));
            $seedSoDelivered('SO-' . str_replace('-', '', $od) . '-00' . ($idx + 1), $custId, $whId, $od, $dd, $note, 'Pengiriman ' . $note, $lines);
        }

        $m1Start = strtotime('first day of -1 month');
        $octPos = [
            ['02', 2, 1, 'Pengadaan kertas awal Oktober', [[8, 50, 32000], [9, 40, 36000], [16, 20, 32000]]],
            ['07', 1, 3, 'Pasokan ATK Hub Surabaya Timur', [[2, 35, 15000], [3, 30, 12000], [18, 30, 16000]]],
            ['12', 5, 1, 'Restock tinta & toner Gudang Utama', [[22, 10, 85000], [23, 5, 260000], [13, 12, 45000]]],
            ['17', 2, 2, 'Pasokan kertas & spidol Sidoarjo', [[8, 40, 32000], [4, 25, 25000], [17, 35, 8500]]],
            ['22', 3, 1, 'Pengadaan perlengkapan arsip Oktober', [[9, 30, 36000], [15, 30, 22000], [19, 20, 18000]]],
            ['27', 1, 3, 'Pengadaan akhir bulan Hub Timur', [[2, 25, 15000], [11, 10, 58000], [12, 20, 18000]]],
        ];
        foreach ($octPos as $idx => [$day, $supId, $whId, $note, $lines]) {
            $od = date('Y-m-', $m1Start) . $day;
            $gd = date('Y-m-d', strtotime($od . ' +1 day'));
            $seedPoReceived('PO-' . str_replace('-', '', $od) . '-00' . ($idx + 1), $supId, $whId, $od, $gd, $note, 'Penerimaan ' . $note, $lines);
        }

        $octSos = [
            ['04', 7, 1, 'Order kertas PT Cakra Buana', [[8, 42, 46000], [9, 32, 52000], [16, 16, 55000]]],
            ['09', 5, 3, 'Pesanan ATK CV Pustaka Mandiri', [[2, 28, 25000], [3, 24, 20000], [18, 24, 26000]]],
            ['14', 4, 1, 'Pengadaan tinta & stapler PT Berkah', [[22, 8, 145000], [23, 4, 395000], [13, 10, 75000]]],
            ['19', 1, 2, 'Order rutin Toko Buku Salemba', [[8, 35, 46000], [4, 20, 40000], [17, 28, 15000]]],
            ['24', 2, 1, 'Order perlengkapan PT Maju Bersama', [[9, 25, 52000], [15, 25, 38000], [19, 16, 32000]]],
            ['29', 3, 3, 'Order akhir bulan Toko Sinar Abadi', [[2, 20, 25000], [11, 8, 95000], [12, 16, 30000]]],
        ];
        foreach ($octSos as $idx => [$day, $custId, $whId, $note, $lines]) {
            $od = date('Y-m-', $m1Start) . $day;
            $dd = date('Y-m-d', strtotime($od . ' +1 day'));
            $seedSoDelivered('SO-' . str_replace('-', '', $od) . '-00' . ($idx + 1), $custId, $whId, $od, $dd, $note, 'Pengiriman ' . $note, $lines);
        }

        // Awal November (tgl 2 - 10) agar grafik bulan berjalan terisi penuh sejak awal bulan
        $m0Start = strtotime('first day of this month');
        $earlyNovPos = [
            ['02', 1, 1, 'Pengadaan awal November Gudang Utama', [[8, 40, 32000], [1, 30, 15000], [17, 35, 8500]]],
            ['06', 2, 2, 'Pasokan awal November Sidoarjo', [[9, 30, 36000], [15, 25, 22000], [4, 20, 25000]]],
        ];
        foreach ($earlyNovPos as $idx => [$day, $supId, $whId, $note, $lines]) {
            $od = date('Y-m-', $m0Start) . $day;
            $gd = date('Y-m-d', strtotime($od . ' +1 day'));
            $seedPoReceived('PO-' . str_replace('-', '', $od) . '-00' . ($idx + 1), $supId, $whId, $od, $gd, $note, 'Penerimaan ' . $note, $lines);
        }
        $earlyNovSos = [
            ['04', 2, 1, 'Order awal November PT Maju Bersama', [[8, 35, 46000], [1, 25, 25000], [17, 25, 15000]]],
            ['08', 6, 2, 'Order awal November Toko Mandiri', [[9, 25, 52000], [15, 20, 38000], [4, 15, 40000]]],
        ];
        foreach ($earlyNovSos as $idx => [$day, $custId, $whId, $note, $lines]) {
            $od = date('Y-m-', $m0Start) . $day;
            $dd = date('Y-m-d', strtotime($od . ' +1 day'));
            $seedSoDelivered('SO-' . str_replace('-', '', $od) . '-00' . ($idx + 1), $custId, $whId, $od, $dd, $note, 'Pengiriman ' . $note, $lines);
        }

        // Bulan Berjalan (November) SO Delivered:

        $seedSoDelivered('SO-' . $n9 . '-001', 2, 1, $d9, $d8, 'Pengadaan kertas & alat tulis operasional kuartal akhir', 'Diterima oleh Pak Slamet (Logistik SIER Surabaya)', [
            [8, 85, 46000], [1, 45, 25000], [14, 30, 24000],
        ]);
        $seedSoDelivered('SO-' . $n7 . '-001', 7, 1, $d7, $d6, 'Pengadaan kertas & perlengkapan kantor pabrik Gresik', 'Dikirim armada pick-up kantor ke Kawasan Industri Gresik', [
            [9, 70, 52000], [10, 45, 50000], [20, 5, 285000], [23, 4, 395000],
        ]);
        $seedSoDelivered('SO-' . $n6 . '-001', 1, 2, $d6, $d5, 'Pesanan mingguan Toko Buku Salemba Indah', 'Diambil langsung menggunakan pick-up toko Salemba', [
            [8, 30, 46000], [9, 25, 52000], [17, 30, 15000], [4, 15, 40000],
        ]);
        $seedSoDelivered('SO-' . $n5 . '-001', 4, 3, $d5, $d4, 'Perlengkapan tender proyek PT Berkah Perkasa Teknik', 'Dikirim kurir internal ke Mayjen Sungkono', [
            [15, 20, 38000], [18, 15, 26000], [11, 12, 95000],
        ]);
        $seedSoDelivered('SO-' . $n4 . '-001', 5, 1, $d4, $d3, 'Kebutuhan paket ujian & komputer bimbingan belajar', 'Diterima staf logistik Pustaka Mandiri Malang', [
            [3, 25, 20000], [5, 20, 18000], [7, 10, 52000], [22, 8, 145000], [11, 17, 95000],
        ]);
        $seedSoDelivered('SO-' . $n2 . '-001', 7, 1, $d2, $d1, 'Repeat order tinta, amplop & perlengkapan divisi admin', 'Diterima bagian gudang PT Cakra Buana Raya', [
            [16, 20, 55000], [21, 14, 110000], [12, 18, 30000], [19, 12, 32000], [2, 20, 25000],
        ]);
        $seedSoDelivered('SO-' . $n3 . '-002', 2, 1, $d3, $d2, 'Pengadaan tambahan kertas, map & perlengkapan arsip kantor', 'Diterima oleh bagian logistik PT Maju Bersama', [
            [8, 30, 46000], [9, 20, 52000], [10, 15, 50000], [15, 30, 38000], [17, 40, 15000], [18, 25, 26000], [4, 20, 40000], [13, 12, 75000],
        ]);
        $seedSoDelivered('SO-' . $n2 . '-003', 3, 2, $d2, $d1, 'Pesanan grosir perlengkapan toko cabang Sidoarjo', 'Dikirim ekspedisi lokal ke Toko Sinar Abadi', [
            [8, 12, 46000], [9, 12, 52000], [1, 30, 25000], [4, 15, 40000], [17, 20, 15000],
        ]);

        // SO 7: Status CONFIRMED (Siap Kirim dari Gudang Sidoarjo - stok dialokasikan saat konfirmasi)
        $so7Num = 'SO-' . $n1 . '-001';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so7Num, 'customer_id' => 6, 'warehouse_id' => 2, 'product_id' => 17, 'qty' => 20, 'unit_price' => 15000, 'order_date' => $d1, 'status' => 'confirmed', 'notes' => 'Toko Mandiri Jaya Krian - Menunggu jadwal pickup', 'created_by' => 4, 'created_at' => $d1 . ' 10:30:00', 'updated_at' => $d1 . ' 11:15:00'],
            ['so_number' => $so7Num, 'customer_id' => 6, 'warehouse_id' => 2, 'product_id' => 13, 'qty' => 8, 'unit_price' => 75000, 'order_date' => $d1, 'status' => 'confirmed', 'notes' => 'Stapler besar kebutuhan toko', 'created_by' => 4, 'created_at' => $d1 . ' 10:30:00', 'updated_at' => $d1 . ' 11:15:00'],
        ]);
        foreach ($this->db->table('sales_orders')->where('so_number', $so7Num)->get()->getResultArray() as $item) {
            $this->applyStockOut($item['product_id'], 2, $item['qty'], 'SALES_ORDER', $item['id'], 'Alokasi stok untuk pesanan dikonfirmasi (' . $so7Num . ')', 4, $d1 . ' 11:15:00');
        }

        // SO 8: Status CONFIRMED (Siap Kirim dari Hub Surabaya Timur)
        $so8Num = 'SO-' . $n1 . '-002';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so8Num, 'customer_id' => 3, 'warehouse_id' => 3, 'product_id' => 9, 'qty' => 15, 'unit_price' => 52000, 'order_date' => $d1, 'status' => 'confirmed', 'notes' => 'Toko Sinar Abadi Kediri - Siap dikirim kargo', 'created_by' => 4, 'created_at' => $d1 . ' 14:00:00', 'updated_at' => $d1 . ' 15:00:00'],
            ['so_number' => $so8Num, 'customer_id' => 3, 'warehouse_id' => 3, 'product_id' => 2, 'qty' => 15, 'unit_price' => 25000, 'order_date' => $d1, 'status' => 'confirmed', 'notes' => 'Pulpen biru pesanan toko', 'created_by' => 4, 'created_at' => $d1 . ' 14:00:00', 'updated_at' => $d1 . ' 15:00:00'],
        ]);
        foreach ($this->db->table('sales_orders')->where('so_number', $so8Num)->get()->getResultArray() as $item) {
            $this->applyStockOut($item['product_id'], 3, $item['qty'], 'SALES_ORDER', $item['id'], 'Alokasi stok untuk pesanan dikonfirmasi (' . $so8Num . ')', 4, $d1 . ' 15:00:00');
        }

        // SO 9: Status REVISION (Butuh Revisi oleh Sales)
        $so9Num = 'SO-' . $n2 . '-002';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so9Num, 'customer_id' => 8, 'warehouse_id' => 1, 'product_id' => 15, 'qty' => 20, 'unit_price' => 38000, 'order_date' => $d2, 'status' => 'revision', 'notes' => 'Pelanggan minta ubah jumlah map zipper sebelum dikirim ulang', 'created_by' => 4, 'created_at' => $d2 . ' 13:00:00', 'updated_at' => $d1 . ' 16:00:00'],
        ]);

        // SO 10: Status DRAFT (Menunggu Konfirmasi Pelanggan)
        $so10Num = 'SO-' . $n1 . '-003';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so10Num, 'customer_id' => 8, 'warehouse_id' => 1, 'product_id' => 8, 'qty' => 30, 'unit_price' => 46000, 'order_date' => $d1, 'status' => 'draft', 'notes' => 'Penawaran kertas HVS koperasi tahunan', 'created_by' => 4, 'created_at' => $d1 . ' 16:30:00', 'updated_at' => $d1 . ' 16:30:00'],
            ['so_number' => $so10Num, 'customer_id' => 8, 'warehouse_id' => 1, 'product_id' => 1, 'qty' => 20, 'unit_price' => 25000, 'order_date' => $d1, 'status' => 'draft', 'notes' => 'Penawaran pulpen koperasi', 'created_by' => 4, 'created_at' => $d1 . ' 16:30:00', 'updated_at' => $d1 . ' 16:30:00'],
        ]);

        // SO 11: Status CANCELLED (Pembatalan Pesanan dengan Alasan Audit)
        $so11Num = 'SO-' . $n4 . '-002';
        $this->db->table('sales_orders')->insertBatch([
            ['so_number' => $so11Num, 'customer_id' => 2, 'warehouse_id' => 1, 'product_id' => 6, 'qty' => 30, 'unit_price' => 14000, 'order_date' => $d4, 'status' => 'cancelled', 'notes' => 'Dibatalkan oleh pelanggan: PT Maju Bersama membatalkan pesanan penggaris karena alokasi anggaran dialihkan ke kertas.', 'created_by' => 4, 'created_at' => $d4 . ' 16:30:00', 'updated_at' => $d3 . ' 08:15:00'],
        ]);

        // -------------------------------------------------------------
        // 9. REKONSILIASI AKHIR SALDO PRODUK (STOCK RECONCILIATION)
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
}
