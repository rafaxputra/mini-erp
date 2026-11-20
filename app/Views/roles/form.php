<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="card">
  <div class="card-header"><h3 class="card-title"><?= esc($title ?? 'Form') ?></h3></div>
  <div class="card-body">
<form action="<?= $item ? '/roles/update/' . esc($item['id']) : '/roles/store' ?>" method="post">
  <div class="mb-3"><label class="form-label">Nama Peran</label><input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" <?= $isCore ? 'disabled' : 'required' ?>><?php if ($isCore): ?><div class="form-text">Nama peran sistem tidak bisa diubah karena dipakai untuk pengaturan hak akses.</div><?php else: ?><div class="form-text">Huruf, angka, dan garis bawah saja, tanpa spasi, maks 20 karakter.</div><?php endif; ?></div>
  <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control"><?= esc(old('description', $item['description'] ?? '')) ?></textarea></div>
  <button type="submit" class="btn btn-primary">Simpan</button>
  <a class="btn btn-secondary" href="/roles">Batal</a>
</form>
  </div>
</div>
<?= $this->endSection() ?>
