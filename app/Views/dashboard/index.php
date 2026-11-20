<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<h4 class="mb-3">Dashboard</h4>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>Jumlah Produk</h6><h3><?= esc($productCount) ?></h3></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>Total Nilai Stok</h6><h3>Rp <?= number_format((float) $stockValue, 0, ',', '.') ?></h3></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>PO Bulan Ini</h6><h3><?= esc($poMonth) ?></h3></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>SO Bulan Ini</h6><h3><?= esc($soMonth) ?></h3></div></div></div>
</div>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card"><div class="card-header"><strong>Produk Stok Menipis</strong></div>
      <div class="card-body">
      <?php if (empty($lowStock)): ?><p class="mb-0 text-muted">Tidak ada produk yang stoknya menipis.</p>
      <?php else: ?>
        <table class="table table-sm mb-0"><thead><tr><th>Kode</th><th>Produk</th><th>Stok</th><th>Min</th></tr></thead><tbody>
        <?php foreach ($lowStock as $p): ?><tr><td><?= esc($p['code']) ?></td><td><?= esc($p['name']) ?></td><td><?= esc($p['stock_qty']) ?></td><td><?= esc($p['min_stock']) ?></td></tr><?php endforeach; ?>
        </tbody></table>
      <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card"><div class="card-header"><strong>5 Mutasi Terakhir</strong></div>
      <div class="card-body">
      <?php if (empty($movements)): ?><p class="mb-0 text-muted">Belum ada mutasi.</p>
      <?php else: ?>
        <table class="table table-sm mb-0"><thead><tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th>Qty</th><th>Stok</th></tr></thead><tbody>
        <?php foreach ($movements as $m): ?><tr><td><?= esc($m['created_at']) ?></td><td><?= esc($m['code']) ?> <?= esc($m['product_name']) ?></td><td><?= esc($m['movement_type']) ?></td><td><?= esc($m['qty']) ?></td><td><?= esc($m['qty_before']) ?> ke <?= esc($m['qty_after']) ?></td></tr><?php endforeach; ?>
        </tbody></table>
      <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
