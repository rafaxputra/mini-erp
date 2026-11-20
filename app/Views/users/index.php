<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Daftar Pengguna</h4>
<p class="text-muted">Halaman ini hanya untuk admin. Daftar pengguna ditampilkan saja, tanpa tambah atau ubah.</p>
<table class="table table-bordered table-striped">
<thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Peran</th><th>Aktif</th><th>Dibuat</th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?><tr><td><?= esc($u['id']) ?></td><td><?= esc($u['name']) ?></td><td><?= esc($u['email']) ?></td><td><?= esc($u['role_name']) ?></td><td><?= $u['is_active'] ? 'Ya' : 'Tidak' ?></td><td><?= esc($u['created_at']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?= $this->endSection() ?>
