<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php $canManage = in_array(session()->get('user_role'), ['admin'], true); ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <form action="/suppliers" method="get" class="d-flex align-items-center gap-1" style="max-width: 350px; width: 100%;">
      <div class="input-icon flex-grow-1">
        <span class="input-icon-addon">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
        </span>
        <input type="text" name="q" class="form-control form-control-sm js-table-search" placeholder="Cari nama, kode, kontak pemasok..." aria-label="Cari pemasok" value="<?= esc($q ?? '') ?>">
      </div>
      <?php if (! empty($q)): ?>
        <a href="/suppliers" class="btn btn-sm btn-outline-secondary" title="Reset filter pencarian">Reset</a>
      <?php endif; ?>
    </form>
    <?php if (in_array(session()->get('user_role'), ['admin'], true)): ?>
      <a class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" href="/suppliers/create">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
        Tambah Pemasok
      </a>
    <?php endif; ?>
  </div>

  <?php if (empty($items)): ?>
    <div class="card-body text-center py-5">
      <div class="text-secondary mb-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M2 3h1a2 2 0 0 1 2 2v10a2 2 0 0 0 2 2h15"/><path d="M9 6m0 3a3 3 0 0 1 3 -3h4a3 3 0 0 1 3 3v2a3 3 0 0 1 -3 3h-4a3 3 0 0 1 -3 -3z"/><path d="M9 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/></svg>
      </div>
      <div class="h4 mb-1">Belum ada pemasok</div>
      <div class="text-secondary small">Klik tombol Tambah Pemasok untuk mendaftarkan mitra suplai baru.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 100px;">Kode</th>
            <th>Nama Pemasok</th>
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
          <td class="fw-semibold text-dark">
            <div class="d-flex align-items-center gap-2">
              <span class="avatar avatar-sm bg-azure-lt text-azure fw-bold"><?= esc(mb_strtoupper(mb_substr(trim((string) $it['name']), 0, 2))) ?></span>
              <span><?= esc($it['name']) ?></span>
            </div>
          </td>
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
              <a class="btn btn-sm btn-outline-warning" href="/suppliers/edit/<?= esc($it['id']) ?>" title="Ubah data">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/></svg>
                Ubah
              </a>
              <form action="/suppliers/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" data-confirm="Apakah Anda yakin ingin menghapus pemasok <strong>'<?= esc($it['name']) ?>'</strong>?" data-confirm-title="Hapus Pemasok" data-confirm-btn="Ya, Hapus">
                <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus pemasok">
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

    <?= view('partials/pagination', ['pager' => $pager ?? null, 'query' => ['q' => $q ?? ''], 'label' => 'Pemasok']) ?>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
