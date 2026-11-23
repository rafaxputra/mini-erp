<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="input-icon" style="max-width: 280px; width: 100%;">
    <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
    <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari gudang..." aria-label="Cari gudang">
  </div>
  <?php if (in_array(session()->get('user_role'), ['admin'], true)): ?>
    <a class="btn btn-primary" href="/warehouses/create">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
      Tambah Gudang
    </a>
  <?php endif; ?>
</div>

<?php if (empty($items)): ?>
  <div class="text-center py-5">
    <div class="text-secondary mb-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7v11m0 -11a2 2 0 0 1 2 -2h2l2 2h8a2 2 0 0 1 2 2v2m0 4v5a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2"/><path d="M3 3l18 18"/></svg>
    </div>
    <div class="h4 mb-1">Belum ada gudang</div>
    <div class="text-secondary small">Klik tombol Tambah Gudang untuk menambahkan data baru.</div>
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead><tr><th style="width: 90px;">Kode</th><th>Nama</th><th>Lokasi</th><th style="width: 110px;">Utama</th><th style="width: 170px;">Aksi</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td><code><?= esc($it['code']) ?></code></td>
        <td class="fw-medium"><?= esc($it['name']) ?></td>
        <td class="text-secondary"><?= esc($it['location']) ?></td>
        <td><?= $it['is_default'] ? '<span class="badge bg-success-lt">Utama</span>' : '<span class="text-secondary">-</span>' ?></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <a class="btn btn-sm btn-outline-warning" href="/warehouses/edit/<?= esc($it['id']) ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/></svg>
              Ubah
            </a>
            <?php if (! $it['is_default']): ?>
            <form action="/warehouses/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus gudang \'<?= esc($it['name']) ?>\'?')">
              <button class="btn btn-sm btn-outline-danger" type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                Hapus
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
