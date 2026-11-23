<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-7 col-md-9">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3">
        <h3 class="card-title m-0 fw-bold"><?= $item ? 'Ubah Pemasok' : 'Tambah Pemasok Baru' ?></h3>
      </div>
      <div class="card-body">
        <form action="<?= $item ? '/suppliers/update/' . esc($item['id']) : '/suppliers/store' ?>" method="post">
          <div class="row g-3 mb-3">
            <div class="col-md-5">
              <label class="form-label required">Kode Pemasok</label>
              <input type="text" name="code" class="form-control font-monospace" value="<?= esc(old('code', $item['code'] ?? '')) ?>" placeholder="Contoh: SUP-001" required autofocus>
              <div class="form-text">Kode unik pemasok.</div>
            </div>
            <div class="col-md-7">
              <label class="form-label required">Nama Pemasok / Distributor</label>
              <input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" placeholder="Contoh: PT Sinar Jaya Perkasa" required>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Nomor Telepon / PIC</label>
              <input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $item['phone'] ?? '')) ?>" placeholder="Contoh: 021-5551234">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= esc(old('email', $item['email'] ?? '')) ?>" placeholder="sales@pemasok.com">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat Kantor / Pergudangan</label>
            <textarea name="address" class="form-control" rows="3" placeholder="Alamat lengkap pemasok"><?= esc(old('address', $item['address'] ?? '')) ?></textarea>
          </div>
          <div class="d-flex align-items-center gap-2 pt-2 border-top">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
              Simpan Data
            </button>
            <a class="btn btn-outline-secondary" href="/suppliers">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
