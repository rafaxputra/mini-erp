<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
$totalQty = 0;
foreach ($items as $it) {
    $totalQty += (int) $it['qty_received'];
}
?>

<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 border-bottom mb-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-dark-lt fs-3 px-2 py-1 font-monospace"><?= esc($gr['gr_number']) ?></span>
          <span class="badge bg-success-lt fs-4 px-2 py-1">Diterima</span>
        </div>
        <div class="text-secondary small">Diterima oleh: <strong class="text-dark"><?= esc($gr['receiver_name'] ?? '-') ?></strong></div>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a class="btn btn-outline-secondary" href="/goods-receipts">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0"/><path d="M5 12l6 6"/><path d="M5 12l6 -6"/></svg>
          Kembali ke Daftar
        </a>
      </div>
    </div>

    <div class="datagrid">
      <div class="datagrid-item">
        <div class="datagrid-title">Nomor Pesanan Beli (PO)</div>
        <div class="datagrid-content">
          <?php if ($canViewPo): ?>
            <a href="/purchase-orders/show/<?= esc($gr['po_id']) ?>" class="fw-bold font-monospace text-primary text-decoration-none"><?= esc($gr['po_number']) ?></a>
          <?php else: ?>
            <code class="text-dark font-monospace fw-bold"><?= esc($gr['po_number']) ?></code>
          <?php endif; ?>
        </div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Pemasok</div>
        <div class="datagrid-content fw-semibold text-dark"><?= esc($gr['supplier_name'] ?? '-') ?></div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Gudang Penerima</div>
        <div class="datagrid-content"><span class="badge bg-secondary-lt"><?= esc($gr['warehouse_name'] ?? '-') ?></span></div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Tanggal Terima</div>
        <div class="datagrid-content font-monospace"><?= date('d/m/Y', strtotime($gr['receipt_date'])) ?></div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Ringkasan Barang</div>
        <div class="datagrid-content"><?= count($items) ?> item &bull; <strong><?= number_format($totalQty, 0, ',', '.') ?></strong> unit diterima</div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Catatan</div>
        <div class="datagrid-content text-secondary" style="white-space: pre-line;"><?= esc($gr['notes'] ?: '-') ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header">
    <h3 class="card-title">Daftar Barang Diterima</h3>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover table-striped">
      <thead>
        <tr>
          <th style="width: 50px;">No</th>
          <th>SKU</th>
          <th>Nama Produk</th>
          <th class="text-end">Qty Pesan (PO)</th>
          <th class="text-end">Qty Diterima</th>
          <th>Satuan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $idx => $item): ?>
        <tr>
          <td class="text-secondary"><?= $idx + 1 ?></td>
          <td><code class="text-dark font-monospace"><?= esc($item['sku']) ?></code></td>
          <td class="fw-medium text-dark"><?= esc($item['product_name']) ?></td>
          <td class="text-end font-monospace text-secondary"><?= number_format((int) $item['po_qty'], 0, ',', '.') ?></td>
          <td class="text-end font-monospace fw-bold text-success"><?= number_format((int) $item['qty_received'], 0, ',', '.') ?></td>
          <td class="text-secondary"><?= esc($item['unit'] ?? 'pcs') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
