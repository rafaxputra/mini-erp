<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3">
        <h3 class="card-title m-0 fw-bold">Koreksi Stok Produk</h3>
      </div>
      <div class="card-body">
        <form action="/inventory/adjust" method="post">
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label required">Produk</label>
              <select name="product_id" class="form-select" required autofocus>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= esc($p['id']) ?>"><?= esc($p['code']) ?> - <?= esc($p['name']) ?> (stok <?= esc($p['stock_qty']) ?>)</option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Produk yang stoknya akan dikoreksi.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label required">Gudang</label>
              <select name="warehouse_id" class="form-select" required>
                <option value="">-- Pilih Gudang --</option>
                <?php foreach ($warehouses as $w): ?>
                  <option value="<?= esc($w['id']) ?>" <?= $w['is_default'] ? 'selected' : '' ?>><?= esc($w['name']) ?> (<?= esc($w['code']) ?>)</option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Koreksi berlaku untuk stok produk di gudang ini.</div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label required">Arah Koreksi</label>
              <select name="direction" class="form-select" required>
                <option value="IN">Masuk (tambah stok)</option>
                <option value="OUT">Keluar (kurangi stok)</option>
              </select>
              <div class="form-text">Pilih Masuk kalau stok bertambah, Keluar kalau stok berkurang.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label required">Jumlah Koreksi</label>
              <input type="number" name="qty" class="form-control" min="1" placeholder="0" required>
              <div class="form-text">Jumlah unit yang ditambah atau dikurangi.</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label required">Alasan Koreksi</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Selisih hitung fisik, barang rusak, dsb." required></textarea>
            <div class="form-text">Alasan wajib diisi supaya mutasi bisa dilacak.</div>
          </div>

          <div class="d-flex align-items-center gap-2 pt-2 border-top">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h8l4 4v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
              Simpan Koreksi Stok
            </button>
            <a class="btn btn-outline-secondary" href="/inventory">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
