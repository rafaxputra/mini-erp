<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body p-3">
    <form action="/reports/top-products" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Tanggal Mulai</label>
          <input type="date" name="start_date" class="form-control" value="<?= esc($start_date) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Tanggal Akhir</label>
          <input type="date" name="end_date" class="form-control" value="<?= esc($end_date) ?>">
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary" type="submit">Tampilkan</button>
        </div>
      </div>
    </form>
  </div>
</div>
<p class="text-muted">Periode: <?= esc($start_date) ?> sampai <?= esc($end_date) ?></p>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h3 class="card-title m-0 fw-bold">Produk Terlaris</h3>
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari kode, nama produk..." aria-label="Cari produk terlaris">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover table-striped">
      <thead>
        <tr>
          <th class="text-center" style="width: 80px;">Peringkat</th>
          <th>Kode</th>
          <th>Produk</th>
          <th class="text-center">Total Jumlah Terjual</th>
          <th class="text-end">Total Penjualan</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach ($items as $it): ?>
        <tr>
          <td class="text-center font-monospace fw-bold"><?= esc($no++) ?></td>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($it['code']) ?></code></td>
          <td class="fw-medium"><?= esc($it['name']) ?></td>
          <td class="text-center font-monospace"><?= esc($it['total_qty']) ?></td>
          <td class="text-end font-monospace">Rp <?= number_format((float) $it['total_sales'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada penjualan pada periode ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
