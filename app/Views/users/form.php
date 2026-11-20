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
  <button type="submit" class="btn btn-primary">Simpan</button>
  <a class="btn btn-secondary" href="/users">Batal</a>
</form>
<?= $this->endSection() ?>
