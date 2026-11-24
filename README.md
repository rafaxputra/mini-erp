<p align="center">
  <img src="app/public/img/logo.png" alt="Logo PT Nusantara Distribusi" width="120">
</p>

<h1 align="center">Mini ERP: Inventory and Sales Management System</h1>

<p align="center">
  Aplikasi internal PT Nusantara Distribusi, distributor alat tulis kantor dan kebutuhan kantor, untuk mencatat pembelian, penjualan, dan persediaan dalam satu tempat.
</p>

---

## Daftar Isi

1. [Tentang Aplikasi](#tentang-aplikasi)
2. [Fitur](#fitur)
3. [Teknologi](#teknologi)
4. [Kebutuhan Sistem](#kebutuhan-sistem)
5. [Instalasi](#instalasi)
6. [Akun Demo](#akun-demo)
7. [Hak Akses per Peran](#hak-akses-per-peran)
8. [Aturan Bisnis Penting](#aturan-bisnis-penting)
9. [Menguji Ulang Alurnya](#menguji-ulang-alurnya)
10. [Struktur Proyek](#struktur-proyek)
11. [Dokumen](#dokumen)
12. [Backup dan Restore Database](#backup-dan-restore-database)
13. [Pemecahan Masalah](#pemecahan-masalah)
14. [Catatan Desain](#catatan-desain)
15. [Lisensi](#lisensi)

---

## Tentang Aplikasi

Mini ERP mengelola alur barang dari pembelian ke supplier sampai pengiriman ke customer, dengan stok yang selalu dapat ditelusuri lewat histori mutasi. Pengembangannya mengacu pada dokumen SRS Mini ERP (Inventory and Sales Management System).

Alur utama:

```
Purchase Order (Draft -> Approved) -> Goods Receipt -> stok bertambah
Sales Order (Draft -> Confirmed)   -> Delivery      -> stok berkurang
```

## Fitur

| Modul | Isi |
|---|---|
| Autentikasi | Login dan logout, lima peran, session dan hak akses sesuai peran |
| Master Data | Kategori, satuan, gudang, produk, supplier, customer. Kode unik, satuan produk yang sudah punya riwayat transaksi tidak bisa diubah, harga jual tidak boleh di bawah harga modal beli |
| Purchasing | Purchase Order Draft dan Approved tanpa gudang, Goods Receipt dengan gudang dipilih petugas, guard batas stok maksimum, Double Order Guard, penolakan PO oleh Manajemen, tab status dengan angka jumlah |
| Sales | Sales Order dengan cek stok per gudang, Delivery dengan gudang terkunci mengikuti SO, harga satuan tidak boleh di bawah harga modal beli, tab status dengan angka jumlah, tombol isi sesuai sisa dan kosongkan di form pengiriman |
| Inventory | Stok per produk dan per gudang, histori stock movement, penyesuaian stok dengan alasan, filter status stok, kategori, jenis mutasi, dan gudang |
| Laporan | Pembelian, penjualan, stok, dan 10 produk terlaris, semua dengan filter periode tanggal |
| Dashboard | Lima versi dashboard sesuai peran, lengkap dengan antrean ACC untuk Manajemen (maksimal 5 PO teratas) dan antrean barang masuk untuk Gudang |

## Teknologi

- **Framework:** CodeIgniter 4.7.6, pola MVC, server rendered
- **Database:** PostgreSQL 16, 13 tabel, tanpa tabel items dan tanpa kolom total_amount. Satu nomor transaksi bisa punya banyak baris, tiap baris satu produk, dan total dihitung di query
- **Tampilan:** Bootstrap 5 dan Tabler 1.7.5, dimuat dari CDN, jadi perlu koneksi internet saat membuka halaman
- **Bahasa:** PHP 8.5.12

## Kebutuhan Sistem

| Komponen | Versi / Keterangan |
|---|---|
| PHP | 8.5.12 |
| Ekstensi PHP | `pgsql`, `intl`, `mbstring`, `xml`, `curl`, dan `zip` |
| PostgreSQL | 16 |
| Composer | 2 |
| Browser | Edge, Chrome, atau Firefox versi terbaru |
| Git | Untuk clone dan riwayat perubahan |

> Ubuntu 24.04 hanya menyediakan PHP 8.3, jadi PHP 8.5 diambil dari PPA Ondrej Sury. Lihat `scripts/setup-env.sh` untuk langkah instalasinya. Untuk Windows 11, lihat `docs/SETUP_WINDOWS.md`.

## Instalasi

### 1. Ambil kode dan pasang dependensi

```bash
git clone https://github.com/rafaxputra/mini-erp.git
cd mini-erp
```

Masuk ke folder `app`, lalu instal dependensi:

```bash
cd app
composer install
```

### 2. Buat database PostgreSQL

```sql
CREATE USER minierp WITH PASSWORD 'isi-password-sendiri';
CREATE DATABASE minierp_dev OWNER minierp;
```

### 3. Buat file `.env`

Salin `env` menjadi `.env`, lalu isi koneksi database dan baseURL:

```bash
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

### 4. Jalankan migrasi dan seeder

```bash
php spark migrate --all
php spark db:seed MainSeeder
```

### 5. Jalankan aplikasi

```bash
php spark serve --port 8080
```

Buka `http://localhost:8080` di browser, lalu login dengan salah satu akun di bawah.

## Akun Demo

Akun ini hanya untuk demo lokal. Ganti passwordnya sebelum dipakai di tempat lain.

| Email | Password | Peran |
|---|---|---|
| `admin@nusantara.test` | `admin123` | Admin. Kelola master data, pengguna, dan peran, penyesuaian stok, dan laporan. Tidak bisa mencatat transaksi operasional dan tidak bisa menyetujui PO |
| `purchasing@nusantara.test` | `purchasing123` | Staff Purchasing. Membuat PO, mengelola produk dan supplier |
| `gudang@nusantara.test` | `gudang123` | Staff Gudang. Mencatat Goods Receipt dan Delivery |
| `sales@nusantara.test` | `sales123` | Staff Sales. Membuat dan mengonfirmasi SO, mengelola customer |
| `manajemen@nusantara.test` | `manajemen123` | Manajemen. Melihat laporan dan stok, serta menyetujui PO lewat antrean ACC |

## Hak Akses per Peran

| Fitur | Admin | Staff Purchasing | Staff Gudang | Staff Sales | Manajemen |
|---|:---:|:---:|:---:|:---:|:---:|
| Kategori | Ubah | - | - | - | - |
| Satuan | Ubah | - | - | - | Lihat |
| Gudang | Ubah | - | Lihat | - | Lihat |
| Produk | Ubah | Ubah | Lihat | Lihat | Lihat |
| Supplier | Ubah | Ubah | - | - | Lihat |
| Customer | Ubah | - | - | Ubah | Lihat |
| Pengguna dan peran | Ubah | - | - | - | - |
| Purchase Order | Lihat | Ubah | - | - | Ubah |
| Goods Receipt | Lihat | Lihat | Ubah | - | Lihat |
| Sales Order | Lihat | - | - | Ubah | Lihat |
| Delivery | Lihat | - | Ubah | Lihat | Lihat |
| Stok dan mutasi | Ubah | Lihat | Lihat | Lihat | Lihat |
| Laporan | Lihat | - | - | - | Lihat |

Hak akses diperiksa di sisi server pada setiap permintaan, bukan hanya dengan menyembunyikan menu. Satu peran satu tugas untuk transaksi operasional. Admin tidak mencatat transaksi operasional. Hanya Manajemen yang bisa menyetujui dan menolak PO.

## Aturan Bisnis Penting

- **PO Draft tidak mengubah stok.** Stok bertambah hanya dari Goods Receipt.
- **Delivery ditolak jika stok tidak mencukupi.** Stok berkurang hanya dari Delivery yang berhasil.
- **Setiap perubahan stok mencatat stock movement.** Stok tidak boleh negatif, dijaga di aplikasi dan di database.
- **Header dan detail transaksi disimpan dalam satu database transaction.**
- **Transaksi yang sudah diproses tidak dihapus, hanya dibatalkan dengan status.** Pembatalan wajib alasan.
- **Total transaksi dihitung dari kuantitas dikalikan harga satuan pada setiap detail, lalu dijumlahkan.**
- **Purchase Order tidak punya gudang.** Gudang ditentukan petugas saat Goods Receipt.
- **Sales Order adalah alokasi stok dari satu gudang tertentu.** Gudang pengiriman terkunci mengikuti gudang SO.
- **Batas stok maksimum adalah batas global per produk.** PO ditolak kalau stok fisik tambah pesanan berjalan sudah mencapai batas, atau kalau kuantitas baru melewati sisa kuota.
- **Sales Order bukan reservasi stok.** Dua SO bisa sama-sama lolos cek stok, dan pengiriman kedua ditolak kalau stok habis.
- **Harga yang diketik dengan pemisah ribuan dibersihkan server sebelum disimpan.**
- **Harga jual tidak boleh lebih rendah dari harga modal beli.** Produk dengan harga jual di bawah harga modal ditolak, dan SO dengan harga satuan di bawah harga modal ditolak seluruhnya.
- **Penolakan PO oleh Manajemen adalah pembatalan dengan label penolakan, bukan status baru.** Status tetap Cancelled dan alasan wajib diisi.
- **Satuan produk yang sudah punya riwayat transaksi tidak bisa diubah.**

## Menguji Ulang Alurnya

Skrip `scripts/test_flow.sh` menjalankan alur penuh lewat HTTP dan memeriksa stok di database. Password database dibaca otomatis dari `app/.env`, jadi jalankan saja dari folder root proyek saat server jalan. Skrip ini memakai akun peran yang benar untuk tiap transaksi.

```bash
bash scripts/test_flow.sh
```

Dua skrip lain menguji hak akses dan pengelolaan akun:

```bash
bash scripts/test_roles.sh
bash scripts/test_users.sh
```

Skrip uji tidak idempotent. Selalu kembalikan database ke kondisi awal dulu sebelum menjalankan ulang:

```bash
cd app
php spark db:reset
```

Perintah `db:reset` menghapus dan membuat ulang skema, menjalankan semua migrasi, lalu mengisi data awal. Cara manualnya:

```bash
php spark migrate:refresh --all
php spark db:seed MainSeeder
```

## Struktur Proyek

```text
mini-erp/
  app/                  Kode CodeIgniter 4 (Controllers, Models, Views, Migrations, Seeds)
  docs/
    BRD.md              Business Requirements Document
    SRS.md              Software Requirements Specification
    ERD.md              ERD dan data dictionary
    DFD.md              Data Flow Diagram, konteks, Level 1, dan Level 2
    TEST_CASES.md       Test case, hasil aktual, dan bug log
    pdf/                Versi PDF dari BRD, SRS, ERD, dan DFD
    activity/           Activity diagram satu per use case (uc01 sampai uc10)
    dfd/                Gambar DFD dan file sumber draw.io
  presentation/
    Mini-ERP-PT-Nusantara-Distribusi.pptx
  scripts/
    setup-env.sh        Instalasi PHP, PostgreSQL, dan Composer di VM Ubuntu 24.04
    test_flow.sh        Uji alur penuh lewat HTTP
    test_roles.sh       Uji hak akses tiap peran
    test_users.sh       Uji pengelolaan pengguna dan peran
    make_google_docs.py Generator dokumen Word dari file .md
    gen.py              Generator file aplikasi (dipakai saat pembangunan awal)
```

## Dokumen

1. `docs/BRD.md`: masalah bisnis, aktor, proses, kebutuhan, dan kriteria penerimaan.
2. `docs/SRS.md`: use case, validasi, kebutuhan nonfungsional, dan pemetaan requirement ke test case. Diagram use case gabungan ada di `docs/usecase-combined.png` (sumber draw.io di `docs/usecase-combined.drawio`), dan activity diagram satu per use case di `docs/activity/` (uc01.png sampai uc10.png, sumber draw.io di `docs/activity/uc01.drawio` sampai `uc10.drawio`), dibuat lewat draw.io web.
3. `docs/ERD.md`: diagram relasi, alasan desain, data dictionary, dan query produk terlaris.
4. `docs/DFD.md`: Data Flow Diagram, diagram konteks, Level 1 (8 proses, 7 store), dan Level 2 Kelola Purchase Order, Catat Goods Receipt, Kelola Sales Order, Proses Delivery, dan Kelola Stok dan Penyesuaian, beserta gambar PNG dan file sumber di `docs/dfd/`.
5. `docs/TEST_CASES.md`: 48 test case dengan hasil aktual dan bug log.
6. `docs/SETUP.md`: panduan setup dari nol di Ubuntu, composer create-project dan pgAdmin 4.
7. `docs/SETUP_WINDOWS.md`: panduan setup dari nol di Windows 11.

## Backup dan Restore Database

Cadangan berkala dianjurkan karena data transaksi tidak pernah dihapus.

**Backup:**

```bash
pg_dump -U minierp -h localhost -F c -f minierp_backup.dump minierp_dev
```

**Restore ke database kosong:**

```bash
createdb -U postgres -O minierp minierp_restore
pg_restore -U minierp -h localhost -d minierp_restore minierp_backup.dump
```

Setelah restore, arahkan `database.default.database` di `.env` ke database yang dipulihkan, lalu bandingkan jumlah data dan saldo stok dengan database asal.

## Pemecahan Masalah

| Masalah | Penyebab dan solusi |
|---|---|
| `could not find driver` | Ekstensi `pgsql` belum aktif di `php.ini` |
| Composer: `zip extension and unzip/7z commands are both missing` | Aktifkan `extension=zip` di `php.ini`, buka terminal baru, lalu ulangi `composer install` |
| Error `intl` saat `composer install` atau `spark` | Aktifkan `extension=intl` di `php.ini` |
| PHP 8.3 di Ubuntu 24.04 | Ubuntu 24.04 hanya menyediakan PHP 8.3. Instal PHP 8.5 dari PPA Ondrej Sury, atau jalankan `scripts/setup-env.sh` |
| `password authentication failed` | Periksa `username` dan `password` di `.env` |
| Tabel tidak ditemukan | Jalankan `php spark migrate --all` |
| Tidak bisa login | Jalankan `php spark db:seed MainSeeder`, atau `php spark db:reset` untuk mengulang dari awal |
| Port 8080 sudah dipakai | Jalankan `php spark serve --port 8081`, lalu buka alamat dengan port itu |
| Uji alur gagal setelah data berubah | Skrip uji tidak idempotent. Jalankan `php spark db:reset` dulu, lalu ulangi |
| Ingin login dua akun di dua tab | Cookie sesi dipakai bersama satu browser. Pakai jendela incognito, browser lain, atau host berbeda (`localhost` dan `127.0.0.1`, tambahkan keduanya di `$allowedHostnames` pada `App.php`) |
| Tanggal di laporan bergeser sehari | Periksa `public string $appTimezone = 'Asia/Jakarta';` di `app/Config/App.php` |

## Catatan Desain

1. Stok disimpan di `products.stock_qty` agar halaman stok cepat dibaca. `stock_movements` adalah jejak audit. Saldo stok harus selalu sama dengan jumlah movement.
2. PO tidak langsung menambah stok karena barang belum tentu datang sesuai pesanan.
3. SO tidak langsung mengurangi stok karena barang belum tentu dikirim pada hari yang sama.
4. Perubahan stok memakai kunci baris `SELECT ... FOR UPDATE` di dalam transaction, agar dua pengguna yang mengeluarkan stok yang sama secara bersamaan tidak membuat stok negatif.
5. Sistem memakai tiga gudang dan stok dicatat per gudang lewat `stock_movements`. Purchase Order tidak punya gudang karena gudang tujuan belum ditentukan sampai barang sampai. Sales Order tetap punya gudang karena SO adalah alokasi stok dari satu gudang tertentu dan pengiriman wajib keluar dari gudang itu.
6. Timezone aplikasi Asia/Jakarta, supaya tanggal di laporan dan penomoran tidak bergeser.

## Lisensi

Proyek internal PT Nusantara Distribusi. Hak penggunaan mengikuti kebijakan perusahaan.
