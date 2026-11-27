<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$doStatus = $do['status'] ?? 'delivered';
$sb = status_badge($doStatus);
$totalQty = 0;
foreach ($items as $it) {
    $totalQty += (int) $it['qty_delivered'];
}
$isActive = in_array($doStatus, ['scheduled', 'shipping'], true);
?>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 border-bottom mb-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-dark-lt fs-3 px-2 py-1 font-monospace"><?= esc($do['delivery_number']) ?></span>
          <?php if ($isActive): ?>
            <span class="badge <?= $sb['badge'] ?> fs-4 px-2 py-1 d-inline-flex align-items-center gap-2">
              <span class="status-dot status-dot-animated <?= $doStatus === 'shipping' ? 'bg-azure' : 'bg-cyan' ?>"></span>
              <?= esc($sb['label']) ?>
            </span>
          <?php else: ?>
            <span class="badge <?= $sb['badge'] ?> fs-4 px-2 py-1"><?= esc($sb['label']) ?></span>
          <?php endif; ?>
        </div>
        <div class="text-secondary small">Dikirim oleh: <strong class="text-dark"><?= esc($do['deliverer_name'] ?? '-') ?></strong></div>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <?php if ($isActive && session()->get('user_role') === 'gudang'): ?>
          <form action="/deliveries/complete/<?= esc($do['id']) ?>" method="post" class="d-inline" data-confirm="Tandai pengiriman <strong><?= esc($do['delivery_number']) ?></strong> sudah <strong>Terkirim</strong>?" data-confirm-title="Selesaikan Pengiriman" data-confirm-btn="Ya, Terkirim" data-confirm-type="primary">
            <button type="submit" class="btn btn-success">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
              Selesaikan Pengiriman
            </button>
          </form>
          <button type="button" class="btn btn-outline-danger btn-cancel-do" data-bs-toggle="modal" data-bs-target="#modalCancelDO" data-id="<?= (int) $do['id'] ?>" data-number="<?= esc($do['delivery_number']) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
            Batalkan
          </button>
        <?php endif; ?>
        <a class="btn btn-outline-secondary" href="/deliveries">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0"/><path d="M5 12l6 6"/><path d="M5 12l6 -6"/></svg>
          Kembali ke Daftar
        </a>
      </div>
    </div>

    <div class="datagrid">
      <div class="datagrid-item">
        <div class="datagrid-title">Nomor Pesanan Jual (SO)</div>
        <div class="datagrid-content">
          <?php if ($canViewSo): ?>
            <a href="/sales-orders/show/<?= esc($do['so_id']) ?>" class="fw-bold font-monospace text-primary text-decoration-none"><?= esc($do['so_number']) ?></a>
          <?php else: ?>
            <code class="text-dark font-monospace fw-bold"><?= esc($do['so_number']) ?></code>
          <?php endif; ?>
        </div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Pelanggan</div>
        <div class="datagrid-content fw-semibold text-dark"><?= esc($do['customer_name'] ?? '-') ?></div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Gudang Pengirim</div>
        <div class="datagrid-content"><span class="badge bg-secondary-lt"><?= esc($do['warehouse_name'] ?? '-') ?></span></div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Tanggal &amp; Jam Kirim</div>
        <div class="datagrid-content font-monospace">
          <?= date('d/m/Y', strtotime($do['delivery_date'])) ?>
          <?= ! empty($do['delivery_time']) ? ' &bull; ' . esc($do['delivery_time']) . ' WIB' : '' ?>
        </div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Ringkasan Barang</div>
        <div class="datagrid-content"><?= count($items) ?> item &bull; <strong><?= number_format($totalQty, 0, ',', '.') ?></strong> unit dikirim</div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Catatan</div>
        <div class="datagrid-content text-secondary" style="white-space: pre-line;"><?= esc($do['notes'] ?: '-') ?></div>
      </div>
      <?php if ($doStatus === 'cancelled' && ! empty($do['cancel_reason'])): ?>
      <div class="datagrid-item">
        <div class="datagrid-title text-danger">Alasan Pembatalan</div>
        <div class="datagrid-content text-danger fw-medium"><?= esc($do['cancel_reason']) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header">
    <h3 class="card-title">Daftar Barang Dikirim</h3>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover table-striped">
      <thead>
        <tr>
          <th style="width: 50px;">No</th>
          <th>SKU</th>
          <th>Nama Produk</th>
          <th class="text-end">Qty Pesan (SO)</th>
          <th class="text-end">Qty Dikirim</th>
          <th>Satuan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $idx => $item): ?>
        <tr>
          <td class="text-secondary"><?= $idx + 1 ?></td>
          <td><code class="text-dark font-monospace"><?= esc($item['sku']) ?></code></td>
          <td class="fw-medium text-dark"><?= esc($item['product_name']) ?></td>
          <td class="text-end font-monospace text-secondary"><?= number_format((int) $item['so_qty'], 0, ',', '.') ?></td>
          <td class="text-end font-monospace fw-bold text-primary"><?= number_format((int) $item['qty_delivered'], 0, ',', '.') ?></td>
          <td class="text-secondary"><?= esc($item['unit'] ?? 'pcs') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($isActive && session()->get('user_role') === 'gudang'): ?>
<div class="modal modal-blur fade" id="modalCancelDO" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formCancelDO" action="/deliveries/cancel/<?= (int) $do['id'] ?>" method="post">
        <div class="modal-header">
          <h5 class="modal-title fw-bold text-danger">Batalkan Pengiriman (<?= esc($do['delivery_number']) ?>)</h5>
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
                    <div class="fw-bold text-danger">Batalkan Pengiriman &amp; Pesanan Jual (Cancel Total)</div>
                    <div class="text-secondary small">DO dan SO langsung berstatus <strong>Dibatalkan</strong>. Seluruh stok produk langsung dikembalikan ke gudang.</div>
                  </div>
                </div>
              </label>
            </div>
          </div>
          <div class="mb-0">
            <label class="form-label required">Alasan Pembatalan / Catatan untuk Sales</label>
            <textarea name="cancel_reason" class="form-control" rows="3" required placeholder="Contoh: Pelanggan minta ubah qty produk A dari 5 menjadi 8, atau pelanggan membatalkan pesanan"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Tutup</button>
          <button type="submit" class="btn btn-danger">Konfirmasi Pembatalan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
