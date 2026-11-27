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
        'pretitle' => 'Operasional Gudang',
        'title' => 'Ringkasan Gudang & Pengiriman',
        'subtitle' => 'Pantau barang masuk dari pemasok, antrean pengiriman ke pelanggan, dan sisa stok di gudang.',
        'pdfOptions' => [
            'summary'   => ['label' => 'Ringkasan Operasional Gudang', 'checked' => true],
            'stock'     => ['label' => 'Daftar Stok Fisik Produk (Tanpa Harga)', 'checked' => true],
            'low_stock' => ['label' => 'Peringatan Stok Menipis / Habis', 'checked' => true],
        ],
    ],
    'sales' => [
        'pretitle' => 'Penjualan & Pelanggan',
        'title' => 'Ringkasan Penjualan',
        'subtitle' => 'Pantau total penjualan, status pesanan pelanggan, pelanggan teratas, dan produk terlaris.',
        'pdfOptions' => [
            'summary'      => ['label' => 'Ringkasan Penjualan', 'checked' => true],
            'sales'        => ['label' => 'Rincian Pesanan Penjualan (SO)', 'checked' => true],
            'top_products' => ['label' => 'Produk Terlaris Periode Ini', 'checked' => true],
            'low_stock'    => ['label' => 'Peringatan Stok Menipis / Habis', 'checked' => false],
        ],
    ],
    'purchasing' => [
        'pretitle' => 'Pembelian & Pemasok',
        'title' => 'Ringkasan Pembelian & Restok',
        'subtitle' => 'Pantau permintaan restok, status persetujuan pesanan beli, dan daftar pemasok utama.',
        'pdfOptions' => [
            'summary'   => ['label' => 'Ringkasan Pembelian', 'checked' => true],
            'purchase'  => ['label' => 'Rincian Pesanan Pembelian (PO)', 'checked' => true],
            'stock'     => ['label' => 'Daftar Stok Produk & Nilai Persediaan', 'checked' => false],
            'low_stock' => ['label' => 'Peringatan Stok Menipis / Habis', 'checked' => true],
        ],
    ],
    'manajemen' => [
        'pretitle' => 'Ringkasan Pimpinan',
        'title' => 'Ringkasan Usaha',
        'subtitle' => 'Pantau perbandingan penjualan dan pembelian, stok per gudang, serta pesanan beli yang menunggu persetujuan.',
        'pdfOptions' => [
            'summary'      => ['label' => 'Ringkasan Utama Usaha', 'checked' => true],
            'sales'        => ['label' => 'Rincian Pesanan Penjualan (SO)', 'checked' => true],
            'purchase'     => ['label' => 'Rincian Pesanan Pembelian (PO)', 'checked' => true],
            'top_products' => ['label' => 'Produk Terlaris Periode Ini', 'checked' => true],
            'stock'        => ['label' => 'Daftar Stok Produk & Nilai Barang', 'checked' => false],
            'low_stock'    => ['label' => 'Peringatan Stok Menipis / Habis', 'checked' => true],
        ],
    ],
    default => [
        'pretitle' => 'Pengaturan Sistem & Data Utama',
        'title' => 'Ringkasan Admin',
        'subtitle' => 'Pantau jumlah produk, pengguna aktif, stok menipis, dan ringkasan transaksi.',
        'pdfOptions' => [
            'summary'      => ['label' => 'Ringkasan Utama Usaha', 'checked' => true],
            'sales'        => ['label' => 'Rincian Pesanan Penjualan (SO)', 'checked' => true],
            'purchase'     => ['label' => 'Rincian Pesanan Pembelian (PO)', 'checked' => true],
            'top_products' => ['label' => 'Produk Terlaris Periode Ini', 'checked' => false],
            'stock'        => ['label' => 'Daftar Stok Produk & Nilai Barang', 'checked' => true],
            'low_stock'    => ['label' => 'Peringatan Stok Menipis / Habis', 'checked' => true],
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
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Barang Masuk (GR)</div>
        <span class="avatar avatar-sm bg-primary-lt text-primary"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3v12"/><path d="M8 11l4 4l4 -4"/><path d="M8 5h-4a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2 -2v-10a2 2 0 0 0 -2 -2h-4"/></svg></span>
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
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Barang Keluar (DO)</div>
        <span class="avatar avatar-sm bg-green-lt text-green"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"/></svg></span>
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
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="subheader">Siap Dikirim</div>
          <span class="avatar avatar-sm bg-azure-lt text-azure"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 7v5l3 3"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($pendingDeliveriesCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">SO</span></div>
        <div class="text-secondary small">Menunggu disiapkan &amp; dikirim &rarr;</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/inventory" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="subheader">Stok Kritis</div>
          <span class="avatar avatar-sm <?= !empty($lowStock) ? 'bg-red-lt text-red' : 'bg-green-lt text-green' ?>"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg></span>
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
      <div class="card-status-top bg-primary"></div>
      <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title m-0">Daftar Kerja Gudang</h3>
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
                <thead><tr><th>No. SO</th><th>Pelanggan &amp; Gudang</th><th class="text-center">Lama Menunggu</th><th class="text-center">Qty</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                  <?php foreach ($pendingDeliveries as $pd):
                    $ageDays = max(0, (int) floor((time() - strtotime($pd['order_date'])) / 86400));
                  ?>
                  <tr>
                    <td><code class="text-dark fw-bold"><?= esc($pd['so_number']) ?></code></td>
                    <td><div class="fw-semibold"><?= esc($pd['customer_name']) ?></div><div class="small text-secondary"><?= esc($pd['warehouse_name']) ?></div></td>
                    <td class="text-center"><span class="badge <?= $ageDays >= 3 ? 'bg-danger-lt' : 'bg-secondary-lt' ?>"><?= $ageDays === 0 ? 'Hari ini' : $ageDays . ' hari' ?></span></td>
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
                  <?php foreach ($lowStock as $p): $pct = (int) min(100, max(5, ((int) $p['min_stock'] > 0 ? round(((int) $p['stock_qty'] / (int) $p['min_stock']) * 100) : 0))); ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= esc($p['name']) ?></div>
                      <div class="small text-secondary mb-1"><?= esc($p['code']) ?></div>
                      <div class="progress progress-sm"><div class="progress-bar <?= $pct <= 25 ? 'bg-danger' : 'bg-warning' ?>" style="width: <?= $pct ?>%"></div></div>
                    </td>
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
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Total Penjualan</div>
        <span class="avatar avatar-sm bg-green-lt text-green"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path d="M12 3v3m0 12v3"/></svg></span>
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
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="subheader">Butuh Revisi</div>
          <span class="avatar avatar-sm bg-yellow-lt text-yellow"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($revisionSoCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">SO</span></div>
        <div class="text-secondary small">Dikembalikan Gudang untuk diubah</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Sudah Terkirim</div>
        <span class="avatar avatar-sm bg-azure-lt text-azure"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg></span>
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
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="subheader">Pelanggan Aktif</div>
          <span class="avatar avatar-sm bg-primary-lt text-primary"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg></span>
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
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Total Belanja PO</div>
        <span class="avatar avatar-sm bg-azure-lt text-azure"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg></span>
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
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="subheader">Menunggu Persetujuan</div>
          <span class="avatar avatar-sm bg-yellow-lt text-yellow"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 7v5l3 3"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($draftPoCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">PO</span></div>
        <div class="text-secondary small">Menunggu persetujuan pimpinan</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/purchase-orders?status=approved" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="subheader">Siap Diterima Gudang</div>
          <span class="avatar avatar-sm bg-primary-lt text-primary"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($approvedPoCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">PO</span></div>
        <div class="text-secondary small">PO aktif dalam perjalanan</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Perlu Segera Restok</div>
        <span class="avatar avatar-sm <?= !empty($lowStock) ? 'bg-red-lt text-red' : 'bg-green-lt text-green' ?>"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg></span>
      </div>
      <div class="kpi-value mb-2 <?= !empty($lowStock) ? 'text-danger' : 'text-success' ?>"><?= count($lowStock ?? []) ?> <span class="fs-5 text-secondary fw-normal">Produk</span></div>
      <div class="text-secondary small"><?= count($restockAlerts ?? []) ?> permintaan dari Sales</div>
    </div></div>
  </div>
</div>

<?php if (! empty($restockAlerts)): ?>
<div class="card mb-4">
  <div class="card-status-top bg-warning"></div>
  <div class="card-header bg-warning-lt d-flex justify-content-between align-items-center">
    <h3 class="card-title m-0 text-dark">Peringatan Stok Kurang dari Pesanan Pelanggan</h3>
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
      <div class="card-status-top bg-primary"></div>
      <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="card-title m-0">Daftar Pembelian &amp; Stok Menipis</h3>
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
                  <?php foreach ($lowStock as $p): $suggestedQty = max(10, ((int) $p['min_stock'] * 2) - (int) $p['stock_qty']); $pct = (int) min(100, max(5, ((int) $p['min_stock'] > 0 ? round(((int) $p['stock_qty'] / (int) $p['min_stock']) * 100) : 0))); ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= esc($p['name']) ?></div>
                      <div class="small text-secondary mb-1"><?= esc($p['code']) ?></div>
                      <div class="progress progress-sm mb-1"><div class="progress-bar <?= $pct <= 25 ? 'bg-danger' : 'bg-warning' ?>" style="width: <?= $pct ?>%"></div></div>
                      <?php if (! empty($p['active_po_number'])): ?>
                        <span class="badge bg-warning-lt mt-1">Sedang dipesan: <?= esc($p['active_po_number']) ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center"><span class="badge bg-danger-lt fw-bold"><?= esc($p['stock_qty']) ?> <?= esc($p['unit']) ?></span></td>
                    <td class="text-center text-secondary"><?= esc($p['min_stock']) ?></td>
                    <td class="text-end">
                      <?php if (! empty($p['active_po_number']) && ! empty($p['active_po_id'])): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="/purchase-orders/show/<?= esc($p['active_po_id']) ?>">Lihat PO</a>
                      <?php else: ?>
                        <a class="btn btn-sm btn-primary" href="/purchase-orders/create?product_id=<?= esc($p['id']) ?>&qty=<?= $suggestedQty ?>">Buat PO</a>
                      <?php endif; ?>
                    </td>
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
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Total Penjualan</div>
        <span class="avatar avatar-sm bg-green-lt text-green"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 17l6 -6l4 4l8 -8"/><path d="M14 7l7 0l0 7"/></svg></span>
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
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Total Pembelian</div>
        <span class="avatar avatar-sm bg-azure-lt text-azure"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg></span>
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
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Arus Kas Operasional</div>
        <span class="avatar avatar-sm <?= $marginPositive ? 'bg-green-lt text-green' : 'bg-red-lt text-red' ?>"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path d="M12 3v3m0 12v3"/></svg></span>
      </div>
      <div class="kpi-value mb-2 <?= $marginPositive ? 'text-success' : 'text-danger' ?>">Rp <?= number_format((float) ($netMargin ?? 0), 0, ',', '.') ?></div>
      <div class="text-secondary small">Selisih penjualan dikurangi belanja</div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="/inventory" class="kpi-card-link">
      <div class="card kpi-card h-100"><div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="subheader">Nilai Aset Stok</div>
          <span class="avatar avatar-sm bg-yellow-lt text-yellow"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg></span>
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
      <div class="card-status-top <?= !empty($pendingPoCount) ? 'bg-warning' : 'bg-success' ?>"></div>
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0">Pesanan Beli Menunggu Persetujuan</h3>
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
                <td class="text-end"><button type="button" class="btn btn-sm btn-warning fw-semibold js-po-drawer" data-po-id="<?= esc($po['id']) ?>">Periksa &amp; Setujui</button></td>
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
      <div class="card-status-top bg-primary"></div>
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
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="subheader">Total Produk</div>
          <span class="avatar avatar-sm bg-primary-lt text-primary"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg></span>
        </div>
        <div class="kpi-value mb-2"><?= esc($productCount ?? 0) ?> <span class="fs-5 text-secondary fw-normal">Produk</span></div>
        <div class="text-secondary small"><?= esc($categoryCount ?? 0) ?> Kategori &bull; <?= esc($warehouseCount ?? 0) ?> Gudang</div>
      </div></div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Nilai Persediaan</div>
        <span class="avatar avatar-sm bg-yellow-lt text-yellow"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0"/><path d="M4 6v6a8 3 0 0 0 16 0v-6"/><path d="M4 12v6a8 3 0 0 0 16 0v-6"/></svg></span>
      </div>
      <div class="kpi-value mb-2">Rp <?= number_format((float) ($stockValue ?? 0), 0, ',', '.') ?></div>
      <div class="text-secondary small">Perkiraan nilai seluruh stok barang</div>
    </div></div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card kpi-card h-100"><div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="subheader">Penjualan (<?= esc($pLabel) ?>)</div>
        <span class="avatar avatar-sm bg-green-lt text-green"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 17l6 -6l4 4l8 -8"/><path d="M14 7l7 0l0 7"/></svg></span>
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
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="subheader">Pengguna Aktif</div>
          <span class="avatar avatar-sm bg-purple-lt text-purple"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg></span>
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
        <h3 class="card-title m-0">Rincian Kategori &amp; Peran</h3>
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
      <div class="card-status-top <?= !empty($lowStock) ? 'bg-danger' : 'bg-success' ?>"></div>
      <div class="card-header bg-transparent"><h3 class="card-title m-0">Produk dengan Stok Menipis</h3></div>
      <?php if (empty($lowStock)): ?>
        <div class="card-body text-center py-4 text-secondary">Seluruh stok aman.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover mb-0">
            <thead><tr><th>Produk</th><th class="text-center">Sisa</th><th class="text-center">Min</th></tr></thead>
            <tbody>
              <?php foreach ($lowStock as $p): $pct = (int) min(100, max(5, ((int) $p['min_stock'] > 0 ? round(((int) $p['stock_qty'] / (int) $p['min_stock']) * 100) : 0))); ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= esc($p['name']) ?></div>
                  <div class="small text-secondary mb-1"><?= esc($p['code']) ?></div>
                  <div class="progress progress-sm"><div class="progress-bar <?= $pct <= 25 ? 'bg-danger' : 'bg-warning' ?>" style="width: <?= $pct ?>%"></div></div>
                </td>
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
    const chartH = Math.max(220, Math.min(280, labelsArr.length * 46 + 48));
    new ApexCharts(el, {
      chart: { type: 'bar', height: chartH, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
      series: [{ name: isCurrencyVal ? 'Nilai' : 'Jumlah', data: seriesArr }],
      colors: [colorHex],
      plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '58%' } },
      dataLabels: { enabled: false },
      yaxis: {
        labels: { maxWidth: 155, style: { fontSize: '11.5px', colors: ['#334155'] } }
      },
      xaxis: {
        categories: labelsArr,
        tickAmount: 4,
        labels: {
          rotate: 0,
          hideOverlappingLabels: true,
          style: { fontSize: '11px', colors: ['#64748b'] },
          formatter: (val) => isCurrencyVal ? fmtRpShort(val).replace('Rp ', '') : Math.round(val)
        }
      },
      tooltip: {
        y: { formatter: (val) => isCurrencyVal ? fmtRp(val) : val.toLocaleString('id-ID') + ' unit' }
      },
      grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
    }).render();

    const totalSum = seriesArr.reduce((acc, v) => acc + (Number(v) || 0), 0);
    const listWrap = document.createElement('div');
    listWrap.className = 'mt-3 pt-3 border-top d-flex flex-column gap-2';
    labelsArr.forEach((lbl, idx) => {
      const val = Number(seriesArr[idx]) || 0;
      const pct = totalSum > 0 ? Math.round((val / totalSum) * 100) : 0;
      const row = document.createElement('div');
      row.className = 'd-flex justify-content-between align-items-center small';
      const left = document.createElement('div');
      left.className = 'd-flex align-items-center gap-2 text-truncate me-2';
      const numBadge = document.createElement('span');
      numBadge.className = 'badge bg-secondary-lt tabular-nums';
      numBadge.textContent = String(idx + 1);
      const nameSpan = document.createElement('span');
      nameSpan.className = 'fw-semibold text-dark text-truncate';
      nameSpan.textContent = String(lbl);
      left.appendChild(numBadge);
      left.appendChild(nameSpan);
      const right = document.createElement('div');
      right.className = 'text-nowrap tabular-nums';
      right.innerHTML = `<strong class="text-dark">${isCurrencyVal ? fmtRp(val) : val.toLocaleString('id-ID') + ' unit'}</strong> <span class="text-secondary">(${pct}%)</span>`;
      row.appendChild(left);
      row.appendChild(right);
      listWrap.appendChild(row);
    });
    el.parentNode.appendChild(listWrap);
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
