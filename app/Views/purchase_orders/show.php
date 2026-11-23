<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<p class="text-secondary mb-3"><code><?= esc($po['po_number']) ?></code></p>
<?php $statusBadge = ['draft' => 'bg-secondary-lt', 'approved' => 'bg-success-lt', 'received' => 'bg-primary-lt', 'cancelled' => 'bg-danger-lt']; ?>
<div class="mb-3">
<p class="mb-1"><strong>Pemasok:</strong> <?= esc($po['supplier_code']) ?> - <?= esc($po['supplier_name']) ?></p>
<p class="mb-1"><strong>Tanggal:</strong> <?= esc($po['order_date']) ?></p>
<p class="mb-1"><strong>Status:</strong> <span class="badge <?= $statusBadge[$po['status']] ?? 'bg-secondary-lt' ?>"><?= esc(status_label($po['status'])) ?></span></p>
<p class="mb-1"><strong>Total:</strong> Rp <?= number_format((float) $po['total_amount'], 0, ',', '.') ?></p>
<p class="mb-1"><strong>Dibuat oleh:</strong> <?= esc($po['creator_name']) ?></p>
<p class="mb-0"><strong>Catatan:</strong> <?= esc($po['notes']) ?></p>
</div>
<div class="d-flex flex-wrap gap-2 mb-3">
<?php if ($po['status'] === 'draft' && session()->get('user_role') === 'admin'): ?>
<form action="/purchase-orders/approve/<?= esc($po['id']) ?>" method="post" class="d-inline"><button class="btn btn-success" type="submit">
  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
  Setujui
</button></form>
<?php endif; ?>
<?php if (($po['status'] === 'draft' || $po['status'] === 'approved') && in_array(session()->get('user_role'), ['admin', 'purchasing'], true)): ?>
<form action="/purchase-orders/cancel/<?= esc($po['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan beli <?= esc($po['po_number']) ?>?')"><button class="btn btn-outline-danger" type="submit">
  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
  Batalkan
</button></form>
<?php endif; ?>
<?php if ($po['status'] === 'approved' && in_array(session()->get('user_role'), ['admin', 'gudang'], true)): ?><a class="btn btn-primary" href="/goods-receipts/create?purchase_order_id=<?= esc($po['id']) ?>"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg> Terima Barang</a><?php endif; ?>
<a class="btn btn-secondary" href="/purchase-orders">Kembali</a>
</div>
<h5>Detail Produk</h5>
<div class="table-responsive mb-3">
  <table class="table table-bordered table-striped"><thead><tr><th>Kode</th><th>Produk</th><th>Jumlah Pesan</th><th>Sudah Diterima</th><th>Sisa</th><th>Harga</th><th>Subtotal</th></tr></thead><tbody>
<?php foreach ($items as $it): ?><tr><td><code><?= esc($it['code']) ?></code></td><td class="fw-medium"><?= esc($it['product_name']) ?></td><td><?= esc($it['qty']) ?></td><td><?= esc($it['received_qty']) ?></td><td><?= esc((int) $it['qty'] - (int) $it['received_qty']) ?></td><td>Rp <?= number_format((float) $it['unit_price'], 0, ',', '.') ?></td><td>Rp <?= number_format((float) $it['subtotal'], 0, ',', '.') ?></td></tr><?php endforeach; ?>
</tbody></table>
</div>
<?php if (! empty($receipts)): ?><h5>Barang Masuk</h5><ul><?php foreach ($receipts as $r): ?><li><code><?= esc($r['gr_number']) ?></code> - <?= esc($r['receipt_date']) ?></li><?php endforeach; ?></ul><?php endif; ?>
<?= $this->endSection() ?>
