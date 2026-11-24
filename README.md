# Mini ERP: Inventory and Sales Management System

Aplikasi internal PT Nusantara Distribusi, distributor alat tulis kantor dan kebutuhan kantor, untuk mencatat pembelian, penjualan, dan persediaan dalam satu tempat.

Teknologi: CodeIgniter 4.7.6, PHP 8.5.12, PostgreSQL 16, Bootstrap 5, Git.
Arsitektur: server rendered MVC.
Database: 13 tabel, tanpa tabel items dan tanpa kolom total_amount. Satu nomor transaksi bisa punya banyak baris, tiap baris satu produk, dan total dihitung di query.

## Fitur

1. Login dan logout dengan lima peran: Admin (Super Admin, kelola master data, pengguna, dan peran, tanpa transaksi operasional), Staff Purchasing, Staff Gudang, Staff Sales, dan Manajemen (lihat laporan dan setujui PO).
2. Master data: kategori, satuan, dan gudang (Admin), produk dan supplier (Staff Purchasing dan Admin), customer (Staff Sales dan Admin). Harga jual produk tidak boleh di bawah harga modal beli, dan satuan produk yang sudah punya riwayat transaksi tidak bisa diubah.
3. Pembelian: Purchase Order Draft dan Approved tanpa gudang, Goods Receipt dengan gudang dipilih petugas, stok bertambah hanya saat barang diterima. Guard batas stok maksimum menolak PO yang melebihi kapasitas. Double Order Guard memberi peringatan kalau produk sudah punya PO aktif. Manajemen bisa menolak PO dengan alasan wajib. Daftar PO menampilkan tab status dengan angka jumlah.
4. Penjualan: Sales Order dengan cek stok per gudang, Delivery dengan gudang terkunci mengikuti SO, stok berkurang hanya saat barang dikirim. Harga satuan SO tidak boleh di bawah harga modal beli produk. Daftar SO menampilkan tab status dengan angka jumlah, dan pill Dikonfirmasi disorot kalau ada SO menunggu kirim. Form pengiriman punya tombol isi sesuai sisa dan kosongkan.
5. Inventory: stok per produk dan per gudang, histori stock movement, dan penyesuaian stok dengan alasan. Daftar stok bisa difilter status stok dan kategori, riwayat mutasi bisa difilter jenis mutasi dan gudang, daftar Barang Masuk dan Pengiriman bisa difilter gudang dan rentang tanggal.
6. Laporan: pembelian, penjualan, stok, dan 10 produk terlaris, semua dengan filter periode tanggal.
7. Dashboard: lima versi dashboard sesuai peran, lengkap dengan antrean ACC untuk Manajemen dan antrean barang masuk untuk Gudang. Antrean ACC menampilkan maksimal 5 PO teratas.

## Aturan bisnis yang dijaga

1. PO Draft tidak mengubah stok.
2. Stok bertambah hanya dari Goods Receipt.
3. Delivery ditolak jika stok tidak mencukupi.
4. Stok berkurang hanya dari Delivery yang berhasil.
5. Setiap perubahan stok mencatat stock movement.
6. Stok tidak boleh negatif, dijaga di aplikasi dan di database.
7. Header dan detail transaksi disimpan dalam satu database transaction.
8. Transaksi yang sudah diproses tidak dihapus, hanya dibatalkan dengan status.
9. Total transaksi dihitung dari kuantitas dikalikan harga satuan pada setiap detail, lalu dijumlahkan.
10. Purchase Order tidak punya gudang. Gudang ditentukan petugas saat Goods Receipt.
11. Sales Order adalah alokasi stok dari satu gudang tertentu. Gudang pengiriman terkunci mengikuti gudang SO.
12. Satu peran satu tugas untuk transaksi operasional. Admin tidak mencatat transaksi operasional. Hanya Manajemen yang bisa menyetujui PO.
13. Batas stok maksimum adalah batas global per produk. PO ditolak kalau stok fisik tambah pesanan berjalan sudah mencapai batas, atau kalau kuantitas baru melewati sisa kuota.
14. Sales Order bukan reservasi stok. Dua SO bisa sama-sama lolos cek stok, dan pengiriman kedua ditolak kalau stok habis.
15. Harga yang diketik dengan pemisah ribuan dibersihkan server sebelum disimpan.
16. Harga jual tidak boleh lebih rendah dari harga modal beli. Produk dengan harga jual di bawah harga modal ditolak, dan SO dengan harga satuan di bawah harga modal ditolak seluruhnya.
17. Penolakan PO oleh Manajemen adalah pembatalan dengan label penolakan, bukan status baru. Status tetap Cancelled dan alasan wajib diisi.
18. Satuan produk yang sudah punya riwayat transaksi tidak bisa diubah.

