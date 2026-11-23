<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<form action="<?= $item ? '/users/update/' . esc($item['id']) : '/users/store' ?>" method="post">
  <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" value="<?= esc(old('name', $item['name'] ?? '')) ?>" required></div>
  <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= esc(old('email', $item['email'] ?? '')) ?>" required></div>
  <div class="mb-3"><label class="form-label">Password<?= $item ? ' Baru (kosongkan kalau tidak diganti)' : '' ?></label><input type="password" name="password" class="form-control" <?= $item ? '' : 'required' ?> minlength="6"></div>
  <div class="mb-3"><label class="form-label">Peran</label>
    <select name="role_id" class="form-select" required>
      <?php foreach ($roles as $role): ?>
      <option value="<?= esc($role['id']) ?>" <?= (int) old('role_id', $item['role_id'] ?? 0) === (int) $role['id'] ? 'selected' : '' ?>><?= esc(ucfirst($role['name'])) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php if ($item): ?>
  <div class="mb-3 form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" <?= old('is_active', $item['is_active'] ? '1' : '0') === '1' ? 'checked' : '' ?>><label class="form-check-label" for="is_active">Akun aktif</label></div>
  <?php endif; ?>
  <div class="mt-2">
    <button type="submit" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
      Simpan
    </button>
    <a class="btn btn-secondary" href="/users">Batal</a>
  </div>
</form>
<?= $this->endSection() ?>
