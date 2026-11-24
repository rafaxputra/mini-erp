<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php $filterActive = (int) ($product_id ?? 0) !== 0 || (int) ($warehouse_id ?? 0) !== 0 || ! empty($movement_type) || ! empty($start_date) || ! empty($end_date); ?>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/inventory/movements" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Produk</label>
          <select name="product_id" class="form-select form-select-sm">
            <option value="0">Semua Produk</option>
            <?php foreach ($products as $p): ?>
              <option value="<?= esc($p['id']) ?>" <?= (int) $product_id === (int) $p['id'] ? 'selected' : '' ?>><?= esc($p['code']) ?> - <?= esc($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Jenis Mutasi</label>
          <select name="movement_type" class="form-select form-select-sm">
            <option value="">Semua Jenis</option>
            <option value="IN" <?= ($movement_type ?? '') === 'IN' ? 'selected' : '' ?>>⬇️ Masuk (IN)</option>
            <option value="OUT" <?= ($movement_type ?? '') === 'OUT' ? 'selected' : '' ?>>⬆️ Keluar (OUT)</option>
            <option value="ADJUSTMENT" <?= ($movement_type ?? '') === 'ADJUSTMENT' ? 'selected' : '' ?>>⚙️ Koreksi (ADJUST)</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Lokasi Gudang</label>
          <select name="warehouse_id" class="form-select form-select-sm">
            <option value="0">Semua Gudang</option>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= esc($wh['id']) ?>" <?= (int) ($warehouse_id ?? 0) === (int) $wh['id'] ? 'selected' : '' ?>><?= esc($wh['name']) ?> (<?= esc($wh['code']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Dari Tanggal</label>
          <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($start_date) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Sampai Tanggal</label>
          <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($end_date) ?>">
        </div>
        <div class="col-md-1 d-flex gap-1 justify-content-end">
          <button class="btn btn-sm btn-primary w-100" type="submit">Filter</button>
          <?php if ($filterActive): ?>
            <a class="btn btn-sm btn-outline-secondary" href="/inventory/movements" title="Reset filter">✕</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <?php if (empty($items)): ?>
    <div class="card-body text-center py-5">
      <div class="text-secondary mb-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8v5l3 2"/><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h-5"/></svg>
      </div>
      <div class="h4 mb-1">Belum ada mutasi stok</div>
      <div class="text-secondary small">Mutasi tercatat saat barang diterima, dikirim, atau dikoreksi.</div>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 170px;">Waktu</th>
            <th>Produk</th>
            <th>Gudang</th>
            <th>Jenis</th>
            <th>Referensi</th>
            <th class="text-center">Jumlah</th>
            <th class="text-center">Sebelum</th>
            <th class="text-center">Sesudah</th>
            <th>Catatan</th>
            <th>Oleh</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td class="text-secondary small font-monospace"><?= date('d/m/Y H:i', strtotime($it['created_at'])) ?></td>
          <td class="fw-semibold text-dark"><code class="text-dark font-monospace"><?= esc($it['code']) ?></code> <?= esc($it['product_name']) ?></td>
          <td><span class="badge bg-secondary-lt"><?= esc($it['warehouse_name'] ?? '-') ?></span></td>
          <td>
            <?php if ($it['movement_type'] === 'IN'): ?>
              <span class="badge bg-success-lt fw-bold"><?= esc(movement_label($it['movement_type'])) ?></span>
            <?php elseif ($it['movement_type'] === 'OUT'): ?>
              <span class="badge bg-danger-lt fw-bold"><?= esc(movement_label($it['movement_type'])) ?></span>
            <?php else: ?>
              <span class="badge bg-warning-lt fw-bold"><?= esc(movement_label($it['movement_type'])) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-secondary small"><?= esc(reference_label($it['reference_type'])) ?> #<?= esc($it['reference_id']) ?></td>
          <td class="text-center font-monospace"><?= esc($it['qty']) ?></td>
          <td class="text-center font-monospace"><?= esc($it['qty_before']) ?></td>
          <td class="text-center font-monospace fw-bold"><?= esc($it['qty_after']) ?></td>
          <td class="text-secondary small"><?= esc($it['notes']) ?></td>
          <td class="text-secondary"><?= esc($it['user_name']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
