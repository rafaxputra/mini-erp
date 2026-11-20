<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-end mb-3"><?php if (in_array(session()->get('user_role'), ['admin'], true)): ?><a class="btn btn-primary" href="/categories/create">Tambah Kategori</a><?php endif; ?></div>
<table class="table table-bordered table-striped"><thead><tr><th>ID</th><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($items as $it): ?><tr><td><?= esc($it['id']) ?></td><td><?= esc($it['name']) ?></td><td><?= esc($it['description']) ?></td>
<td><a class="btn btn-sm btn-warning" href="/categories/edit/<?= esc($it['id']) ?>">Ubah</a>
<form action="/categories/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table>
<?= $this->endSection() ?>
