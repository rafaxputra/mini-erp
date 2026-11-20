<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<form action="<?= $item ? '/products/update/' . esc($item['id']) : '/products/store' ?>" method="post">
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Kode Produk</label><input type="text" name="code" class="form-control" value="<?= esc(old('code', $item['code'] ?? '')) ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Nama Produk</label><input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" required></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Kategori</label><select name="category_id" class="form-select" required><?php foreach ($categories as $c): ?><option value="<?= esc($c['id']) ?>" <?= (string) old('category_id', $item['category_id'] ?? '') === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6 mb-3"><label class="form-label">Satuan</label><input type="text" name="unit" class="form-control" value="<?= esc(old('unit', $item['unit'] ?? 'pcs')) ?>" required></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Harga Beli</label><input type="number" step="0.01" min="0" name="purchase_price" class="form-control" value="<?= esc(old('purchase_price', $item['purchase_price'] ?? '')) ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Harga Jual</label><input type="number" step="0.01" min="0" name="selling_price" class="form-control" value="<?= esc(old('selling_price', $item['selling_price'] ?? '')) ?>" required></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Stok</label><input type="number" min="0" name="stock_qty" class="form-control" value="<?= esc(old('stock_qty', $item['stock_qty'] ?? 0)) ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Stok Minimum</label><input type="number" min="0" name="min_stock" class="form-control" value="<?= esc(old('min_stock', $item['min_stock'] ?? 0)) ?>" required></div>
  </div>
  <button type="submit" class="btn btn-primary">Simpan</button>
  <a class="btn btn-secondary" href="/products">Batal</a>
</form>
<?= $this->endSection() ?>
