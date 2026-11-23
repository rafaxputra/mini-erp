<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="input-icon" style="max-width: 280px; width: 100%;">
    <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
    <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari nama, email, peran..." aria-label="Cari pengguna">
  </div>
  <a class="btn btn-primary" href="/users/create">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
    Tambah Pengguna
  </a>
</div>

<?php if (empty($users)): ?>
  <div class="text-center py-5">
    <div class="text-secondary mb-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg>
    </div>
    <div class="h4 mb-1">Belum ada pengguna</div>
    <div class="text-secondary small">Klik tombol Tambah Pengguna untuk membuat akun baru.</div>
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th style="width: 70px;">ID</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Peran</th>
          <th style="width: 100px;">Status</th>
          <th>Dibuat</th>
          <th style="width: 200px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= esc($u['id']) ?></td>
        <td class="fw-medium"><?= esc($u['name']) ?></td>
        <td><?= esc($u['email']) ?></td>
        <td><span class="badge bg-secondary-lt"><?= esc(ucfirst($u['role_name'])) ?></span></td>
        <td>
          <?php if ($u['is_active']): ?>
            <span class="badge bg-success-lt">Aktif</span>
          <?php else: ?>
            <span class="badge bg-danger-lt">Nonaktif</span>
          <?php endif; ?>
        </td>
        <td class="text-secondary small"><?= esc($u['created_at']) ?></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <a class="btn btn-sm btn-outline-warning" href="/users/edit/<?= esc($u['id']) ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/></svg>
              Ubah
            </a>
            <?php if ($u['is_active']): ?>
              <form action="/users/toggle/<?= esc($u['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan akun \'<?= esc($u['name']) ?>\'?')">
                <button class="btn btn-sm btn-outline-danger" type="submit">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M5.7 5.7l12.6 12.6"/></svg>
                  Nonaktifkan
                </button>
              </form>
            <?php else: ?>
              <form action="/users/toggle/<?= esc($u['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mengaktifkan kembali akun \'<?= esc($u['name']) ?>\'?')">
                <button class="btn btn-sm btn-outline-success" type="submit">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
                  Aktifkan
                </button>
              </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?= $this->endSection() ?>
