<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body p-3">
    <form action="/reports/sales" method="get">
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
    <div class="text-secondary small">Total Nilai SO pada periode</div>
    <div class="fs-2 text-primary font-monospace">Rp <?= number_format((float) $total, 0, ',', '.') ?></div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h3 class="card-title m-0 fw-bold">Pesanan Jual</h3>
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari nomor SO, pelanggan..." aria-label="Cari pesanan jual">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover table-striped">
      <thead>
        <tr>
          <th>Nomor Pesanan Jual</th>
          <th>Pelanggan</th>
          <th>Tanggal</th>
          <th>Status</th>
          <th class="text-end">Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sos as $so): ?>
        <tr>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($so['so_number']) ?></code></td>
          <td class="fw-medium"><?= esc($so['customer_name']) ?></td>
          <td><?= esc($so['order_date']) ?></td>
          <td><span class="badge <?= esc(status_badge_class($so['status'])) ?>"><?= esc(status_label($so['status'])) ?></span></td>
          <td class="text-end font-monospace">Rp <?= number_format((float) $so['total_amount'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($sos)): ?>
        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada pesanan jual pada periode ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-transparent py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h3 class="card-title m-0 fw-bold">Pengiriman</h3>
    <div class="input-icon" style="max-width: 300px; width: 100%;">
      <span class="input-icon-addon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="10" cy="10" r="7"/><path d="M21 21l-6 -6"/></svg>
      </span>
      <input type="text" class="form-control form-control-sm js-table-search" placeholder="Cari nomor DO, nomor SO..." aria-label="Cari pengiriman">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover table-striped">
      <thead>
        <tr>
          <th>Nomor DO</th>
          <th>Nomor Pesanan Jual</th>
          <th>Tanggal Kirim</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($dos as $d): ?>
        <tr>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($d['delivery_number']) ?></code></td>
          <td><code class="text-dark font-monospace fw-bold"><?= esc($d['so_number']) ?></code></td>
          <td><?= esc($d['delivery_date']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($dos)): ?>
        <tr><td colspan="3" class="text-center text-secondary py-4">Belum ada pengiriman pada periode ini.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
