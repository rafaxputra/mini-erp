<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php $st = status_badge($po['status']); ?>

<?php
$poStatus = $po['status'];
$poSteps = [
  ['key' => 'draft',    'label' => 'Draf Pengajuan'],
  ['key' => 'approved', 'label' => 'Disetujui (ACC)'],
  ['key' => 'received', 'label' => 'Diterima di Gudang'],
];
$poOrder = ['draft' => 0, 'approved' => 1, 'received' => 2];
$curIdx = $poOrder[$poStatus] ?? 0;
$stepsColor = $poStatus === 'cancelled' ? 'steps-red' : 'steps-green';
?>
<ul class="steps <?= $stepsColor ?> steps-counter my-3">
  <?php if ($poStatus === 'cancelled'): ?>
    <li class="step-item">Draf Pengajuan</li>
    <li class="step-item active">Dibatalkan</li>
  <?php else: ?>
    <?php foreach ($poSteps as $idx => $step): ?>
      <li class="step-item <?= $idx === $curIdx ? 'active' : '' ?>"><?= esc($step['label']) ?></li>
    <?php endforeach; ?>
  <?php endif; ?>
</ul>

<!-- Kartu Ringkasan Informasi Pesanan -->
<div class="card mb-3 border-0 shadow-sm">
  <div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 border-bottom mb-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-dark-lt fs-3 px-2 py-1 font-monospace"><?= esc($po['po_number']) ?></span>
          <span class="badge <?= $st['badge'] ?> fs-4 px-2 py-1"><?= esc($st['label']) ?></span>
        </div>
        <div class="text-secondary small">Dibuat oleh: <strong class="text-dark"><?= esc($po['creator_name']) ?></strong></div>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <?php if ($po['status'] === 'draft' && session()->get('user_role') === 'manajemen'): ?>
          <form action="/purchase-orders/approve/<?= esc($po['id']) ?>" method="post" class="d-inline" data-confirm="Apakah Anda yakin ingin menyetujui (ACC) Pesanan Beli <strong><?= esc($po['po_number']) ?></strong>?" data-confirm-title="Persetujuan Pesanan Beli (ACC)" data-confirm-btn="Ya, Setujui (ACC)" data-confirm-type="primary">
            <button class="btn btn-success" type="submit">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
              Setujui Pesanan (ACC)
            </button>
          </form>
          <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelPO">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
            Tolak Pengajuan
          </button>
        <?php endif; ?>

        <?php if ($po['status'] === 'approved' && session()->get('user_role') === 'gudang'): ?>
          <a class="btn btn-primary" href="/goods-receipts/create?purchase_order_id=<?= esc($po['id']) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
            Proses Penerimaan Barang
          </a>
        <?php endif; ?>

        <?php if (($po['status'] === 'draft' || $po['status'] === 'approved') && session()->get('user_role') === 'purchasing'): ?>
          <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelPO">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
            Batalkan Pesanan
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Parsing Catatan: Pisahkan Catatan PO dengan Alasan Pembatalan / Penolakan agar tidak dobel -->
    <?php
      $rawNotes = (string) ($po['notes'] ?? '');
      $cancelNote = '';
      $initialNotes = [];
      if ($rawNotes !== '') {
          foreach (explode("\n", $rawNotes) as $line) {
              $trimmed = trim($line);
              $clean = ltrim($trimmed, '[');
              if (str_starts_with($clean, 'Ditolak') || str_starts_with($clean, 'Dibatalkan') || stripos($clean, 'alasan') !== false) {
                  $cancelNote .= ($cancelNote !== '' ? "\n" : '') . $trimmed;
              } elseif ($trimmed !== '') {
                  $initialNotes[] = $trimmed;
              }
          }
          if ($po['status'] === 'cancelled' && $cancelNote === '') {
              $cancelNote = $rawNotes;
              $initialNotes = [];
          }
      }
      $initialNotesStr = implode("\n", $initialNotes);
    ?>

    <!-- Banner Peringatan Jika Pesanan Dibatalkan -->
    <?php if ($po['status'] === 'cancelled'): ?>
      <div class="alert alert-danger mb-3 d-flex align-items-start gap-2" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg>
        <div>
          <div class="fw-bold">Pesanan Beli Ini Telah Dibatalkan / Ditolak</div>
          <div class="small mt-1 text-danger-emphasis" style="white-space: pre-line;"><?= esc($cancelNote ?: ($rawNotes ?: 'Tidak ada catatan alasan pembatalan.')) ?></div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Ringkasan Informasi (Tabler Datagrid) -->
    <div class="datagrid">
      <div class="datagrid-item">
        <div class="datagrid-title">Pemasok</div>
        <div class="datagrid-content">
          <div class="fw-bold text-dark"><?= esc($po['supplier_name']) ?></div>
          <div class="text-secondary small font-monospace"><?= esc($po['supplier_code']) ?></div>
        </div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Tanggal Pesanan</div>
        <div class="datagrid-content">
          <div class="fw-bold text-dark"><?= date('d M Y', strtotime($po['order_date'])) ?></div>
          <div class="text-secondary small font-monospace"><?= esc($po['order_date']) ?></div>
        </div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Total Nilai Pesanan</div>
        <div class="datagrid-content">
          <span class="fw-bold fs-2 text-primary">Rp <?= number_format((float) $po['total_amount'], 0, ',', '.') ?></span>
        </div>
      </div>
    </div>

    <!-- Kotak Catatan PO (Hanya tampil jika ada catatan awal pemesanan) -->
    <?php if ($initialNotesStr !== ''): ?>
    <div class="mt-3 p-2 px-3 rounded bg-light border text-secondary small">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1 text-secondary" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 21v-13a3 3 0 0 1 3 -3h10a3 3 0 0 1 3 3v6a3 3 0 0 1 -3 3h-9l-4 4"/></svg>
      <strong>Catatan PO:</strong> <span style="white-space: pre-line;"><?= esc($initialNotesStr) ?></span>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Kartu Daftar Produk Pesanan -->
