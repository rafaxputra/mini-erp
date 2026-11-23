<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<form action="<?= $item ? '/products/update/' . esc($item['id']) : '/products/store' ?>" method="post">
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Kode Produk</label><input type="text" name="code" class="form-control" placeholder="Contoh: PRD-001" value="<?= esc(old('code', $item['code'] ?? '')) ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Nama Produk</label><input type="text" name="name" class="form-control" placeholder="Nama produk lengkap" value="<?= esc(old('name', $item['name'] ?? '')) ?>" required></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Kategori</label><select name="category_id" class="form-select" required><?php foreach ($categories as $c): ?><option value="<?= esc($c['id']) ?>" <?= (string) old('category_id', $item['category_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6 mb-3"><label class="form-label">Satuan</label><input type="text" name="unit" class="form-control" placeholder="pcs, box, kg..." value="<?= esc(old('unit', $item['unit'] ?? 'pcs')) ?>" required></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Harga Beli</label>
      <div class="input-group">
        <span class="input-group-text">Rp</span>
        <input type="text" inputmode="numeric" name="purchase_price" class="form-control js-rupiah" value="<?= esc(old('purchase_price', isset($item['purchase_price']) ? (int) $item['purchase_price'] : '')) ?>" placeholder="0" required>
      </div>
      <div class="form-text">Biaya modal beli per satuan barang.</div>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Harga Jual</label>
      <div class="input-group">
        <span class="input-group-text">Rp</span>
        <input type="text" inputmode="numeric" name="selling_price" class="form-control js-rupiah" value="<?= esc(old('selling_price', isset($item['selling_price']) ? (int) $item['selling_price'] : '')) ?>" placeholder="0" required>
      </div>
      <div class="form-text">Harga patokan jual ke pelanggan.</div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Stok</label><input type="number" min="0" name="stock_qty" class="form-control" value="<?= esc(old('stock_qty', $item['stock_qty'] ?? 0)) ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Stok Minimum</label><input type="number" min="0" name="min_stock" class="form-control" value="<?= esc(old('min_stock', $item['min_stock'] ?? 0)) ?>" required><div class="form-text">Batas peringatan ketika stok mulai menipis.</div></div>
  </div>
  <div class="mt-2">
    <button type="submit" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
      Simpan
    </button>
    <a class="btn btn-secondary" href="/products">Batal</a>
  </div>
</form>
<?= $this->endSection() ?>
