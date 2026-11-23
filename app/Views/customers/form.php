<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<form action="<?= $item ? '/customers/update/' . esc($item['id']) : '/customers/store' ?>" method="post">
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Kode</label><input type="text" name="code" class="form-control" value="<?= esc(old('code', $item['code'] ?? '')) ?>" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" required></div>
  </div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $item['phone'] ?? '')) ?>"></div>
    <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= esc(old('email', $item['email'] ?? '')) ?>"></div>
  </div>
  <div class="mb-3"><label class="form-label">Alamat</label><textarea name="address" class="form-control"><?= esc(old('address', $item['address'] ?? '')) ?></textarea></div>
  <div class="mt-2">
    <button type="submit" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
      Simpan
    </button>
    <a class="btn btn-secondary" href="/customers">Batal</a>
  </div>
</form>
<?= $this->endSection() ?>
