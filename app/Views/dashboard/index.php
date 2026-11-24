<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php $userRole = $role ?? session()->get('user_role'); ?>

<?php if ($userRole === 'gudang'): ?>
<!-- ======================================================================= -->
<!-- 1. DASHBOARD KHUSUS STAFF GUDANG & LOGISTIK (BEBAS DATA FINANSIAL) -->
<!-- ======================================================================= -->

<div class="page-header d-print-none mt-0 mb-3">
  <div class="row align-items-center">
    <div class="col">
      <div class="page-pretitle text-uppercase font-monospace text-primary fw-semibold" style="letter-spacing: 0.5px;">Operasional Pergudangan & Logistik</div>
      <h2 class="page-title fw-bold text-dark">Dashboard Logistik</h2>
      <div class="text-secondary small mt-1">Pantau penerimaan barang dari pemasok, proses pengiriman pesanan penjualan, dan kontrol fisik stok.</div>
    </div>
    <div class="col-auto ms-auto d-print-none">
      <div class="btn-list">
        <a href="/goods-receipts/create" class="btn btn-outline-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
          Catat Barang Masuk (GR)
        </a>
        <a href="/deliveries/create" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"/></svg>
          Catat Pengiriman (DO)
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #1d4ed8 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Barang Masuk (GR)</span>
          <span class="avatar avatar-xs rounded bg-blue-lt text-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($grThisMonth ?? 0) ?> <span class="fs-4 text-muted fw-normal">Siklus</span></div>
        <div class="text-secondary small"><?= number_format((float) ($grQtyThisMonth ?? 0), 0, ',', '.') ?> unit diterima bulan ini</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #2fb344 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Barang Terkirim (DO)</span>
          <span class="avatar avatar-xs rounded bg-success-lt text-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($doThisMonth ?? 0) ?> <span class="fs-4 text-muted fw-normal">Kirim</span></div>
        <div class="text-secondary small"><?= number_format((float) ($doQtyThisMonth ?? 0), 0, ',', '.') ?> unit keluar gudang</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Antrean Kirim (SO)</span>
          <span class="avatar avatar-xs rounded bg-azure-lt text-azure">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8l0 4l2 2"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($pendingDeliveriesCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Pesanan</span></div>
        <div class="text-secondary small">Siap dikemas & diproses kirim</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid <?= !empty($lowStock) ? '#d63939' : '#2fb344' ?> !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Stok Kritis / Menipis</span>
          <span class="avatar avatar-xs rounded <?= !empty($lowStock) ? 'bg-danger-lt text-danger' : 'bg-success-lt text-success' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold font-monospace <?= !empty($lowStock) ? 'text-danger' : 'text-success' ?>">
          <?= count($lowStock) ?> <span class="fs-4 text-muted fw-normal">SKU</span>
        </div>
        <div class="text-secondary small"><?= !empty($lowStock) ? 'Mencapai batas minimum' : 'Seluruh stok fisik aman' ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"/></svg>
          <h3 class="card-title m-0 fw-bold">Pesanan Siap Dikirim (Perlu DO)</h3>
        </div>
        <span class="badge bg-primary-lt fw-bold"><?= esc($pendingDeliveriesCount ?? 0) ?> Menunggu Pengiriman</span>
      </div>

      <?php if (empty($pendingDeliveries)): ?>
        <div class="card-body text-center py-4 text-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted mb-2" width="36" height="36" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
          <div>Semua pesanan penjualan yang dikonfirmasi telah selesai dikirim.</div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>No. Pesanan (SO)</th>
                <th>Pelanggan</th>
                <th>Gudang Asal</th>
                <th class="text-center">Jml Barang</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pendingDeliveries as $pd): ?>
              <tr>
                <td><code class="text-dark font-monospace fw-bold"><?= esc($pd['so_number']) ?></code></td>
                <td>
                  <div class="fw-semibold text-dark"><?= esc($pd['customer_name']) ?></div>
                  <div class="small text-muted"><?= date('d/m/Y', strtotime($pd['order_date'])) ?></div>
                </td>
                <td class="text-secondary small"><?= esc($pd['warehouse_name']) ?></td>
                <td class="text-center font-monospace fw-bold text-dark"><?= esc($pd['total_qty']) ?> unit</td>
                <td class="text-end">
                  <a href="/deliveries/create?sales_order_id=<?= (int) $pd['id'] ?>" class="btn btn-sm btn-primary">
                    Proses DO
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-azure" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
          <h3 class="card-title m-0 fw-bold">Barang Masuk Menunggu Diterima (Perlu GR)</h3>
        </div>
        <span class="badge bg-azure-lt fw-bold"><?= esc($pendingReceiptsCount ?? 0) ?> Menunggu Penerimaan</span>
      </div>

      <?php if (empty($pendingReceipts)): ?>
        <div class="card-body text-center py-4 text-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted mb-2" width="36" height="36" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
          <div>Tidak ada jadwal kedatangan pesanan beli dari pemasok saat ini.</div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>No. Pesanan (PO)</th>
                <th>Pemasok</th>
                <th>Gudang Tujuan</th>
                <th class="text-center">Jml Barang</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pendingReceipts as $pr): ?>
              <tr>
                <td><code class="text-dark font-monospace fw-bold"><?= esc($pr['po_number']) ?></code></td>
                <td>
                  <div class="fw-semibold text-dark"><?= esc($pr['supplier_name']) ?></div>
                  <div class="small text-muted"><?= date('d/m/Y', strtotime($pr['order_date'])) ?></div>
                </td>
                <td class="text-secondary small"><?= esc($pr['warehouse_name']) ?></td>
                <td class="text-center font-monospace fw-bold text-dark"><?= esc($pr['total_qty']) ?> unit</td>
                <td class="text-end">
                  <a href="/goods-receipts/create?purchase_order_id=<?= (int) $pr['id'] ?>" class="btn btn-sm btn-outline-primary">
                    Terima GR
                  </a>
                </td>
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
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-danger" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
          <h3 class="card-title m-0 fw-bold">Peringatan Stok Fisik</h3>
        </div>
        <a href="/inventory" class="btn btn-sm btn-link text-decoration-none p-0">Semua Stok &rarr;</a>
      </div>

      <?php if (empty($lowStock)): ?>
        <div class="card-body text-center py-5">
          <div class="text-success mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/><path d="M9 12l2 2l4 -4"/></svg>
          </div>
          <div class="h4 mb-1 text-dark">Kondisi Stok Fisik Aman</div>
          <div class="text-secondary small">Seluruh kuantitas fisik produk saat ini berada di atas batas minimum.</div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover table-striped">
            <thead>
              <tr>
                <th>Produk</th>
                <th class="text-center" style="width: 100px;">Sisa Fisik</th>
                <th class="text-center" style="width: 80px;">Min</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lowStock as $p): ?>
              <tr>
                <td>
                  <div class="fw-semibold text-dark text-truncate" style="max-width: 180px;" title="<?= esc($p['name']) ?>"><?= esc($p['name']) ?></div>
                  <div class="small text-muted font-monospace"><?= esc($p['code']) ?></div>
                  <?php if (! empty($p['active_po_number'])): ?>
                    <div class="mt-1">
                      <?php if ($p['active_po_status'] === 'approved'): ?>
                        <span class="badge bg-warning-lt text-warning fw-bold border border-warning-subtle py-0" title="PO <?= esc($p['active_po_number']) ?> berstatus Approved (Disetujui) & sedang menunggu kedatangan fisik di gudang">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs" width="12" height="12" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
                          Menunggu Kirim: <?= esc($p['active_po_number']) ?> (<?= esc($p['active_po_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?>)
                        </span>
                      <?php else: ?>
                        <span class="badge bg-azure-lt text-primary fw-medium border border-info-subtle py-0" title="PO <?= esc($p['active_po_number']) ?> masih berstatus Draft di purchasing">
                          Draft PO: <?= esc($p['active_po_number']) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <span class="badge bg-danger-lt fw-bold px-2 py-1"><?= esc($p['stock_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?></span>
                </td>
                <td class="text-center text-secondary font-monospace"><?= esc($p['min_stock']) ?></td>
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
<!-- 2. DASHBOARD KHUSUS STAFF SALES (PENJUALAN & PELANGGAN) -->
<!-- ======================================================================= -->

<div class="page-header d-print-none mt-0 mb-3">
  <div class="row align-items-center">
    <div class="col">
      <div class="page-pretitle text-uppercase font-monospace text-success fw-semibold" style="letter-spacing: 0.5px;">Divisi Penjualan & Komersial</div>
      <h2 class="page-title fw-bold text-dark">Dashboard Penjualan</h2>
      <div class="text-secondary small mt-1">Pantau performa omset pesanan penjualan, status pesanan pelanggan, dan produk terlaris.</div>
    </div>
    <div class="col-auto ms-auto d-print-none">
      <div class="btn-list">
        <a href="/customers/create" class="btn btn-outline-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 11h6m-3 -3v6"/></svg>
          + Pelanggan Baru
        </a>
        <a href="/sales-orders/create" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
          + Buat Pesanan Jual (SO)
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #2fb344 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Omset Bulan Ini</span>
          <span class="avatar avatar-xs rounded bg-success-lt text-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path d="M12 3v3m0 12v3"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) ($salesThisMonth ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($ordersThisMonth ?? 0) ?> pesanan berhasil</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #f59e0b !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">SO Draf / Menunggu</span>
          <span class="avatar avatar-xs rounded bg-warning-lt text-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8l0 4l2 2"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($draftSoCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Pesanan</span></div>
        <div class="text-secondary small">Perlu dikonfirmasi agar diproses</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">SO Terkirim Bulan Ini</span>
          <span class="avatar avatar-xs rounded bg-azure-lt text-azure">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($deliveredSoCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Pesanan</span></div>
        <div class="text-secondary small">Telah diterima oleh pelanggan</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #1d4ed8 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Pelanggan Aktif</span>
          <span class="avatar avatar-xs rounded bg-blue-lt text-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($customerCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Klien</span></div>
        <div class="text-secondary small">Mitra bisnis terdaftar</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
          <h3 class="card-title m-0 fw-bold">Pesanan Penjualan Terbaru (SO)</h3>
        </div>
        <a href="/sales-orders" class="btn btn-sm btn-link text-decoration-none p-0">Semua Pesanan &rarr;</a>
      </div>

      <?php if (empty($recentOrders)): ?>
        <div class="card-body text-center py-5 text-secondary">
          Belum ada riwayat pesanan penjualan.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>No. Pesanan</th>
                <th>Pelanggan</th>
                <th>Nilai SO</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $ro): ?>
              <tr>
                <td><code class="text-dark font-monospace fw-bold"><?= esc($ro['so_number']) ?></code></td>
                <td>
                  <div class="fw-semibold text-dark"><?= esc($ro['customer_name']) ?></div>
                  <div class="small text-muted"><?= date('d/m/Y', strtotime($ro['order_date'])) ?></div>
                </td>
                <td class="font-monospace text-dark fw-bold">
                  Rp <?= number_format((float) $ro['total_amount'], 0, ',', '.') ?>
                </td>
                <td class="text-center">
                  <?php if ($ro['status'] === 'confirmed'): ?>
                    <span class="badge bg-blue-lt">Dikonfirmasi</span>
                  <?php elseif ($ro['status'] === 'delivered'): ?>
                    <span class="badge bg-success-lt">Terkirim</span>
                  <?php elseif ($ro['status'] === 'cancelled'): ?>
                    <span class="badge bg-danger-lt">Dibatalkan</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-lt">Draf</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <a href="/sales-orders/show/<?= (int) $ro['id'] ?>" class="btn btn-sm btn-outline-primary">
                    Lihat
                  </a>
                </td>
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
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6l4 6l5 -4l-2 10h-14l-2 -10l5 4z"/></svg>
          <h3 class="card-title m-0 fw-bold">Top 5 Produk Terlaris</h3>
        </div>
        <span class="badge bg-primary-lt">Bulan Ini</span>
      </div>

      <?php if (empty($topProducts)): ?>
        <div class="card-body text-center py-5 text-secondary">
          Belum ada riwayat pesanan penjualan bulan ini.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th class="text-center" style="width: 45px;">#</th>
                <th>Produk</th>
                <th class="text-center" style="width: 80px;">Qty</th>
                <th class="text-end">Omset</th>
              </tr>
            </thead>
            <tbody>
              <?php $rank = 1; foreach ($topProducts as $tp): ?>
              <tr>
                <td class="text-center">
                  <?php if ($rank === 1): ?>
                    <span class="badge bg-yellow text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">1</span>
                  <?php elseif ($rank === 2): ?>
                    <span class="badge bg-secondary text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">2</span>
                  <?php elseif ($rank === 3): ?>
                    <span class="badge bg-azure text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">3</span>
                  <?php else: ?>
                    <span class="text-muted fw-bold"><?= $rank ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="fw-semibold text-dark text-truncate" style="max-width: 160px;" title="<?= esc($tp['name']) ?>"><?= esc($tp['name']) ?></div>
                  <div class="small text-muted font-monospace"><?= esc($tp['code']) ?></div>
                </td>
                <td class="text-center font-monospace fw-bold text-dark">
                  <?= esc($tp['total_qty']) ?> <span class="small text-muted fw-normal"><?= esc($tp['unit'] ?? '') ?></span>
                </td>
                <td class="text-end font-monospace text-primary fw-bold" style="font-size: 0.85rem;">
                  Rp <?= number_format((float) $tp['total_sales'], 0, ',', '.') ?>
                </td>
              </tr>
              <?php $rank++; endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php elseif ($userRole === 'purchasing'): ?>
<!-- ======================================================================= -->
<!-- 3. DASHBOARD KHUSUS STAFF PURCHASING (PENGADAAN & PEMASOK) -->
<!-- ======================================================================= -->

<div class="page-header d-print-none mt-0 mb-3">
  <div class="row align-items-center">
    <div class="col">
      <div class="page-pretitle text-uppercase font-monospace text-primary fw-semibold" style="letter-spacing: 0.5px;">Divisi Pengadaan & Hubungan Pemasok</div>
      <h2 class="page-title fw-bold text-dark">Dashboard Pengadaan</h2>
      <div class="text-secondary small mt-1">Pantau anggaran belanja modal, pesanan aktif ke supplier, dan inventaris yang harus di-restock.</div>
    </div>
    <div class="col-auto ms-auto d-print-none">
      <div class="btn-list">
        <a href="/suppliers/create" class="btn btn-outline-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg>
          + Pemasok Baru
        </a>
        <a href="/purchase-orders/create" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5v14m-7 -7h14"/></svg>
          + Buat Pesanan Beli (PO)
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Belanja Bulan Ini</span>
          <span class="avatar avatar-xs rounded bg-azure-lt text-azure">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) ($purchasesThisMonth ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($ordersThisMonth ?? 0) ?> pesanan beli berjalan</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #f59e0b !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">PO Draf (Belum Approve)</span>
          <span class="avatar avatar-xs rounded bg-warning-lt text-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8l0 4l2 2"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($draftPoCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Pesanan</span></div>
        <div class="text-secondary small">Menunggu persetujuan manajemen</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #1d4ed8 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">PO Approved (Menunggu Kirim)</span>
          <span class="avatar avatar-xs rounded bg-blue-lt text-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($approvedPoCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">Pesanan</span></div>
        <div class="text-secondary small">Barang sedang dikirim vendor</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid <?= !empty($lowStock) ? '#d63939' : '#2fb344' ?> !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Perlu Restock Kritis</span>
          <span class="avatar avatar-xs rounded <?= !empty($lowStock) ? 'bg-danger-lt text-danger' : 'bg-success-lt text-success' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold font-monospace <?= !empty($lowStock) ? 'text-danger' : 'text-success' ?>">
          <?= count($lowStock) ?> <span class="fs-4 text-muted fw-normal">SKU</span>
        </div>
        <div class="text-secondary small"><?= !empty($lowStock) ? 'Harus segera dipesan ulang' : 'Seluruh stok aman' ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-danger" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
          <h3 class="card-title m-0 fw-bold">Peringatan Stok Menipis (Restock)</h3>
        </div>
        <div class="d-flex align-items-center gap-1">
          <span class="badge bg-danger-lt fw-bold"><?= count($lowStock) ?> SKU Perlu PO</span>
          <?php $onOrderCount = count(array_filter($lowStock, static fn($x) => !empty($x['active_po_number']))); ?>
          <?php if ($onOrderCount > 0): ?>
            <span class="badge bg-warning-lt fw-bold border border-warning-subtle" title="<?= $onOrderCount ?> SKU sudah memiliki PO aktif (Draft atau Approved)">
              <?= $onOrderCount ?> On Order
            </span>
          <?php endif; ?>
        </div>
      </div>

      <?php if (empty($lowStock)): ?>
        <div class="card-body text-center py-5">
          <div class="text-success mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/><path d="M9 12l2 2l4 -4"/></svg>
          </div>
          <div class="h4 mb-1 text-dark">Persediaan Gudang Mencukupi</div>
          <div class="text-secondary small">Tidak ada produk yang berada di bawah kuantitas minimum.</div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover table-striped">
            <thead>
              <tr>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th class="text-center">Sisa Stok</th>
                <th class="text-center">Min</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lowStock as $p): ?>
              <tr>
                <td><code class="text-dark font-monospace fw-bold"><?= esc($p['code']) ?></code></td>
                <td>
                  <div class="fw-semibold text-dark"><?= esc($p['name']) ?></div>
                  <div class="small text-muted"><?= esc($p['category_name'] ?? 'Umum') ?></div>
                  <?php if (! empty($p['active_po_number'])): ?>
                    <div class="mt-1">
                      <?php if ($p['active_po_status'] === 'approved'): ?>
                        <span class="badge bg-warning-lt text-warning fw-bold border border-warning-subtle d-inline-flex align-items-center gap-1 px-2 py-0" title="Sudah dibuatkan PO <?= esc($p['active_po_number']) ?> (Disetujui) & sedang menunggu penerimaan barang (GR).">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
                          Sedang Dipesan: <?= esc($p['active_po_number']) ?> (<?= esc($p['active_po_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?> &bull; Approved)
                        </span>
                      <?php else: ?>
                        <span class="badge bg-azure-lt text-primary fw-bold border border-info-subtle d-inline-flex align-items-center gap-1 px-2 py-0" title="Sudah masuk di Draft PO <?= esc($p['active_po_number']) ?> & menunggu persetujuan.">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12h6"/><path d="M9 16h6"/><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"/></svg>
                          Draft PO: <?= esc($p['active_po_number']) ?> (<?= esc($p['active_po_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?>)
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <span class="badge bg-danger-lt fw-bold px-2 py-1"><?= esc($p['stock_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?></span>
                </td>
                <td class="text-center text-secondary font-monospace"><?= esc($p['min_stock']) ?></td>
                <td class="text-end">
                  <?php if (! empty($p['active_po_number'])): ?>
                    <div class="d-inline-flex align-items-center gap-1">
                      <a class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1" href="/purchase-orders/show/<?= esc($p['active_po_id']) ?>" title="Lihat PO <?= esc($p['active_po_number']) ?> yang sedang berjalan">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                        Lihat PO
                      </a>
                      <a class="btn btn-sm btn-outline-secondary" href="/purchase-orders/create?product_id=<?= esc($p['id']) ?>&qty=<?= max(1, (int) $p['min_stock'] - (int) $p['stock_qty']) ?>" title="Pesan tambahan lagi jika diperlukan">+</a>
                    </div>
                  <?php else: ?>
                    <a class="btn btn-sm btn-primary" href="/purchase-orders/create?product_id=<?= esc($p['id']) ?>&qty=<?= max(1, (int) $p['min_stock'] - (int) $p['stock_qty']) ?>">
                      + Buat PO
                    </a>
                  <?php endif; ?>
                </td>
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
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
          <h3 class="card-title m-0 fw-bold">Pesanan Pembelian Terbaru (PO)</h3>
        </div>
        <a href="/purchase-orders" class="btn btn-sm btn-link text-decoration-none p-0">Semua PO &rarr;</a>
      </div>

      <?php if (empty($recentPurchases)): ?>
        <div class="card-body text-center py-5 text-secondary">
          Belum ada riwayat pesanan pembelian.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th>No. PO</th>
                <th>Pemasok</th>
                <th>Nilai PO</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentPurchases as $rp): ?>
              <tr>
                <td><code class="text-dark font-monospace fw-bold"><?= esc($rp['po_number']) ?></code></td>
                <td>
                  <div class="fw-semibold text-dark text-truncate" style="max-width: 140px;"><?= esc($rp['supplier_name']) ?></div>
                  <div class="small text-muted"><?= date('d/m/Y', strtotime($rp['order_date'])) ?></div>
                </td>
                <td class="font-monospace text-dark fw-bold">
                  Rp <?= number_format((float) $rp['total_amount'], 0, ',', '.') ?>
                </td>
                <td class="text-center">
                  <?php if ($rp['status'] === 'approved'): ?>
                    <span class="badge bg-blue-lt">Disetujui</span>
                  <?php elseif ($rp['status'] === 'received'): ?>
                    <span class="badge bg-success-lt">Diterima</span>
                  <?php elseif ($rp['status'] === 'cancelled'): ?>
                    <span class="badge bg-danger-lt">Dibatalkan</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-lt">Draf</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <a href="/purchase-orders/show/<?= (int) $rp['id'] ?>" class="btn btn-sm btn-outline-primary">
                    Lihat
                  </a>
                </td>
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
<!-- 4. DASHBOARD KHUSUS MANAJEMEN (EKSEKUTIF / DIREKSI) -->
<!-- ======================================================================= -->

<div class="page-header d-print-none mt-0 mb-3">
  <div class="row align-items-center">
    <div class="col">
      <div class="page-pretitle text-uppercase font-monospace text-indigo fw-semibold" style="letter-spacing: 0.5px;">Ringkasan Eksekutif & Direksi</div>
      <h2 class="page-title fw-bold text-dark">Dashboard Eksekutif</h2>
      <div class="text-secondary small mt-1">Laporan tingkat tinggi performa komersial, perputaran kas pengadaan, dan valuasi aset persediaan.</div>
    </div>
    <div class="col-auto ms-auto d-print-none">
      <div class="btn-list">
        <a href="/reports/purchase" class="btn btn-outline-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z"/></svg>
          Laporan Pembelian
        </a>
        <a href="/reports/sales" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 13a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -6"/><path d="M15 9a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1l0 -10"/></svg>
          Laporan Penjualan
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #2fb344 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Pendapatan Penjualan</span>
          <span class="avatar avatar-xs rounded bg-success-lt text-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2"/><path d="M12 3v3m0 12v3"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) ($totalSales ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($soCount ?? 0) ?> pesanan jual bulan ini</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Pengeluaran Pengadaan</span>
          <span class="avatar avatar-xs rounded bg-azure-lt text-azure">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) ($totalPurchases ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($poCount ?? 0) ?> pesanan beli bulan ini</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid <?= ($netMargin >= 0) ? '#16a34a' : '#dc2626' ?> !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Arus Perdagangan Bersih</span>
          <span class="avatar avatar-xs rounded <?= ($netMargin >= 0) ? 'bg-success-lt text-success' : 'bg-danger-lt text-danger' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 17l6 -6l4 4l8 -8"/><path d="M14 7l7 0l0 7"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold font-monospace <?= ($netMargin >= 0) ? 'text-success' : 'text-danger' ?>">
          Rp <?= number_format((float) $netMargin, 0, ',', '.') ?>
        </div>
        <div class="text-secondary small">Selisih Penjualan vs Pengadaan</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #f59e0b !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Valuasi Aset Persediaan</span>
          <span class="avatar avatar-xs rounded bg-warning-lt text-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) ($stockValue ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small">Nilai modal barang di seluruh gudang</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0"/><path d="M9 8l1 0"/><path d="M9 12l1 0"/><path d="M9 16l1 0"/><path d="M14 8l1 0"/><path d="M14 12l1 0"/><path d="M14 16l1 0"/><path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16"/></svg>
          <h3 class="card-title m-0 fw-bold">Ringkasan Sebaran Stok per Gudang</h3>
        </div>
        <a href="/inventory" class="btn btn-sm btn-link text-decoration-none p-0">Detail Stok &rarr;</a>
      </div>

      <div class="table-responsive">
        <table class="table table-vcenter card-table table-hover">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama Gudang</th>
              <th>Lokasi Operasional</th>
              <th class="text-center">Ragam SKU</th>
              <th class="text-end">Total Fisik</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($warehouseSummary as $ws): ?>
            <tr>
              <td><code class="text-dark font-monospace fw-bold"><?= esc($ws['code']) ?></code></td>
              <td><div class="fw-semibold text-dark"><?= esc($ws['name']) ?></div></td>
              <td class="text-secondary small"><?= esc($ws['location']) ?></td>
              <td class="text-center font-monospace"><?= esc($ws['itemCount']) ?> SKU</td>
              <td class="text-end font-monospace fw-bold text-dark"><?= number_format((float) $ws['totalQty'], 0, ',', '.') ?> unit</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer bg-transparent py-2 text-secondary small d-flex justify-content-between">
        <span>Status Peringatan Stok Global:</span>
        <span class="fw-bold <?= $lowStockCount > 0 ? 'text-danger' : 'text-success' ?>">
          <?= $lowStockCount > 0 ? "{$lowStockCount} SKU di bawah batas minimum" : "Semua produk aman" ?>
        </span>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6l4 6l5 -4l-2 10h-14l-2 -10l5 4z"/></svg>
          <h3 class="card-title m-0 fw-bold">Top 5 Produk Paling Menghasilkan</h3>
        </div>
        <a href="/reports/top-products" class="btn btn-sm btn-link text-decoration-none p-0">Semua &rarr;</a>
      </div>

      <?php if (empty($topProducts)): ?>
        <div class="card-body text-center py-5 text-secondary">
          Belum ada riwayat pesanan penjualan bulan ini.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th class="text-center" style="width: 45px;">#</th>
                <th>Produk</th>
                <th class="text-center" style="width: 75px;">Qty</th>
                <th class="text-end">Kontribusi</th>
              </tr>
            </thead>
            <tbody>
              <?php $rank = 1; foreach ($topProducts as $tp): ?>
              <tr>
                <td class="text-center">
                  <?php if ($rank === 1): ?>
                    <span class="badge bg-yellow text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">1</span>
                  <?php elseif ($rank === 2): ?>
                    <span class="badge bg-secondary text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">2</span>
                  <?php elseif ($rank === 3): ?>
                    <span class="badge bg-azure text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">3</span>
                  <?php else: ?>
                    <span class="text-muted fw-bold"><?= $rank ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="fw-semibold text-dark text-truncate" style="max-width: 150px;" title="<?= esc($tp['name']) ?>"><?= esc($tp['name']) ?></div>
                  <div class="small text-muted font-monospace"><?= esc($tp['code']) ?></div>
                </td>
                <td class="text-center font-monospace fw-bold text-dark">
                  <?= esc($tp['total_qty']) ?> <span class="small text-muted fw-normal"><?= esc($tp['unit'] ?? '') ?></span>
                </td>
                <td class="text-end font-monospace text-primary fw-bold" style="font-size: 0.85rem;">
                  Rp <?= number_format((float) $tp['total_sales'], 0, ',', '.') ?>
                </td>
              </tr>
              <?php $rank++; endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ======================================================================= -->
<!-- 5. DASHBOARD KHUSUS ADMINISTRATOR (SUPER ADMIN) -->
<!-- ======================================================================= -->

<div class="page-header d-print-none mt-0 mb-3">
  <div class="row align-items-center">
    <div class="col">
      <div class="page-pretitle text-uppercase font-monospace text-primary fw-semibold" style="letter-spacing: 0.5px;">Pusat Kendali Sistem & Pengaturan</div>
      <h2 class="page-title fw-bold text-dark">Dashboard Administrator</h2>
      <div class="text-secondary small mt-1">Pengawasan menyeluruh terhadap performa operasional, katalog produk, valuasi stok, dan hak akses pengguna.</div>
    </div>
    <div class="col-auto ms-auto d-print-none">
      <div class="btn-list">
        <a href="/users/create" class="btn btn-outline-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 11h6m-3 -3v6"/></svg>
          + Tambah Pengguna
        </a>
        <a href="/inventory/adjust" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
          Penyesuaian Stok (Opname)
        </a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #1d4ed8 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Katalog Produk</span>
          <span class="avatar avatar-xs rounded bg-blue-lt text-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($productCount) ?> <span class="fs-4 text-muted fw-normal">SKU</span></div>
        <div class="text-secondary small">Total ragam barang aktif</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #f59e0b !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Valuasi Stok</span>
          <span class="avatar avatar-xs rounded bg-warning-lt text-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"/><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) ($stockValue ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small">Estimasi modal persediaan fisik</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #2fb344 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Penjualan Bulan Ini</span>
          <span class="avatar avatar-xs rounded bg-success-lt text-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) ($salesThisMonth ?? 0), 0, ',', '.') ?></div>
        <div class="text-secondary small"><?= esc($ordersThisMonth ?? 0) ?> pesanan jual berhasil</div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #0284c7 !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Pengguna Aktif</span>
          <span class="avatar avatar-xs rounded bg-azure-lt text-azure">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace"><?= esc($userCount ?? 0) ?> <span class="fs-4 text-muted fw-normal">User</span></div>
        <div class="text-secondary small">Akun aktif terdaftar di sistem</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-danger" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
          <h3 class="card-title m-0 fw-bold">Peringatan Stok Menipis</h3>
        </div>
        <div class="d-flex align-items-center gap-1">
          <?php if (! empty($lowStock)): ?>
            <span class="badge bg-danger-lt fw-bold"><?= count($lowStock) ?> Perlu Restock</span>
            <?php $onOrderCount = count(array_filter($lowStock, static fn($x) => !empty($x['active_po_number']))); ?>
            <?php if ($onOrderCount > 0): ?>
              <span class="badge bg-warning-lt fw-bold border border-warning-subtle" title="<?= $onOrderCount ?> produk sudah dalam proses PO"><?= $onOrderCount ?> On Order</span>
            <?php endif; ?>
          <?php else: ?>
            <span class="badge bg-success-lt fw-bold">Semua Aman</span>
          <?php endif; ?>
        </div>
      </div>

      <?php if (empty($lowStock)): ?>
        <div class="card-body text-center py-5">
          <div class="text-success mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/><path d="M9 12l2 2l4 -4"/></svg>
          </div>
          <div class="h4 mb-1 text-dark">Kondisi Persediaan Aman</div>
          <div class="text-secondary small">Seluruh kuantitas fisik produk saat ini berada di atas batas minimum pemesanan ulang.</div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover table-striped">
            <thead>
              <tr>
                <th style="width: 100px;">Kode</th>
                <th>Nama Produk</th>
                <th class="text-center" style="width: 110px;">Sisa Stok</th>
                <th class="text-center" style="width: 90px;">Min</th>
                <th class="text-end" style="width: 120px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lowStock as $p): ?>
              <tr>
                <td><code class="text-dark font-monospace fw-bold"><?= esc($p['code']) ?></code></td>
                <td>
                  <div class="fw-semibold text-dark"><?= esc($p['name']) ?></div>
                  <?php if (! empty($p['active_po_number'])): ?>
                    <div class="mt-1">
                      <?php if ($p['active_po_status'] === 'approved'): ?>
                        <span class="badge bg-warning-lt text-warning fw-bold border border-warning-subtle d-inline-flex align-items-center gap-1 px-2 py-0" title="PO <?= esc($p['active_po_number']) ?> (Disetujui) & sedang menunggu penerimaan barang (GR).">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
                          Sedang Dipesan: <?= esc($p['active_po_number']) ?> (<?= esc($p['active_po_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?> &bull; Approved)
                        </span>
                      <?php else: ?>
                        <span class="badge bg-azure-lt text-primary fw-bold border border-info-subtle d-inline-flex align-items-center gap-1 px-2 py-0" title="Draft PO <?= esc($p['active_po_number']) ?> & menunggu persetujuan.">
                          <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12h6"/><path d="M9 16h6"/><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"/></svg>
                          Draft PO: <?= esc($p['active_po_number']) ?> (<?= esc($p['active_po_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?>)
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <span class="badge bg-danger-lt fw-bold px-2 py-1"><?= esc($p['stock_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?></span>
                </td>
                <td class="text-center text-secondary font-monospace"><?= esc($p['min_stock']) ?></td>
                <td class="text-end">
                  <?php if (! empty($p['active_po_number'])): ?>
                    <div class="d-inline-flex align-items-center gap-1">
                      <a class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1" href="/purchase-orders/show/<?= esc($p['active_po_id']) ?>" title="Lihat PO <?= esc($p['active_po_number']) ?> yang sedang berjalan">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                        Lihat PO
                      </a>
                      <a class="btn btn-sm btn-outline-secondary" href="/purchase-orders/create?product_id=<?= esc($p['id']) ?>&qty=<?= max(1, (int) $p['min_stock'] - (int) $p['stock_qty']) ?>" title="Pesan tambahan lagi jika diperlukan">+</a>
                    </div>
                  <?php else: ?>
                    <a class="btn btn-sm btn-outline-primary" href="/purchase-orders/create?product_id=<?= esc($p['id']) ?>&qty=<?= max(1, (int) $p['min_stock'] - (int) $p['stock_qty']) ?>">
                      Restock
                    </a>
                  <?php endif; ?>
                </td>
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
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6l4 6l5 -4l-2 10h-14l-2 -10l5 4z"/></svg>
          <h3 class="card-title m-0 fw-bold">Top Produk Terlaris</h3>
        </div>
        <a href="/reports/top-products" class="btn btn-sm btn-link text-decoration-none p-0">Semua Laporan &rarr;</a>
      </div>

      <?php if (empty($topProducts)): ?>
        <div class="card-body text-center py-5 text-secondary">
          Belum ada riwayat pesanan penjualan bulan ini.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-vcenter card-table table-hover">
            <thead>
              <tr>
                <th class="text-center" style="width: 45px;">#</th>
                <th>Produk</th>
                <th class="text-center" style="width: 90px;">Terjual</th>
                <th class="text-end">Nominal</th>
              </tr>
            </thead>
            <tbody>
              <?php $rank = 1; foreach ($topProducts as $tp): ?>
              <tr>
                <td class="text-center">
                  <?php if ($rank === 1): ?>
                    <span class="badge bg-yellow text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">1</span>
                  <?php elseif ($rank === 2): ?>
                    <span class="badge bg-secondary text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">2</span>
                  <?php elseif ($rank === 3): ?>
                    <span class="badge bg-azure text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">3</span>
                  <?php else: ?>
                    <span class="text-muted fw-bold"><?= $rank ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="fw-semibold text-dark text-truncate" style="max-width: 170px;" title="<?= esc($tp['name']) ?>"><?= esc($tp['name']) ?></div>
                  <div class="small text-muted font-monospace"><?= esc($tp['code']) ?></div>
                </td>
                <td class="text-center font-monospace fw-bold text-dark">
                  <?= esc($tp['total_qty']) ?> <span class="small text-muted fw-normal"><?= esc($tp['unit'] ?? '') ?></span>
                </td>
                <td class="text-end font-monospace text-primary fw-bold" style="font-size: 0.85rem;">
                  Rp <?= number_format((float) $tp['total_sales'], 0, ',', '.') ?>
                </td>
              </tr>
              <?php $rank++; endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