## Hak akses per peran

Ya berarti bisa melihat dan memproses. L berarti hanya melihat. Strip berarti tidak ada akses.

| Fitur | Admin | Staff Purchasing | Staff Gudang | Staff Sales | Manajemen |
| --- | --- | --- | --- | --- | --- |
| Dashboard | Ya | Ya | Ya | Ya | Ya |
| Kelola Kategori | Ya | - | - | - | - |
| Kelola Produk | Ya | Ya | L | L | L |
| Kelola Supplier | Ya | Ya | - | - | L |
| Kelola Customer | Ya | - | - | Ya | L |
| Kelola Gudang | Ya | - | L | - | L |
| Kelola Satuan | Ya | - | - | - | L |
| Kelola Pengguna | Ya | - | - | - | - |
| Buat Purchase Order | L | Ya | - | - | L |
| Approve Purchase Order | - | - | - | - | Ya |
| Batalkan Purchase Order | - | Ya | - | - | Ya |
| Batalkan Sales Order | - | - | - | Ya | - |
| Catat Goods Receipt | - | - | Ya | - | - |
| Buat Sales Order | L | - | - | Ya | L |
| Konfirmasi Sales Order | - | - | - | Ya | - |
| Catat Delivery | - | - | Ya | - | - |
| Lihat Stok dan Mutasi | Ya | Ya | Ya | Ya | Ya |
| Penyesuaian Stok | Ya | - | - | - | - |
| Lihat Laporan | Ya | - | - | - | Ya |

Daftar Goods Receipt masih bisa dibuka Admin, Staff Purchasing, dan Manajemen, dan daftar Delivery masih bisa dibuka Admin, Staff Sales, dan Manajemen, karena daftar itu hanya menampilkan data. Yang dibatasi adalah siapa yang boleh mencatat.

## Cara menjalankan

### Syarat

PHP 8.5.12 dengan ekstensi pgsql, intl, mbstring, xml, curl, dan zip. PostgreSQL 16. Composer 2. Ubuntu 24.04 hanya menyediakan PHP 8.3, jadi PHP 8.5 diambil dari PPA Ondrej Sury.

### Langkah

1. Buat database dan pengguna PostgreSQL.

```sql
CREATE USER minierp WITH PASSWORD 'isi-password-sendiri'
CREATE DATABASE minierp_dev OWNER minierp
```

2. Masuk ke folder `app`, salin `env` menjadi `.env`, lalu isi koneksi database dan baseURL.

```bash
cd app
cp env .env
```

Isi yang perlu diubah di `.env`:

```text
app.baseURL = 'http://localhost:8080'
database.default.hostname = localhost
database.default.database = minierp_dev
database.default.username = minierp
database.default.password = isi-password-sendiri
database.default.DBDriver = PostgreSQL
database.default.port = 5432
```

3. Instal dependensi, migration, dan seeder.

```bash
composer install
php spark migrate --all
php spark db:seed MainSeeder
```

4. Jalankan server.

```bash
php spark serve --port 8080
```

5. Buka `http://localhost:8080` di browser.

### Akun demo

Akun ini hanya untuk demo lokal. Ganti passwordnya sebelum dipakai di tempat lain.

1. Admin: `admin@nusantara.test` dengan password `admin123`. Kelola master data, pengguna, dan peran, penyesuaian stok, dan laporan. Tidak bisa mencatat transaksi operasional dan tidak bisa menyetujui PO.
2. Staff Purchasing: `purchasing@nusantara.test` dengan password `purchasing123`. Membuat PO, mengelola produk dan supplier.
3. Staff Gudang: `gudang@nusantara.test` dengan password `gudang123`. Mencatat Goods Receipt dan Delivery.
4. Staff Sales: `sales@nusantara.test` dengan password `sales123`. Membuat dan mengonfirmasi SO, mengelola customer.
5. Manajemen: `manajemen@nusantara.test` dengan password `manajemen123`. Melihat laporan dan stok, serta menyetujui PO lewat antrean ACC.

