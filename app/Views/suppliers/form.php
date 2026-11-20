<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h4 class="mb-3"><?= esc($title) ?></h4>
<form action="<?= $item ? '/suppliers/update/' . esc($item['id']) : '/suppliers/store' ?>" method="post">
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Kode</label><input type="text" name="code" class="form-control" value="<?= esc(old('code', $item['code'] ?? '')) ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" required></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $item['phone'] ?? '')) ?>"></div>
    <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= esc(old('email', $item['email'] ?? '')) ?>"></div>
  </div>
  <div class="mb-3"><label class="form-label">Alamat</label><textarea name="address" class="form-control"><?= esc(old('address', $item['address'] ?? '')) ?></textarea></div>
  <button type="submit" class="btn btn-primary">Simpan</button>
  <a class="btn btn-secondary" href="/suppliers">Batal</a>
</form>
<?= $this->endSection() ?>
