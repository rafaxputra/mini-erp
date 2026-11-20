<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between mb-3"><h4>Daftar Pengguna</h4><a class="btn btn-primary" href="/users/create">Tambah Pengguna</a></div>
<p class="text-muted">Halaman ini hanya untuk admin.</p>
<table class="table table-bordered table-striped">
<thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Peran</th><th>Aktif</th><th>Dibuat</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?><tr><td><?= esc($u['id']) ?></td><td><?= esc($u['name']) ?></td><td><?= esc($u['email']) ?></td><td><?= esc($u['role_name']) ?></td><td><?= $u['is_active'] ? 'Ya' : 'Tidak' ?></td><td><?= esc($u['created_at']) ?></td>
<td><a class="btn btn-sm btn-warning" href="/users/edit/<?= esc($u['id']) ?>">Ubah</a>
<form action="/users/delete/<?= esc($u['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus pengguna ini?')"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table>
<?= $this->endSection() ?>
