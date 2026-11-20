<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between mb-3"><h4>Customer</h4><?php if (in_array(session()->get('user_role'), ['admin', 'sales'], true)): ?><a class="btn btn-primary" href="/customers/create">Tambah Customer</a><?php endif; ?></div>
<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Kode</th><th>Nama</th><th>Telepon</th><th>Email</th><th>Alamat</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($items as $it): ?><tr><td><?= esc($it['code']) ?></td><td><?= esc($it['name']) ?></td><td><?= esc($it['phone']) ?></td><td><?= esc($it['email']) ?></td><td><?= esc($it['address']) ?></td>
<td><a class="btn btn-sm btn-warning" href="/customers/edit/<?= esc($it['id']) ?>">Ubah</a>
<form action="/customers/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus data ini?')"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?= $this->endSection() ?>
