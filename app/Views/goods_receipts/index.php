<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="input-icon" style="max-width: 280px; width: 100%;">
    <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
    <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari nomor GR, nomor PO..." aria-label="Cari barang masuk">
  </div>
  <?php if (in_array(session()->get('user_role'), ['admin', 'gudang'], true)): ?>
    <a class="btn btn-primary" href="/goods-receipts/create">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
      Terima Barang
    </a>
  <?php endif; ?>
</div>

<?php if (empty($items)): ?>
  <div class="text-center py-5">
    <div class="text-secondary mb-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
    </div>
    <div class="h4 mb-1">Belum ada barang masuk</div>
    <div class="text-secondary small">Klik tombol Terima Barang untuk mencatat penerimaan dari pesanan beli yang sudah disetujui.</div>
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Nomor GR</th>
          <th>Nomor Pesanan Beli</th>
          <th>Gudang</th>
          <th>Tanggal Terima</th>
          <th>Diterima Oleh</th>
          <th>Catatan</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td><code><?= esc($it['gr_number']) ?></code></td>
        <td><code><?= esc($it['po_number']) ?></code></td>
        <td><?= esc($it['warehouse_name'] ?? '-') ?></td>
        <td><?= esc($it['receipt_date']) ?></td>
        <td><?= esc($it['receiver_name']) ?></td>
        <td><?= esc($it['notes']) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?= $this->endSection() ?>
