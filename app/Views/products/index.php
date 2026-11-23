<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="input-icon" style="max-width: 280px; width: 100%;">
    <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
    <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari nama, kode, kategori..." aria-label="Cari produk">
  </div>
  <?php if (in_array(session()->get('user_role'), ['admin', 'purchasing'], true)): ?>
    <a class="btn btn-primary" href="/products/create">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
      Tambah Produk
    </a>
  <?php endif; ?>
</div>

<?php if (empty($items)): ?>
  <div class="text-center py-5">
    <div class="text-secondary mb-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
    </div>
    <div class="h4 mb-1">Belum ada produk</div>
    <div class="text-secondary small">Klik tombol Tambah Produk untuk mendaftarkan barang baru.</div>
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama</th>
          <th>Kategori</th>
          <th>Satuan</th>
          <th>Harga Beli</th>
          <th>Harga Jual</th>
          <th>Stok</th>
          <th>Stok Min.</th>
          <th style="width: 170px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td><code><?= esc($it['code']) ?></code></td>
        <td class="fw-medium"><?= esc($it['name']) ?></td>
        <td><span class="badge bg-secondary-lt"><?= esc($it['category_name']) ?></span></td>
        <td><?= esc($it['unit']) ?></td>
        <td>Rp <?= number_format((float) $it['purchase_price'], 0, ',', '.') ?></td>
        <td>Rp <?= number_format((float) $it['selling_price'], 0, ',', '.') ?></td>
        <td>
          <?php if ((int) $it['stock_qty'] <= (int) $it['min_stock']): ?>
            <span class="badge bg-danger-lt fw-bold" title="Stok di bawah batas minimum!"><?= esc($it['stock_qty']) ?></span>
          <?php else: ?>
            <?= esc($it['stock_qty']) ?>
          <?php endif; ?>
        </td>
        <td><?= esc($it['min_stock']) ?></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <a class="btn btn-sm btn-outline-warning" href="/products/edit/<?= esc($it['id']) ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/></svg>
              Ubah
            </a>
            <form action="/products/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk \'<?= esc($it['name']) ?>\'?')">
              <button class="btn btn-sm btn-outline-danger" type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                Hapus
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?= $this->endSection() ?>
