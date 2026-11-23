<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php $st = status_badge($po['status']); ?>

<!-- Kartu Ringkasan Informasi Pesanan -->
<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 border-bottom mb-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-dark-lt fs-3 px-2 py-1 font-monospace"><?= esc($po['po_number']) ?></span>
          <span class="badge <?= $st['badge'] ?> fs-4 px-2 py-1"><?= esc($st['label']) ?></span>
        </div>
        <div class="text-secondary small">Dibuat oleh: <strong class="text-dark"><?= esc($po['creator_name']) ?></strong></div>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <?php if ($po['status'] === 'draft' && session()->get('user_role') === 'admin'): ?>
          <form action="/purchase-orders/approve/<?= esc($po['id']) ?>" method="post" class="d-inline" data-confirm="Apakah Anda yakin ingin menyetujui Pesanan Beli <strong><?= esc($po['po_number']) ?></strong>?" data-confirm-title="Persetujuan Pesanan Beli" data-confirm-btn="Ya, Setujui" data-confirm-type="primary">
            <button class="btn btn-success" type="submit">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
              Setujui Pesanan
            </button>
          </form>
        <?php endif; ?>

        <?php if ($po['status'] === 'approved' && in_array(session()->get('user_role'), ['admin', 'gudang'], true)): ?>
          <a class="btn btn-primary" href="/goods-receipts/create?purchase_order_id=<?= esc($po['id']) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
            Proses Penerimaan Barang
          </a>
        <?php endif; ?>

        <?php if (($po['status'] === 'draft' || $po['status'] === 'approved') && in_array(session()->get('user_role'), ['admin', 'purchasing'], true)): ?>
          <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelPO">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
            Batalkan Pesanan
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Banner Peringatan Jika Pesanan Dibatalkan -->
    <?php if ($po['status'] === 'cancelled'): ?>
      <div class="alert alert-danger mb-3 d-flex align-items-start gap-2" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg>
        <div>
          <div class="fw-bold">Pesanan Beli Ini Telah Dibatalkan</div>
          <div class="small mt-1 text-danger-emphasis" style="white-space: pre-line;"><?= esc($po['notes'] ?: 'Tidak ada catatan alasan pembatalan.') ?></div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Grid Detail Informasi -->
    <div class="row g-3">
      <div class="col-sm-6 col-lg-3">
        <div class="text-secondary small">Pemasok</div>
        <div class="fw-bold text-dark mt-1"><?= esc($po['supplier_name']) ?></div>
        <div class="text-secondary small font-monospace"><?= esc($po['supplier_code']) ?></div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="text-secondary small">Tanggal Pesanan</div>
        <div class="fw-bold text-dark mt-1"><?= date('d M Y', strtotime($po['order_date'])) ?></div>
        <div class="text-secondary small font-monospace"><?= esc($po['order_date']) ?></div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="text-secondary small">Gudang Tujuan</div>
        <div class="fw-bold text-dark mt-1"><?= esc($po['warehouse_name'] ?? 'Gudang Utama') ?></div>
        <div class="text-secondary small">Penyimpanan fisik stok</div>
      </div>
      <div class="col-sm-6 col-lg-3">
        <div class="text-secondary small">Total Nilai Pesanan</div>
        <div class="fw-bold fs-2 text-primary mt-1">Rp <?= number_format((float) $po['total_amount'], 0, ',', '.') ?></div>
      </div>
    </div>

    <?php if (! empty($po['notes'])): ?>
    <div class="mt-3 p-2 px-3 rounded bg-light border text-secondary small">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1 text-secondary" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 21v-13a3 3 0 0 1 3 -3h10a3 3 0 0 1 3 3v6a3 3 0 0 1 -3 3h-9l-4 4"/></svg>
      <strong>Catatan:</strong> <?= esc($po['notes']) ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Kartu Daftar Produk Pesanan -->
