<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<?php if (! $po): ?>
<form action="/goods-receipts/create" method="get" class="mb-3">
  <div class="row"><div class="col-md-6"><label class="form-label">Pilih PO Disetujui</label>
  <select name="purchase_order_id" class="form-select" required><option value="">Pilih PO</option><?php foreach ($pos as $p): ?><option value="<?= esc($p['id']) ?>"><?= esc($p['po_number']) ?> - <?= esc($p['order_date']) ?></option><?php endforeach; ?></select><small class="form-hint">Hanya pesanan beli berstatus Disetujui yang bisa diterima.</small></div>
  <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary" type="submit">Pilih</button></div></div>
</form>
<p class="text-muted">Pesanan beli berstatus Draf tidak bisa diterima, harus disetujui admin dulu.</p>
<?php else: ?>
<p><strong>PO:</strong> <code><?= esc($po['po_number']) ?></code> | <strong>Tanggal:</strong> <?= esc($po['order_date']) ?> | <strong>Status:</strong> <?= esc(status_label($po['status'])) ?></p>
<form action="/goods-receipts/store" method="post">
  <input type="hidden" name="purchase_order_id" value="<?= esc($po['id']) ?>">
  <div class="row"><div class="col-md-6 mb-3"><label class="form-label">Tanggal Terima</label><input type="date" name="receipt_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div><div class="col-md-6 mb-3"><label class="form-label">Gudang</label><select name="warehouse_id" class="form-select" required><?php foreach ($warehouses as $w): ?><option value="<?= esc($w['id']) ?>" <?= (int) $po['warehouse_id'] === (int) $w['id'] ? 'selected' : '' ?>><?= esc($w['name']) ?> (<?= esc($w['code']) ?>)</option><?php endforeach; ?></select><small class="form-hint">Barang diterima masuk ke gudang ini.</small></div></div>
  <div class="mb-3"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" placeholder="Contoh: Barang diterima lengkap, kondisi baik"></textarea></div>
  <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Kode</th><th>Produk</th><th>Jumlah Pesan</th><th>Sudah Diterima</th><th>Sisa Terima</th><th>Jumlah Diterima</th></tr></thead><tbody>
  <?php foreach ($items as $it): $sisa = (int) $it['qty'] - (int) $it['received_qty']; ?>
  <tr><td><code><?= esc($it['code']) ?></code></td><td class="fw-medium"><?= esc($it['product_name']) ?></td><td><?= esc($it['qty']) ?></td><td><?= esc($it['received_qty']) ?></td><td><?= esc($sisa) ?></td>
  <td><input type="number" name="qty_received[<?= esc($it['id']) ?>]" class="form-control" min="0" max="<?= esc($sisa) ?>" value="0"></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <div class="mt-2">
    <button type="submit" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h8l4 4v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
      Simpan Penerimaan Barang
    </button>
    <a class="btn btn-secondary" href="/goods-receipts/create">Ganti PO</a>
  </div>
</form>
<?php endif; ?>
<?= $this->endSection() ?>
