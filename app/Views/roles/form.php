<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<form action="<?= $item ? '/roles/update/' . esc($item['id']) : '/roles/store' ?>" method="post">
  <div class="mb-3">
    <label class="form-label">Nama Peran</label>
    <input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" <?= $isCore ? 'disabled' : 'required' ?>>
    <?php if ($isCore): ?>
      <div class="form-text">Nama peran sistem tidak bisa diubah karena dipakai untuk pengaturan hak akses.</div>
    <?php else: ?>
      <div class="form-text">Huruf, angka, dan garis bawah saja, tanpa spasi, maks 20 karakter.</div>
    <?php endif; ?>
  </div>
  <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="description" class="form-control" placeholder="Deskripsi peran dan tanggung jawab"><?= esc(old('description', $item['description'] ?? '')) ?></textarea></div>
  <div class="mt-2">
    <button type="submit" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
      Simpan
    </button>
    <a class="btn btn-secondary" href="/roles">Batal</a>
  </div>
</form>
<?= $this->endSection() ?>
