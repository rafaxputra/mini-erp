<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php $filterActive = ($q ?? '') !== '' || (int) ($product_id ?? 0) !== 0 || (int) ($warehouse_id ?? 0) !== 0 || ! empty($movement_type) || ! empty($start_date) || ! empty($end_date); ?>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-3">
    <form action="/inventory/movements" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-secondary mb-1">Pencarian Produk</label>
          <div class="input-icon">
            <span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/><path d="M21 21l-6 -6"/></svg></span>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama atau kode..." value="<?= esc($q ?? '') ?>">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-secondary mb-1">Jenis Mutasi</label>
          <select name="movement_type" class="form-select form-select-sm">
            <option value="">Semua Jenis</option>
            <option value="IN" <?= ($movement_type ?? '') === 'IN' ? 'selected' : '' ?>>Masuk (IN)</option>
            <option value="OUT" <?= ($movement_type ?? '') === 'OUT' ? 'selected' : '' ?>>Keluar (OUT)</option>
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
    <div class="empty">
      <div class="empty-icon">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8v5l3 2"/><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h-5"/></svg>
      </div>
      <p class="empty-title">Belum ada mutasi stok</p>
      <p class="empty-subtitle text-secondary">Mutasi tercatat otomatis saat barang diterima (GR) atau dialokasikan/dikirim (SO/DO).</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table table-hover table-striped">
        <thead>
          <tr>
            <th style="width: 135px;">Waktu</th>
            <th>Produk</th>
            <th>Gudang</th>
            <th>Jenis &amp; Referensi</th>
            <th class="text-center">Perubahan Stok</th>
            <th>Catatan</th>
            <th>Oleh</th>
          </tr>
        </thead>
        <tbody>
        <?php $curRole = (string) session()->get('user_role'); ?>
        <?php foreach ($items as $it):
          $refType = strtoupper((string) ($it['reference_type'] ?? ''));
          $refId = (int) ($it['reference_id'] ?? 0);
          $refPoId = (int) ($it['ref_po_id'] ?? 0);
          $refUrl = null;
          $refLabel = reference_label($refType) . ' #' . $refId;
          if ($refType === 'SALES_ORDER' && $refId > 0) {
              if (! empty($it['ref_so_number'])) {
                  $refLabel = $it['ref_so_number'];
              }
              if (in_array($curRole, ['admin', 'sales', 'manajemen'], true)) {
                  $refUrl = '/sales-orders/show/' . $refId;
              } elseif ($curRole === 'gudang' && ! empty($it['ref_so_number'])) {
                  $refUrl = '/deliveries?q=' . urlencode((string) $it['ref_so_number']);
              }
          } elseif ($refType === 'PURCHASE_RECEIPT' && $refId > 0) {
              if (! empty($it['ref_gr_number'])) {
                  $refLabel = $it['ref_gr_number'];
              }
              if ($refPoId > 0 && in_array($curRole, ['admin', 'purchasing', 'manajemen'], true)) {
                  $refUrl = '/purchase-orders/show/' . $refPoId;
              } elseif ($curRole === 'gudang' && ! empty($it['ref_gr_number'])) {
                  $refUrl = '/goods-receipts?q=' . urlencode((string) $it['ref_gr_number']);
              }
          }
          $isIn = $it['movement_type'] === 'IN';
          $isOut = $it['movement_type'] === 'OUT';
          $qtyPrefix = $isIn ? '+' : ($isOut ? '-' : '');
        ?>
        <tr>
          <td class="text-secondary small tabular-nums text-nowrap"><?= date('d/m/Y H:i', strtotime($it['created_at'])) ?></td>
          <td>
            <div class="fw-semibold text-dark"><?= esc($it['product_name']) ?></div>
            <code class="text-secondary small"><?= esc($it['code']) ?></code>
          </td>
          <td><span class="badge bg-secondary-lt"><?= esc($it['warehouse_name'] ?? '-') ?></span></td>
          <td>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <?php if ($isIn): ?>
                <span class="badge bg-success-lt fw-bold"><?= esc(movement_label($it['movement_type'])) ?></span>
              <?php elseif ($isOut): ?>
                <span class="badge bg-danger-lt fw-bold"><?= esc(movement_label($it['movement_type'])) ?></span>
              <?php else: ?>
                <span class="badge bg-warning-lt fw-bold"><?= esc(movement_label($it['movement_type'])) ?></span>
              <?php endif; ?>
              <?php if ($refUrl): ?>
                <a href="<?= $refUrl ?>" class="small fw-semibold text-primary text-decoration-none font-monospace"><?= esc($refLabel) ?> &rarr;</a>
              <?php else: ?>
                <span class="text-secondary small font-monospace"><?= esc($refLabel) ?></span>
              <?php endif; ?>
            </div>
          </td>
          <td class="text-center tabular-nums text-nowrap">
            <span class="text-secondary small"><?= esc($it['qty_before']) ?> &rarr;</span>
            <strong class="text-dark"><?= esc($it['qty_after']) ?></strong>
            <span class="badge ms-1 <?= $isIn ? 'bg-success-lt' : ($isOut ? 'bg-danger-lt' : 'bg-warning-lt') ?> fw-bold"><?= $qtyPrefix . esc($it['qty']) ?></span>
          </td>
          <td class="text-secondary small"><?= esc($it['notes']) ?></td>
          <td class="text-secondary small"><?= esc($it['user_name']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
