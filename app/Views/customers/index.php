<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php $canManage = in_array(session()->get('user_role'), ['admin', 'sales'], true); ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari nama, kode, kontak pelanggan..." aria-label="Cari pelanggan">
    </div>
    <?php if (in_array(session()->get('user_role'), ['admin', 'sales'], true)): ?>
      <a class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" href="/customers/create">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
        Tambah Pelanggan
      </a>
    <?php endif; ?>
  </div>

  <?php if (empty($items)): ?>
    <div class="card-body text-center py-5">
      <div class="text-secondary mb-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg>
      </div>
      <div class="h4 mb-1">Belum ada pelanggan</div>
      <div class="text-secondary small">Klik tombol Tambah Pelanggan untuk menambahkan data baru.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 100px;">Kode</th>
            <th>Nama Pelanggan</th>
            <th>Kontak (Telepon & Email)</th>
            <th>Alamat</th>
            <?php if ($canManage): ?>
              <th class="text-end" style="width: 150px;">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($it['code']) ?></code></td>
          <td class="fw-semibold text-dark"><?= esc($it['name']) ?></td>
          <td>
            <div class="text-dark"><?= esc($it['phone'] ?: '-') ?></div>
            <?php if (!empty($it['email'])): ?>
              <div class="text-muted small"><?= esc($it['email']) ?></div>
            <?php endif; ?>
          </td>
          <td class="text-secondary text-truncate" style="max-width: 250px;" title="<?= esc($it['address']) ?>">
            <?= esc($it['address'] ?: '-') ?>
          </td>
          <?php if ($canManage): ?>
          <td class="text-end">
            <div class="d-inline-flex align-items-center gap-1">
              <a class="btn btn-sm btn-outline-warning" href="/customers/edit/<?= esc($it['id']) ?>" title="Ubah data">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/></svg>
                Ubah
              </a>
              <form action="/customers/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan \'<?= esc($it['name']) ?>\'?')">
                <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus pelanggan">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                  Hapus
                </button>
              </form>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
