<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
  <!-- Card Metrik Produk -->
  <div class="col-sm-6 col-lg-6">
    <div class="card border-0 shadow-sm" style="border-top: 3px solid #1d4ed8 !important;">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Katalog Persediaan</span>
          <span class="avatar avatar-sm rounded bg-blue-lt text-blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
          </span>
        </div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace"><?= esc($productCount) ?> <span class="fs-4 text-muted fw-normal">SKU</span></div>
        <div class="text-secondary small">Total ragam barang aktif di dalam katalog</div>
      </div>
    </div>
  </div>

  <!-- Card Metrik Valuasi Stok -->
  <div class="col-sm-6 col-lg-6">
    <div class="card border-0 shadow-sm" style="border-top: 3px solid #f59e0b !important;">
      <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Valuasi Aset Stok</span>
          <span class="avatar avatar-sm rounded bg-warning-lt text-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12"/><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4"/></svg>
          </span>
        </div>
        <div class="h1 mb-1 fw-bold text-dark font-monospace">Rp <?= number_format((float) $stockValue, 0, ',', '.') ?></div>
        <div class="text-secondary small">Total estimasi modal persediaan barang fisik</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon text-warning" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
          <h3 class="card-title m-0 fw-bold">Peringatan Stok Menipis</h3>
        </div>
        <?php if (! empty($lowStock)): ?>
          <span class="badge bg-danger-lt fw-bold"><?= count($lowStock) ?> Produk Perlu Restock</span>
        <?php else: ?>
          <span class="badge bg-success-lt fw-bold">Semua Stok Aman</span>
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
                <th style="width: 120px;">Kode</th>
                <th>Nama Produk</th>
                <th class="text-center" style="width: 140px;">Sisa Stok Fisik</th>
                <th class="text-center" style="width: 140px;">Batas Minimum</th>
                <th class="text-end" style="width: 130px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lowStock as $p): ?>
              <tr>
                <td><code class="text-dark font-monospace fw-bold"><?= esc($p['code']) ?></code></td>
                <td class="fw-semibold text-dark"><?= esc($p['name']) ?></td>
                <td class="text-center">
                  <span class="badge bg-danger-lt fw-bold"><?= esc($p['stock_qty']) ?> <?= esc($p['unit'] ?? 'unit') ?></span>
                </td>
                <td class="text-center text-secondary"><?= esc($p['min_stock']) ?></td>
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
</div>

<?= $this->endSection() ?>
