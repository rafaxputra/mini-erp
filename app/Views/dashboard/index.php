<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$userRole = $role ?? session()->get('user_role');
$pType = $periodType ?? 'month';
$pYear = (int) ($year ?? date('Y'));
$pMonth = (int) ($month ?? date('m'));
$pWeek = $weekStart ?? date('Y-m-d', strtotime('monday this week'));
$pLabel = $periodLabel ?? date('F Y');
$exportQueryBase = http_build_query([
    'period_type' => $pType,
    'year' => $pYear,
    'month' => $pMonth,
    'week_start' => $pWeek,
]);
$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
];

$roleMeta = match ($userRole) {
    'gudang' => [
        'pretitle' => 'Operasional Pergudangan',
        'pretitleClass' => 'text-primary',
        'title' => 'Ringkasan Gudang & Pengiriman',
        'subtitle' => 'Pantau barang yang baru tiba dari pemasok, jadwal pengiriman ke pelanggan, dan ketersediaan fisik barang.',
        'pdfOptions' => [
            'summary'   => ['label' => 'Ringkasan Operasional Gudang', 'checked' => true],
            'stock'     => ['label' => 'Daftar Stok Fisik Produk (Tanpa Harga)', 'checked' => true],
            'low_stock' => ['label' => 'Peringatan Stok Menipis / Kritis', 'checked' => true],
        ],
    ],
    'sales' => [
        'pretitle' => 'Penjualan & Layanan Pelanggan',
        'pretitleClass' => 'text-success',
        'title' => 'Dashboard & Laporan Penjualan',
        'subtitle' => 'Pantau total penjualan, produk paling diminati, serta status pesanan pelanggan.',
        'pdfOptions' => [
            'summary'      => ['label' => 'Ringkasan Kinerja Penjualan (KPI)', 'checked' => true],
            'sales'        => ['label' => 'Rincian Transaksi Penjualan (SO)', 'checked' => true],
            'top_products' => ['label' => 'Produk Terlaris Periode Ini', 'checked' => true],
            'low_stock'    => ['label' => 'Peringatan Stok Menipis / Kritis', 'checked' => false],
        ],
    ],
    'purchasing' => [
        'pretitle' => 'Pengadaan & Pemasok',
        'pretitleClass' => 'text-azure',
        'title' => 'Dashboard & Laporan Pembelian',
        'subtitle' => 'Pantau kebutuhan restok barang dari gudang/penjualan serta belanja ke pemasok.',
        'pdfOptions' => [
            'summary'   => ['label' => 'Ringkasan Pengadaan & Pembelian (KPI)', 'checked' => true],
            'purchase'  => ['label' => 'Rincian Transaksi Pembelian (PO)', 'checked' => true],
            'stock'     => ['label' => 'Daftar Stok Produk & Nilai Persediaan', 'checked' => false],
            'low_stock' => ['label' => 'Peringatan Stok Menipis / Kritis', 'checked' => true],
        ],
    ],
    'manajemen' => [
        'pretitle' => 'Ringkasan Bisnis & Pelaporan',
        'pretitleClass' => 'text-primary',
        'title' => 'Dashboard Eksekutif',
        'subtitle' => 'Pantau hasil penjualan, pengeluaran belanja barang, nilai stok di gudang, serta persetujuan pembelian dalam satu layar.',
        'pdfOptions' => [
            'summary'      => ['label' => 'Ringkasan Eksekutif (KPI Utama)', 'checked' => true],
            'sales'        => ['label' => 'Rincian Transaksi Penjualan (SO)', 'checked' => true],
            'purchase'     => ['label' => 'Rincian Transaksi Pembelian (PO)', 'checked' => true],
            'top_products' => ['label' => 'Produk Terlaris Periode Ini', 'checked' => true],
            'stock'        => ['label' => 'Daftar Stok Produk & Nilai Aset', 'checked' => false],
            'low_stock'    => ['label' => 'Peringatan Stok Menipis / Kritis', 'checked' => true],
        ],
    ],
    default => [
        'pretitle' => 'Tata Kelola Sistem & Master Data',
        'pretitleClass' => 'text-primary',
        'title' => 'Dashboard Administrator',
        'subtitle' => 'Kelola katalog produk, satuan, kategori, gudang, serta pengaturan pengguna dan hak akses.',
        'pdfOptions' => [
            'summary'      => ['label' => 'Ringkasan Eksekutif (KPI Utama)', 'checked' => true],
            'sales'        => ['label' => 'Rincian Transaksi Penjualan (SO)', 'checked' => true],
            'purchase'     => ['label' => 'Rincian Transaksi Pembelian (PO)', 'checked' => true],
            'top_products' => ['label' => 'Produk Terlaris Periode Ini', 'checked' => false],
            'stock'        => ['label' => 'Daftar Stok Produk & Nilai Aset', 'checked' => true],
            'low_stock'    => ['label' => 'Peringatan Stok Menipis / Kritis', 'checked' => true],
        ],
    ],
};
?>

