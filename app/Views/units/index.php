<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari satuan..." aria-label="Cari satuan">
    </div>
    <?php if ($isAdmin): ?>
      <a class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" href="/units/create">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
        Tambah Satuan
      </a>
    <?php endif; ?>
  </div>

  <?php if (empty($items)): ?>
    <div class="card-body text-center py-5">
      <div class="text-secondary mb-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7v10"/><path d="M20 7v10"/><path d="M4 12h16"/></svg>
      </div>
      <div class="h4 mb-1">Belum ada satuan</div>
      <div class="text-secondary small">Klik tombol Tambah Satuan untuk menambahkan master data baru.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 70px;">ID</th>
            <th style="width: 120px;">Kode Satuan</th>
            <th>Nama Satuan</th>
            <th>Deskripsi</th>
            <?php if ($isAdmin): ?>
              <th class="text-end" style="width: 150px;">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><span class="text-muted font-monospace">#<?= esc($it['id']) ?></span></td>
          <td><code class="text-primary font-monospace fw-bold"><?= esc($it['code']) ?></code></td>
          <td class="fw-semibold text-dark"><?= esc($it['name']) ?></td>
          <td class="text-secondary"><?= esc($it['description'] ?: '-') ?></td>
          <?php if ($isAdmin): ?>
          <td class="text-end">
            <div class="d-inline-flex align-items-center gap-1">
              <a class="btn btn-sm btn-outline-warning" href="/units/edit/<?= esc($it['id']) ?>" title="Ubah data">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/></svg>
                Ubah
              </a>
              <form action="/units/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" data-confirm="Apakah Anda yakin ingin menghapus satuan <strong>'<?= esc($it['name']) ?> (<?= esc($it['code']) ?>)'</strong>?" data-confirm-title="Hapus Satuan" data-confirm-btn="Ya, Hapus">
                <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus satuan">
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
