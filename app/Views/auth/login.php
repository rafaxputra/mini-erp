<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h2 class="card-title text-center mb-1">Masuk ke Mini ERP</h2>
        <p class="text-secondary text-center mb-4">Gunakan akun PT Nusantara Distribusi Anda.</p>
        <form action="/auth/login" method="post" id="loginForm">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="loginEmail" class="form-control" value="<?= esc(old('email')) ?>" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group input-group-flat">
              <input type="password" name="password" id="loginPassword" class="form-control" required minlength="6">
              <span class="input-group-text">
                <a href="#" class="link-secondary" id="togglePassword" title="Tampilkan password" tabindex="-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                </a>
              </span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/><path d="M20 12h-13l3 -3m0 6l-3 -3"/></svg>
            Masuk
          </button>
        </form>

        <div class="hr-text text-secondary my-4">Akun Demo (Klik untuk Isi)</div>
        <div class="d-flex flex-wrap gap-1 justify-content-center">
          <button type="button" class="btn btn-sm btn-outline-secondary demo-login-btn" data-email="admin@nusantara.test" data-pass="admin123">Admin</button>
          <button type="button" class="btn btn-sm btn-outline-secondary demo-login-btn" data-email="purchasing@nusantara.test" data-pass="purchasing123">Purchasing</button>
          <button type="button" class="btn btn-sm btn-outline-secondary demo-login-btn" data-email="gudang@nusantara.test" data-pass="gudang123">Gudang</button>
          <button type="button" class="btn btn-sm btn-outline-secondary demo-login-btn" data-email="sales@nusantara.test" data-pass="sales123">Sales</button>
          <button type="button" class="btn btn-sm btn-outline-secondary demo-login-btn" data-email="manajemen@nusantara.test" data-pass="manajemen123">Manajemen</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const emailInput = document.getElementById('loginEmail');
  const passInput = document.getElementById('loginPassword');
  const toggleBtn = document.getElementById('togglePassword');

  document.querySelectorAll('.demo-login-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (emailInput && passInput) {
        emailInput.value = this.dataset.email;
        passInput.value = this.dataset.pass;
        emailInput.focus();
      }
    });
  });

  if (toggleBtn && passInput) {
    toggleBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const isPassword = passInput.type === 'password';
      passInput.type = isPassword ? 'text' : 'password';
      this.classList.toggle('text-primary', isPassword);
    });
  }
});
</script>
<?= $this->endSection() ?>
