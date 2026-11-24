<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php 
$filterQuery = array_filter(['q' => $q ?? '', 'warehouse_id' => $warehouseId ?? 0, 'start_date' => $start_date ?? '', 'end_date' => $end_date ?? ''], static fn ($v) => $v !== '' && $v !== 0); 
$filterActive = ! empty($filterQuery);
?>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/goods-receipts" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Pencarian</label>
          <div class="input-icon">
            <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
            <input type="text" name="q" class="form-control form-control-sm js-table-search" placeholder="No. GR, PO, pemasok..." value="<?= esc($q ?? '') ?>">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Gudang Penerima</label>
          <select name="warehouse_id" class="form-select form-select-sm">
            <option value="0">Semua Gudang</option>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= esc($wh['id']) ?>" <?= (int) ($warehouseId ?? 0) === (int) $wh['id'] ? 'selected' : '' ?>><?= esc($wh['name']) ?> (<?= esc($wh['code']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Dari Tanggal</label>
          <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($start_date ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Sampai Tanggal</label>
          <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($end_date ?? '') ?>">
        </div>
        <div class="col-md-2 d-flex gap-2 justify-content-end">
          <button class="btn btn-sm btn-primary" type="submit">Filter</button>
          <?php if ($filterActive): ?>
            <a class="btn btn-sm btn-outline-secondary" href="/goods-receipts">Reset</a>
          <?php endif; ?>
          <?php if (session()->get('user_role') === 'gudang'): ?>
            <a class="btn btn-sm btn-success d-inline-flex align-items-center gap-1" href="/goods-receipts/create">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
              Terima
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
      <div class="h4 mb-1"><?= $filterActive ? 'Tidak ada penerimaan barang yang cocok dengan filter.' : 'Belum ada barang masuk' ?></div>
      <div class="text-secondary small"><?= $filterActive ? 'Coba ubah kata kunci atau reset filternya.' : 'Klik tombol Terima Barang untuk mencatat penerimaan dari pesanan beli yang sudah disetujui.' ?></div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 140px;">Nomor GR</th>
            <th style="width: 140px;">Nomor Pesanan Beli</th>
            <th>Gudang Penerima</th>
            <th style="width: 120px;">Tanggal Terima</th>
            <th>Diterima Oleh</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><code class="text-primary font-monospace fw-bold"><?= esc($it['gr_number']) ?></code></td>
          <td><code class="text-dark font-monospace"><?= esc($it['po_number']) ?></code></td>
          <td><span class="badge bg-secondary-lt"><?= esc($it['warehouse_name'] ?? '-') ?></span></td>
          <td class="text-secondary font-monospace"><?= date('d/m/Y', strtotime($it['receipt_date'])) ?></td>
          <td class="fw-medium text-dark"><?= esc($it['receiver_name']) ?></td>
          <td class="text-secondary text-truncate" style="max-width: 250px;" title="<?= esc($it['notes']) ?>">
            <?= esc($it['notes'] ?: '-') ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (isset($pager)): ?>
    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center py-2 bg-transparent gap-2">
      <span class="text-muted small">Menampilkan <?= esc($pager['from']) ?> sampai <?= esc($pager['to']) ?> dari <?= esc($pager['total']) ?> Dokumen Barang Masuk</span>
      <?php if ($pager['lastPage'] > 1): ?>
        <ul class="pagination pagination-sm m-0">
          <li class="page-item <?= $pager['page'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filterQuery, ['page' => max(1, $pager['page'] - 1)])) ?>">&laquo;</a>
          </li>
          <?php 
          $pages = []; 
          for ($p = 1; $p <= $pager['lastPage']; $p++) { 
            if ($p === 1 || $p === $pager['lastPage'] || abs($p - $pager['page']) <= 1) $pages[] = $p; 
          } 
          $pages = array_values(array_unique($pages)); 
          $prev = 0; 
          foreach ($pages as $p): 
            if ($p - $prev > 1): ?>
              <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <li class="page-item <?= $p === $pager['page'] ? 'active' : '' ?>">
              <a class="page-link" href="?<?= http_build_query(array_merge($filterQuery, ['page' => $p])) ?>"><?= esc($p) ?></a>
            </li>
          <?php $prev = $p; endforeach; ?>
          <li class="page-item <?= $pager['page'] >= $pager['lastPage'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filterQuery, ['page' => min($pager['lastPage'], $pager['page'] + 1)])) ?>">&raquo;</a>
          </li>
        </ul>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
