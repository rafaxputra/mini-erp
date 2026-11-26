<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php $filterQuery = array_filter(['q' => $q, 'status' => $status, 'start_date' => $start_date, 'end_date' => $end_date], static fn ($v) => $v !== ''); ?>
<?php $filterActive = $q !== '' || $status !== '' || $start_date !== '' || $end_date !== ''; ?>
<?php $isManajemen = session()->get('user_role') === 'manajemen'; ?>

<!-- Quick Status Tabs (Pills) & Tombol Tambah PO -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <ul class="nav nav-pills gap-1">
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $status === '' ? 'active fw-bold' : 'text-secondary' ?>" href="/purchase-orders?<?= http_build_query(array_filter(['q' => $q, 'start_date' => $start_date, 'end_date' => $end_date])) ?>">
        Semua
        <span class="badge ms-1 <?= $status === '' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['all'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $status === 'draft' ? 'active bg-warning text-dark fw-bold' : (! empty($pendingDraftCount) && $isManajemen ? 'text-warning fw-bold border border-warning-subtle' : 'text-secondary') ?>" href="/purchase-orders?<?= http_build_query(array_filter(['q' => $q, 'status' => 'draft', 'start_date' => $start_date, 'end_date' => $end_date])) ?>">
        <?= $isManajemen ? 'Butuh ACC' : 'Draf' ?>
        <span class="badge ms-1 <?= $status === 'draft' ? 'bg-white text-dark' : (! empty($pendingDraftCount) ? 'bg-warning text-dark' : 'bg-secondary-lt') ?>"><?= esc($statusCounts['draft'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $status === 'approved' ? 'active fw-bold' : 'text-secondary' ?>" href="/purchase-orders?<?= http_build_query(array_filter(['q' => $q, 'status' => 'approved', 'start_date' => $start_date, 'end_date' => $end_date])) ?>">
        Disetujui
        <span class="badge ms-1 <?= $status === 'approved' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['approved'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $status === 'received' ? 'active fw-bold' : 'text-secondary' ?>" href="/purchase-orders?<?= http_build_query(array_filter(['q' => $q, 'status' => 'received', 'start_date' => $start_date, 'end_date' => $end_date])) ?>">
        Diterima
        <span class="badge ms-1 <?= $status === 'received' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['received'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $status === 'cancelled' ? 'active fw-bold' : 'text-secondary' ?>" href="/purchase-orders?<?= http_build_query(array_filter(['q' => $q, 'status' => 'cancelled', 'start_date' => $start_date, 'end_date' => $end_date])) ?>">
        Dibatalkan
        <span class="badge ms-1 <?= $status === 'cancelled' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['cancelled'] ?? 0) ?></span>
      </a>
    </li>
  </ul>

  <?php if (session()->get('user_role') === 'purchasing'): ?>
    <a class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 shadow-sm px-3" href="/purchase-orders/create">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
      Buat PO Baru
    </a>
  <?php endif; ?>
</div>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/purchase-orders" method="get">
      <input type="hidden" name="status" value="<?= esc($status) ?>">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label small text-secondary mb-1">Pencarian</label>
          <div class="input-icon">
            <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="No. PO atau Pemasok..." value="<?= esc($q) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Dari Tanggal</label>
          <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($start_date) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Sampai Tanggal</label>
          <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($end_date) ?>">
        </div>
        <div class="col-md-2 d-flex gap-2 justify-content-end">
          <button class="btn btn-sm btn-primary" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
            Filter
          </button>
          <?php if ($filterActive): ?>
            <a class="btn btn-sm btn-outline-secondary" href="/purchase-orders">Reset</a>
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
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
      </div>
      <div class="h4 mb-1"><?= $filterActive ? 'Tidak ada Pesanan Beli yang cocok dengan filter.' : 'Belum ada pesanan beli' ?></div>
      <div class="text-secondary small"><?= $filterActive ? 'Coba ubah kata kunci atau reset filternya.' : 'Klik tombol Buat PO untuk membuat pesanan baru.' ?></div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 150px;">Nomor PO</th>
            <th>Pemasok</th>
            <th style="width: 120px;">Tanggal</th>
            <th class="text-center" style="width: 120px;">Status</th>
            <th class="text-end" style="width: 160px;">Total Nilai</th>
            <th>Dibuat Oleh</th>
            <th class="text-end" style="width: 100px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <?php $isDraftAction = $it['status'] === 'draft' && $isManajemen; ?>
        <tr style="<?= $isDraftAction ? 'background-color: rgba(245, 158, 11, 0.05); box-shadow: inset 3px 0 0 #f59e0b;' : '' ?>">
          <td><code class="text-dark font-monospace fw-bold"><?= esc($it['po_number']) ?></code></td>
          <td class="fw-semibold text-dark"><?= esc($it['supplier_name']) ?></td>
          <td class="text-secondary font-monospace"><?= date('d/m/Y', strtotime($it['order_date'])) ?></td>
          <td class="text-center">
            <?php if ($isDraftAction): ?>
              <span class="badge bg-warning-lt text-warning fw-bold border border-warning-subtle d-inline-flex align-items-center gap-1 px-2 py-1" title="Pesanan ini menunggu persetujuan (ACC) dari Anda">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.76 2.75"/></svg>
                Butuh ACC
              </span>
            <?php else: ?>
              <span class="badge <?= esc(status_badge_class($it['status'])) ?> fw-bold"><?= esc(status_label($it['status'])) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-end fw-semibold text-dark font-monospace">Rp <?= number_format((float) $it['total_amount'], 0, ',', '.') ?></td>
          <td class="text-secondary"><?= esc($it['creator_name']) ?></td>
          <td class="text-end">
            <?php if ($isDraftAction): ?>
              <a class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm px-2 py-1 fw-semibold" href="/purchase-orders/show/<?= esc($it['id']) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z"/><path d="M9 14l2 2l4 -4"/></svg>
                Review / ACC
              </a>
            <?php else: ?>
              <a class="btn btn-sm btn-outline-secondary px-2 py-1" href="/purchase-orders/show/<?= esc($it['id']) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-xs" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                Detail
              </a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?= view('partials/pagination', ['pager' => $pager ?? null, 'query' => $filterQuery, 'label' => 'Pesanan Pembelian (PO)']) ?>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
