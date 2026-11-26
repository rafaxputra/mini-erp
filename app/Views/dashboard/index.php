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

$renderDelta = static function (?float $delta): string {
    if ($delta === null) {
        return '<span class="kpi-delta is-neutral">Periode baru</span>';
    }
    if ($delta > 0) {
        return '<span class="kpi-delta is-up">&uarr; ' . number_format($delta, 1, ',', '.') . '%</span>';
    }
    if ($delta < 0) {
        return '<span class="kpi-delta is-down">&darr; ' . number_format(abs($delta), 1, ',', '.') . '%</span>';
    }
    return '<span class="kpi-delta is-neutral">0,0%</span>';
};

$roleMeta = match ($userRole) {
    'gudang' => [
        'pretitle' => 'Operasional Pergudangan',
        'title' => 'Logistik & Pergudangan',
        'subtitle' => 'Kontrol barang masuk, antrean pengiriman pelanggan, dan kesehatan stok fisik.',
        'pdfOptions' => [
            'summary'   => ['label' => 'Ringkasan Operasional Gudang', 'checked' => true],
            'stock'     => ['label' => 'Daftar Stok Fisik Produk (Tanpa Harga)', 'checked' => true],
            'low_stock' => ['label' => 'Peringatan Stok Menipis / Kritis', 'checked' => true],
        ],
    ],
    'sales' => [
        'pretitle' => 'Penjualan & Pelanggan',
        'title' => 'Performa & Pipeline Penjualan',
        'subtitle' => 'Pantau omzet, distribusi status SO, pelanggan utama, dan produk terlaris.',
        'pdfOptions' => [
            'summary'      => ['label' => 'Ringkasan Kinerja Penjualan (KPI)', 'checked' => true],
            'sales'        => ['label' => 'Rincian Transaksi Penjualan (SO)', 'checked' => true],
            'top_products' => ['label' => 'Produk Terlaris Periode Ini', 'checked' => true],
            'low_stock'    => ['label' => 'Peringatan Stok Menipis / Kritis', 'checked' => false],
        ],
    ],
    'purchasing' => [
        'pretitle' => 'Pengadaan & Pemasok',
        'title' => 'Pengadaan & Radar Restok',
        'subtitle' => 'Kelola permintaan restok, status persetujuan PO, dan kontribusi pemasok.',
        'pdfOptions' => [
            'summary'   => ['label' => 'Ringkasan Pengadaan & Pembelian (KPI)', 'checked' => true],
            'purchase'  => ['label' => 'Rincian Transaksi Pembelian (PO)', 'checked' => true],
            'stock'     => ['label' => 'Daftar Stok Produk & Nilai Persediaan', 'checked' => false],
            'low_stock' => ['label' => 'Peringatan Stok Menipis / Kritis', 'checked' => true],
        ],
    ],
    'manajemen' => [
        'pretitle' => 'Executive Command Center',
        'title' => 'Dashboard Eksekutif',
        'subtitle' => 'Arus kas penjualan vs belanja, komposisi kategori, sebaran gudang, dan antrean ACC PO.',
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
        'title' => 'Dashboard Administrator',
        'subtitle' => 'Ringkasan katalog produk, pengguna aktif, serta aktivitas transaksi sistem.',
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
<!-- COMPACT EXECUTIVE HEADER & SEGMENTED PERIOD SWITCHER                  -->
<!-- ===================================================================== -->
<div class="d-print-none mb-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <div>
      <div class="page-pretitle text-uppercase mb-1"><?= esc($roleMeta['pretitle']) ?></div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <h1 class="page-title mb-0"><?= esc($roleMeta['title']) ?></h1>
        <span class="badge bg-primary-lt text-primary px-2 py-1 fw-semibold"><?= esc($pLabel) ?></span>
      </div>
      <div class="text-secondary small mt-1"><?= esc($roleMeta['subtitle']) ?></div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2">
      <?php if ($userRole === 'gudang'): ?>
        <a href="/goods-receipts?status=pending_po" class="btn btn-outline-primary btn-sm px-3">+ Barang Masuk</a>
        <a href="/deliveries?status=pending_so" class="btn btn-primary btn-sm px-3">+ Kirim Barang</a>
      <?php elseif ($userRole === 'sales'): ?>
        <?php if (!empty($revisionSoCount)): ?>
          <a href="/sales-orders?status=revision" class="btn btn-warning btn-sm px-3 fw-bold"><?= esc($revisionSoCount) ?> Butuh Revisi</a>
        <?php endif; ?>
        <a href="/sales-orders/create" class="btn btn-primary btn-sm px-3">+ Buat SO Baru</a>
      <?php elseif ($userRole === 'purchasing'): ?>
        <a href="/purchase-orders/create" class="btn btn-primary btn-sm px-3">+ Buat PO Baru</a>
      <?php elseif ($userRole === 'admin'): ?>
        <a href="/products/create" class="btn btn-outline-primary btn-sm px-3">+ Produk</a>
        <a href="/users/create" class="btn btn-primary btn-sm px-3">+ Pengguna</a>
      <?php endif; ?>

      <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-inline-flex align-items-center gap-1 px-3" type="button" data-bs-toggle="dropdown">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
          Export
        </button>
        <div class="dropdown-menu dropdown-menu-end" style="border-radius: 12px;">
          <button type="button" class="dropdown-item text-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
            Cetak Laporan PDF (A4)
          </button>
          <div class="dropdown-divider"></div>
          <?php if ($userRole === 'manajemen' || $userRole === 'admin'): ?>
            <a class="dropdown-item" href="/dashboard/export?type=sales&amp;<?= $exportQueryBase ?>">Excel Penjualan (.xlsx)</a>
            <a class="dropdown-item" href="/dashboard/export?type=purchase&amp;<?= $exportQueryBase ?>">Excel Pembelian (.xlsx)</a>
            <a class="dropdown-item" href="/dashboard/export?type=stock&amp;<?= $exportQueryBase ?>">Excel Stok Produk (.xlsx)</a>
          <?php elseif ($userRole === 'sales'): ?>
            <a class="dropdown-item" href="/dashboard/export?type=sales&amp;<?= $exportQueryBase ?>">Excel Penjualan (.xlsx)</a>
          <?php elseif ($userRole === 'purchasing'): ?>
            <a class="dropdown-item" href="/dashboard/export?type=purchase&amp;<?= $exportQueryBase ?>">Excel Pembelian (.xlsx)</a>
          <?php elseif ($userRole === 'gudang'): ?>
            <a class="dropdown-item" href="/dashboard/export?type=stock&amp;<?= $exportQueryBase ?>">Excel Stok Produk (.xlsx)</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Segmented Period Filter Bar (Auto-Submit) -->
  <form action="/dashboard" method="get" id="dashboardPeriodForm" class="d-flex flex-wrap align-items-center justify-content-between gap-2 bg-white border rounded-3 px-3 py-2 shadow-sm">
    <input type="hidden" name="period_type" id="periodTypeInput" value="<?= esc($pType) ?>">
    <div class="d-flex flex-wrap align-items-center gap-2">
      <span class="text-secondary small fw-semibold me-1">Periode:</span>
      <div class="erp-segmented">
        <button type="button" class="erp-segmented-btn <?= $pType === 'week' ? 'active' : '' ?>" data-period="week">Mingguan</button>
        <button type="button" class="erp-segmented-btn <?= $pType === 'month' ? 'active' : '' ?>" data-period="month">Bulanan</button>
        <button type="button" class="erp-segmented-btn <?= $pType === 'year' ? 'active' : '' ?>" data-period="year">Tahunan</button>
      </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2">
      <div id="filterWeekWrap" class="<?= $pType === 'week' ? '' : 'd-none' ?>">
        <input type="date" name="week_start" class="form-control form-control-sm js-auto-submit" value="<?= esc($pWeek) ?>" title="Mulai Tanggal (7 Hari)">
      </div>
      <div id="filterMonthWrap" class="<?= $pType === 'month' ? '' : 'd-none' ?>">
        <select name="month" class="form-select form-select-sm js-auto-submit" style="width: 135px;">
          <?php foreach ($monthNames as $mNum => $mName): ?>
            <option value="<?= $mNum ?>" <?= $pMonth === $mNum ? 'selected' : '' ?>><?= esc($mName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div id="filterYearWrap" class="<?= $pType !== 'week' ? '' : 'd-none' ?>">
        <select name="year" class="form-select form-select-sm js-auto-submit" style="width: 95px;">
          <?php for ($y = (int) date('Y'); $y >= (int) date('Y') - 3; $y--): ?>
            <option value="<?= $y ?>" <?= $pYear === $y ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>
  </form>
</div>

<?php if ($userRole === 'gudang'): ?>
<!-- ======================================================================= -->
<!-- 1. DASHBOARD GUDANG & LOGISTIK                                          -->
<!-- ======================================================================= -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Barang Masuk (GR)</span>
        <span class="kpi-icon-pill kpi-icon-primary"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12"/><path d="m8 11 4 4 4-4"/><path d="M8 5H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-4"/></svg></span>
      </div>
      <div class="kpi-value mb-2"><?= number_format((float) ($grQtyThisMonth ?? 0), 0, ',', '.') ?> <span class="fs-5 text-secondary fw-normal">unit</span></div>
      <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="text-secondary small"><?= esc($grThisMonth ?? 0) ?> penerimaan</span>
        <?= $renderDelta($deltaGrQty ?? null) ?>
      </div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Barang Keluar (DO)</span>
        <span class="kpi-icon-pill kpi-icon-success"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg></span>
      </div>
      <div class="kpi-value mb-2"><?= number_format((float) ($doQtyThisMonth ?? 0), 0, ',', '.') ?> <span class="fs-5 text-secondary fw-normal">unit</span></div>
      <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="text-secondary small"><?= esc($doThisMonth ?? 0) ?> kiriman</span>
        <?= $renderDelta($deltaDoQty ?? null) ?>
      </div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/deliveries?status=pending_so" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="text-secondary small fw-bold text-uppercase">Siap Dikirim</span>
          <span class="kpi-icon-pill kpi-icon-azure"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($pendingDeliveriesCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">SO</span></div>
        <div class="text-secondary small">Menunggu proses packing &amp; DO &rarr;</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/inventory" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="text-secondary small fw-bold text-uppercase">Stok Kritis</span>
          <span class="kpi-icon-pill <?= !empty($lowStock) ? 'kpi-icon-danger' : 'kpi-icon-success' ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>
        </div>
        <div class="kpi-value mb-2 <?= !empty($lowStock) ? 'text-danger' : 'text-success' ?>"><?= count($lowStock ?? []) ?> <span class="fs-5 text-secondary fw-normal">Produk</span></div>
        <div class="text-secondary small"><?= !empty($lowStock) ? 'Di bawah batas minimum' : 'Semua stok fisik aman' ?></div>
      </div></div>
    </a>
  </div>
</div>

<!-- Row 2: Grouped Bar Chart + Donut Kesehatan Stok -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Arus Barang Masuk vs Keluar</h3>
        <span class="text-secondary small"><?= esc($pLabel) ?></span>
      </div>
      <div class="card-body">
        <div id="mainDashboardChart" style="min-height: 280px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header bg-transparent">
        <h3 class="card-title m-0">Kesehatan Stok Fisik</h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div id="donutDashboardChart" class="w-100" style="min-height: 270px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Row 3: Antrean Operasional Tabbed + Fast Moving Bar Chart -->
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title m-0">Antrean Operasional Gudang</h3>
        <ul class="nav erp-card-tabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabGudangDO" type="button">Siap Kirim (<?= esc($pendingDeliveriesCount ?? 0) ?>)</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabGudangGR" type="button">Kedatangan PO (<?= esc($pendingReceiptsCount ?? 0) ?>)</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabGudangLow" type="button">Stok Tipis (<?= count($lowStock ?? []) ?>)</button></li>
        </ul>
      </div>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="tabGudangDO">
          <?php if (empty($pendingDeliveries)): ?>
            <div class="card-body text-center py-5 text-secondary">Semua pesanan pelanggan sudah diproses kirim.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-hover mb-0">
                <thead><tr><th>No. SO</th><th>Pelanggan &amp; Gudang</th><th class="text-center">Umur</th><th class="text-center">Qty</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                  <?php foreach ($pendingDeliveries as $pd):
                    $ageDays = max(0, (int) floor((time() - strtotime($pd['order_date'])) / 86400));
                  ?>
                  <tr>
                    <td><code class="text-dark fw-bold"><?= esc($pd['so_number']) ?></code></td>
                    <td><div class="fw-semibold"><?= esc($pd['customer_name']) ?></div><div class="small text-secondary"><?= esc($pd['warehouse_name']) ?></div></td>
                    <td class="text-center"><span class="badge <?= $ageDays >= 3 ? 'bg-danger-lt' : 'bg-secondary-lt' ?>"><?= $ageDays ?> hr</span></td>
                    <td class="text-center fw-bold tabular-nums"><?= esc($pd['total_qty']) ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-primary" href="/deliveries/create?sales_order_id=<?= esc($pd['id']) ?>">Kirim</a></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
        <div class="tab-pane fade" id="tabGudangGR">
          <?php if (empty($pendingReceipts)): ?>
            <div class="card-body text-center py-5 text-secondary">Belum ada jadwal kedatangan barang dari pemasok.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-hover mb-0">
                <thead><tr><th>No. PO</th><th>Pemasok</th><th class="text-center">Qty</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                  <?php foreach ($pendingReceipts as $pr): ?>
                  <tr>
                    <td><code class="text-dark fw-bold"><?= esc($pr['po_number']) ?></code></td>
                    <td class="fw-semibold"><?= esc($pr['supplier_name']) ?></td>
                    <td class="text-center fw-bold tabular-nums"><?= esc($pr['total_qty']) ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="/goods-receipts/create?purchase_order_id=<?= esc($pr['id']) ?>">Terima</a></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
        <div class="tab-pane fade" id="tabGudangLow">
          <?php if (empty($lowStock)): ?>
            <div class="card-body text-center py-5 text-secondary">Seluruh stok fisik aman.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-hover mb-0">
                <thead><tr><th>Produk</th><th class="text-center">Sisa</th><th class="text-center">Batas Min</th></tr></thead>
                <tbody>
                  <?php foreach ($lowStock as $p): ?>
                  <tr>
                    <td><div class="fw-semibold"><?= esc($p['name']) ?></div><div class="small text-secondary"><?= esc($p['code']) ?></div></td>
                    <td class="text-center"><span class="badge bg-danger-lt fw-bold"><?= esc($p['stock_qty']) ?> <?= esc($p['unit']) ?></span></td>
                    <td class="text-center text-secondary"><?= esc($p['min_stock']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Top 5 Produk Fast Moving</h3>
        <span class="text-secondary small">Total dikirim keluar</span>
      </div>
      <div class="card-body">
        <div id="barDashboardChart" style="min-height: 260px;"></div>
      </div>
    </div>
  </div>
</div>

<?php elseif ($userRole === 'sales'): ?>
<!-- ======================================================================= -->
<!-- 2. DASHBOARD STAFF SALES                                                -->
<!-- ======================================================================= -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Total Penjualan</span>
        <span class="kpi-icon-pill kpi-icon-success"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
      </div>
      <div class="kpi-value text-success mb-2">Rp <?= number_format((float) ($salesThisMonth ?? 0), 0, ',', '.') ?></div>
      <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="text-secondary small"><?= esc($ordersThisMonth ?? 0) ?> pesanan</span>
        <?= $renderDelta($deltaSales ?? null) ?>
      </div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/sales-orders?status=revision" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="text-secondary small fw-bold text-uppercase">Perlu Tindak Lanjut</span>
          <span class="kpi-icon-pill kpi-icon-warning"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M3.6 9h16.8a1 1 0 0 1 .9 1.45l-8.4 15.1a1 1 0 0 1-1.8 0L2.7 10.45A1 1 0 0 1 3.6 9z"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc(($draftSoCount ?? 0) + ($revisionSoCount ?? 0)) ?> <span class="fs-5 text-secondary fw-normal">SO</span></div>
        <div class="text-secondary small"><?= esc($draftSoCount ?? 0) ?> Draf &bull; <?= esc($revisionSoCount ?? 0) ?> Revisi</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Sudah Terkirim</span>
        <span class="kpi-icon-pill kpi-icon-azure"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></span>
      </div>
      <div class="kpi-value mb-2"><?= esc($deliveredSoCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">SO</span></div>
      <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="text-secondary small">Selesai periode ini</span>
        <?= $renderDelta($deltaOrders ?? null) ?>
      </div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/customers" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="text-secondary small fw-bold text-uppercase">Pelanggan Aktif</span>
          <span class="kpi-icon-pill kpi-icon-primary"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($customerCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">Mitra</span></div>
        <div class="text-secondary small">Kelola daftar pelanggan &rarr;</div>
      </div></div>
    </a>
  </div>
</div>

<!-- Row 2: Sales Trend + Donut Status SO -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Tren Nilai Penjualan</h3>
        <span class="text-secondary small"><?= esc($pLabel) ?></span>
      </div>
      <div class="card-body">
        <div id="mainDashboardChart" style="min-height: 280px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header bg-transparent">
        <h3 class="card-title m-0">Distribusi Status SO</h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div id="donutDashboardChart" class="w-100" style="min-height: 270px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Row 3: Recent Orders + Top Customers Bar Chart / Top Products Tab -->
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Pesanan Pelanggan Terbaru</h3>
        <a href="/sales-orders" class="btn btn-sm btn-outline-primary">Semua SO</a>
      </div>
      <?php if (empty($recentOrders)): ?>
        <div class="card-body text-center py-5 text-secondary">Belum ada transaksi penjualan pada periode ini.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead><tr><th>No. SO</th><th>Pelanggan</th><th>Tanggal</th><th class="text-end">Total</th><th class="text-center">Status</th></tr></thead>
            <tbody>
              <?php foreach (array_slice($recentOrders, 0, 8) as $ro): $sb = status_badge($ro['status']); ?>
              <tr>
                <td><a href="/sales-orders/show/<?= esc($ro['id']) ?>" class="fw-bold tabular-nums"><?= esc($ro['so_number']) ?></a></td>
                <td class="fw-medium"><?= esc($ro['customer_name']) ?></td>
                <td class="text-secondary small"><?= date('d/m/Y', strtotime($ro['order_date'])) ?></td>
                <td class="text-end tabular-nums fw-bold">Rp <?= number_format((float) $ro['total_amount'], 0, ',', '.') ?></td>
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
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Peringkat Penjualan</h3>
        <ul class="nav erp-card-tabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabSalesCust" type="button">Top Pelanggan</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSalesProd" type="button">Produk Laris</button></li>
        </ul>
      </div>
      <div class="tab-content">
        <div class="tab-pane fade show active p-3" id="tabSalesCust">
          <div id="barDashboardChart" style="min-height: 260px;"></div>
        </div>
        <div class="tab-pane fade" id="tabSalesProd">
          <?php if (empty($topProducts)): ?>
            <div class="card-body text-center py-5 text-secondary">Belum ada data penjualan produk.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-hover mb-0">
                <thead><tr><th>Produk</th><th class="text-center">Terjual</th><th class="text-end">Omzet</th></tr></thead>
                <tbody>
                  <?php foreach (array_slice($topProducts, 0, 7) as $tp): ?>
                  <tr>
                    <td><div class="fw-semibold"><?= esc($tp['name']) ?></div><div class="small text-secondary"><?= esc($tp['code']) ?></div></td>
                    <td class="text-center"><span class="badge bg-success-lt fw-bold"><?= esc($tp['total_qty']) ?> <?= esc($tp['unit']) ?></span></td>
                    <td class="text-end tabular-nums fw-semibold">Rp <?= number_format((float) $tp['total_sales'], 0, ',', '.') ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php elseif ($userRole === 'purchasing'): ?>
<!-- ======================================================================= -->
<!-- 3. DASHBOARD STAFF PURCHASING                                           -->
<!-- ======================================================================= -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Total Belanja PO</span>
        <span class="kpi-icon-pill kpi-icon-azure"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg></span>
      </div>
      <div class="kpi-value text-primary mb-2">Rp <?= number_format((float) ($purchasesThisMonth ?? 0), 0, ',', '.') ?></div>
      <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="text-secondary small"><?= esc($ordersThisMonth ?? 0) ?> pesanan PO</span>
        <?= $renderDelta($deltaPurchases ?? null) ?>
      </div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/purchase-orders?status=draft" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="text-secondary small fw-bold text-uppercase">Menunggu ACC</span>
          <span class="kpi-icon-pill kpi-icon-warning"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($draftPoCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">PO</span></div>
        <div class="text-secondary small">Menunggu persetujuan Direksi</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/purchase-orders?status=approved" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="text-secondary small fw-bold text-uppercase">Siap Diterima Gudang</span>
          <span class="kpi-icon-pill kpi-icon-primary"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($approvedPoCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">PO</span></div>
        <div class="text-secondary small">PO aktif dalam perjalanan</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Perlu Segera Restok</span>
        <span class="kpi-icon-pill <?= !empty($lowStock) ? 'kpi-icon-danger' : 'kpi-icon-success' ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>
      </div>
      <div class="kpi-value mb-2 <?= !empty($lowStock) ? 'text-danger' : 'text-success' ?>"><?= count($lowStock ?? []) ?> <span class="fs-5 text-secondary fw-normal">Produk</span></div>
      <div class="text-secondary small"><?= count($restockAlerts ?? []) ?> permintaan dari Sales</div>
    </div></div>
  </div>
</div>

<?php if (! empty($restockAlerts)): ?>
<div class="card mb-4 border-warning">
  <div class="card-header bg-warning-lt d-flex justify-content-between align-items-center">
    <h3 class="card-title m-0 text-dark">Notifikasi Kebutuhan Restok dari Pesanan Pelanggan</h3>
    <span class="badge bg-warning text-white fw-bold"><?= count($restockAlerts) ?> Permintaan</span>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover mb-0">
      <thead><tr><th>No. SO</th><th>Produk</th><th>Gudang</th><th class="text-center">Diminta</th><th class="text-center">Tersedia</th><th class="text-center">Kurang</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($restockAlerts as $al): ?>
        <tr>
          <td><code class="text-dark fw-bold"><?= esc($al['sales_order_number']) ?></code></td>
          <td><div class="fw-semibold"><?= esc($al['product_name']) ?></div><div class="small text-secondary"><?= esc($al['product_code']) ?></div></td>
          <td><span class="badge bg-secondary-lt"><?= esc($al['warehouse_name']) ?></span></td>
          <td class="text-center tabular-nums"><?= esc($al['requested_qty']) ?> <?= esc($al['unit'] ?? 'unit') ?></td>
          <td class="text-center tabular-nums"><?= esc($al['available_qty']) ?> <?= esc($al['unit'] ?? 'unit') ?></td>
          <td class="text-center"><span class="badge bg-danger-lt fw-bold tabular-nums"><?= esc($al['shortage_qty']) ?> <?= esc($al['unit'] ?? 'unit') ?></span></td>
          <td class="text-end"><a href="/purchase-orders/create?product_id=<?= esc($al['product_id']) ?>&qty=<?= esc($al['shortage_qty']) ?>" class="btn btn-sm btn-primary">Buat PO</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Row 2: Purchase Trend + Donut Status PO -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Tren Belanja Pembelian</h3>
        <span class="text-secondary small"><?= esc($pLabel) ?></span>
      </div>
      <div class="card-body">
        <div id="mainDashboardChart" style="min-height: 270px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header bg-transparent">
        <h3 class="card-title m-0">Distribusi Status PO</h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div id="donutDashboardChart" class="w-100" style="min-height: 260px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Row 3: Top Suppliers Bar + Tabbed Restock/Recent PO -->
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-transparent">
        <h3 class="card-title m-0">Top 5 Pemasok (Nilai Belanja)</h3>
      </div>
      <div class="card-body">
        <div id="barDashboardChart" style="min-height: 260px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title m-0">Tindak Lanjut Pengadaan</h3>
        <ul class="nav erp-card-tabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPurchLow" type="button">Stok Menipis (<?= count($lowStock ?? []) ?>)</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPurchPO" type="button">PO Terbaru</button></li>
        </ul>
      </div>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="tabPurchLow">
          <?php if (empty($lowStock)): ?>
            <div class="card-body text-center py-5 text-secondary">Persediaan seluruh produk mencukupi.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-hover mb-0">
                <thead><tr><th>Produk</th><th class="text-center">Sisa</th><th class="text-center">Min</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                  <?php foreach ($lowStock as $p): $suggestedQty = max(10, ((int) $p['min_stock'] * 2) - (int) $p['stock_qty']); ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= esc($p['name']) ?></div>
                      <div class="small text-secondary"><?= esc($p['code']) ?></div>
                      <?php if (! empty($p['active_po_number'])): ?>
                        <span class="badge bg-warning-lt mt-1">Sedang dipesan: <?= esc($p['active_po_number']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center"><span class="badge bg-danger-lt fw-bold"><?= esc($p['stock_qty']) ?> <?= esc($p['unit']) ?></span></td>
                    <td class="text-center text-secondary"><?= esc($p['min_stock']) ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="/purchase-orders/create?product_id=<?= esc($p['id']) ?>&qty=<?= $suggestedQty ?>">Buat PO</a></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
        <div class="tab-pane fade" id="tabPurchPO">
          <?php if (empty($recentPurchases)): ?>
            <div class="card-body text-center py-5 text-secondary">Belum ada pembelian pada periode ini.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-hover mb-0">
                <thead><tr><th>No. PO</th><th>Pemasok</th><th class="text-end">Total</th><th class="text-center">Status</th></tr></thead>
                <tbody>
                  <?php foreach (array_slice($recentPurchases, 0, 8) as $rp): $sb = status_badge($rp['status']); ?>
                  <tr>
                    <td><a href="/purchase-orders/show/<?= esc($rp['id']) ?>" class="fw-bold tabular-nums"><?= esc($rp['po_number']) ?></a></td>
                    <td class="small fw-medium"><?= esc($rp['supplier_name']) ?></td>
                    <td class="text-end tabular-nums small fw-bold">Rp <?= number_format((float) $rp['total_amount'], 0, ',', '.') ?></td>
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
  </div>
</div>

<?php elseif ($userRole === 'manajemen'): ?>
<!-- ======================================================================= -->
<!-- 4. DASHBOARD EKSEKUTIF (MANAJEMEN)                                      -->
<!-- ======================================================================= -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Total Penjualan</span>
        <span class="kpi-icon-pill kpi-icon-success"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg></span>
      </div>
      <div class="kpi-value text-success mb-2">Rp <?= number_format((float) ($totalSales ?? 0), 0, ',', '.') ?></div>
      <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="text-secondary small"><?= esc($soCount ?? 0) ?> pesanan SO</span>
        <?= $renderDelta($deltaSales ?? null) ?>
      </div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Total Pembelian</span>
        <span class="kpi-icon-pill kpi-icon-azure"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg></span>
      </div>
      <div class="kpi-value text-primary mb-2">Rp <?= number_format((float) ($totalPurchases ?? 0), 0, ',', '.') ?></div>
      <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="text-secondary small"><?= esc($poCount ?? 0) ?> pesanan PO</span>
        <?= $renderDelta($deltaPurchases ?? null) ?>
      </div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <?php $marginPositive = ($netMargin ?? 0) >= 0; ?>
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Arus Kas Operasional</span>
        <span class="kpi-icon-pill <?= $marginPositive ? 'kpi-icon-success' : 'kpi-icon-danger' ?>"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span>
      </div>
      <div class="kpi-value mb-2 <?= $marginPositive ? 'text-success' : 'text-danger' ?>">Rp <?= number_format((float) ($netMargin ?? 0), 0, ',', '.') ?></div>
      <div class="text-secondary small">Selisih omzet &minus; belanja stok</div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/inventory" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="text-secondary small fw-bold text-uppercase">Nilai Aset Stok</span>
          <span class="kpi-icon-pill kpi-icon-warning"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></span>
        </div>
        <div class="kpi-value mb-2">Rp <?= number_format((float) ($stockValue ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($lowStockCount ?? 0) ?> produk perlu restok ulang</div>
      </div></div>
    </a>
  </div>
</div>

<!-- Row 2: Combo Penjualan vs Pembelian + Donut Omzet per Kategori -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Perbandingan Penjualan vs Pembelian</h3>
        <span class="text-secondary small"><?= esc($pLabel) ?></span>
      </div>
      <div class="card-body">
        <div id="mainDashboardChart" style="min-height: 290px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header bg-transparent">
        <h3 class="card-title m-0">Omzet per Kategori Produk</h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div id="donutDashboardChart" class="w-100" style="min-height: 280px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Row 3: Antrean ACC Direktur + Bar Sebaran Stok Gudang -->
<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Antrean Persetujuan Pembelian (ACC Direktur)</h3>
        <span class="badge <?= !empty($pendingPoCount) ? 'bg-warning text-white' : 'bg-success-lt' ?> fw-bold"><?= esc($pendingPoCount ?? 0) ?> Menunggu</span>
      </div>
      <?php if (empty($pendingApprovalPOs)): ?>
        <div class="card-body text-center py-5 text-secondary">Tidak ada pesanan pembelian yang menunggu persetujuan.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead><tr><th>No. PO</th><th>Pemasok</th><th>Pembuat</th><th class="text-end">Total Nilai</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
              <?php foreach ($pendingApprovalPOs as $po): ?>
              <tr>
                <td><code class="text-dark fw-bold"><?= esc($po['po_number']) ?></code></td>
                <td class="fw-semibold"><?= esc($po['supplier_name']) ?></td>
                <td class="small text-secondary"><?= esc($po['creator_name']) ?></td>
                <td class="text-end tabular-nums fw-bold">Rp <?= number_format((float) $po['total_amount'], 0, ',', '.') ?></td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-warning fw-semibold js-po-drawer" data-po-id="<?= esc($po['id']) ?>">Review / ACC</button></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Sebaran Stok per Gudang</h3>
        <a href="/dashboard/export?type=stock&amp;<?= $exportQueryBase ?>" class="btn btn-sm btn-outline-success">Export</a>
      </div>
      <div class="card-body">
        <div id="barDashboardChart" style="min-height: 250px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Row 4: Tabbed Ledger (SO / PO) + Top Products -->
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title m-0">Ringkasan Transaksi (<?= esc($pLabel) ?>)</h3>
        <ul class="nav erp-card-tabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabMgmtSales" type="button">Penjualan (SO)</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMgmtPO" type="button">Pembelian (PO)</button></li>
        </ul>
      </div>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="tabMgmtSales">
          <?php if (empty($reportSales)): ?>
            <div class="card-body text-center py-4 text-secondary">Belum ada data penjualan pada periode ini.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-hover mb-0">
                <thead><tr><th>No. SO</th><th>Pelanggan</th><th class="text-end">Total</th><th class="text-center">Status</th></tr></thead>
                <tbody>
                  <?php foreach (array_slice($reportSales, 0, 8) as $rs): $sb = status_badge($rs['status']); ?>
                  <tr>
                    <td><a href="/sales-orders/show/<?= esc($rs['id']) ?>" class="fw-bold tabular-nums"><?= esc($rs['so_number']) ?></a></td>
                    <td class="small fw-medium"><?= esc($rs['customer_name']) ?></td>
                    <td class="text-end tabular-nums fw-bold">Rp <?= number_format((float) $rs['total_amount'], 0, ',', '.') ?></td>
                    <td class="text-center"><span class="badge <?= $sb['badge'] ?>"><?= esc($sb['label']) ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
        <div class="tab-pane fade" id="tabMgmtPO">
          <?php if (empty($reportPurchases)): ?>
            <div class="card-body text-center py-4 text-secondary">Belum ada data pembelian pada periode ini.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-vcenter card-table table-hover mb-0">
                <thead><tr><th>No. PO</th><th>Pemasok</th><th class="text-end">Total</th><th class="text-center">Status</th></tr></thead>
                <tbody>
                  <?php foreach (array_slice($reportPurchases, 0, 8) as $rp): $sb = status_badge($rp['status']); ?>
                  <tr>
                    <td><a href="/purchase-orders/show/<?= esc($rp['id']) ?>" class="fw-bold tabular-nums"><?= esc($rp['po_number']) ?></a></td>
                    <td class="small fw-medium"><?= esc($rp['supplier_name']) ?></td>
                    <td class="text-end tabular-nums fw-bold">Rp <?= number_format((float) $rp['total_amount'], 0, ',', '.') ?></td>
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
  </div>
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-transparent"><h3 class="card-title m-0">Produk Paling Laris</h3></div>
      <?php if (empty($topProducts)): ?>
        <div class="card-body text-center py-4 text-secondary">Belum ada penjualan produk pada periode ini.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead><tr><th>Produk</th><th class="text-center">Terjual</th><th class="text-end">Total</th></tr></thead>
            <tbody>
              <?php foreach ($topProducts as $tp): ?>
              <tr>
                <td><div class="fw-semibold"><?= esc($tp['name']) ?></div><div class="small text-secondary"><?= esc($tp['code']) ?></div></td>
                <td class="text-center"><span class="badge bg-success-lt fw-bold"><?= esc($tp['total_qty']) ?> <?= esc($tp['unit']) ?></span></td>
                <td class="text-end tabular-nums fw-bold">Rp <?= number_format((float) $tp['total_sales'], 0, ',', '.') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ======================================================================= -->
<!-- 5. DASHBOARD ADMINISTRATOR                                              -->
<!-- ======================================================================= -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <a href="/products" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="text-secondary small fw-bold text-uppercase">Total Produk</span>
          <span class="kpi-icon-pill kpi-icon-primary"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($productCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">SKU</span></div>
        <div class="text-secondary small"><?= esc($categoryCount ?? 0) ?> Kategori &bull; <?= esc($warehouseCount ?? 0) ?> Gudang</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Nilai Persediaan</span>
        <span class="kpi-icon-pill kpi-icon-warning"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></span>
      </div>
      <div class="kpi-value mb-2">Rp <?= number_format((float) ($stockValue ?? 0), 0, ',', '.') ?></div>
      <div class="text-secondary small">Estimasi modal seluruh stok</div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="text-secondary small fw-bold text-uppercase">Penjualan (<?= esc($pLabel) ?>)</span>
        <span class="kpi-icon-pill kpi-icon-success"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg></span>
      </div>
      <div class="kpi-value text-success mb-2">Rp <?= number_format((float) ($salesThisMonth ?? 0), 0, ',', '.') ?></div>
      <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="text-secondary small"><?= esc($ordersThisMonth ?? 0) ?> pesanan</span>
        <?= $renderDelta($deltaSales ?? null) ?>
      </div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/users" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="text-secondary small fw-bold text-uppercase">Pengguna Aktif</span>
          <span class="kpi-icon-pill kpi-icon-purple"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($userCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">Akun</span></div>
        <div class="text-secondary small">Kelola pengguna &amp; peran &rarr;</div>
      </div></div>
    </a>
  </div>
</div>

<!-- Row 2: Penjualan vs Pembelian + Donut Kategori/Role -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Aktivitas Penjualan vs Pembelian</h3>
        <span class="text-secondary small"><?= esc($pLabel) ?></span>
      </div>
      <div class="card-body">
        <div id="mainDashboardChart" style="min-height: 280px;"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Komposisi Data</h3>
        <ul class="nav erp-card-tabs" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabAdminCat" type="button">Kategori</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabAdminRole" type="button">Peran</button></li>
        </ul>
      </div>
      <div class="tab-content card-body d-flex align-items-center justify-content-center">
        <div class="tab-pane fade show active w-100" id="tabAdminCat">
          <div id="donutDashboardChart" style="min-height: 260px;"></div>
        </div>
        <div class="tab-pane fade w-100" id="tabAdminRole">
          <div id="donutSecondaryChart" style="min-height: 260px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Row 3: Low Stock + Top Products -->
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header bg-transparent"><h3 class="card-title m-0">Produk dengan Stok Menipis</h3></div>
      <?php if (empty($lowStock)): ?>
        <div class="card-body text-center py-4 text-secondary">Seluruh stok aman.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead><tr><th>Produk</th><th class="text-center">Sisa</th><th class="text-center">Min</th></tr></thead>
            <tbody>
              <?php foreach ($lowStock as $p): ?>
              <tr>
                <td><div class="fw-semibold"><?= esc($p['name']) ?></div><div class="small text-secondary"><?= esc($p['code']) ?></div></td>
                <td class="text-center"><span class="badge bg-danger-lt fw-bold"><?= esc($p['stock_qty']) ?> <?= esc($p['unit']) ?></span></td>
                <td class="text-center text-secondary"><?= esc($p['min_stock']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header bg-transparent"><h3 class="card-title m-0">Produk Paling Laris</h3></div>
      <?php if (empty($topProducts)): ?>
        <div class="card-body text-center py-4 text-secondary">Belum ada data penjualan.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead><tr><th>Produk</th><th class="text-center">Terjual</th><th class="text-end">Total</th></tr></thead>
            <tbody>
              <?php foreach ($topProducts as $tp): ?>
              <tr>
                <td><div class="fw-semibold"><?= esc($tp['name']) ?></div><div class="small text-secondary"><?= esc($tp['code']) ?></div></td>
                <td class="text-center"><span class="badge bg-success-lt fw-bold"><?= esc($tp['total_qty']) ?> <?= esc($tp['unit']) ?></span></td>
                <td class="text-end tabular-nums fw-bold">Rp <?= number_format((float) $tp['total_sales'], 0, ',', '.') ?></td>
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
  // 1. Segmented Period Filter + Auto-Submit
  const form = document.getElementById('dashboardPeriodForm');
  const periodInput = document.getElementById('periodTypeInput');
  const weekWrap = document.getElementById('filterWeekWrap');
  const monthWrap = document.getElementById('filterMonthWrap');
  const yearWrap = document.getElementById('filterYearWrap');

  document.querySelectorAll('.erp-segmented-btn[data-period]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const val = this.dataset.period;
      periodInput.value = val;
      if (weekWrap) weekWrap.classList.toggle('d-none', val !== 'week');
      if (monthWrap) monthWrap.classList.toggle('d-none', val !== 'month');
      if (yearWrap) yearWrap.classList.toggle('d-none', val === 'week');
      if (form) form.submit();
    });
  });

  document.querySelectorAll('.js-auto-submit').forEach(function (el) {
    el.addEventListener('change', function () {
      if (form) form.submit();
    });
  });

  if (typeof ApexCharts === 'undefined') return;

  const userRole = <?= json_encode($userRole) ?>;
  const labels = <?= json_encode($chartData['labels'] ?? []) ?>;
  const primaryData = <?= json_encode($chartData['primary'] ?? []) ?>;
  const secondaryData = <?= json_encode($chartData['secondary'] ?? []) ?>;

  const fmtRp = (val) => 'Rp ' + Math.round(val).toLocaleString('id-ID');
  const fmtRpShort = (val) => {
    if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1).replace('.', ',') + ' M';
    if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1).replace('.', ',') + ' jt';
    if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
    return 'Rp ' + Math.round(val);
  };

  // 2. Main Trend Chart
  const mainEl = document.getElementById('mainDashboardChart');
  if (mainEl) {
    let chartType = 'area';
    let series = [];
    let colors = ['#2563eb', '#f59e0b'];
    let isCurrency = true;

    if (userRole === 'gudang') {
      chartType = 'bar';
      isCurrency = false;
      series = [
        { name: 'Barang Masuk (Unit)', data: primaryData },
        { name: 'Barang Keluar (Unit)', data: secondaryData }
      ];
      colors = ['#2563eb', '#10b981'];
    } else if (userRole === 'sales') {
      series = [{ name: 'Total Penjualan', data: primaryData }];
      colors = ['#10b981'];
    } else if (userRole === 'purchasing') {
      series = [{ name: 'Belanja Pembelian', data: secondaryData }];
      colors = ['#0284c7'];
    } else if (userRole === 'manajemen') {
      series = [
        { name: 'Penjualan', data: primaryData },
        { name: 'Pembelian', data: secondaryData }
      ];
      colors = ['#10b981', '#2563eb'];
    } else {
      series = [
        { name: 'Penjualan', data: primaryData },
        { name: 'Pembelian', data: secondaryData }
      ];
      colors = ['#10b981', '#2563eb'];
    }

    new ApexCharts(mainEl, {
      chart: { type: chartType, height: 280, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
      series: series,
      colors: colors,
      plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
      dataLabels: { enabled: false },
      stroke: chartType === 'area' ? { curve: 'smooth', width: 2.5 } : { width: 0 },
      fill: chartType === 'area'
        ? { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.32, opacityTo: 0.04 } }
        : { opacity: 0.92 },
      xaxis: { categories: labels, axisBorder: { show: false }, axisTicks: { show: false } },
      yaxis: {
        labels: {
          formatter: (val) => isCurrency ? fmtRpShort(val) : Math.round(val) + ' unit'
        }
      },
      tooltip: {
        y: {
          formatter: (val) => isCurrency ? fmtRp(val) : Math.round(val).toLocaleString('id-ID') + ' unit'
        }
      },
      grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
      legend: { position: 'top', horizontalAlign: 'right' }
    }).render();
  }

  // 3. Helper Donut Renderer
  function renderDonut(elId, labelsArr, seriesArr, colorsArr, isCurrencyVal) {
    const el = document.getElementById(elId);
    if (!el) return;
    const total = (seriesArr || []).reduce((a, b) => a + Number(b || 0), 0);
    if (!seriesArr || seriesArr.length === 0 || total === 0) {
      el.innerHTML = '<div class="text-center text-secondary small py-5">Belum ada data untuk ditampilkan pada periode ini.</div>';
      return;
    }
    new ApexCharts(el, {
      chart: { type: 'donut', height: 285, fontFamily: 'Plus Jakarta Sans, sans-serif' },
      labels: labelsArr,
      series: seriesArr,
      colors: colorsArr,
      legend: { position: 'bottom', fontSize: '12px' },
      dataLabels: { enabled: false },
      plotOptions: {
        pie: {
          donut: {
            size: '72%',
            labels: {
              show: true,
              name: {
                show: true,
                fontSize: '12px',
                fontWeight: 600,
                color: '#64748b',
                offsetY: -4,
                formatter: (val) => val && val.length > 18 ? val.slice(0, 16) + '...' : val
              },
              value: {
                show: true,
                fontSize: '16px',
                fontWeight: 800,
                color: '#0f172a',
                offsetY: 4,
                formatter: (val) => {
                  const num = Number(val || 0);
                  return isCurrencyVal ? fmtRpShort(num) : num.toLocaleString('id-ID');
                }
              },
              total: {
                show: true,
                label: 'Total',
                fontSize: '12px',
                fontWeight: 600,
                color: '#64748b',
                formatter: () => isCurrencyVal ? fmtRpShort(total) : total.toLocaleString('id-ID')
              }
            }
          }
        }
      },
      tooltip: {
        y: {
          formatter: (val) => isCurrencyVal ? fmtRp(val) : val.toLocaleString('id-ID')
        }
      }
    }).render();
  }

  // 4. Helper Horizontal Bar Renderer
  function renderHBar(elId, labelsArr, seriesArr, colorHex, isCurrencyVal) {
    const el = document.getElementById(elId);
    if (!el) return;
    if (!seriesArr || seriesArr.length === 0) {
      el.innerHTML = '<div class="text-center text-secondary small py-5">Belum ada data peringkat pada periode ini.</div>';
      return;
    }
    new ApexCharts(el, {
      chart: { type: 'bar', height: 255, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
      series: [{ name: isCurrencyVal ? 'Nilai' : 'Jumlah', data: seriesArr }],
      colors: [colorHex],
      plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '55%' } },
      dataLabels: { enabled: false },
      xaxis: {
        categories: labelsArr,
        labels: { formatter: (val) => isCurrencyVal ? fmtRpShort(val) : Math.round(val) }
      },
      tooltip: {
        y: { formatter: (val) => isCurrencyVal ? fmtRp(val) : val.toLocaleString('id-ID') + ' unit' }
      },
      grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
    }).render();
  }

  // Render Role-Specific Donut & Bar Charts
  if (userRole === 'gudang') {
    const sh = <?= json_encode($stockHealth ?? ['labels' => [], 'series' => []]) ?>;
    const fm = <?= json_encode($fastMoving ?? ['labels' => [], 'series' => []]) ?>;
    renderDonut('donutDashboardChart', sh.labels, sh.series, ['#10b981', '#f59e0b', '#ef4444'], false);
    renderHBar('barDashboardChart', fm.labels, fm.series, '#2563eb', false);
  } else if (userRole === 'sales') {
    const sd = <?= json_encode($soStatusDist ?? ['labels' => [], 'series' => []]) ?>;
    const tc = <?= json_encode($topCustomers ?? ['labels' => [], 'series' => []]) ?>;
    renderDonut('donutDashboardChart', sd.labels, sd.series, ['#94a3b8', '#f59e0b', '#2563eb', '#10b981', '#ef4444'], false);
    renderHBar('barDashboardChart', tc.labels, tc.series, '#10b981', true);
  } else if (userRole === 'purchasing') {
    const pd = <?= json_encode($poStatusDist ?? ['labels' => [], 'series' => []]) ?>;
    const ts = <?= json_encode($topSuppliers ?? ['labels' => [], 'series' => []]) ?>;
    renderDonut('donutDashboardChart', pd.labels, pd.series, ['#f59e0b', '#2563eb', '#10b981', '#ef4444'], false);
    renderHBar('barDashboardChart', ts.labels, ts.series, '#0284c7', true);
  } else if (userRole === 'manajemen') {
    const cs = <?= json_encode($categorySalesDist ?? ['labels' => [], 'series' => []]) ?>;
    const wsLabels = <?= json_encode(array_column($warehouseSummary ?? [], 'name')) ?>;
    const wsSeries = <?= json_encode(array_map('intval', array_column($warehouseSummary ?? [], 'totalQty'))) ?>;
    renderDonut('donutDashboardChart', cs.labels, cs.series, ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899'], true);
    renderHBar('barDashboardChart', wsLabels, wsSeries, '#2563eb', false);
  } else {
    const cp = <?= json_encode($categoryProductDist ?? ['labels' => [], 'series' => []]) ?>;
    const ur = <?= json_encode($userRoleDist ?? ['labels' => [], 'series' => []]) ?>;
    renderDonut('donutDashboardChart', cp.labels, cp.series, ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#64748b'], false);
    renderDonut('donutSecondaryChart', ur.labels, ur.series, ['#0f172a', '#2563eb', '#10b981', '#f59e0b', '#7c3aed'], false);
  }

  // 5. Slide-Over Quick Drawer (PO Review / ACC)
  const drawerEl = document.getElementById('erpQuickDrawer');
  const drawerTitle = document.getElementById('erpDrawerTitle');
  const drawerBody = document.getElementById('erpDrawerBody');
  const drawerFooter = document.getElementById('erpDrawerFooter');

  document.querySelectorAll('.js-po-drawer').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const poId = this.dataset.poId;
      if (!drawerEl || !poId) return;
      drawerTitle.textContent = 'Memuat Detail PO...';
      drawerBody.innerHTML = '<div class="text-center py-5 text-secondary"><span class="spinner-border spinner-border-sm me-2"></span>Mengambil data...</div>';
      drawerFooter.innerHTML = '';

      const bs = window.bootstrap || (window.tabler && window.tabler.bootstrap);
      if (bs && bs.Offcanvas) {
        bs.Offcanvas.getOrCreateInstance(drawerEl).show();
      } else {
        window.location.href = '/purchase-orders/show/' + poId;
        return;
      }

      fetch('/dashboard/po-preview/' + poId)
        .then(r => r.json())
        .then(data => {
          if (data.error || !data.po) {
            drawerBody.innerHTML = '<div class="alert alert-danger">' + (data.error || 'Gagal memuat data.') + '</div>';
            return;
          }
          const po = data.po;
          const items = data.items || [];
          let total = 0;
          const rowsHtml = items.map(it => {
            total += Number(it.subtotal || 0);
            return `<tr>
              <td><div class="fw-semibold small">${it.name}</div><div class="text-secondary" style="font-size:0.72rem;">${it.code}</div></td>
              <td class="text-center small tabular-nums">${it.qty} ${it.unit}</td>
              <td class="text-end small fw-bold tabular-nums">${fmtRp(it.subtotal)}</td>
            </tr>`;
          }).join('');

          drawerTitle.textContent = 'Review PO ' + po.po_number;
          drawerBody.innerHTML = `
            <div class="mb-3 p-3 bg-light rounded-3 border">
              <div class="d-flex justify-content-between mb-1"><span class="text-secondary small">Pemasok</span><span class="fw-bold small">${po.supplier_name}</span></div>
              <div class="d-flex justify-content-between mb-1"><span class="text-secondary small">Tanggal</span><span class="small">${po.order_date}</span></div>
              <div class="d-flex justify-content-between"><span class="text-secondary small">Dibuat Oleh</span><span class="small">${po.creator_name || '-'}</span></div>
              ${po.notes ? `<div class="mt-2 pt-2 border-top small text-secondary">Catatan: ${po.notes}</div>` : ''}
            </div>
            <div class="table-responsive border rounded-3 mb-3">
              <table class="table table-sm table-vcenter mb-0">
                <thead><tr><th>Produk</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>${rowsHtml}</tbody>
                <tfoot><tr class="table-light"><th colspan="2" class="text-end">Total Nilai PO</th><th class="text-end text-primary tabular-nums">${fmtRp(total)}</th></tr></tfoot>
              </table>
            </div>
          `;
          drawerFooter.innerHTML = `
            <div class="d-flex justify-content-between align-items-center w-100 gap-2">
              <a href="/purchase-orders/show/${po.id}" class="btn btn-outline-secondary btn-sm">Detail Lengkap</a>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-danger btn-sm js-drawer-reject-po" data-po-id="${po.id}" data-po-number="${po.po_number}">Tolak / Batal</button>
                <form action="/purchase-orders/approve/${po.id}" method="post" class="d-inline">
                  <input type="hidden" name="return_to" value="/dashboard">
                  <button type="submit" class="btn btn-success btn-sm px-3 fw-bold">Setujui (ACC)</button>
                </form>
              </div>
            </div>
          `;

          const rejectBtn = drawerFooter.querySelector('.js-drawer-reject-po');
          if (rejectBtn) {
            rejectBtn.addEventListener('click', function () {
              const targetId = this.dataset.poId;
              const targetNum = this.dataset.poNumber;
              const cancelModalEl = document.getElementById('modalCancelPoDrawer');
              const cancelForm = document.getElementById('formCancelPoDrawer');
              const cancelPoNumEl = document.getElementById('cancelPoDrawerNumber');
              const cancelReasonEl = document.getElementById('cancelPoDrawerReason');
              if (!cancelModalEl || !cancelForm) return;

              cancelForm.action = '/purchase-orders/cancel/' + targetId;
              if (cancelPoNumEl) cancelPoNumEl.textContent = targetNum;
              if (cancelReasonEl) cancelReasonEl.value = '';

              const bs = window.bootstrap || (window.tabler && window.tabler.bootstrap);
              if (bs) {
                const oc = bs.Offcanvas.getInstance(drawerEl);
                if (oc) oc.hide();
                setTimeout(function () {
                  bs.Modal.getOrCreateInstance(cancelModalEl).show();
                  setTimeout(function () { if (cancelReasonEl) cancelReasonEl.focus(); }, 200);
                }, 150);
              }
            });
          }
        })
        .catch(() => {
          drawerBody.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan jaringan.</div>';
        });
    });
  });
});
</script>

<!-- Slide-Over Quick Drawer -->
<div class="offcanvas offcanvas-end d-print-none" tabindex="-1" id="erpQuickDrawer" aria-labelledby="erpDrawerTitle" style="width: 460px;">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title fw-bold" id="erpDrawerTitle">Detail Cepat</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
  </div>
  <div class="offcanvas-body" id="erpDrawerBody"></div>
  <div class="offcanvas-footer border-top p-3 bg-light d-flex" id="erpDrawerFooter"></div>
</div>

<!-- Modal Konfirmasi Penolakan PO (Seragam dengan Detail PO) -->
<div class="modal modal-blur fade d-print-none" id="modalCancelPoDrawer" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form id="formCancelPoDrawer" action="" method="post">
        <input type="hidden" name="return_to" value="/dashboard">
        <div class="modal-header">
          <h5 class="modal-title text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
            Konfirmasi Penolakan Pesanan Beli
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-secondary mb-3">
            Apakah Anda yakin ingin menolak pengajuan pesanan beli <strong id="cancelPoDrawerNumber"></strong>? Tindakan ini akan menghentikan proses pengadaan dan tidak dapat diubah kembali.
          </p>
          <div class="mb-3">
            <label class="form-label required">Alasan Penolakan</label>
            <textarea id="cancelPoDrawerReason" name="cancel_reason" class="form-control" rows="3" placeholder="Sebutkan alasan penolakan (misal: Anggaran operasional dialihkan, harga vendor kemahalan, dsb.)" required></textarea>
            <div class="form-text">Alasan ini beserta nama akun Anda akan tercatat secara permanen di riwayat pesanan.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button>
          <button type="submit" class="btn btn-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
            Ya, Tolak Pengajuan PO
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

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
