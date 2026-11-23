<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body p-3">
    <form action="/reports/purchase" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Tanggal Mulai</label>
          <input type="date" name="start_date" class="form-control" value="<?= esc($start_date) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Tanggal Akhir</label>
          <input type="date" name="end_date" class="form-control" value="<?= esc($end_date) ?>">
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary" type="submit">Tampilkan</button>
        </div>
      </div>
    </form>
  </div>
</div>
<p class="text-muted">Periode: <?= esc($start_date) ?> sampai <?= esc($end_date) ?></p>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <div class="text-secondary small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Total Nilai Pembelian Efektif</div>
        <div class="fs-1 fw-bold text-primary font-monospace mt-1">Rp <?= number_format((float) $total, 0, ',', '.') ?></div>
        <div class="text-secondary small mt-1">
          Dihitung dari <strong><?= esc($activeCount ?? 0) ?> pesanan aktif</strong> (Draf, Disetujui, Diterima).
        </div>
      </div>
      <?php if (!empty($cancelledCount) && $cancelledCount > 0): ?>
        <div class="text-end">
          <span class="badge bg-danger-lt px-3 py-2 fs-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M10 10l4 4m0 -4l-4 4"/></svg>
            <?= esc($cancelledCount) ?> PO Dibatalkan: Rp <?= number_format((float) ($totalCancelled ?? 0), 0, ',', '.') ?>
          </span>
          <div class="text-muted small mt-1">Tidak diakumulasi ke dalam total pengeluaran pembelian</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h3 class="card-title m-0 fw-bold">Pesanan Pembelian (PO)</h3>
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari nomor PO, pemasok..." aria-label="Cari pesanan beli">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover table-striped">
      <thead>
        <tr>
          <th>Nomor Pesanan Beli</th>
          <th>Pemasok</th>
          <th>Tanggal</th>
          <th>Status</th>
          <th class="text-end">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pos as $po): ?>
        <tr>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($po['po_number']) ?></code></td>
          <td class="fw-medium"><?= esc($po['supplier_name']) ?></td>
          <td><?= date('d/m/Y', strtotime($po['order_date'])) ?></td>
          <td><span class="badge <?= esc(status_badge_class($po['status'])) ?>"><?= esc(status_label($po['status'])) ?></span></td>
          <td class="text-end font-monospace">Rp <?= number_format((float) $po['total_amount'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($pos)): ?>
        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada pesanan beli pada periode ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h3 class="card-title m-0 fw-bold">Penerimaan Barang (GR)</h3>
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari nomor GR, nomor PO..." aria-label="Cari barang masuk">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover table-striped">
      <thead>
        <tr>
          <th>Nomor GR</th>
          <th>Nomor Pesanan Beli</th>
          <th>Tanggal Terima</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($grs as $gr): ?>
        <tr>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($gr['gr_number']) ?></code></td>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($gr['po_number']) ?></code></td>
          <td><?= date('d/m/Y', strtotime($gr['receipt_date'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($grs)): ?>
        <tr><td colspan="3" class="text-center text-secondary py-4">Belum ada barang masuk pada periode ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
