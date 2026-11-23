<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body p-3">
    <form action="/reports/stock" method="get">
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
  <div class="card-body p-4">
    <div class="text-secondary small">Total Nilai Persediaan (harga beli)</div>
    <div class="fs-2 text-primary font-monospace">Rp <?= number_format((float) $total, 0, ',', '.') ?></div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h3 class="card-title m-0 fw-bold">Stok Per Produk</h3>
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari kode, nama produk..." aria-label="Cari produk">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover table-striped">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Produk</th>
          <th>Kategori</th>
          <th class="text-center">Stok</th>
          <th class="text-end">Harga Beli</th>
          <th class="text-end">Nilai</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($p['code']) ?></code></td>
          <td class="fw-medium"><?= esc($p['name']) ?></td>
          <td><span class="badge bg-secondary-lt"><?= esc($p['category_name']) ?></span></td>
          <td class="text-center">
            <?php if ($p['stock_qty'] <= $p['min_stock']): ?>
              <span class="badge bg-danger-lt"><?= esc($p['stock_qty']) ?></span>
            <?php else: ?>
              <span class="badge bg-success-lt"><?= esc($p['stock_qty']) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-end font-monospace">Rp <?= number_format((float) $p['purchase_price'], 0, ',', '.') ?></td>
          <td class="text-end font-monospace">Rp <?= number_format((float) $p['stock_qty'] * (float) $p['purchase_price'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada produk terdaftar.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h3 class="card-title m-0 fw-bold">Perubahan Stok Pada Periode</h3>
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari produk, gudang..." aria-label="Cari perubahan stok">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover table-striped">
      <thead>
        <tr>
          <th>Waktu</th>
          <th>Produk</th>
          <th>Gudang</th>
          <th>Jenis</th>
          <th>Referensi</th>
          <th class="text-center">Jumlah</th>
          <th class="text-center">Sebelum</th>
          <th class="text-center">Sesudah</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($movements as $m): ?>
        <tr>
          <td><?= esc($m['created_at']) ?></td>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($m['code']) ?></code> <span class="fw-medium"><?= esc($m['product_name']) ?></span></td>
          <td><?= esc($m['warehouse_name'] ?? '-') ?></td>
          <td><span class="badge bg-secondary-lt"><?= esc(movement_label($m['movement_type'])) ?></span></td>
          <td><?= esc(reference_label($m['reference_type'])) ?> #<?= esc($m['reference_id']) ?></td>
          <td class="text-center font-monospace"><?= esc($m['qty']) ?></td>
          <td class="text-center font-monospace"><?= esc($m['qty_before']) ?></td>
          <td class="text-center font-monospace"><?= esc($m['qty_after']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($movements)): ?>
        <tr><td colspan="8" class="text-center text-secondary py-4">Belum ada perubahan stok pada periode ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