<!-- ===================================================================== -->
<!-- PAGE HEADER TERPADU (JUDUL, AKSI CEPAT, EXPORT & FILTER PERIODE)      -->
<!-- ===================================================================== -->
<div class="page-header d-print-none mt-0 mb-4">
  <div class="row align-items-center g-3">
    <div class="col">
      <div class="page-pretitle text-uppercase font-monospace <?= esc($roleMeta['pretitleClass']) ?> fw-semibold"><?= esc($roleMeta['pretitle']) ?></div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <h2 class="page-title fw-bold text-dark mb-0"><?= esc($roleMeta['title']) ?></h2>
        <span class="badge bg-azure-lt text-azure px-2 py-1 fw-semibold">Periode: <?= esc($pLabel) ?></span>
      </div>
      <div class="text-secondary small mt-1"><?= esc($roleMeta['subtitle']) ?></div>
    </div>

    <div class="col-auto ms-auto">
      <div class="btn-list">
        <?php if ($userRole === 'gudang'): ?>
          <a href="/goods-receipts/create" class="btn btn-outline-primary">Catat Barang Masuk</a>
          <a href="/deliveries/create" class="btn btn-primary">Jadwalkan / Kirim Barang</a>
        <?php elseif ($userRole === 'sales'): ?>
          <?php if (!empty($revisionSoCount)): ?>
            <a href="/sales-orders?status=revision" class="btn btn-warning fw-bold"><?= esc($revisionSoCount) ?> Pesanan Butuh Revisi</a>
          <?php endif; ?>
          <a href="/sales-orders/create" class="btn btn-primary">+ Buat Pesanan Pelanggan Baru</a>
        <?php elseif ($userRole === 'purchasing'): ?>
          <a href="/purchase-orders/create" class="btn btn-primary">+ Buat Pesanan Pembelian (PO)</a>
        <?php elseif ($userRole === 'admin'): ?>
          <a href="/products/create" class="btn btn-outline-primary">+ Tambah Produk</a>
          <a href="/users/create" class="btn btn-primary">+ Tambah Pengguna</a>
        <?php endif; ?>

        <button type="button" class="btn btn-outline-danger d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4"/><path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6"/><path d="M17 18h2"/><path d="M20 15h-3v6"/><path d="M11 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1z"/></svg>
          Export PDF
        </button>

        <?php if ($userRole === 'manajemen' || $userRole === 'admin'): ?>
          <div class="dropdown">
            <button class="btn btn-success dropdown-toggle d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
              Export Excel
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="/dashboard/export?type=sales&amp;<?= $exportQueryBase ?>">Laporan Penjualan (.xlsx)</a>
              <a class="dropdown-item" href="/dashboard/export?type=purchase&amp;<?= $exportQueryBase ?>">Laporan Pembelian (.xlsx)</a>
              <a class="dropdown-item" href="/dashboard/export?type=stock&amp;<?= $exportQueryBase ?>">Laporan Stok Produk (.xlsx)</a>
            </div>
          </div>
        <?php elseif ($userRole === 'sales'): ?>
          <a class="btn btn-success d-inline-flex align-items-center gap-1" href="/dashboard/export?type=sales&amp;<?= $exportQueryBase ?>">
            Export Excel
          </a>
        <?php elseif ($userRole === 'purchasing'): ?>
          <a class="btn btn-success d-inline-flex align-items-center gap-1" href="/dashboard/export?type=purchase&amp;<?= $exportQueryBase ?>">
            Export Excel
          </a>
        <?php elseif ($userRole === 'gudang'): ?>
          <a class="btn btn-success d-inline-flex align-items-center gap-1" href="/dashboard/export?type=stock&amp;<?= $exportQueryBase ?>">
            Export Excel
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Sub-bar Filter Periode Terintegrasi -->
  <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
    <form action="/dashboard" method="get" class="d-flex flex-wrap align-items-center gap-2" id="dashboardPeriodForm">
      <span class="text-secondary small fw-semibold me-1">Filter Periode:</span>
      <select name="period_type" id="periodTypeSelect" class="form-select form-select-sm" style="width: 125px;">
        <option value="week" <?= $pType === 'week' ? 'selected' : '' ?>>Mingguan</option>
        <option value="month" <?= $pType === 'month' ? 'selected' : '' ?>>Bulanan</option>
        <option value="year" <?= $pType === 'year' ? 'selected' : '' ?>>Tahunan</option>
      </select>

      <div id="filterWeekWrap" class="<?= $pType === 'week' ? '' : 'd-none' ?>">
        <input type="date" name="week_start" class="form-control form-control-sm" value="<?= esc($pWeek) ?>" title="Mulai Tanggal (7 Hari)">
      </div>

      <div id="filterMonthWrap" class="<?= $pType === 'month' ? '' : 'd-none' ?>">
        <select name="month" class="form-select form-select-sm" style="width: 130px;">
          <?php foreach ($monthNames as $mNum => $mName): ?>
            <option value="<?= $mNum ?>" <?= $pMonth === $mNum ? 'selected' : '' ?>><?= esc($mName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="filterYearWrap" class="<?= $pType !== 'week' ? '' : 'd-none' ?>">
        <select name="year" class="form-select form-select-sm" style="width: 95px;">
          <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 3; $y--): ?>
            <option value="<?= $y ?>" <?= $pYear === $y ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-sm btn-primary px-3">Terapkan</button>
    </form>
  </div>