<div class="card mb-3 border-0 shadow-sm">
  <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
    <h3 class="card-title m-0 fw-bold">Detail Produk yang Dipesan</h3>
    <span class="badge bg-secondary-lt"><?= count($items) ?> item</span>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter table-striped mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 50px;">No</th>
          <th>Kode</th>
          <th>Produk</th>
          <th class="text-center" style="width: 130px;">Jumlah Pesan</th>
          <th class="text-center" style="width: 130px;">Sudah Diterima</th>
          <th class="text-center" style="width: 100px;">Sisa</th>
          <th class="text-end" style="width: 160px;">Harga Satuan</th>
          <th class="text-end" style="width: 170px;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $idx => $it): ?>
        <?php $sisa = (int) $it['qty'] - (int) $it['received_qty']; ?>
        <tr>
          <td class="text-secondary"><?= $idx + 1 ?></td>
          <td><code class="text-dark"><?= esc($it['code']) ?></code></td>
          <td>
            <div class="fw-bold text-dark"><?= esc($it['product_name']) ?></div>
          </td>
          <td class="text-center fw-medium">
            <?= esc($it['qty']) ?> <span class="text-secondary small"><?= esc($it['unit']) ?></span>
          </td>
          <td class="text-center">
            <?php if ((int) $it['received_qty'] > 0): ?>
              <span class="badge bg-green-lt fw-bold"><?= esc($it['received_qty']) ?> <span class="fw-normal"><?= esc($it['unit']) ?></span></span>
            <?php else: ?>
              <span class="text-secondary">0</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?php if ($sisa === 0): ?>
              <span class="badge bg-success-lt">Lengkap</span>
            <?php else: ?>
              <span class="badge bg-warning-lt fw-bold"><?= esc($sisa) ?> <span class="fw-normal"><?= esc($it['unit']) ?></span></span>
            <?php endif; ?>
          </td>
          <td class="text-end">Rp <?= number_format((float) $it['unit_price'], 0, ',', '.') ?></td>
          <td class="text-end fw-bold text-dark">Rp <?= number_format((float) $it['subtotal'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="7" class="text-end fw-bold fs-3">Total Keseluruhan:</td>
          <td class="text-end fw-bold fs-3 text-primary">Rp <?= number_format((float) $po['total_amount'], 0, ',', '.') ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Jejak Aktivitas & Alur Dokumen (Tabler Timeline) -->
<div class="card border-0 shadow-sm mt-3">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Jejak Aktivitas &amp; Alur Dokumen</h3>
  </div>
  <div class="card-body">
    <ul class="timeline">
      <li class="timeline-event">
        <div class="timeline-event-icon bg-primary-lt text-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= ! empty($po['created_at']) ? date('d M Y H:i', strtotime($po['created_at'])) : date('d M Y', strtotime($po['order_date'])) ?></div>
            <h4 class="mb-1">Pengajuan PO Dibuat</h4>
            <p class="text-secondary mb-0">Dokumen <strong><?= esc($po['po_number']) ?></strong> ke pemasok <?= esc($po['supplier_name']) ?> diajukan.</p>
          </div>
        </div>
      </li>
      <?php if (in_array($po['status'], ['approved', 'received'], true)): ?>
      <li class="timeline-event">
        <div class="timeline-event-icon bg-azure-lt text-azure">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= ! empty($po['updated_at']) ? date('d M Y H:i', strtotime($po['updated_at'])) : date('d M Y', strtotime($po['order_date'])) ?></div>
            <h4 class="mb-1">Disetujui Manajemen (ACC)</h4>
            <p class="text-secondary mb-0">Pesanan pembelian telah disetujui dan siap diterima oleh tim Gudang.</p>
          </div>
        </div>
      </li>
      <?php endif; ?>
      <?php if (! empty($receipts)): foreach ($receipts as $r): ?>
      <li class="timeline-event">
        <div class="timeline-event-icon bg-green-lt text-green">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= date('d M Y', strtotime($r['receipt_date'])) ?></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
              <span class="badge bg-primary-lt font-monospace px-2 py-1"><?= esc($r['gr_number']) ?></span>
              <span class="badge bg-success-lt">Tercatat di Gudang</span>
            </div>
            <p class="text-secondary mb-0">Barang masuk dari pemasok telah dicatat ke stok gudang melalui penerimaan <strong><?= esc($r['gr_number']) ?></strong>.</p>
          </div>
        </div>
      </li>
      <?php endforeach; endif; ?>
      <?php if ($po['status'] === 'received'): ?>
      <li class="timeline-event">
        <div class="timeline-event-icon bg-success-lt text-success">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12l2 2l4 -4"/><path d="M12 3a9 9 0 1 0 0 18a9 9 0 0 0 0 -18z"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= ! empty($po['updated_at']) ? date('d M Y H:i', strtotime($po['updated_at'])) : '-' ?></div>
            <h4 class="mb-1 text-success">PO Diterima Lengkap</h4>
            <p class="text-secondary mb-0">Seluruh item pesanan pembelian telah diterima di gudang.</p>
          </div>
        </div>
      </li>
      <?php elseif ($po['status'] === 'cancelled'): ?>
      <li class="timeline-event">
        <div class="timeline-event-icon bg-danger-lt text-danger">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= ! empty($po['updated_at']) ? date('d M Y H:i', strtotime($po['updated_at'])) : '-' ?></div>
            <h4 class="mb-1 text-danger">Pesanan Dibatalkan / Ditolak</h4>
            <p class="text-secondary mb-0">Pengajuan pembelian dihentikan dan tidak diproses lanjut.</p>
          </div>
        </div>
      </li>
      <?php endif; ?>
    </ul>
  </div>
</div>

<!-- Modal Konfirmasi & Catatan Pembatalan/Penolakan Pesanan Beli -->
<div class="modal modal-blur fade" id="modalCancelPO" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form action="/purchase-orders/cancel/<?= esc($po['id']) ?>" method="post">
        <div class="modal-header">
          <h5 class="modal-title text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
            <?= session()->get('user_role') === 'manajemen' ? 'Konfirmasi Penolakan Pesanan Beli' : 'Konfirmasi Pembatalan Pesanan' ?>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-secondary mb-3">
            Apakah Anda yakin ingin <?= session()->get('user_role') === 'manajemen' ? 'menolak pengajuan' : 'membatalkan' ?> pesanan beli <strong><?= esc($po['po_number']) ?></strong>? Tindakan ini akan menghentikan proses pengadaan dan tidak dapat diubah kembali.
          </p>
          <div class="mb-3">
            <label class="form-label required">Alasan <?= session()->get('user_role') === 'manajemen' ? 'Penolakan' : 'Pembatalan' ?></label>
            <textarea name="cancel_reason" class="form-control" rows="3" placeholder="<?= session()->get('user_role') === 'manajemen' ? 'Sebutkan alasan penolakan (misal: Anggaran operasional dialihkan, harga vendor kemahalan, dsb.)' : 'Sebutkan alasan pembatalan (misal: Supplier kehabisan stok barang, harga tidak sesuai, dsb.)' ?>" required></textarea>
            <div class="form-text">Alasan ini beserta nama akun Anda akan tercatat secara permanen di riwayat pesanan.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button>
          <button type="submit" class="btn btn-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
            <?= session()->get('user_role') === 'manajemen' ? 'Ya, Tolak Pengajuan PO' : 'Ya, Batalkan Pesanan' ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
