<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/inventory" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label small text-secondary mb-1">Pencarian Produk</label>
          <div class="input-icon">
            <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
            <input type="text" name="q" class="form-control form-control-sm js-table-search" placeholder="Cari nama, kode, kategori..." value="<?= esc($q) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Lokasi Gudang</label>
          <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="0">Semua Gudang (Konsolidasi)</option>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= esc($wh['id']) ?>" <?= (int) ($warehouseId ?? 0) === (int) $wh['id'] ? 'selected' : '' ?>><?= esc($wh['name']) ?> (<?= esc($wh['code']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Nilai Persediaan</label>
          <div class="fw-bold text-dark font-monospace text-truncate">Rp <?= number_format((float) $totalValue, 0, ',', '.') ?></div>
        </div>
        <div class="col-md-3 d-flex gap-2 justify-content-end">
          <button class="btn btn-sm btn-primary" type="submit">Filter</button>
          <?php if ($q !== '' || ! empty($warehouseId)): ?>
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
      <div class="h4 mb-1"><?= ($q !== '' || ! empty($warehouseId)) ? 'Tidak ada produk yang cocok dengan filter pencarian.' : 'Belum ada produk' ?></div>
      <div class="text-secondary small">Coba ubah kata kunci atau reset filter pencarian.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 100px;">Kode</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Lokasi & Stok per Gudang</th>
            <th class="text-center" style="width: 100px;"><?= ! empty($warehouseId) ? 'Stok Gudang' : 'Total Stok' ?></th>
            <th class="text-center" style="width: 90px;">Stok Min.</th>
            <th class="text-end" style="width: 120px;">Harga Beli</th>
            <th class="text-end" style="width: 130px;">Nilai Stok</th>
            <th class="text-center" style="width: 90px;">Status</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <?php 
          $activeWhStock = ! empty($warehouseId) ? ($stocksMap[$it['id']][$warehouseId] ?? 0) : (int) $it['stock_qty'];
        ?>
        <tr>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($it['code']) ?></code></td>
          <td>
            <div class="fw-semibold text-dark"><?= esc($it['name']) ?></div>
          </td>
          <td><span class="badge bg-secondary-lt"><?= esc($it['category_name']) ?></span></td>
          <td>
            <div class="d-flex flex-wrap gap-1">
              <?php 
              $hasStockInAny = false;
              foreach ($warehouses as $wh): 
                $qtyInWh = $stocksMap[$it['id']][$wh['id']] ?? 0;
                $isFilteredWh = ! empty($warehouseId) && (int) $warehouseId === (int) $wh['id'];
                if ($qtyInWh > 0): 
                  $hasStockInAny = true;
              ?>
                <span class="badge <?= $isFilteredWh ? 'bg-primary text-white fw-bold' : 'bg-azure-lt' ?> px-2 py-1" title="<?= esc($wh['name']) ?>: <?= esc($qtyInWh) ?> <?= esc($it['unit']) ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-inline me-1" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-13l9 -4l9 4v13"/><path d="M13 13h4v8h-10v-6h6"/></svg>
                  <?= esc($wh['name']) ?>: <strong><?= esc($qtyInWh) ?></strong> <?= esc($it['unit']) ?>
                </span>
              <?php 
                endif;
              endforeach; 
              if (! $hasStockInAny): ?>
                <span class="text-secondary small fst-italic">Belum ada stok fisik di gudang</span>
              <?php endif; ?>
            </div>
          </td>
          <td class="text-center">
            <?php if ($activeWhStock <= 0): ?>
              <span class="badge bg-danger-lt fw-bold">0</span>
            <?php elseif ($activeWhStock <= (int) $it['min_stock']): ?>
              <span class="badge bg-danger-lt fw-bold" title="Stok berada di bawah atau sama dengan batas minimum!"><?= esc($activeWhStock) ?> <span class="fw-normal"><?= esc($it['unit']) ?></span></span>
            <?php else: ?>
              <span class="badge bg-success-lt fw-bold"><?= esc($activeWhStock) ?> <span class="fw-normal"><?= esc($it['unit']) ?></span></span>
            <?php endif; ?>
          </td>
          <td class="text-center text-muted"><?= esc($it['min_stock']) ?></td>
          <td class="text-end text-muted font-monospace">Rp <?= number_format((float) $it['purchase_price'], 0, ',', '.') ?></td>
          <td class="text-end fw-semibold text-dark font-monospace">Rp <?= number_format((float) $activeWhStock * (float) $it['purchase_price'], 0, ',', '.') ?></td>
          <td class="text-center">
            <?php if ($activeWhStock <= (int) $it['min_stock']): ?>
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
