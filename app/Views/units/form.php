<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3">
        <h3 class="card-title m-0 fw-bold"><?= $item ? 'Ubah Satuan' : 'Tambah Satuan Baru' ?></h3>
      </div>
      <div class="card-body">
        <form action="<?= $item ? '/units/update/' . esc($item['id']) : '/units/store' ?>" method="post">
          <div class="row g-3">
            <div class="col-sm-5">
              <label class="form-label required">Kode Satuan</label>
              <input type="text" name="code" class="form-control font-monospace" value="<?= esc(old('code', $item['code'] ?? '')) ?>" placeholder="Contoh: pcs, rim, box" required autofocus>
              <div class="form-text">Singkatan satuan (huruf kecil).</div>
            </div>
            <div class="col-sm-7">
              <label class="form-label required">Nama Satuan</label>
              <input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" placeholder="Contoh: Pieces (Buah)" required>
              <div class="form-text">Label lengkap nama satuan.</div>
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Keterangan singkat satuan barang ini"><?= esc(old('description', $item['description'] ?? '')) ?></textarea>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2 pt-3 mt-3 border-top">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
              Simpan Data
            </button>
            <a class="btn btn-outline-secondary" href="/units">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
