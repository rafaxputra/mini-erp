<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3">
        <h3 class="card-title m-0 fw-bold"><?= $item ? 'Ubah Peran' : 'Tambah Peran Baru' ?></h3>
      </div>
      <div class="card-body">
        <form action="<?= $item ? '/roles/update/' . esc($item['id']) : '/roles/store' ?>" method="post">
          <div class="mb-3">
            <label class="form-label required">Nama Peran (Kode Identifier)</label>
            <input type="text" name="name" class="form-control font-monospace" value="<?= esc(old('name', $item['name'] ?? '')) ?>" <?= $isCore ? 'readonly' : 'required autofocus' ?> placeholder="Contoh: supervisor">
            <?php if ($isCore): ?>
              <div class="form-text text-muted">Peran sistem ini tidak dapat diganti namanya demi integritas hak akses sistem.</div>
            <?php else: ?>
              <div class="form-text">Hanya gunakan huruf kecil, angka, atau garis bawah tanpa spasi (maksimal 20 karakter).</div>
            <?php endif; ?>
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi Tanggung Jawab</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi tugas dan hak wewenang peran ini"><?= esc(old('description', $item['description'] ?? '')) ?></textarea>
          </div>
          <div class="d-flex align-items-center gap-2 pt-2 border-top">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
              Simpan Data
            </button>
            <a class="btn btn-outline-secondary" href="/roles">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
