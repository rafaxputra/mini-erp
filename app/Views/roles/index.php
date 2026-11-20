<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-end mb-3"><a class="btn btn-primary" href="/roles/create"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Tambah Peran</a></div>
<div class="card">
  <div class="card-header"><h3 class="card-title">Daftar Peran</h3></div>
  <div class="card-body">
    <div class="table-responsive"><table class="table table-bordered table-striped table-hover"><thead><tr><th>ID</th><th>Nama</th><th>Deskripsi</th><th>Pengguna</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($items as $it): ?><tr><td><?= esc($it['id']) ?></td><td><?= esc($it['name']) ?> <?php if (in_array($it['name'], $core, true)): ?><span class="badge bg-blue-lt">Sistem</span><?php endif; ?></td><td><?= esc($it['description']) ?></td><td><?= esc($it['user_count']) ?></td><td><?= esc($it['created_at']) ?></td>
<td><a class="btn btn-sm btn-warning" href="/roles/edit/<?= esc($it['id']) ?>">Ubah</a>
<?php if (! in_array($it['name'], $core, true)): ?>
<form action="/roles/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus peran ini?')"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form>
<?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
  </div>
</div>
<?= $this->endSection() ?>
