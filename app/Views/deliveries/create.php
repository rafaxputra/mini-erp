<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php if (! $so): ?>
  <!-- Form Pemilihan SO -->
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent py-3">
          <h3 class="card-title m-0 fw-bold">Pilih Pesanan Jual (SO) yang Akan Dikirim</h3>
        </div>
        <?php if (empty($sos)): ?>
          <div class="card-body p-4 text-center py-5">
            <div class="text-secondary mb-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-info" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
            </div>
            <div class="h4 mb-1">Tidak Ada Pesanan Jual yang Menunggu Pengiriman</div>
            <div class="text-secondary small mb-3">Semua pesanan sudah dikirim lengkap, atau belum ada pesanan berstatus <strong>Dikonfirmasi</strong> oleh Sales.</div>
            <a href="/sales-orders" class="btn btn-outline-primary">Lihat Daftar Pesanan Jual</a>
          </div>
        <?php else: ?>
          <div class="card-body p-4">
          <form action="/deliveries/create" method="get">
            <div class="mb-3">
              <label class="form-label required">Nomor Pesanan Jual (Status Dikonfirmasi)</label>
              <select name="sales_order_id" class="form-select" required>
                <option value="">-- Pilih Pesanan Jual --</option>
                <?php foreach ($sos as $s): ?>
                  <option value="<?= esc($s['id']) ?>">
                    <?= esc($s['so_number']) ?> - Tanggal: <?= esc($s['order_date']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text text-secondary mt-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1 text-info" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9h.01"/><path d="M11 12h1v4h1"/><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"/></svg>
                Hanya Pesanan Jual dengan status <strong>Dikonfirmasi</strong> yang dapat diproses pengiriman barangnya dari gudang.
              </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
              <a href="/deliveries" class="btn btn-secondary">Batal</a>
              <button class="btn btn-primary" type="submit">
                Lanjutkan Pengiriman
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
  <!-- Form Pencatatan Pengiriman Barang -->
  <form action="/deliveries/store" method="post">
    <input type="hidden" name="sales_order_id" value="<?= esc($so['id']) ?>">

    <!-- Kartu Informasi SO -->
    <div class="card mb-3 border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 border-bottom mb-3">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark-lt fs-3 px-2 py-1 font-monospace"><?= esc($so['so_number']) ?></span>
            <span class="badge bg-azure-lt fs-4 px-2 py-1">Dikonfirmasi</span>
          </div>
          <a href="/deliveries/create" class="btn btn-sm btn-outline-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/></svg>
            Ganti Pesanan Jual
          </a>
        </div>

        <div class="row g-3">
          <div class="col-sm-6 col-lg-3">
            <div class="text-secondary small">Pelanggan</div>
            <div class="fw-bold text-dark mt-1"><?= esc($so['customer_name']) ?></div>
            <div class="text-secondary small font-monospace"><?= esc($so['customer_code']) ?></div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="text-secondary small">Tanggal Pesanan</div>
            <div class="fw-bold text-dark mt-1"><?= date('d M Y', strtotime($so['order_date'])) ?></div>
            <div class="text-secondary small font-monospace"><?= esc($so['order_date']) ?></div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="text-secondary small">Gudang Pengirim</div>
            <div class="fw-bold text-dark mt-1"><?= esc($so['warehouse_name'] ?? 'Gudang Utama') ?></div>
            <div class="text-secondary small">Stok diperiksa di gudang ini</div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="text-secondary small">Status</div>
            <div class="fw-bold text-dark mt-1"><?= esc(status_label($so['status'])) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Kartu Detail Pengiriman -->
    <div class="card mb-3 border-0 shadow-sm">
      <div class="card-header bg-transparent py-3">
        <h3 class="card-title m-0 fw-bold">Detail Pengiriman</h3>
      </div>
      <div class="card-body">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label required">Tanggal Kirim</label>
            <input type="date" name="delivery_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label required">Gudang Pengirim</label>
            <select name="warehouse_id" class="form-select" required>
              <?php foreach ($warehouses as $w): ?>
                <option value="<?= esc($w['id']) ?>" <?= (int) $so['warehouse_id'] === (int) $w['id'] ? 'selected' : '' ?>><?= esc($w['name']) ?> (<?= esc($w['code']) ?>)</option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Barang dikirim dari gudang ini, stok diperiksa di gudang ini.</div>
          </div>
          <div class="col-12">
            <label class="form-label">Catatan Pengiriman</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Dikirim via kurir, resi menyusul"></textarea>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-vcenter table-bordered mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 120px;">Kode</th>
                <th>Produk</th>
                <th class="text-center" style="width: 110px;">Jumlah Pesan</th>
                <th class="text-center" style="width: 120px;">Sudah Dikirim</th>
                <th class="text-center" style="width: 100px;">Sisa Kirim</th>
                <th class="text-center" style="width: 110px;">Stok Saat Ini</th>
                <th class="text-center" style="width: 130px;">Jumlah Dikirim</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $it): $sisa = (int) $it['qty'] - (int) $it['delivered_qty']; ?>
              <tr>
                <td><code class="text-dark"><?= esc($it['code']) ?></code></td>
                <td class="fw-semibold text-dark"><?= esc($it['product_name']) ?></td>
                <td class="text-center"><?= esc($it['qty']) ?></td>
                <td class="text-center"><?= esc($it['delivered_qty']) ?></td>
                <td class="text-center">
                  <?php if ($sisa === 0): ?>
                    <span class="badge bg-success-lt">Lengkap</span>
                  <?php else: ?>
                    <span class="badge bg-warning-lt"><?= esc($sisa) ?></span>
                  <?php endif; ?>
                </td>
                <td class="text-center"><?= esc($it['stock_qty']) ?></td>
                <td><input type="number" name="qty_delivered[<?= esc($it['id']) ?>]" class="form-control text-center" min="0" max="<?= esc($sisa) ?>" value="0"></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-transparent d-flex align-items-center gap-2 pt-2 border-top">
        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h8l4 4v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
          Simpan Pengiriman
        </button>
        <a class="btn btn-outline-secondary" href="/deliveries/create">Ganti SO</a>
      </div>
    </div>
  </form>
<?php endif; ?>

<?= $this->endSection() ?>
