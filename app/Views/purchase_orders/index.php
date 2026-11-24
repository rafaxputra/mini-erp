<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php $filterQuery = array_filter(['q' => $q, 'status' => $status, 'start_date' => $start_date, 'end_date' => $end_date], static fn ($v) => $v !== ''); ?>
<?php $filterActive = $q !== '' || $status !== '' || $start_date !== '' || $end_date !== ''; ?>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/purchase-orders" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Pencarian</label>
          <div class="input-icon">
            <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="No. PO atau Pemasok..." value="<?= esc($q) ?>">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">Semua Status</option>
            <?php foreach ($statuses as $s): ?>
              <option value="<?= esc($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= esc(status_label($s)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Dari Tanggal</label>
          <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($start_date) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Sampai Tanggal</label>
          <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($end_date) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2 justify-content-end">
          <button class="btn btn-sm btn-primary" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
            Filter
          </button>
          <?php if ($filterActive): ?>
            <a class="btn btn-sm btn-outline-secondary" href="/purchase-orders">Reset</a>
          <?php endif; ?>
          <?php if (session()->get('user_role') === 'purchasing'): ?>
            <a class="btn btn-sm btn-success d-inline-flex align-items-center gap-1" href="/purchase-orders/create">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
              Buat PO
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
        <tr>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($it['po_number']) ?></code></td>
          <td class="fw-semibold text-dark"><?= esc($it['supplier_name']) ?></td>
          <td class="text-secondary font-monospace"><?= date('d/m/Y', strtotime($it['order_date'])) ?></td>
          <td class="text-center"><span class="badge <?= esc(status_badge_class($it['status'])) ?> fw-bold"><?= esc(status_label($it['status'])) ?></span></td>
          <td class="text-end fw-semibold text-dark font-monospace">Rp <?= number_format((float) $it['total_amount'], 0, ',', '.') ?></td>
          <td class="text-secondary"><?= esc($it['creator_name']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="/purchase-orders/show/<?= esc($it['id']) ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
              Detail
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center py-2 bg-transparent gap-2">
      <span class="text-muted small">Menampilkan <?= esc($pager['from']) ?> sampai <?= esc($pager['to']) ?> dari <?= esc($pager['total']) ?> Pesanan Pembelian (PO)</span>
      <?php if ($pager['lastPage'] > 1): ?>
        <ul class="pagination pagination-sm m-0">
          <li class="page-item <?= $pager['page'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="/purchase-orders?<?= http_build_query(array_merge($filterQuery, ['page' => max(1, $pager['page'] - 1)])) ?>">&laquo;</a>
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
              <a class="page-link" href="/purchase-orders?<?= http_build_query(array_merge($filterQuery, ['page' => $p])) ?>"><?= esc($p) ?></a>
            </li>
          <?php $prev = $p; endforeach; ?>
          <li class="page-item <?= $pager['page'] >= $pager['lastPage'] ? 'disabled' : '' ?>">
            <a class="page-link" href="/purchase-orders?<?= http_build_query(array_merge($filterQuery, ['page' => min($pager['lastPage'], $pager['page'] + 1)])) ?>">&raquo;</a>
          </li>
        </ul>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