</div>

<?php if ($userRole === 'gudang'): ?>
<!-- ======================================================================= -->
<!-- 1. DASHBOARD KHUSUS STAFF GUDANG & LOGISTIK (RAMAH AWAM & BEBAS HARGA)  -->
<!-- ======================================================================= -->

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #1d4ed8 !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Barang Masuk</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($grThisMonth ?? 0) ?> <span class="fs-4 text-muted fw-normal">Kali</span></div>
        <div class="text-secondary small"><?= number_format((float) ($grQtyThisMonth ?? 0), 0, ',', '.') ?> unit diterima pada periode ini</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #16a34a !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Barang Dikirim</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($doThisMonth ?? 0) ?> <span class="fs-4 text-muted fw-normal">Kiriman</span></div>
        <div class="text-secondary small"><?= number_format((float) ($doQtyThisMonth ?? 0), 0, ',', '.') ?> unit keluar ke pelanggan</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Menunggu Dikirim</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($pendingDeliveriesCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Pesanan</span></div>
        <div class="text-secondary small">Pesanan pelanggan siap dikemas</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid <?= !empty($lowStock) ? '#dc2626' : '#16a34a' ?> !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Stok Hampir Habis</div>
        <div class="h1 mb-1 fw-bold font-monospace <?= !empty($lowStock) ? 'text-danger' : 'text-success' ?>"><?= count($lowStock) ?> <span class="fs-4 text-muted fw-normal">Produk</span></div>
        <div class="text-secondary small"><?= !empty($lowStock) ? 'Segera lapor ke tim pembelian' : 'Semua stok fisik aman' ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Grafik Pergerakan Barang -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Grafik Barang Masuk vs Barang Keluar (<?= esc($pLabel) ?>)</h3>
  </div>
  <div class="card-body">
    <div id="mainDashboardChart" style="min-height: 270px;"></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0 fw-bold">Pesanan Pelanggan Siap Dikirim</h3>
        <span class="badge bg-azure-lt fw-bold"><?= esc($pendingDeliveriesCount ?? 0) ?> Antrean</span>
      </div>
      <?php if (empty($pendingDeliveries)): ?>
        <div class="card-body text-center py-4 text-secondary">Semua pesanan pelanggan sudah diproses kirim.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr><th>No. Pesanan</th><th>Pelanggan &amp; Gudang</th><th class="text-center">Jumlah</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
              <?php foreach ($pendingDeliveries as $pd): ?>
              <tr>
                <td><code class="text-dark fw-bold"><?= esc($pd['so_number']) ?></code></td>
                <td><div class="fw-semibold"><?= esc($pd['customer_name']) ?></div><div class="small text-muted"><?= esc($pd['warehouse_name']) ?></div></td>
                <td class="text-center fw-bold"><?= esc($pd['total_qty']) ?></td>
                <td class="text-end"><a class="btn btn-sm btn-primary" href="/deliveries/create?sales_order_id=<?= esc($pd['id']) ?>">Proses Kirim</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0 fw-bold">Jadwal Kedatangan Barang dari Pemasok</h3>
        <span class="badge bg-primary-lt fw-bold"><?= esc($pendingReceiptsCount ?? 0) ?> Menunggu</span>
      </div>
      <?php if (empty($pendingReceipts)): ?>
        <div class="card-body text-center py-4 text-secondary">Belum ada barang datang dari pemasok saat ini.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr><th>No. Pembelian</th><th>Pemasok</th><th class="text-center">Jumlah</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
              <?php foreach ($pendingReceipts as $pr): ?>
              <tr>
                <td><code class="text-dark fw-bold"><?= esc($pr['po_number']) ?></code></td>
                <td><div class="fw-semibold"><?= esc($pr['supplier_name']) ?></div><div class="small text-muted">Lokasi simpan dipilih saat terima</div></td>
                <td class="text-center fw-bold"><?= esc($pr['total_qty']) ?></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="/goods-receipts/create?purchase_order_id=<?= esc($pr['id']) ?>">Terima Barang</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3"><h3 class="card-title m-0 fw-bold">Barang dengan Stok Menipis</h3></div>
      <?php if (empty($lowStock)): ?>
        <div class="card-body text-center py-5 text-secondary">Seluruh stok fisik aman.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead><tr><th>Produk</th><th class="text-center">Sisa</th><th class="text-center">Batas Min</th></tr></thead>
            <tbody>
              <?php foreach ($lowStock as $p): ?>
              <tr>
                <td><div class="fw-semibold"><?= esc($p['name']) ?></div><div class="small text-muted"><?= esc($p['code']) ?></div></td>
                <td class="text-center"><span class="badge bg-danger-lt fw-bold"><?= esc($p['stock_qty']) ?> <?= esc($p['unit']) ?></span></td>
                <td class="text-center text-muted"><?= esc($p['min_stock']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php elseif ($userRole === 'sales'): ?>
<!-- ======================================================================= -->
<!-- 2. DASHBOARD & LAPORAN KHUSUS STAFF SALES                               -->
<!-- ======================================================================= -->

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #16a34a !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Total Nilai Penjualan</div>
        <div class="h2 mb-1 fw-bold text-success font-monospace">Rp <?= number_format((float) ($salesThisMonth ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small">Dari <?= esc($ordersThisMonth ?? 0) ?> pesanan pada <?= esc($pLabel) ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #f59e0b !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Perlu Ditindaklanjuti</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc(($draftSoCount ?? 0) + ($revisionSoCount ?? 0)) ?> <span class="fs-4 text-muted fw-normal">Pesanan</span></div>
        <div class="text-secondary small"><?= esc($draftSoCount ?? 0) ?> Draf &bull; <?= esc($revisionSoCount ?? 0) ?> Butuh Revisi</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Sudah Terkirim</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($deliveredSoCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Pesanan</span></div>
        <div class="text-secondary small">Diterima pelanggan pada periode ini</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #1d4ed8 !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Pelanggan Terdaftar</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($customerCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Mitra</span></div>
        <div class="text-secondary small">Perusahaan &amp; toko rekanan aktif</div>
      </div>
    </div>
  </div>
</div>

<!-- Grafik Tren Penjualan -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Grafik Perkembangan Penjualan (<?= esc($pLabel) ?>)</h3>
  </div>
  <div class="card-body">
    <div id="mainDashboardChart" style="min-height: 270px;"></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0 fw-bold">Laporan Pesanan Pelanggan (<?= esc($pLabel) ?>)</h3>
        <a href="/sales-orders" class="btn btn-sm btn-outline-primary d-print-none">Kelola Pesanan</a>
      </div>
      <?php if (empty($recentOrders)): ?>
        <div class="card-body text-center py-5 text-secondary">Belum ada transaksi penjualan pada periode ini.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover table-striped">
            <thead><tr><th>No. Pesanan</th><th>Pelanggan</th><th>Tanggal</th><th class="text-end">Total</th><th class="text-center">Status</th></tr></thead>
            <tbody>
              <?php foreach ($recentOrders as $ro): $sb = status_badge($ro['status']); ?>
              <tr>
                <td><a href="/sales-orders/show/<?= esc($ro['id']) ?>" class="fw-bold font-monospace"><?= esc($ro['so_number']) ?></a></td>
                <td class="fw-medium"><?= esc($ro['customer_name']) ?></td>
                <td class="text-secondary small"><?= date('d/m/Y', strtotime($ro['order_date'])) ?></td>
                <td class="text-end font-monospace fw-bold">Rp <?= number_format((float) $ro['total_amount'], 0, ',', '.') ?></td>
                <td class="text-center"><span class="badge <?= $sb['badge'] ?>"><?= esc($sb['label']) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3"><h3 class="card-title m-0 fw-bold">10 Produk Paling Laris</h3></div>
      <?php if (empty($topProducts)): ?>
        <div class="card-body text-center py-5 text-secondary">Belum ada data penjualan produk.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead><tr><th>Produk</th><th class="text-center">Terjual</th><th class="text-end">Total Penjualan</th></tr></thead>
            <tbody>
              <?php foreach ($topProducts as $tp): ?>
              <tr>
                <td><div class="fw-semibold"><?= esc($tp['name']) ?></div><div class="small text-muted"><?= esc($tp['code']) ?></div></td>
                <td class="text-center"><span class="badge bg-success-lt fw-bold"><?= esc($tp['total_qty']) ?> <?= esc($tp['unit']) ?></span></td>
                <td class="text-end font-monospace fw-semibold">Rp <?= number_format((float) $tp['total_sales'], 0, ',', '.') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php elseif ($userRole === 'purchasing'): ?>
<!-- ======================================================================= -->
<!-- 3. DASHBOARD & LAPORAN KHUSUS STAFF PURCHASING                          -->
<!-- ======================================================================= -->

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Total Belanja Pembelian</div>
        <div class="h2 mb-1 fw-bold text-primary font-monospace">Rp <?= number_format((float) ($purchasesThisMonth ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($ordersThisMonth ?? 0) ?> pesanan pada <?= esc($pLabel) ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #f59e0b !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Menunggu Persetujuan</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($draftPoCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">PO</span></div>
        <div class="text-secondary small">Menunggu ACC dari Pimpinan</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #1d4ed8 !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Menunggu Barang Tiba</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($approvedPoCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">PO</span></div>
        <div class="text-secondary small">Sudah disetujui, siap diterima Gudang</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid <?= !empty($lowStock) ? '#dc2626' : '#16a34a' ?> !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Perlu Segera Dipesan</div>
        <div class="h1 mb-1 fw-bold font-monospace <?= !empty($lowStock) ? 'text-danger' : 'text-success' ?>"><?= count($lowStock) ?> <span class="fs-4 text-muted fw-normal">Produk</span></div>
        <div class="text-secondary small">Stok di bawah batas aman</div>
      </div>
    </div>
  </div>
</div>

<?php if (! empty($restockAlerts)): ?>
<div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #f59e0b !important;">
  <div class="card-header bg-warning-lt py-3 d-flex justify-content-between align-items-center">
    <h3 class="card-title m-0 fw-bold text-dark">Notifikasi Kebutuhan Barang dari Pesanan Pelanggan (Stok Kurang)</h3>
    <span class="badge bg-warning text-white fw-bold"><?= count($restockAlerts) ?> Permintaan</span>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover mb-0">
      <thead><tr><th>No. SO</th><th>Produk</th><th>Gudang</th><th class="text-center">Diminta</th><th class="text-center">Tersedia</th><th class="text-center">Kurang (Min. Beli)</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($restockAlerts as $al): ?>
        <tr>
          <td><code class="text-dark fw-bold"><?= esc($al['sales_order_number']) ?></code></td>
          <td><div class="fw-semibold"><?= esc($al['product_name']) ?></div><div class="small text-muted"><?= esc($al['product_code']) ?></div></td>
          <td><span class="badge bg-secondary-lt"><?= esc($al['warehouse_name']) ?></span></td>
          <td class="text-center font-monospace"><?= esc($al['requested_qty']) ?> <?= esc($al['unit'] ?? 'unit') ?></td>
          <td class="text-center font-monospace"><?= esc($al['available_qty']) ?> <?= esc($al['unit'] ?? 'unit') ?></td>
          <td class="text-center"><span class="badge bg-danger-lt fw-bold font-monospace"><?= esc($al['shortage_qty']) ?> <?= esc($al['unit'] ?? 'unit') ?></span></td>
          <td class="text-end"><a href="/purchase-orders/create?product_id=<?= esc($al['product_id']) ?>&qty=<?= esc($al['shortage_qty']) ?>" class="btn btn-sm btn-primary">Buat PO (Min. <?= esc($al['shortage_qty']) ?>)</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Grafik Tren Pembelian -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Grafik Tren Belanja Pembelian (<?= esc($pLabel) ?>)</h3>
  </div>
  <div class="card-body">
    <div id="mainDashboardChart" style="min-height: 260px;"></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3"><h3 class="card-title m-0 fw-bold">Barang dengan Stok Menipis (Restock)</h3></div>
      <?php if (empty($lowStock)): ?>
        <div class="card-body text-center py-5 text-secondary">Persediaan seluruh produk mencukupi.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead><tr><th>Produk</th><th class="text-center">Sisa</th><th class="text-center">Min</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
              <?php foreach ($lowStock as $p): $suggestedQty = max(10, ((int) $p['min_stock'] * 2) - (int) $p['stock_qty']); ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= esc($p['name']) ?></div>
                  <div class="small text-muted"><?= esc($p['code']) ?></div>
                  <?php if (! empty($p['active_po_number'])): ?>
                    <span class="badge bg-warning-lt mt-1">Sedang dipesan: <?= esc($p['active_po_number']) ?> (<?= esc($p['active_po_qty']) ?> <?= esc($p['unit']) ?>)</span>
                  <?php endif; ?>
                </td>
                <td class="text-center"><span class="badge bg-danger-lt fw-bold"><?= esc($p['stock_qty']) ?> <?= esc($p['unit']) ?></span></td>
                <td class="text-center text-muted"><?= esc($p['min_stock']) ?></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="/purchase-orders/create?product_id=<?= esc($p['id']) ?>&qty=<?= $suggestedQty ?>">Buat PO</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3"><h3 class="card-title m-0 fw-bold">Laporan Pembelian (<?= esc($pLabel) ?>)</h3></div>
      <?php if (empty($recentPurchases)): ?>
        <div class="card-body text-center py-5 text-secondary">Belum ada pembelian pada periode ini.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead><tr><th>No. PO</th><th>Pemasok</th><th class="text-end">Total</th><th class="text-center">Status</th></tr></thead>
            <tbody>
              <?php foreach ($recentPurchases as $rp): $sb = status_badge($rp['status']); ?>
              <tr>
                <td><a href="/purchase-orders/show/<?= esc($rp['id']) ?>" class="fw-bold font-monospace"><?= esc($rp['po_number']) ?></a></td>
                <td class="small fw-medium"><?= esc($rp['supplier_name']) ?></td>
                <td class="text-end font-monospace small fw-bold">Rp <?= number_format((float) $rp['total_amount'], 0, ',', '.') ?></td>
                <td class="text-center"><span class="badge <?= $sb['badge'] ?>"><?= esc($sb['label']) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php elseif ($userRole === 'manajemen'): ?>
<!-- ======================================================================= -->
<!-- 4. DASHBOARD EKSEKUTIF & LAPORAN TERPADU (DIREKTUR / MANAJEMEN)         -->
<!-- ======================================================================= -->

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #16a34a !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Total Penjualan</div>
        <div class="h2 mb-1 fw-bold text-success font-monospace">Rp <?= number_format((float) ($totalSales ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($soCount ?? 0) ?> pesanan pelanggan (<?= esc($pLabel) ?>)</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Total Pembelian</div>
        <div class="h2 mb-1 fw-bold text-primary font-monospace">Rp <?= number_format((float) ($totalPurchases ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($poCount ?? 0) ?> pesanan ke pemasok</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <?php $marginPositive = ($netMargin ?? 0) >= 0; ?>
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid <?= $marginPositive ? '#16a34a' : '#dc2626' ?> !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Untung / Rugi Periode Ini</div>
        <div class="h2 mb-1 fw-bold font-monospace <?= $marginPositive ? 'text-success' : 'text-danger' ?>">Rp <?= number_format((float) ($netMargin ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= $marginPositive ? 'Penjualan lebih besar daripada biaya belanja stok' : 'Biaya belanja stok lebih besar daripada penjualan' ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #f59e0b !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Total Nilai Stok Gudang</div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) ($stockValue ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($lowStockCount ?? 0) ?> produk perlu restok ulang</div>
      </div>
    </div>
  </div>
</div>

<!-- Grafik Penjualan vs Pembelian -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Grafik Perbandingan Penjualan vs Pembelian (<?= esc($pLabel) ?>)</h3>
  </div>
  <div class="card-body">
    <div id="mainDashboardChart" style="min-height: 290px;"></div>
  </div>
</div>

<!-- Antrean Persetujuan PO -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
    <h3 class="card-title m-0 fw-bold">Antrean Persetujuan Pembelian (Butuh ACC Direktur)</h3>
    <span class="badge <?= !empty($pendingPoCount) ? 'bg-warning text-white' : 'bg-success-lt' ?> fw-bold"><?= esc($pendingPoCount ?? 0) ?> Menunggu</span>
  </div>
  <?php if (empty($pendingApprovalPOs)): ?>
    <div class="card-body text-center py-4 text-secondary">Tidak ada pesanan pembelian yang menunggu persetujuan.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover">
        <thead><tr><th>No. PO</th><th>Pemasok</th><th>Pembuat</th><th class="text-end">Total Nilai</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
          <?php foreach ($pendingApprovalPOs as $po): ?>
          <tr>
            <td><code class="text-dark fw-bold"><?= esc($po['po_number']) ?></code></td>
            <td class="fw-semibold"><?= esc($po['supplier_name']) ?></td>
            <td class="small text-secondary"><?= esc($po['creator_name']) ?></td>
            <td class="text-end font-monospace fw-bold">Rp <?= number_format((float) $po['total_amount'], 0, ',', '.') ?></td>
            <td class="text-end"><a class="btn btn-sm btn-warning fw-semibold" href="/purchase-orders/show/<?= esc($po['id']) ?>">Review / ACC</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (($pendingPoCount ?? 0) > 5): ?>
      <div class="card-footer bg-transparent d-flex justify-content-between align-items-center py-2">
        <span class="small text-secondary">Menampilkan 5 dari <?= esc($pendingPoCount) ?> antrean</span>
        <a href="/purchase-orders?status=draft" class="btn btn-sm btn-outline-warning">Lihat Semua</a>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Laporan Terpadu (Penjualan, Pembelian, Top Produk, & Kapasitas Gudang) -->
<div class="row g-3 mb-4">
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0 fw-bold">Laporan Penjualan (<?= esc($pLabel) ?>)</h3>
        <a href="/dashboard/export?type=sales&amp;<?= $exportQueryBase ?>" class="btn btn-sm btn-outline-success d-print-none">Excel</a>
      </div>
      <?php if (empty($reportSales)): ?>
        <div class="card-body text-center py-4 text-secondary">Belum ada data penjualan pada periode ini.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead><tr><th>No. SO</th><th>Pelanggan</th><th class="text-end">Total</th><th class="text-center">Status</th></tr></thead>
            <tbody>
              <?php foreach ($reportSales as $rs): $sb = status_badge($rs['status']); ?>
              <tr>
                <td><a href="/sales-orders/show/<?= esc($rs['id']) ?>" class="fw-bold font-monospace"><?= esc($rs['so_number']) ?></a></td>
                <td class="small fw-medium"><?= esc($rs['customer_name']) ?></td>
                <td class="text-end font-monospace fw-bold">Rp <?= number_format((float) $rs['total_amount'], 0, ',', '.') ?></td>
                <td class="text-center"><span class="badge <?= $sb['badge'] ?>"><?= esc($sb['label']) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0 fw-bold">Laporan Pembelian (<?= esc($pLabel) ?>)</h3>
        <a href="/dashboard/export?type=purchase&amp;<?= $exportQueryBase ?>" class="btn btn-sm btn-outline-success d-print-none">Excel</a>
      </div>
      <?php if (empty($reportPurchases)): ?>
        <div class="card-body text-center py-4 text-secondary">Belum ada data pembelian pada periode ini.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead><tr><th>No. PO</th><th>Pemasok</th><th class="text-end">Total</th><th class="text-center">Status</th></tr></thead>
            <tbody>
              <?php foreach ($reportPurchases as $rp): $sb = status_badge($rp['status']); ?>
              <tr>
                <td><a href="/purchase-orders/show/<?= esc($rp['id']) ?>" class="fw-bold font-monospace"><?= esc($rp['po_number']) ?></a></td>
                <td class="small fw-medium"><?= esc($rp['supplier_name']) ?></td>
                <td class="text-end font-monospace fw-bold">Rp <?= number_format((float) $rp['total_amount'], 0, ',', '.') ?></td>
                <td class="text-center"><span class="badge <?= $sb['badge'] ?>"><?= esc($sb['label']) ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3"><h3 class="card-title m-0 fw-bold">Produk Paling Laris (<?= esc($pLabel) ?>)</h3></div>
      <?php if (empty($topProducts)): ?>
        <div class="card-body text-center py-4 text-secondary">Belum ada penjualan produk pada periode ini.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead><tr><th>Produk</th><th class="text-center">Terjual</th><th class="text-end">Total Penjualan</th></tr></thead>
            <tbody>
              <?php foreach ($topProducts as $tp): ?>
              <tr>
                <td><div class="fw-semibold"><?= esc($tp['name']) ?></div><div class="small text-muted"><?= esc($tp['code']) ?></div></td>
                <td class="text-center"><span class="badge bg-success-lt fw-bold"><?= esc($tp['total_qty']) ?> <?= esc($tp['unit']) ?></span></td>
                <td class="text-end font-monospace fw-bold">Rp <?= number_format((float) $tp['total_sales'], 0, ',', '.') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0 fw-bold">Sebaran Stok per Gudang</h3>
        <a href="/dashboard/export?type=stock&amp;<?= $exportQueryBase ?>" class="btn btn-sm btn-outline-success d-print-none">Export Stok</a>
      </div>
      <div class="table-responsive">
        <table class="table table-vcenter card-table table-hover">
          <thead><tr><th>Gudang</th><th class="text-center">Jumlah Jenis Produk</th><th class="text-end">Total Unit Fisik</th></tr></thead>
          <tbody>
            <?php foreach (($warehouseSummary ?? []) as $ws): ?>
            <tr>
              <td><div class="fw-semibold"><?= esc($ws['name']) ?></div><div class="small text-muted"><?= esc($ws['location']) ?></div></td>
              <td class="text-center"><span class="badge bg-azure-lt"><?= esc($ws['itemCount']) ?> jenis</span></td>
              <td class="text-end font-monospace fw-bold"><?= number_format((float) $ws['totalQty'], 0, ',', '.') ?> unit</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ======================================================================= -->
<!-- 5. DASHBOARD ADMINISTRATOR                                              -->
<!-- ======================================================================= -->

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #1d4ed8 !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Total Produk Terdaftar</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($productCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Produk</span></div>
        <div class="text-secondary small"><?= esc($categoryCount ?? 0) ?> Kategori &bull; <?= esc($warehouseCount ?? 0) ?> Gudang</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #f59e0b !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Nilai Persediaan</div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) ($stockValue ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small">Estimasi modal seluruh stok gudang</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #16a34a !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Penjualan (<?= esc($pLabel) ?>)</div>
        <div class="h2 mb-1 fw-bold text-success font-monospace">Rp <?= number_format((float) ($salesThisMonth ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($ordersThisMonth ?? 0) ?> pesanan pelanggan</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100 stat-card-hover" style="border-top: 4px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="text-secondary text-uppercase fw-bold small mb-2">Pengguna Aktif</div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($userCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Akun</span></div>
        <div class="text-secondary small">Akses lintas peran aktif</div>
      </div>
    </div>
  </div>
</div>

<!-- Grafik Penjualan vs Pembelian -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Grafik Penjualan vs Pembelian (<?= esc($pLabel) ?>)</h3>
  </div>
  <div class="card-body">
    <div id="mainDashboardChart" style="min-height: 270px;"></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3"><h3 class="card-title m-0 fw-bold">Produk dengan Stok Menipis</h3></div>
      <?php if (empty($lowStock)): ?>
        <div class="card-body text-center py-4 text-secondary">Seluruh stok aman.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead><tr><th>Produk</th><th class="text-center">Sisa</th><th class="text-center">Min</th></tr></thead>
            <tbody>
              <?php foreach ($lowStock as $p): ?>
              <tr>
                <td><div class="fw-semibold"><?= esc($p['name']) ?></div><div class="small text-muted"><?= esc($p['code']) ?></div></td>
                <td class="text-center"><span class="badge bg-danger-lt fw-bold"><?= esc($p['stock_qty']) ?> <?= esc($p['unit']) ?></span></td>
                <td class="text-center text-muted"><?= esc($p['min_stock']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3"><h3 class="card-title m-0 fw-bold">Produk Paling Laris</h3></div>
      <?php if (empty($topProducts)): ?>
        <div class="card-body text-center py-4 text-secondary">Belum ada data penjualan.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead><tr><th>Produk</th><th class="text-center">Terjual</th><th class="text-end">Total Penjualan</th></tr></thead>
            <tbody>
              <?php foreach ($topProducts as $tp): ?>
              <tr>
                <td><div class="fw-semibold"><?= esc($tp['name']) ?></div><div class="small text-muted"><?= esc($tp['code']) ?></div></td>
                <td class="text-center"><span class="badge bg-success-lt fw-bold"><?= esc($tp['total_qty']) ?> <?= esc($tp['unit']) ?></span></td>
                <td class="text-end font-monospace fw-bold">Rp <?= number_format((float) $tp['total_sales'], 0, ',', '.') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Toggle input filter Mingguan / Bulanan / Tahunan
  const periodSelect = document.getElementById('periodTypeSelect');
  const weekWrap = document.getElementById('filterWeekWrap');
  const monthWrap = document.getElementById('filterMonthWrap');
  const yearWrap = document.getElementById('filterYearWrap');
  if (periodSelect) {
    periodSelect.addEventListener('change', function () {
      const val = this.value;
      weekWrap.classList.toggle('d-none', val !== 'week');
      monthWrap.classList.toggle('d-none', val !== 'month');
      yearWrap.classList.toggle('d-none', val === 'week');
    });
  }

  // Render ApexCharts interaktif
  const chartEl = document.getElementById('mainDashboardChart');
  if (chartEl && typeof ApexCharts !== 'undefined') {
    const userRole = <?= json_encode($userRole) ?>;
    const labels = <?= json_encode($chartData['labels'] ?? []) ?>;
    const primaryData = <?= json_encode($chartData['primary'] ?? []) ?>;
    const secondaryData = <?= json_encode($chartData['secondary'] ?? []) ?>;

    let series = [];
    let colors = ['#1d4ed8', '#f59e0b'];
    let isCurrency = true;

    if (userRole === 'gudang') {
      isCurrency = false;
      series = [
        { name: 'Barang Masuk (Unit)', data: primaryData },
        { name: 'Barang Keluar (Unit)', data: secondaryData }
      ];
      colors = ['#1d4ed8', '#16a34a'];
    } else if (userRole === 'sales') {
      series = [{ name: 'Total Penjualan', data: primaryData }];
      colors = ['#16a34a'];
    } else if (userRole === 'purchasing') {
      series = [{ name: 'Belanja Pembelian', data: secondaryData }];
      colors = ['#0284c7'];
    } else {
      series = [
        { name: 'Penjualan', data: primaryData },
        { name: 'Pembelian', data: secondaryData }
      ];
      colors = ['#16a34a', '#1d4ed8'];
    }

    const chart = new ApexCharts(chartEl, {
      chart: {
        type: 'area',
        height: 280,
        toolbar: { show: false },
        fontFamily: 'Plus Jakarta Sans, sans-serif'
      },
      series: series,
      colors: colors,
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2.5 },
      fill: {
        type: 'gradient',
        gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 }
      },
      xaxis: {
        categories: labels,
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        labels: {
          formatter: function (val) {
            if (!isCurrency) return Math.round(val) + ' unit';
            if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
            if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
            return 'Rp ' + val;
          }
        }
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return isCurrency
              ? 'Rp ' + Math.round(val).toLocaleString('id-ID')
              : Math.round(val).toLocaleString('id-ID') + ' unit';
          }
        }
      },
      grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
      legend: { position: 'top', horizontalAlign: 'right' }
    });
    chart.render();
  }
});
</script>

<!-- Modal Pengaturan Export PDF (Pilih Data yang Ditampilkan) -->
<div class="modal modal-blur fade d-print-none" id="exportPdfModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="/dashboard/export-pdf" method="get" target="_blank" class="modal-content">
      <input type="hidden" name="period_type" value="<?= esc($pType) ?>">
      <input type="hidden" name="year" value="<?= esc($pYear) ?>">
      <input type="hidden" name="month" value="<?= esc($pMonth) ?>">
      <input type="hidden" name="week_start" value="<?= esc($pWeek) ?>">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">Pengaturan Export Laporan PDF</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info py-2 px-3 small mb-3">
          <strong>Periode Aktif:</strong> <?= esc($pLabel) ?><br>
          Dokumen akan dicetak dalam format laporan resmi A4.
        </div>

        <label class="form-label fw-bold mb-2">Pilih Bagian Data yang Ingin Ditampilkan:</label>
        <div class="d-flex flex-column gap-2 mb-3">
          <?php foreach ($roleMeta['pdfOptions'] as $secKey => $secInfo): ?>
            <label class="form-check form-check-inline border rounded-2 px-3 py-2 m-0 bg-light-lt d-flex align-items-center gap-2" style="cursor: pointer;">
              <input class="form-check-input m-0" type="checkbox" name="sections[]" value="<?= esc($secKey) ?>" <?= $secInfo['checked'] ? 'checked' : '' ?>>
              <span class="form-check-label fw-medium text-dark"><?= esc($secInfo['label']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>

        <div>
          <label class="form-label fw-semibold small">Catatan Tambahan di Dokumen (Opsional)</label>
          <input type="text" name="report_note" class="form-control form-control-sm" placeholder="Contoh: Laporan evaluasi bulanan untuk rapat direksi">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-1" onclick="setTimeout(function(){ var m = bootstrap.Modal.getInstance(document.getElementById('exportPdfModal')); if(m) m.hide(); }, 250);">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2"/><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4"/><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z"/></svg>
          Buat &amp; Cetak PDF
        </button>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
