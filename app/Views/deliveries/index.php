<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php 
$curStatus = $status ?? '';
$filterQuery = array_filter(['q' => $q ?? '', 'status' => $curStatus, 'warehouse_id' => $warehouseId ?? 0, 'start_date' => $start_date ?? '', 'end_date' => $end_date ?? ''], static fn ($v) => $v !== '' && $v !== 0); 
$filterActive = ($q ?? '') !== '' || ($warehouseId ?? 0) !== 0 || ($start_date ?? '') !== '' || ($end_date ?? '') !== '';
?>

<!-- Tab Status Cepat Pengiriman -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <ul class="nav nav-pills gap-1">
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $curStatus === '' ? 'active fw-bold' : 'text-secondary' ?>" href="/deliveries?<?= http_build_query(array_filter(['q' => $q ?? '', 'warehouse_id' => $warehouseId ?? 0, 'start_date' => $start_date ?? '', 'end_date' => $end_date ?? ''])) ?>">
        Semua Pengiriman
        <span class="badge ms-1 <?= $curStatus === '' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['all'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $curStatus === 'pending_so' ? 'active bg-primary text-white fw-bold' : (! empty($statusCounts['pending_so']) ? 'text-primary fw-bold border border-primary-subtle' : 'text-secondary') ?>" href="/deliveries?status=pending_so">
        Siap Dikirim (SO Dikonfirmasi)
        <span class="badge ms-1 <?= $curStatus === 'pending_so' ? 'bg-white text-primary' : (! empty($statusCounts['pending_so']) ? 'bg-primary text-white' : 'bg-secondary-lt') ?>"><?= esc($statusCounts['pending_so'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $curStatus === 'scheduled' ? 'active fw-bold' : 'text-secondary' ?>" href="/deliveries?status=scheduled">
        Terjadwal
        <span class="badge ms-1 <?= $curStatus === 'scheduled' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['scheduled'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $curStatus === 'shipping' ? 'active fw-bold' : 'text-secondary' ?>" href="/deliveries?status=shipping">
        Dalam Pengiriman
        <span class="badge ms-1 <?= $curStatus === 'shipping' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['shipping'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $curStatus === 'delivered' ? 'active fw-bold' : 'text-secondary' ?>" href="/deliveries?status=delivered">
        Terkirim
        <span class="badge ms-1 <?= $curStatus === 'delivered' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['delivered'] ?? 0) ?></span>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link px-3 py-1 <?= $curStatus === 'cancelled' ? 'active fw-bold' : 'text-secondary' ?>" href="/deliveries?status=cancelled">
        Dibatalkan
        <span class="badge ms-1 <?= $curStatus === 'cancelled' ? 'bg-white text-primary' : 'bg-secondary-lt' ?>"><?= esc($statusCounts['cancelled'] ?? 0) ?></span>
      </a>
    </li>
  </ul>
</div>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/deliveries" method="get">
      <input type="hidden" name="status" value="<?= esc($curStatus) ?>">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Pencarian</label>
          <div class="input-icon">
            <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="<?= $curStatus === 'pending_so' ? 'No. SO atau pelanggan...' : 'No. DO, SO, pelanggan...' ?>" value="<?= esc($q ?? '') ?>">
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
          <button class="btn btn-sm btn-primary" type="submit">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
            Filter
          </button>
          <?php if ($filterActive): ?>
            <a class="btn btn-sm btn-outline-secondary" href="/deliveries<?= $curStatus !== '' ? '?status=' . urlencode($curStatus) : '' ?>">Reset</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<?php if ($curStatus === 'pending_so'): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Pesanan Pelanggan (SO) Siap Dikirim</h3>
  </div>
  <?php if (empty($pendingSos)): ?>
    <div class="empty">
      <div class="empty-icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12l2 2l4 -4"/><path d="M12 3a9 9 0 1 0 0 18a9 9 0 0 0 0 -18z"/></svg>
      </div>
      <p class="empty-title">Tidak ada Pesanan Jual yang menunggu pengiriman</p>
      <p class="empty-subtitle text-secondary">Semua pesanan berstatus Dikonfirmasi sudah diproses.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th>Nomor SO</th>
            <th>Pelanggan</th>
            <th>Gudang Pengirim</th>
            <th>Tanggal SO</th>
            <th class="text-center">Item / Unit</th>
            <th>Catatan</th>
            <?php if (session()->get('user_role') === 'gudang'): ?>
              <th class="text-end">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingSos as $ps): ?>
          <tr>
            <td><code class="text-primary font-monospace fw-bold text-nowrap"><?= esc($ps['so_number']) ?></code></td>
            <td class="fw-semibold text-dark"><?= esc($ps['customer_name'] ?? '-') ?></td>
            <td><span class="badge bg-secondary-lt"><?= esc($ps['warehouse_name'] ?? '-') ?></span></td>
            <td class="text-secondary font-monospace text-nowrap"><?= date('d/m/Y', strtotime($ps['order_date'])) ?></td>
            <td class="text-center text-nowrap"><?= (int) $ps['item_count'] ?> item &bull; <strong><?= (int) $ps['total_qty'] ?></strong> unit</td>
            <td class="text-secondary small" style="min-width: 220px; white-space: pre-line;"><?= esc($ps['notes'] ?: '-') ?></td>
            <?php if (session()->get('user_role') === 'gudang'): ?>
              <td class="text-end text-nowrap">
                <a href="/deliveries/create?sales_order_id=<?= esc($ps['id']) ?>" class="btn btn-sm btn-primary">Proses Kirim &rarr;</a>
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
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304"/><path d="M9 11v-5a3 3 0 0 1 6 0v5"/></svg>
      </div>
      <p class="empty-title"><?= $filterActive ? 'Tidak ada pengiriman yang cocok dengan filter.' : 'Belum ada pengiriman' ?></p>
      <p class="empty-subtitle text-secondary"><?= $filterActive ? 'Coba ubah kata kunci atau reset filternya.' : 'Klik tombol Buat Pengiriman untuk mengirim barang dari pesanan jual yang sudah dikonfirmasi.' ?></p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th>Nomor DO</th>
            <th>Nomor Pesanan Jual</th>
            <th>Gudang Pengirim</th>
            <th>Tanggal Kirim</th>
            <th>Status</th>
            <th>Dikirim Oleh</th>
            <th>Catatan</th>
            <?php if (session()->get('user_role') === 'gudang'): ?>
              <th class="text-end" style="width: 115px;">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): 
          $doStatus = $it['status'] ?? 'delivered';
          $sb = status_badge($doStatus);
        ?>
        <tr class="<?= $doStatus === 'cancelled' ? 'opacity-75' : '' ?>" style="cursor: pointer;" onclick="if (!event.target.closest('a, button, form, input')) { window.location='/deliveries/show/<?= (int) $it['id'] ?>'; }">
          <td><a href="/deliveries/show/<?= esc($it['id']) ?>" class="text-decoration-none"><code class="text-primary font-monospace fw-bold text-nowrap"><?= esc($it['delivery_number']) ?></code></a></td>
          <td><code class="text-dark font-monospace text-nowrap"><?= esc($it['so_number']) ?></code></td>
          <td><span class="badge bg-secondary-lt"><?= esc($it['warehouse_name'] ?? '-') ?></span></td>
          <td class="text-secondary font-monospace">
            <?= date('d/m/Y', strtotime($it['delivery_date'])) ?>
            <?php if (! empty($it['delivery_time'])): ?>
              <div class="small text-muted"><?= esc($it['delivery_time']) ?> WIB</div>
            <?php endif; ?>
          </td>
          <td>
            <?php if (in_array($doStatus, ['scheduled', 'shipping'], true)): ?>
              <span class="badge <?= $sb['badge'] ?> d-inline-flex align-items-center gap-2">
                <span class="status-dot status-dot-animated <?= $doStatus === 'shipping' ? 'bg-azure' : 'bg-cyan' ?>"></span>
                <?= esc($sb['label']) ?>
              </span>
            <?php else: ?>
              <span class="badge <?= $sb['badge'] ?>"><?= esc($sb['label']) ?></span>
            <?php endif; ?>
          </td>
          <td class="fw-semibold text-dark"><?= esc($it['deliverer_name']) ?></td>
          <td class="text-secondary small" style="min-width: 140px; white-space: pre-line;">
            <?= esc($it['notes']) ?>
            <?php if ($doStatus === 'cancelled' && ! empty($it['cancel_reason'])): ?>
              <div class="text-danger mt-1"><strong>Alasan Batal:</strong> <?= esc($it['cancel_reason']) ?></div>
            <?php endif; ?>
          </td>
          <?php if (session()->get('user_role') === 'gudang'): ?>
            <td class="text-end">
              <div class="d-flex flex-column gap-1 align-items-stretch">
                <?php if (in_array($doStatus, ['scheduled', 'shipping'], true)): ?>
                  <form action="/deliveries/complete/<?= esc($it['id']) ?>" method="post" class="d-grid" data-confirm="Tandai pengiriman <strong><?= esc($it['delivery_number']) ?></strong> sudah <strong>Terkirim</strong>?" data-confirm-title="Selesaikan Pengiriman" data-confirm-btn="Ya, Terkirim" data-confirm-type="primary">
                    <button type="submit" class="btn btn-sm btn-success w-100">Selesaikan</button>
                  </form>
                  <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-cancel-do" data-bs-toggle="modal" data-bs-target="#modalCancelDO" data-id="<?= (int) $it['id'] ?>" data-number="<?= esc($it['delivery_number']) ?>">Batalkan</button>
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

    <?= view('partials/pagination', ['pager' => $pager ?? null, 'query' => $filterQuery, 'label' => 'Pengiriman']) ?>
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
          <div class="mb-3">
            <label class="form-label required">Pilih Tindakan Lanjutan untuk Pesanan Jual (SO)</label>
            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column gap-2">
              <label class="form-selectgroup-item flex-fill m-0">
                <input type="radio" name="cancel_type" value="revision" class="form-selectgroup-input" checked>
                <div class="form-selectgroup-label d-flex align-items-start p-3 text-start">
                  <div class="me-3"><span class="form-selectgroup-check"></span></div>
                  <div>
                    <div class="fw-bold text-dark">Customer Minta Revisi (Ganti / Tambah / Kurang Barang)</div>
                    <div class="text-secondary small">DO dibatalkan, SO masuk status <strong>Butuh Revisi</strong> agar Staff Sales memperbaiki pesanan sesuai catatan Anda. Stok tetap ditahan sementara.</div>
                  </div>
                </div>
              </label>
              <label class="form-selectgroup-item flex-fill m-0">
                <input type="radio" name="cancel_type" value="cancel_order" class="form-selectgroup-input">
                <div class="form-selectgroup-label d-flex align-items-start p-3 text-start">
                  <div class="me-3"><span class="form-selectgroup-check"></span></div>
                  <div>
                    <div class="fw-bold text-danger">Customer Batal Total / Menolak Barang</div>
                    <div class="text-secondary small">DO dan SO otomatis <strong>Dibatalkan</strong>, serta seluruh stok langsung dikembalikan ke gudang.</div>
                  </div>
                </div>
              </label>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label required">Catatan / Alasan Pembatalan</label>
            <textarea name="cancel_reason" class="form-control" rows="3" placeholder="Contoh: Customer minta tambah pulpen 5 pack / Customer batal beli karena pindah kantor" required></textarea>
            <div class="form-text">Pesan ini akan tampil di halaman detail SO untuk dibaca Staff Sales.</div>
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
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('modalCancelDO');
  if (modalEl) {
    modalEl.addEventListener('show.bs.modal', function (event) {
      const btn = event.relatedTarget;
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      const doNumber = btn.getAttribute('data-number');
      document.getElementById('cancelDoNumber').textContent = doNumber || '';
      document.getElementById('formCancelDO').action = '/deliveries/cancel/' + id;
    });
  }
});
</script>
<?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>
