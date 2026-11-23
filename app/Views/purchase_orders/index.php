<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php $filterQuery = array_filter(['q' => $q, 'status' => $status, 'start_date' => $start_date, 'end_date' => $end_date], static fn ($v) => $v !== ''); ?>
<?php $filterActive = $q !== '' || $status !== '' || $start_date !== '' || $end_date !== ''; ?>
<form action="/purchase-orders" method="get" class="mb-3"><div class="row g-2">
<div class="col-md-3"><input type="text" name="q" class="form-control" placeholder="Cari nomor atau pemasok" value="<?= esc($q) ?>"></div>
<div class="col-md-2"><select name="status" class="form-select"><option value="">Semua status</option><?php foreach ($statuses as $s): ?><option value="<?= esc($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= esc(status_label($s)) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><input type="date" name="start_date" class="form-control" value="<?= esc($start_date) ?>"></div>
<div class="col-md-2"><input type="date" name="end_date" class="form-control" value="<?= esc($end_date) ?>"></div>
<div class="col-md-3"><button class="btn btn-primary" type="submit">Filter</button> <a class="btn btn-outline-secondary" href="/purchase-orders">Reset</a></div>
</div></form>
<div class="d-flex flex-wrap justify-content-end align-items-center gap-2 mb-3">
  <?php if (in_array(session()->get('user_role'), ['admin', 'purchasing'], true)): ?>
    <a class="btn btn-primary" href="/purchase-orders/create">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
      Buat Pesanan Beli
    </a>
  <?php endif; ?>
</div>

<?php if (empty($items)): ?>
  <div class="text-center py-5">
    <div class="text-secondary mb-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
    </div>
    <div class="h4 mb-1"><?= $filterActive ? 'Tidak ada Pesanan Beli yang cocok dengan filter.' : 'Belum ada pesanan beli' ?></div>
    <div class="text-secondary small"><?= $filterActive ? 'Coba ubah kata kunci atau reset filternya.' : 'Klik tombol Buat Pesanan Beli untuk membuat pesanan baru.' ?></div>
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Nomor Pesanan Beli</th>
          <th>Pemasok</th>
          <th>Tanggal</th>
          <th>Status</th>
          <th>Total</th>
          <th>Dibuat Oleh</th>
          <th style="width: 120px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php $statusBadge = ['draft' => 'bg-secondary-lt', 'approved' => 'bg-success-lt', 'received' => 'bg-primary-lt', 'cancelled' => 'bg-danger-lt']; ?>
      <?php foreach ($items as $it): ?>
      <tr>
        <td><code><?= esc($it['po_number']) ?></code></td>
        <td class="fw-medium"><?= esc($it['supplier_name']) ?></td>
        <td><?= esc($it['order_date']) ?></td>
        <td><span class="badge <?= $statusBadge[$it['status']] ?? 'bg-secondary-lt' ?>"><?= esc(status_label($it['status'])) ?></span></td>
        <td>Rp <?= number_format((float) $it['total_amount'], 0, ',', '.') ?></td>
        <td><?= esc($it['creator_name']) ?></td>
        <td>
          <a class="btn btn-sm btn-outline-info" href="/purchase-orders/show/<?= esc($it['id']) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
            Detail
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="d-flex justify-content-between align-items-center mt-3"><span class="text-muted">Menampilkan <?= esc($pager['from']) ?> sampai <?= esc($pager['to']) ?> dari <?= esc($pager['total']) ?> Pesanan Beli</span>
<?php if ($pager['lastPage'] > 1): ?><ul class="pagination m-0">
<li class="page-item <?= $pager['page'] <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="/purchase-orders?<?= http_build_query(array_merge($filterQuery, ['page' => max(1, $pager['page'] - 1)])) ?>">&laquo;</a></li>
<?php $pages = []; for ($p = 1; $p <= $pager['lastPage']; $p++) { if ($p === 1 || $p === $pager['lastPage'] || abs($p - $pager['page']) <= 1) $pages[] = $p; } $pages = array_values(array_unique($pages)); $prev = 0; foreach ($pages as $p): if ($p - $prev > 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?><li class="page-item <?= $p === $pager['page'] ? 'active' : '' ?>"><a class="page-link" href="/purchase-orders?<?= http_build_query(array_merge($filterQuery, ['page' => $p])) ?>"><?= esc($p) ?></a></li><?php $prev = $p; endforeach; ?>
<li class="page-item <?= $pager['page'] >= $pager['lastPage'] ? 'disabled' : '' ?>"><a class="page-link" href="/purchase-orders?<?= http_build_query(array_merge($filterQuery, ['page' => min($pager['lastPage'], $pager['page'] + 1)])) ?>">&raquo;</a></li>
</ul><?php endif; ?></div>
<?php endif; ?>
<?= $this->endSection() ?>
