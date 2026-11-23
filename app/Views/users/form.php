<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-6 col-md-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3">
        <h3 class="card-title m-0 fw-bold"><?= $item ? 'Ubah Pengguna' : 'Tambah Pengguna Baru' ?></h3>
      </div>
      <div class="card-body">
        <form action="<?= $item ? '/users/update/' . esc($item['id']) : '/users/store' ?>" method="post">
          <div class="mb-3">
            <label class="form-label required">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" placeholder="Nama karyawan / staf" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label required">Alamat Email</label>
            <input type="email" name="email" class="form-control" value="<?= esc(old('email', $item['email'] ?? '')) ?>" placeholder="nama@perusahaan.com" required>
            <div class="form-text">Digunakan sebagai username saat masuk ke sistem.</div>
          </div>
          <div class="mb-3">
            <label class="form-label <?= $item ? '' : 'required' ?>">
              Password<?= $item ? ' Baru (Kosongkan bila tidak ingin diganti)' : '' ?>
            </label>
            <input type="password" name="password" class="form-control" <?= $item ? '' : 'required' ?> minlength="6" placeholder="Minimal 6 karakter">
          </div>
          <div class="mb-3">
            <label class="form-label required">Peran Akses (Role)</label>
            <select name="role_id" class="form-select" required>
              <option value="">-- Pilih Peran --</option>
              <?php foreach ($roles as $role): ?>
                <option value="<?= esc($role['id']) ?>" <?= (int) old('role_id', $item['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : '' ?>>
                  <?= esc(ucfirst($role['name'])) ?> - <?= esc($role['description'] ?: 'Akses modul') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if ($item): ?>
            <div class="mb-3">
              <label class="form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" <?= old('is_active', $item['is_active'] ? '1' : '0') === '1' ? 'checked' : '' ?>>
                <span class="form-check-label fw-medium">Status Akun Aktif</span>
              </label>
              <div class="form-hint">Jika dimatikan, pengguna ini tidak akan dapat login ke aplikasi.</div>
            </div>
          <?php endif; ?>
          <div class="d-flex align-items-center gap-2 pt-2 border-top">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
              Simpan Pengguna
            </button>
            <a class="btn btn-outline-secondary" href="/users">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
