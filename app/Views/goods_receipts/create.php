<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php if (! $po): ?>
  <!-- Form Pemilihan PO -->
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent py-3">
          <h3 class="card-title m-0 fw-bold">Pilih Pesanan Beli (PO) yang Akan Diterima</h3>
        </div>
        <?php if (empty($pos)): ?>
          <div class="card-body p-4 text-center py-5">
            <div class="text-secondary mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-info" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
            </div>
            <div class="h4 mb-1">Tidak Ada Pesanan Beli yang Menunggu Penerimaan</div>
            <div class="text-secondary small mb-3">Semua pesanan sudah diterima lengkap, atau belum ada pesanan berstatus <strong>Disetujui</strong> oleh Admin.</div>
            <a href="/purchase-orders" class="btn btn-outline-primary">Lihat Daftar Pesanan Beli</a>
          </div>
        <?php else: ?>
          <div class="card-body p-4">
            <form action="/goods-receipts/create" method="get">
              <div class="mb-3">
                <label class="form-label required">Nomor Pesanan Beli (Status Disetujui)</label>
                <select name="purchase_order_id" class="form-select" required>
                  <option value="">-- Pilih Pesanan Beli --</option>
                  <?php foreach ($pos as $p): ?>
                    <option value="<?= esc($p['id']) ?>">
                      <?= esc($p['po_number']) ?> - Tanggal: <?= esc($p['order_date']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="form-text text-secondary mt-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1 text-info" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9h.01"/><path d="M11 12h1v4h1"/><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"/></svg>
                  Hanya Pesanan Beli dengan status <strong>Disetujui</strong> yang dapat diproses penerimaan barangnya di gudang.
                </div>
              </div>
              <div class="d-flex justify-content-end gap-2">
                <a href="/goods-receipts" class="btn btn-secondary">Batal</a>
                <button class="btn btn-primary" type="submit">
                  Lanjutkan Penerimaan
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0"/><path d="M13 18l6 -6"/><path d="M13 6l6 6"/></svg>
                </button>
              </div>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php else: ?>
  <!-- Form Pencatatan Penerimaan Barang -->
  <form action="/goods-receipts/store" method="post">
    <input type="hidden" name="purchase_order_id" value="<?= esc($po['id']) ?>">

    <!-- Kartu Informasi PO -->
    <div class="card mb-3 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 border-bottom mb-3">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark-lt fs-3 px-2 py-1 font-monospace"><?= esc($po['po_number']) ?></span>
            <span class="badge bg-success-lt fs-4 px-2 py-1">Disetujui</span>
          </div>
          <a href="/goods-receipts/create" class="btn btn-sm btn-outline-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/></svg>
            Ganti Pesanan Beli
          </a>
        </div>

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label required">Tanggal Diterima</label>
            <input type="date" name="receipt_date" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= esc($po['order_date']) ?>" required>
            <div class="form-text">Minimal sama dengan tanggal PO (<?= esc($po['order_date']) ?>).</div>
          </div>
          <div class="col-md-4">
            <label class="form-label required">Gudang Penyimpanan</label>
            <select name="warehouse_id" class="form-select" required>
              <?php foreach ($warehouses as $w): ?>
                <option value="<?= esc($w['id']) ?>" <?= (int) $po['warehouse_id'] === (int) $w['id'] ? 'selected' : '' ?>>
                  <?= esc($w['name']) ?> (<?= esc($w['code']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Catatan Penerimaan</label>
            <input type="text" name="notes" class="form-control" placeholder="Kondisi barang, nomor surat jalan, dll.">
          </div>
        </div>
      </div>
    </div>

    <!-- Kartu Detail Barang Masuk -->
    <div class="card mb-3 border-0 shadow-sm">
      <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0 fw-bold">Konfirmasi Jumlah Barang yang Diterima Fisik</h3>
        <button type="button" class="btn btn-sm btn-outline-info" onclick="fillAllRemaining()">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
          Terima Semua Sisa
        </button>
      </div>
      <div class="table-responsive">
        <table class="table table-vcenter table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 120px;">Kode</th>
              <th>Nama Produk</th>
              <th class="text-center" style="width: 140px;">Jumlah Pesan</th>
              <th class="text-center" style="width: 140px;">Sudah Diterima</th>
              <th class="text-center" style="width: 130px;">Sisa Menunggu</th>
              <th style="width: 200px;">Jumlah Diterima Kali Ini</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): $sisa = (int) $it['qty'] - (int) $it['received_qty']; ?>
            <tr>
              <td><code><?= esc($it['code']) ?></code></td>
              <td class="fw-medium"><?= esc($it['product_name']) ?></td>
              <td class="text-center"><?= esc($it['qty']) ?></td>
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
                  <span class="badge bg-warning-lt fw-bold"><?= esc($sisa) ?></span>
                <?php endif; ?>
              </td>
              <td>
                <div class="input-group">
                  <input type="number" name="qty_received[<?= esc($it['id']) ?>]" class="form-control text-center received-input" min="0" max="<?= esc($sisa) ?>" value="<?= $sisa > 0 ? $sisa : 0 ?>" data-max="<?= esc($sisa) ?>" <?= $sisa === 0 ? 'readonly' : '' ?>>
                  <span class="input-group-text small text-secondary">unit</span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="submit" class="btn btn-primary px-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h8l4 4v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
        Simpan Penerimaan Barang (GR)
      </button>
      <a class="btn btn-secondary" href="/goods-receipts">Batal</a>
    </div>
  </form>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function fillAllRemaining() {
  document.querySelectorAll('.received-input').forEach(function (inp) {
    if (!inp.readOnly) {
      inp.value = inp.dataset.max || 0;
    }
  });
}
</script>
<?= $this->endSection() ?>
