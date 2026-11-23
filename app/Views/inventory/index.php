<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/inventory" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-5">
          <label class="form-label small text-secondary mb-1">Pencarian Produk</label>
          <div class="input-icon">
            <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama produk..." value="<?= esc($q) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Total Nilai Persediaan</label>
          <div class="fw-bold text-dark font-monospace">Rp <?= number_format((float) $totalValue, 0, ',', '.') ?></div>
        </div>
        <div class="col-md-4 d-flex gap-2 justify-content-end">
          <button class="btn btn-sm btn-primary" type="submit">Cari</button>
          <?php if ($q !== ''): ?>
            <a class="btn btn-sm btn-outline-secondary" href="/inventory">Reset</a>
          <?php endif; ?>
          <?php if (session()->get('user_role') === 'admin'): ?>
            <a class="btn btn-sm btn-success d-inline-flex align-items-center gap-1" href="/inventory/adjust">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
              Koreksi Stok
            </a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <?php if (empty($items)): ?>
    <div class="card-body text-center py-5">
      <div class="text-secondary mb-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
      </div>
      <div class="h4 mb-1"><?= $q !== '' ? 'Tidak ada produk yang cocok dengan pencarian.' : 'Belum ada produk' ?></div>
      <div class="text-secondary small"><?= $q !== '' ? 'Coba ubah kata kunci atau reset pencariannya.' : 'Tambahkan produk dulu di Data Master.' ?></div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 110px;">Kode</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th style="width: 80px;">Satuan</th>
            <th class="text-center" style="width: 90px;">Stok</th>
            <th class="text-center" style="width: 90px;">Stok Min.</th>
            <th class="text-end" style="width: 130px;">Harga Beli</th>
            <th class="text-end" style="width: 140px;">Nilai Stok</th>
            <th class="text-center" style="width: 90px;">Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($it['code']) ?></code></td>
          <td class="fw-semibold text-dark"><?= esc($it['name']) ?></td>
          <td><span class="badge bg-secondary-lt"><?= esc($it['category_name']) ?></span></td>
          <td class="text-muted small"><?= esc($it['unit']) ?></td>
          <td class="text-center">
            <?php if ((int) $it['stock_qty'] <= (int) $it['min_stock']): ?>
              <span class="badge bg-danger-lt fw-bold" title="Stok di bawah batas minimum!"><?= esc($it['stock_qty']) ?></span>
            <?php else: ?>
              <span class="badge bg-success-lt fw-bold"><?= esc($it['stock_qty']) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-center text-muted"><?= esc($it['min_stock']) ?></td>
          <td class="text-end text-muted font-monospace">Rp <?= number_format((float) $it['purchase_price'], 0, ',', '.') ?></td>
          <td class="text-end fw-semibold text-dark font-monospace">Rp <?= number_format((float) $it['stock_qty'] * (float) $it['purchase_price'], 0, ',', '.') ?></td>
          <td class="text-center">
            <?php if ((int) $it['stock_qty'] <= (int) $it['min_stock']): ?>
              <span class="badge bg-danger-lt fw-bold">Menipis</span>
            <?php else: ?>
              <span class="badge bg-success-lt fw-bold">Aman</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
