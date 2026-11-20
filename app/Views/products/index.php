<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-end mb-3"><?php if (in_array(session()->get('user_role'), ['admin', 'purchasing'], true)): ?><a class="btn btn-primary" href="/products/create">Tambah Produk</a><?php endif; ?></div>
<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Satuan</th><th>Harga Beli</th><th>Harga Jual</th><th>Stok</th><th>Stok Min.</th><th>Aksi</th></tr></thead><tbody>
<?php foreach ($items as $it): ?><tr><td><?= esc($it['code']) ?></td><td><?= esc($it['name']) ?></td><td><?= esc($it['category_name']) ?></td><td><?= esc($it['unit']) ?></td><td>Rp <?= number_format((float) $it['purchase_price'], 0, ',', '.') ?></td><td>Rp <?= number_format((float) $it['selling_price'], 0, ',', '.') ?></td><td><?= esc($it['stock_qty']) ?></td><td><?= esc($it['min_stock']) ?></td>
<td><a class="btn btn-sm btn-warning" href="/products/edit/<?= esc($it['id']) ?>">Ubah</a>
<form action="/products/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus produk ini?')"><button class="btn btn-sm btn-danger" type="submit">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?= $this->endSection() ?>
