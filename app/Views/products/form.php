<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3">
        <h3 class="card-title m-0 fw-bold"><?= $item ? 'Ubah Data Produk' : 'Tambah Produk Baru' ?></h3>
      </div>
      <div class="card-body">
        <form action="<?= $item ? '/products/update/' . esc($item['id']) : '/products/store' ?>" method="post">
          <div class="row g-3 mb-3">
            <div class="col-md-5">
              <label class="form-label required">Kode Produk (SKU)</label>
              <?php if (!empty($hasTransactions)): ?>
                <input type="text" name="code" class="form-control font-monospace bg-light fw-bold" value="<?= esc(old('code', $item['code'] ?? '')) ?>" readonly>
                <div class="form-text text-muted">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon text-secondary" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/></svg>
                  Kode produk tidak dapat diubah karena telah memiliki riwayat transaksi/mutasi gudang.
                </div>
              <?php else: ?>
                <input type="text" name="code" class="form-control font-monospace" placeholder="Contoh: PRD-001" value="<?= esc(old('code', $item['code'] ?? '')) ?>" required autofocus>
                <div class="form-text">Kode unik internal produk.</div>
              <?php endif; ?>
            </div>
            <div class="col-md-7">
              <label class="form-label required">Nama Produk</label>
              <input type="text" name="name" class="form-control" placeholder="Nama produk lengkap dan spesifikasi" value="<?= esc(old('name', $item['name'] ?? '')) ?>" required>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label required">Kategori</label>
              <select name="category_id" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $c): ?>
                  <option value="<?= esc($c['id']) ?>" <?= (string) old('category_id', $item['category_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>>
                    <?= esc($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label required">Satuan</label>
              <?php if (!empty($hasTransactions)): ?>
                <?php 
                  $currentUnitText = $item['unit'] ?? 'unit';
                  foreach ($units as $u) {
                    if ((string)$u['id'] === (string)($item['unit_id'] ?? '') || strtolower($u['code']) === strtolower($item['unit'] ?? '')) {
                      $currentUnitText = $u['name'];
                      break;
                    }
                  }
                ?>
                <input type="hidden" name="unit_id" value="<?= esc($item['unit_id']) ?>">
                <input type="text" class="form-control bg-light fw-bold text-dark" value="<?= esc($currentUnitText) ?>" readonly>
                <div class="form-text text-muted">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon text-secondary" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/></svg>
                  Satuan tidak dapat diubah karena produk telah memiliki riwayat transaksi/mutasi gudang.
                </div>
              <?php else: ?>
                <select name="unit_id" class="form-select" id="unitSelect" required>
                  <option value="">-- Pilih Satuan --</option>
                  <?php foreach ($units as $u): ?>
                    <option value="<?= esc($u['id']) ?>" data-code="<?= esc($u['code']) ?>" <?= (string) old('unit_id', $item['unit_id'] ?? '') === (string) $u['id'] || (empty($item['unit_id']) && strtolower($item['unit'] ?? '') === strtolower($u['code'])) ? 'selected' : '' ?>>
                      <?= esc($u['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="form-text">Satuan fisik produk (kelola di menu Data Master &rarr; Satuan).</div>
              <?php endif; ?>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label required">Harga Beli (Modal)</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" inputmode="numeric" name="purchase_price" class="form-control font-monospace js-rupiah" value="<?= esc(old('purchase_price', isset($item['purchase_price']) ? (int) $item['purchase_price'] : '')) ?>" placeholder="0" required>
              </div>
              <div class="form-text">Biaya modal beli per satuan barang.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label required">Harga Jual</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" inputmode="numeric" name="selling_price" class="form-control font-monospace js-rupiah" value="<?= esc(old('selling_price', isset($item['selling_price']) ? (int) $item['selling_price'] : '')) ?>" placeholder="0" required>
              </div>
              <div class="form-text">Harga standar jual ke pelanggan.</div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-12">
              <?php if (isset($item)): ?>
                <label class="form-label">Stok Saat Ini (Terkonsolidasi Seluruh Gudang)</label>
                <input type="text" class="form-control bg-light font-monospace fw-bold text-dark" value="<?= esc($item['stock_qty']) ?> <?= esc($item['unit'] ?? 'unit') ?>" readonly>
                <div class="form-text text-muted">Stok fisik adalah hasil akumulasi transaksi riil dan tidak dapat diedit langsung di master produk.</div>
              <?php else: ?>
                <label class="form-label required">Stok Awal Fisik</label>
                <input type="number" min="0" name="stock_qty" class="form-control" value="<?= esc(old('stock_qty', 0)) ?>" required>
                <div class="form-text">Saldo awal fisik saat pendaftaran barang (akan otomatis dialokasikan ke Gudang Utama dan tercatat di riwayat mutasi).</div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label required">Batas Stok Minimum (Reorder Point)</label>
              <input type="number" min="0" name="min_stock" class="form-control" value="<?= esc(old('min_stock', $item['min_stock'] ?? 5)) ?>" required>
              <div class="form-text">Peringatan otomatis muncul bila stok mencapai atau di bawah angka ini.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Batas Stok Maksimum (Kapasitas Simpan)</label>
              <input type="number" min="0" name="max_stock" class="form-control" value="<?= esc(old('max_stock', $item['max_stock'] ?? 0)) ?>" placeholder="0 = Tanpa batas">
              <div class="form-text">Batas maksimal kapasitas gudang. Saat membuat PO, pemesanan dicegah jika melampaui batas ini (0 = tidak dibatasi).</div>
            </div>
          </div>

          <div class="d-flex align-items-center gap-2 pt-2 border-top">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
              Simpan Data Produk
            </button>
            <a class="btn btn-outline-secondary" href="/products">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
