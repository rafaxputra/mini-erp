<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<form action="<?= $item ? '/categories/update/' . esc($item['id']) : '/categories/store' ?>" method="post">
  <div class="mb-3"><label class="form-label">Nama Kategori</label><input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" required></div>
  <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control"><?= esc(old('description', $item['description'] ?? '')) ?></textarea></div>
  <button type="submit" class="btn btn-primary">Simpan</button>
  <a class="btn btn-secondary" href="/categories">Batal</a>
</form>
<?= $this->endSection() ?>