### Menguji ulang alurnya

Skrip `scripts/test_flow.sh` menjalankan alur penuh lewat HTTP dan memeriksa stok di database. Password database dibaca otomatis dari `app/.env`, jadi jalankan saja dari folder root proyek saat server jalan. Skrip ini memakai akun peran yang benar untuk tiap transaksi.

```bash
bash scripts/test_flow.sh
```

Dua skrip lain menguji hak akses dan akun pengguna.

```bash
bash scripts/test_roles.sh
bash scripts/test_users.sh
```

Skrip tes tidak idempotent. Jalankan `php spark db:reset` dari folder `app` dulu sebelum menjalankan skrip tes, supaya database kembali ke kondisi seeder.

### Mengembalikan database ke kondisi awal

```bash
cd app
php spark db:reset
```

Perintah ini menghapus skema public, menjalankan semua migrasi, lalu mengisi data seeder. Cara manualnya:

```bash
cd app
php spark migrate:refresh --all
php spark db:seed MainSeeder
```

## Masalah yang sering muncul

1. PHP 8.5 tidak ada di Ubuntu 24.04. Ubuntu 24.04 hanya menyediakan PHP 8.3. Ambil PHP 8.5 dari PPA Ondrej Sury, lalu pasang ekstensi pgsql, intl, mbstring, xml, curl, dan zip.
2. Koneksi database ditolak. Periksa isi `.env`, pastikan pengguna dan database `minierp_dev` sudah dibuat, dan PostgreSQL sudah jalan. Lihat langkah 1 dan 2 di atas.
3. Tabel tidak ditemukan. Migrasi belum dijalankan. Jalankan `php spark migrate --all` dari folder `app`.
4. Login gagal dengan akun demo. Seeder belum dijalankan. Jalankan `php spark db:seed MainSeeder` dari folder `app`, atau `php spark db:reset` untuk mengulang dari awal.
5. Skrip tes gagal di tengah jalan. Skrip tes tidak idempotent. Jalankan `php spark db:reset` dulu sebelum menjalankan skrip tes.
6. Server tidak mau jalan, port 8080 sudah dipakai. Matikan proses `spark serve` yang lama, atau jalankan dengan port lain: `php spark serve --port 8081`.
7. Tanggal di laporan bergeser. Timezone aplikasi sudah Asia/Jakarta. Kalau tanggal tetap bergeser, periksa timezone di `.env` dan di PostgreSQL.

## Struktur proyek

```text
mini-erp/
  app/                  Kode CodeIgniter 4 (Controllers, Models, Views, Migrations, Seeds)
  diagram/              Diagram DFD dan ERD dalam format Mermaid (.mmd)
  tests/                Tes bawaan CodeIgniter
  composer.json         Daftar dependensi
  spark                 Perintah CodeIgniter
  writable/             Cache, log, dan upload
```

## Catatan desain

1. Stok disimpan di `products.stock_qty` agar halaman stok cepat dibaca. `stock_movements` adalah jejak audit. Saldo stok harus selalu sama dengan jumlah movement.
2. PO tidak langsung menambah stok karena barang belum tentu datang sesuai pesanan.
3. SO tidak langsung mengurangi stok karena barang belum tentu dikirim pada hari yang sama.
4. Perubahan stok memakai kunci baris `SELECT ... FOR UPDATE` di dalam transaction, agar dua pengguna yang mengeluarkan stok yang sama secara bersamaan tidak membuat stok negatif.
5. Sistem memakai tiga gudang dan stok dicatat per gudang lewat `stock_movements`. Purchase Order tidak punya gudang karena gudang tujuan belum ditentukan sampai barang sampai. Sales Order tetap punya gudang karena SO adalah alokasi stok dari satu gudang tertentu dan pengiriman wajib keluar dari gudang itu.
6. Timezone aplikasi Asia/Jakarta, supaya tanggal di laporan dan penomoran tidak bergeser.
