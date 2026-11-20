<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-end mb-3"><a class="btn btn-primary" href="/users/create"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah Pengguna</a></div>
<div class="table-responsive"><table class="table table-bordered table-striped">
<thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Peran</th><th>Aktif</th><th>Dibuat</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?><tr><td><?= esc($u['id']) ?></td><td><?= esc($u['name']) ?></td><td><?= esc($u['email']) ?></td><td><?= esc($u['role_name']) ?></td><td><?= $u['is_active'] ? 'Ya' : 'Tidak' ?></td><td><?= esc($u['created_at']) ?></td>
<td><a class="btn btn-sm btn-warning" href="/users/edit/<?= esc($u['id']) ?>">Ubah</a>
<?php if ($u['is_active']): ?>
<form action="/users/toggle/<?= esc($u['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Nonaktifkan pengguna ini?')"><button class="btn btn-sm btn-danger" type="submit">Nonaktifkan</button></form>
<?php else: ?>
<form action="/users/toggle/<?= esc($u['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Aktifkan pengguna ini?')"><button class="btn btn-sm btn-success" type="submit">Aktifkan</button></form>
<?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?= $this->endSection() ?>
