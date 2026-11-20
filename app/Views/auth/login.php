<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Login Mini ERP</h5></div>
      <div class="card-body">
        <p class="text-muted">Masuk dengan akun PT Nusantara Distribusi.</p>
        <form action="/auth/login" method="post">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="6">
          </div>
          <button type="submit" class="btn btn-primary w-100">Masuk</button>
        </form>
        <p class="mt-3 mb-0 text-muted small">Admin: admin@nusantara.test / admin123<br>Purchasing: purchasing@nusantara.test / purchasing123<br>Gudang: gudang@nusantara.test / gudang123<br>Sales: sales@nusantara.test / sales123<br>Manajemen: manajemen@nusantara.test / manajemen123</p>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
