<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php $userRole = session()->get('user_role'); ?>
<p class="text-muted">Pilih jenis laporan dan periode. Jika periode tidak diisi, dipakai bulan berjalan.</p>
<div class="row g-3">
  <?php if (in_array($userRole, ['admin', 'manajemen'], true)): ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100 stat-card-hover">
      <div class="card-body p-4">
        <h3 class="card-title fw-bold mb-1">Laporan Pembelian</h3>
        <p class="text-secondary small mb-3">Daftar pesanan beli dan barang masuk pada periode.</p>
        <a class="btn btn-primary" href="/reports/purchase?start_date=<?= esc($start_date) ?>&amp;end_date=<?= esc($end_date) ?>">Buka Laporan</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($userRole, ['admin', 'manajemen', 'sales'], true)): ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100 stat-card-hover">
      <div class="card-body p-4">
        <h3 class="card-title fw-bold mb-1">Laporan Penjualan</h3>
        <p class="text-secondary small mb-3">Daftar pesanan jual dan pengiriman pada periode.</p>
        <a class="btn btn-primary" href="/reports/sales?start_date=<?= esc($start_date) ?>&amp;end_date=<?= esc($end_date) ?>">Buka Laporan</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($userRole, ['admin', 'manajemen'], true)): ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100 stat-card-hover">
      <div class="card-body p-4">
        <h3 class="card-title fw-bold mb-1">Laporan Stok</h3>
        <p class="text-secondary small mb-3">Stok per produk dan perubahan stok pada periode.</p>
        <a class="btn btn-primary" href="/reports/stock?start_date=<?= esc($start_date) ?>&amp;end_date=<?= esc($end_date) ?>">Buka Laporan</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (in_array($userRole, ['admin', 'manajemen', 'sales'], true)): ?>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm h-100 stat-card-hover">
      <div class="card-body p-4">
        <h3 class="card-title fw-bold mb-1">Produk Terlaris</h3>
        <p class="text-secondary small mb-3">10 produk dengan kuantitas penjualan tertinggi.</p>
        <a class="btn btn-primary" href="/reports/top-products?start_date=<?= esc($start_date) ?>&amp;end_date=<?= esc($end_date) ?>">Buka Laporan</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mt-4">
  <div class="card-body p-3">
    <?php
      $formAction = ($userRole === 'sales') ? '/reports/sales' : '/reports/purchase';
      $btnText = ($userRole === 'sales') ? 'Tampilkan Laporan Penjualan' : 'Tampilkan Laporan Pembelian';
    ?>
    <form action="<?= $formAction ?>" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Tanggal Mulai</label>
          <input type="date" name="start_date" class="form-control" value="<?= esc($start_date) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Tanggal Akhir</label>
          <input type="date" name="end_date" class="form-control" value="<?= esc($end_date) ?>">
        </div>
        <div class="col-md-4">
          <button class="btn btn-primary" type="submit"><?= $btnText ?></button>
        </div>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
