<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php 
$curStatus = $status ?? '';
$filterQuery = array_filter(['q' => $q ?? '', 'status' => $curStatus, 'warehouse_id' => $warehouseId ?? 0, 'start_date' => $start_date ?? '', 'end_date' => $end_date ?? ''], static fn ($v) => $v !== '' && $v !== 0); 
$filterActive = ($q ?? '') !== '' || ($warehouseId ?? 0) !== 0 || ($start_date ?? '') !== '' || ($end_date ?? '') !== '';
?>

<!-- Tab Status Cepat Penerimaan Barang -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <ul class="nav nav-pills gap-1">
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $curStatus === '' ? 'active fw-bold' : 'text-secondary' ?>" href="/goods-receipts?<?= http_build_query(array_filter(['q' => $q ?? '', 'warehouse_id' => $warehouseId ?? 0, 'start_date' => $start_date ?? '', 'end_date' => $end_date ?? ''])) ?>">
        Riwayat Penerimaan (GR)
        <span class="badge ms-1 <?= $curStatus === '' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['all'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $curStatus === 'pending_po' ? 'active bg-primary text-white fw-bold' : (! empty($statusCounts['pending_po']) ? 'text-primary fw-bold border border-primary-subtle' : 'text-secondary') ?>" href="/goods-receipts?status=pending_po">
        Menunggu Diterima (PO Disetujui)
        <span class="badge ms-1 <?= $curStatus === 'pending_po' ? 'bg-white text-primary' : (! empty($statusCounts['pending_po']) ? 'bg-primary text-white' : 'bg-secondary-lt') ?>"><?= esc($statusCounts['pending_po'] ?? 0) ?></span>
      </a>
    </li>
  </ul>
</div>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/goods-receipts" method="get">
      <input type="hidden" name="status" value="<?= esc($curStatus) ?>">
      <div class="row g-2 align-items-end">
        <div class="<?= $curStatus === 'pending_po' ? 'col-md-4' : 'col-md-3' ?>">
          <label class="form-label small text-secondary mb-1">Pencarian</label>
          <div class="input-icon">
            <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="<?= $curStatus === 'pending_po' ? 'No. PO atau Pemasok...' : 'No. GR, PO, pemasok...' ?>" value="<?= esc($q ?? '') ?>">
          </div>
        </div>
        <?php if ($curStatus !== 'pending_po'): ?>
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Gudang Penerima</label>
          <select name="warehouse_id" class="form-select form-select-sm">
            <option value="0">Semua Gudang</option>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= esc($wh['id']) ?>" <?= (int) ($warehouseId ?? 0) === (int) $wh['id'] ? 'selected' : '' ?>><?= esc($wh['name']) ?> (<?= esc($wh['code']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="<?= $curStatus === 'pending_po' ? 'col-md-3' : 'col-md-2' ?>">
          <label class="form-label small text-secondary mb-1">Dari Tanggal</label>
          <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($start_date ?? '') ?>">
        </div>
        <div class="<?= $curStatus === 'pending_po' ? 'col-md-3' : 'col-md-2' ?>">
          <label class="form-label small text-secondary mb-1">Sampai Tanggal</label>
          <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($end_date ?? '') ?>">
        </div>
        <div class="col-md-2 d-flex gap-2 justify-content-end">
          <button class="btn btn-sm btn-primary" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
            Filter
          </button>
          <?php if ($filterActive): ?>
            <a class="btn btn-sm btn-outline-secondary" href="/goods-receipts<?= $curStatus !== '' ? '?status=' . urlencode($curStatus) : '' ?>">Reset</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<?php if ($curStatus === 'pending_po'): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Pesanan Pembelian (PO) Siap Diterima di Gudang</h3>
  </div>
  <?php if (empty($pendingPos)): ?>
    <div class="empty">
      <div class="empty-icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12l2 2l4 -4"/><path d="M12 3a9 9 0 1 0 0 18a9 9 0 0 0 0 -18z"/></svg>
      </div>
      <p class="empty-title">Tidak ada Pesanan Beli yang menunggu penerimaan</p>
      <p class="empty-subtitle text-secondary">Semua PO berstatus Disetujui sudah diterima di gudang.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th>Nomor PO</th>
            <th>Pemasok</th>
            <th>Tanggal PO</th>
            <th class="text-center">Item / Unit</th>
            <th>Catatan</th>
            <?php if (session()->get('user_role') === 'gudang'): ?>
              <th class="text-end">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingPos as $pp): ?>
          <tr>
            <td><code class="text-primary font-monospace fw-bold text-nowrap"><?= esc($pp['po_number']) ?></code></td>
            <td class="fw-semibold text-dark"><?= esc($pp['supplier_name'] ?? '-') ?></td>
            <td class="text-secondary font-monospace text-nowrap"><?= date('d/m/Y', strtotime($pp['order_date'])) ?></td>
            <td class="text-center text-nowrap"><?= (int) $pp['item_count'] ?> item &bull; <strong><?= (int) $pp['total_qty'] ?></strong> unit</td>
            <td class="text-secondary small" style="min-width: 220px;"><?= esc($pp['notes'] ?: '-') ?></td>
            <?php if (session()->get('user_role') === 'gudang'): ?>
              <td class="text-end text-nowrap">
                <a href="/goods-receipts/create?purchase_order_id=<?= esc($pp['id']) ?>" class="btn btn-sm btn-primary">Terima Barang &rarr;</a>
              </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php else: ?>

<div class="card border-0 shadow-sm">
  <?php if (empty($items)): ?>
    <div class="empty">
      <div class="empty-icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
      </div>
      <p class="empty-title"><?= $filterActive ? 'Tidak ada penerimaan barang yang cocok dengan filter.' : 'Belum ada barang masuk' ?></p>
      <p class="empty-subtitle text-secondary"><?= $filterActive ? 'Coba ubah kata kunci atau reset filternya.' : 'Klik tombol Terima Barang untuk mencatat penerimaan dari pesanan beli yang sudah disetujui.' ?></p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th>Nomor GR</th>
            <th>Nomor Pesanan Beli</th>
            <th>Gudang Penerima</th>
            <th>Tanggal Terima</th>
            <th>Diterima Oleh</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><code class="text-primary font-monospace fw-bold text-nowrap"><?= esc($it['gr_number']) ?></code></td>
          <td><code class="text-dark font-monospace text-nowrap"><?= esc($it['po_number']) ?></code></td>
          <td><span class="badge bg-secondary-lt"><?= esc($it['warehouse_name'] ?? '-') ?></span></td>
          <td class="text-secondary font-monospace text-nowrap"><?= date('d/m/Y', strtotime($it['receipt_date'])) ?></td>
          <td class="fw-medium text-dark"><?= esc($it['receiver_name']) ?></td>
          <td class="text-secondary small" style="min-width: 220px;">
            <?= esc($it['notes'] ?: '-') ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?= view('partials/pagination', ['pager' => $pager ?? null, 'query' => $filterQuery, 'label' => 'Dokumen Barang Masuk']) ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
