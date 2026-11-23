<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3">
        <h3 class="card-title m-0 fw-bold"><?= $item ? 'Ubah Gudang' : 'Tambah Gudang Baru' ?></h3>
      </div>
      <div class="card-body">
        <form action="<?= $item ? '/warehouses/update/' . esc($item['id']) : '/warehouses/store' ?>" method="post">
          <div class="mb-3">
            <label class="form-label required">Kode Gudang</label>
            <?php if (!empty($hasTransactions)): ?>
              <input type="text" name="code" class="form-control font-monospace bg-light fw-bold" value="<?= esc(old('code', $item['code'] ?? '')) ?>" readonly>
              <div class="form-text text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-secondary" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/></svg>
                Kode gudang tidak dapat diubah karena telah memiliki riwayat mutasi stok fisik atau transaksi.
              </div>
            <?php else: ?>
              <input type="text" name="code" class="form-control font-monospace" value="<?= esc(old('code', $item['code'] ?? '')) ?>" placeholder="Contoh: GUD-02" required autofocus>
              <div class="form-text">Kode unik identifikasi gudang.</div>
            <?php endif; ?>
          </div>
          <div class="mb-3">
            <label class="form-label required">Nama Gudang</label>
            <input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" placeholder="Contoh: Gudang Transit Barat" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Lokasi / Alamat</label>
            <input type="text" name="location" class="form-control" value="<?= esc(old('location', $item['location'] ?? '')) ?>" placeholder="Contoh: Sidoarjo, Jawa Timur">
          </div>
          <div class="mb-3">
            <label class="form-check">
              <input class="form-check-input" type="checkbox" name="is_default" value="1" <?= old('is_default', $item['is_default'] ?? false) ? 'checked' : '' ?>>
              <span class="form-check-label fw-medium">Jadikan sebagai Gudang Utama</span>
            </label>
            <div class="form-hint">Gudang utama digunakan secara default pada transaksi. Hanya satu gudang yang dapat menjadi utama.</div>
          </div>
          <div class="d-flex align-items-center gap-2 pt-2 border-top">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
              Simpan Data
            </button>
            <a class="btn btn-outline-secondary" href="/warehouses">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
