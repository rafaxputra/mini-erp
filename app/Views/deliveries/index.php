<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php 
$filterQuery = array_filter(['q' => $q ?? '', 'warehouse_id' => $warehouseId ?? 0, 'start_date' => $start_date ?? '', 'end_date' => $end_date ?? ''], static fn ($v) => $v !== '' && $v !== 0); 
$filterActive = ! empty($filterQuery);
?>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/deliveries" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Pencarian</label>
          <div class="input-icon">
            <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
            <input type="text" name="q" class="form-control form-control-sm js-table-search" placeholder="No. DO, SO, pelanggan..." value="<?= esc($q ?? '') ?>">
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Gudang Pengirim</label>
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
            <a class="btn btn-sm btn-outline-secondary" href="/deliveries">Reset</a>
          <?php endif; ?>
          <?php if (session()->get('user_role') === 'gudang'): ?>
            <a class="btn btn-sm btn-success d-inline-flex align-items-center gap-1" href="/deliveries/create">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
              Kirim
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
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304"/><path d="M9 11v-5a3 3 0 0 1 6 0v5"/></svg>
      </div>
      <div class="h4 mb-1"><?= $filterActive ? 'Tidak ada pengiriman yang cocok dengan filter.' : 'Belum ada pengiriman' ?></div>
      <div class="text-secondary small"><?= $filterActive ? 'Coba ubah kata kunci atau reset filternya.' : 'Klik tombol Buat Pengiriman untuk mengirim barang dari pesanan jual yang sudah dikonfirmasi.' ?></div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 140px;">Nomor DO</th>
            <th style="width: 140px;">Nomor Pesanan Jual</th>
            <th>Gudang Pengirim</th>
            <th style="width: 115px;">Tanggal Kirim</th>
            <th style="width: 110px;">Status</th>
            <th>Dikirim Oleh</th>
            <th>Catatan</th>
            <?php if (session()->get('user_role') === 'gudang'): ?>
              <th class="text-end" style="width: 180px;">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): 
          $doStatus = $it['status'] ?? 'delivered';
          $sb = status_badge($doStatus);
        ?>
        <tr class="<?= $doStatus === 'cancelled' ? 'opacity-75' : '' ?>">
          <td><code class="text-primary font-monospace fw-bold"><?= esc($it['delivery_number']) ?></code></td>
          <td><code class="text-dark font-monospace"><?= esc($it['so_number']) ?></code></td>
          <td><span class="badge bg-secondary-lt"><?= esc($it['warehouse_name'] ?? '-') ?></span></td>
          <td class="text-secondary font-monospace"><?= date('d/m/Y', strtotime($it['delivery_date'])) ?></td>
          <td><span class="badge <?= $sb['badge'] ?>"><?= esc($sb['label']) ?></span></td>
          <td class="fw-semibold text-dark"><?= esc($it['deliverer_name']) ?></td>
          <td class="text-secondary small">
            <?= esc($it['notes']) ?>
            <?php if ($doStatus === 'cancelled' && ! empty($it['cancel_reason'])): ?>
              <div class="text-danger mt-1"><strong>Alasan Batal:</strong> <?= esc($it['cancel_reason']) ?></div>
            <?php endif; ?>
          </td>
          <?php if (session()->get('user_role') === 'gudang'): ?>
            <td class="text-end">
              <div class="d-inline-flex gap-1">
                <?php if ($doStatus === 'scheduled'): ?>
                  <form action="/deliveries/complete/<?= esc($it['id']) ?>" method="post" class="d-inline" data-confirm="Tandai pengiriman <strong><?= esc($it['delivery_number']) ?></strong> sudah <strong>Terkirim</strong>?" data-confirm-title="Selesaikan Pengiriman" data-confirm-btn="Ya, Terkirim" data-confirm-type="primary">
                    <button type="submit" class="btn btn-sm btn-success">Selesaikan</button>
                  </form>
                <?php endif; ?>
                <?php if ($doStatus !== 'cancelled'): ?>
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="openCancelDoModal(<?= (int) $it['id'] ?>, <?= json_encode($it['delivery_number']) ?>)">Batalkan</button>
                <?php else: ?>
                  <span class="text-muted small">-</span>
                <?php endif; ?>
              </div>
            </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (isset($pager)): ?>
    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center py-2 bg-transparent gap-2">
      <span class="text-muted small">Menampilkan <?= esc($pager['from']) ?> sampai <?= esc($pager['to']) ?> dari <?= esc($pager['total']) ?> Pengiriman</span>
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

<?php if (session()->get('user_role') === 'gudang'): ?>
<!-- Modal Pembatalan DO -->
<div class="modal modal-blur fade" id="modalCancelDO" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formCancelDO" action="" method="post">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-danger">Batalkan Pengiriman (<span id="cancelDoNumber"></span>)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning small mb-3">
            Pembatalan Pengiriman (DO) akan mengembalikan status Pesanan Jual (SO) terkait menjadi <strong>Dikonfirmasi</strong> agar dapat diproses ulang atau diubah oleh Sales.
          </div>
          <div class="mb-3">
            <label class="form-label required">Alasan Pembatalan</label>
            <textarea name="cancel_reason" class="form-control" rows="3" placeholder="Contoh: Pelanggan meminta ganti/tambah barang pada pesanan" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-danger">Ya, Batalkan DO</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openCancelDoModal(id, doNumber) {
  document.getElementById('cancelDoNumber').textContent = doNumber;
  document.getElementById('formCancelDO').action = '/deliveries/cancel/' + id;
  const modal = new bootstrap.Modal(document.getElementById('modalCancelDO'));
  modal.show();
}
</script>
<?php endif; ?>

<?= $this->endSection() ?>
