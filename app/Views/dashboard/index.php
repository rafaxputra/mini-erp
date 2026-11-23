<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
  <!-- Card Metrik Produk -->
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

  <!-- Card Metrik Valuasi Stok -->
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid #f59e0b !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Valuasi Stok</span>
          <span class="avatar avatar-xs rounded bg-warning-lt text-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"/><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) $stockValue, 0, ',', '.') ?></div>
        <div class="text-secondary small">Estimasi modal persediaan fisik</div>
      </div>
    </div>
  </div>

  <!-- Card Metrik Penjualan Bulan Ini -->
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

  <!-- Card Metrik Peringatan Stok Menipis -->
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm h-100" style="border-top: 3px solid <?= !empty($lowStock) ? '#d63939' : '#2fb344' ?> !important;">
      <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status Persediaan</span>
          <span class="avatar avatar-xs rounded <?= !empty($lowStock) ? 'bg-danger-lt text-danger' : 'bg-success-lt text-success' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
          </span>
        </div>
        <div class="h2 mb-1 fw-bold font-monospace <?= !empty($lowStock) ? 'text-danger' : 'text-success' ?>">
          <?= count($lowStock) ?> <span class="fs-4 text-muted fw-normal">SKU</span>
        </div>
        <div class="text-secondary small"><?= !empty($lowStock) ? 'Mencapai batas minimum' : 'Seluruh stok saat ini aman' ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Kolom Kiri: Peringatan Stok Menipis -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-danger" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
          <h3 class="card-title m-0 fw-bold">Peringatan Stok Menipis</h3>
        </div>
        <?php if (! empty($lowStock)): ?>
          <span class="badge bg-danger-lt fw-bold"><?= count($lowStock) ?> Perlu Restock</span>
        <?php else: ?>
          <span class="badge bg-success-lt fw-bold">Semua Aman</span>
        <?php endif; ?>
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
                <th class="text-end" style="width: 100px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lowStock as $p): ?>
              <tr>
                <td><code class="text-dark font-monospace fw-bold"><?= esc($p['code']) ?></code></td>
                <td>
                  <div class="fw-semibold text-dark"><?= esc($p['name']) ?></div>
                </td>
                <td class="text-center">
                  <span class="badge bg-danger-lt fw-bold px-2 py-1"><?= esc($p['stock_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?></span>
                </td>
                <td class="text-center text-secondary font-monospace"><?= esc($p['min_stock']) ?></td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="/purchase-orders/create">
                    Restock
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

  <!-- Kolom Kanan: Top Produk Terlaris -->
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

<?= $this->endSection() ?>
