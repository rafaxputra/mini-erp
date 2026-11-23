<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari peran..." aria-label="Cari peran">
    </div>
    <a class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" href="/roles/create">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
      Tambah Peran
    </a>
  </div>

  <?php if (empty($items)): ?>
    <div class="card-body text-center py-5">
      <div class="text-secondary mb-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/></svg>
      </div>
      <div class="h4 mb-1">Belum ada peran</div>
      <div class="text-secondary small">Klik tombol Tambah Peran untuk menambahkan peran pengguna baru.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 70px;">ID</th>
            <th>Nama Peran</th>
            <th>Deskripsi</th>
            <th class="text-center" style="width: 140px;">Pengguna Terdaftar</th>
            <th style="width: 140px;">Dibuat</th>
            <th class="text-end" style="width: 150px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><span class="text-muted font-monospace">#<?= esc($it['id']) ?></span></td>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($it['name']) ?></code></td>
          <td class="text-secondary"><?= esc($it['description'] ?: '-') ?></td>
          <td class="text-center"><span class="badge bg-secondary-lt fw-bold"><?= esc($it['user_count']) ?> orang</span></td>
          <td class="text-secondary small"><?= esc(substr($it['created_at'], 0, 10)) ?></td>
          <td class="text-end">
            <div class="d-inline-flex align-items-center gap-1">
              <a class="btn btn-sm btn-outline-warning" href="/roles/edit/<?= esc($it['id']) ?>" title="Ubah data">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"/></svg>
                Ubah
              </a>
              <?php if (! in_array($it['name'], $core, true)): ?>
                <form action="/roles/delete/<?= esc($it['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus peran \'<?= esc($it['name']) ?>\'?')">
                  <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus peran">
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
</div>

<?= $this->endSection() ?>