<div class="card mb-3 border-0 shadow-sm">
  <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
    <h3 class="card-title m-0 fw-bold">Detail Produk yang Dipesan</h3>
    <span class="badge bg-secondary-lt"><?= count($items) ?> item</span>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter table-striped mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">No</th>
          <th>Kode</th>
          <th>Produk</th>
          <th class="text-center" style="width: 130px;">Jumlah Pesan</th>
          <th class="text-center" style="width: 130px;">Sudah Diterima</th>
          <th class="text-center" style="width: 100px;">Sisa</th>
          <th class="text-end" style="width: 160px;">Harga Satuan</th>
          <th class="text-end" style="width: 170px;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $idx => $it): ?>
        <?php $sisa = (int) $it['qty'] - (int) $it['received_qty']; ?>
        <tr>
          <td class="text-secondary"><?= $idx + 1 ?></td>
          <td><code class="text-dark"><?= esc($it['code']) ?></code></td>
          <td>
            <div class="fw-bold"><?= esc($it['product_name']) ?></div>
            <div class="text-secondary small">Satuan: <?= esc($it['unit']) ?></div>
          </td>
          <td class="text-center fw-medium"><?= esc($it['qty']) ?></td>
          <td class="text-center">
            <?php if ((int) $it['received_qty'] > 0): ?>
              <span class="badge bg-green-lt fw-bold"><?= esc($it['received_qty']) ?></span>
            <?php else: ?>
              <span class="text-secondary">0</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?php if ($sisa === 0): ?>
              <span class="badge bg-success-lt">Lengkap</span>
            <?php else: ?>
              <span class="badge bg-warning-lt"><?= esc($sisa) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-end">Rp <?= number_format((float) $it['unit_price'], 0, ',', '.') ?></td>
          <td class="text-end fw-bold text-dark">Rp <?= number_format((float) $it['subtotal'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="7" class="text-end fw-bold fs-3">Total Keseluruhan:</td>
          <td class="text-end fw-bold fs-3 text-primary">Rp <?= number_format((float) $po['total_amount'], 0, ',', '.') ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Riwayat Penerimaan Barang (Goods Receipt) -->
<?php if (! empty($receipts)): ?>
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Riwayat Penerimaan Barang Masuk (Goods Receipt)</h3>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 250px;">Nomor Penerimaan (GR)</th>
          <th>Tanggal Penerimaan</th>
          <th>Status Dokumen</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($receipts as $r): ?>
        <tr>
          <td>
            <span class="badge bg-primary-lt font-monospace px-2 py-1 fs-4"><?= esc($r['gr_number']) ?></span>
          </td>
          <td>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm text-secondary me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/></svg>
            <?= date('d M Y', strtotime($r['receipt_date'])) ?>
          </td>
          <td>
            <span class="badge bg-success-lt">Tercatat di Gudang</span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Modal Konfirmasi & Catatan Pembatalan Pesanan Beli -->
<div class="modal modal-blur fade" id="modalCancelPO" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form action="/purchase-orders/cancel/<?= esc($po['id']) ?>" method="post">
        <div class="modal-header">
          <h5 class="modal-title text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
            Konfirmasi Pembatalan Pesanan
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-secondary mb-3">
            Apakah Anda yakin ingin membatalkan pesanan beli <strong><?= esc($po['po_number']) ?></strong>? Tindakan ini akan menghentikan proses pengadaan dan tidak dapat diubah kembali.
          </p>
          <div class="mb-3">
            <label class="form-label required">Alasan Pembatalan</label>
            <textarea name="cancel_reason" class="form-control" rows="3" placeholder="Sebutkan alasan pembatalan (misal: Supplier kehabisan stok barang, harga tidak sesuai, dsb.)" required></textarea>
            <div class="form-text">Alasan pembatalan dan nama staf akan tersimpan secara permanen pada catatan pesanan.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button>
          <button type="submit" class="btn btn-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
            Ya, Batalkan Pesanan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
