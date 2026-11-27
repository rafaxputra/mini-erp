<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php $st = status_badge($so['status']); ?>

<?php
$soStatus = $so['status'];
$soSteps = [
  ['key' => 'draft',     'label' => 'Draf Pesanan'],
  ['key' => 'confirmed', 'label' => $soStatus === 'revision' ? 'Butuh Revisi' : 'Dikonfirmasi'],
  ['key' => 'delivered', 'label' => 'Terkirim (DO)'],
];
$soOrder = ['draft' => 0, 'revision' => 1, 'confirmed' => 1, 'delivered' => 2];
$curIdx = $soOrder[$soStatus] ?? 0;
$stepsColor = match ($soStatus) {
  'cancelled' => 'steps-red',
  'revision'  => 'steps-orange',
  default     => 'steps-green',
};
?>
<ul class="steps <?= $stepsColor ?> steps-counter my-3">
  <?php if ($soStatus === 'cancelled'): ?>
    <li class="step-item">Draf Pesanan</li>
    <li class="step-item active">Dibatalkan</li>
  <?php else: ?>
    <?php foreach ($soSteps as $idx => $step): ?>
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
          <span class="badge bg-dark-lt fs-3 px-2 py-1 font-monospace"><?= esc($so['so_number']) ?></span>
          <span class="badge <?= $st['badge'] ?> fs-4 px-2 py-1"><?= esc($st['label']) ?></span>
        </div>
        <div class="text-secondary small">Dibuat oleh: <strong class="text-dark"><?= esc($so['creator_name']) ?></strong></div>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <?php if ($so['status'] === 'draft' && session()->get('user_role') === 'sales'): ?>
          <form action="/sales-orders/confirm/<?= esc($so['id']) ?>" method="post" class="d-inline" data-confirm="Apakah Anda yakin ingin mengonfirmasi Pesanan Jual <strong><?= esc($so['so_number']) ?></strong>?" data-confirm-title="Konfirmasi Pesanan Jual" data-confirm-btn="Ya, Konfirmasi" data-confirm-type="primary">
            <button class="btn btn-success" type="submit">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
              Konfirmasi Pesanan
            </button>
          </form>
        <?php endif; ?>

        <?php if ($so['status'] === 'confirmed' && session()->get('user_role') === 'gudang'): ?>
          <a class="btn btn-primary" href="/deliveries/create?sales_order_id=<?= esc($so['id']) ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg>
            Proses Pengiriman Barang
          </a>
        <?php endif; ?>

        <?php
          $hasActiveDelivery = false;
          foreach (($deliveries ?? []) as $dlv) {
              if (($dlv['status'] ?? 'delivered') !== 'cancelled') {
                  $hasActiveDelivery = true;
                  break;
              }
          }
        ?>
        <?php if (in_array($so['status'], ['draft', 'confirmed', 'revision'], true) && session()->get('user_role') === 'sales'): ?>
          <?php if (! $hasActiveDelivery): ?>
            <a href="/sales-orders/edit/<?= esc($so['id']) ?>" class="btn <?= $so['status'] === 'revision' ? 'btn-warning' : 'btn-outline-primary' ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/><path d="M16 5l3 3"/></svg>
              Revisi / Edit Pesanan
            </a>
          <?php endif; ?>
          <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalCancelSO">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
            Batalkan Pesanan
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Parsing Catatan: Pisahkan Catatan SO dengan Alasan Pembatalan agar tidak dobel -->
    <?php
      $rawNotes = (string) ($so['notes'] ?? '');
      $cancelNote = '';
      $revisionNote = '';
      $initialNotes = [];
      if ($rawNotes !== '') {
          foreach (explode("\n", $rawNotes) as $line) {
              $trimmed = trim($line);
              $clean = ltrim($trimmed, '[');
              if (str_starts_with($clean, 'Ditolak') || str_starts_with($clean, 'Dibatalkan') || stripos($clean, 'alasan') !== false) {
                  $cancelNote .= ($cancelNote !== '' ? "\n" : '') . $trimmed;
              } elseif (str_starts_with($clean, 'Butuh Revisi')) {
                  $revisionNote .= ($revisionNote !== '' ? "\n" : '') . $trimmed;
              } elseif ($trimmed !== '') {
                  $initialNotes[] = $trimmed;
              }
          }
          if ($so['status'] === 'cancelled' && $cancelNote === '') {
              $cancelNote = $rawNotes;
              $initialNotes = [];
          }
      }
      $initialNotesStr = implode("\n", $initialNotes);
    ?>

    <!-- Banner Peringatan Jika Pesanan Butuh Revisi dari Gudang -->
    <?php if ($so['status'] === 'revision'): ?>
      <div class="alert alert-warning mb-3 d-flex align-items-start gap-2 border-warning" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon text-warning" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
        <div>
          <div class="fw-bold text-dark">Pesanan Ini Membutuhkan Revisi (Permintaan Pelanggan via Gudang)</div>
          <div class="small mt-1 text-dark" style="white-space: pre-line;"><?= esc($revisionNote ?: ($rawNotes ?: 'Silakan periksa dan revisi daftar barang pada pesanan ini.')) ?></div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Banner Peringatan Jika Pesanan Dibatalkan -->
    <?php if ($so['status'] === 'cancelled'): ?>
      <div class="alert alert-danger mb-3 d-flex align-items-start gap-2" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg>
        <div>
          <div class="fw-bold">Pesanan Jual Ini Telah Dibatalkan</div>
          <div class="small mt-1 text-danger-emphasis" style="white-space: pre-line;"><?= esc($cancelNote ?: ($rawNotes ?: 'Tidak ada catatan alasan pembatalan.')) ?></div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Ringkasan Informasi (Tabler Datagrid) -->
    <div class="datagrid">
      <div class="datagrid-item">
        <div class="datagrid-title">Pelanggan</div>
        <div class="datagrid-content">
          <div class="fw-bold text-dark"><?= esc($so['customer_name']) ?></div>
          <div class="text-secondary small font-monospace"><?= esc($so['customer_code']) ?></div>
        </div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Tanggal Pesanan</div>
        <div class="datagrid-content">
          <div class="fw-bold text-dark"><?= date('d M Y', strtotime($so['order_date'])) ?></div>
          <div class="text-secondary small font-monospace"><?= esc($so['order_date']) ?></div>
        </div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Gudang Pengirim</div>
        <div class="datagrid-content">
          <div class="fw-bold text-dark"><?= esc($so['warehouse_name'] ?? 'Gudang Utama') ?></div>
          <div class="text-secondary small">Gudang asal barang saat dikirim</div>
        </div>
      </div>
      <div class="datagrid-item">
        <div class="datagrid-title">Total Nilai Pesanan</div>
        <div class="datagrid-content">
          <span class="fw-bold fs-2 text-primary">Rp <?= number_format((float) $so['total_amount'], 0, ',', '.') ?></span>
        </div>
      </div>
    </div>

    <!-- Kotak Catatan SO (Hanya tampil jika ada catatan awal pemesanan) -->
    <?php if ($initialNotesStr !== ''): ?>
    <div class="mt-3 p-2 px-3 rounded bg-light border text-secondary small">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1 text-secondary" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 21v-13a3 3 0 0 1 3 -3h10a3 3 0 0 1 3 3v6a3 3 0 0 1 -3 3h-9l-4 4"/></svg>
      <strong>Catatan SO:</strong> <span style="white-space: pre-line;"><?= esc($initialNotesStr) ?></span>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Tabel Detail Item -->
<div class="card mb-3 border-0 shadow-sm">
  <div class="card-header bg-transparent py-3">
    <h3 class="card-title m-0 fw-bold">Detail Produk yang Dijual</h3>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 40px;">No</th>
          <th style="width: 120px;">Kode</th>
          <th>Nama Produk</th>
          <th class="text-center" style="width: 110px;">Jumlah Pesan</th>
          <th class="text-center" style="width: 120px;">Sudah Dikirim</th>
          <th class="text-center" style="width: 100px;">Sisa Kirim</th>
          <th class="text-center" style="width: 110px;">Stok Saat Ini</th>
          <th class="text-end" style="width: 140px;">Harga Satuan</th>
          <th class="text-end" style="width: 150px;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $idx => $it): ?>
        <?php $sisa = (int) $it['qty'] - (int) $it['delivered_qty']; ?>
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
            <?php if ((int) $it['delivered_qty'] > 0): ?>
              <span class="badge bg-green-lt fw-bold"><?= esc($it['delivered_qty']) ?> <span class="fw-normal"><?= esc($it['unit']) ?></span></span>
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
          <td class="text-center">
            <?php if ((int) $it['stock_qty'] > 0): ?>
              <span class="fw-medium text-dark"><?= esc($it['stock_qty']) ?></span> <span class="text-secondary small"><?= esc($it['unit']) ?></span>
            <?php else: ?>
              <span class="badge bg-danger-lt fw-bold">0</span>
            <?php endif; ?>
          </td>
          <td class="text-end">Rp <?= number_format((float) $it['unit_price'], 0, ',', '.') ?></td>
          <td class="text-end fw-bold text-dark">Rp <?= number_format((float) $it['subtotal'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light">
        <tr>
          <td colspan="8" class="text-end fw-bold fs-3">Total Keseluruhan:</td>
          <td class="text-end fw-bold fs-3 text-primary">Rp <?= number_format((float) $so['total_amount'], 0, ',', '.') ?></td>
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
            <div class="text-secondary float-end small"><?= ! empty($so['created_at']) ? date('d M Y H:i', strtotime($so['created_at'])) : date('d M Y', strtotime($so['order_date'])) ?></div>
            <h4 class="mb-1">Pesanan Jual Dibuat</h4>
            <p class="text-secondary mb-0">Dokumen <strong><?= esc($so['so_number']) ?></strong> untuk <?= esc($so['customer_name']) ?> tercatat sebagai draf.</p>
          </div>
        </div>
      </li>
      <?php if ($so['status'] === 'revision'): ?>
      <li class="timeline-event">
        <div class="timeline-event-icon bg-warning-lt text-warning">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= ! empty($so['updated_at']) ? date('d M Y H:i', strtotime($so['updated_at'])) : '-' ?></div>
            <h4 class="mb-1 text-warning">Diminta Revisi</h4>
            <p class="text-secondary mb-0">Pesanan dikembalikan ke tim Sales untuk penyesuaian sebelum diproses lanjut.</p>
          </div>
        </div>
      </li>
      <?php endif; ?>
      <?php if (in_array($so['status'], ['confirmed', 'delivered'], true)): ?>
      <li class="timeline-event">
        <div class="timeline-event-icon bg-azure-lt text-azure">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= ! empty($so['updated_at']) ? date('d M Y H:i', strtotime($so['updated_at'])) : date('d M Y', strtotime($so['order_date'])) ?></div>
            <h4 class="mb-1">Pesanan Dikonfirmasi</h4>
            <p class="text-secondary mb-0">Pesanan disetujui dan siap dijadwalkan pengirimannya dari <?= esc($so['warehouse_name'] ?? 'Gudang Utama') ?>.</p>
          </div>
        </div>
      </li>
      <?php endif; ?>
      <?php if (! empty($deliveries)): foreach ($deliveries as $d): $ds = $d['status'] ?? 'delivered'; $dsb = status_badge($ds); ?>
      <li class="timeline-event">
        <div class="timeline-event-icon <?= $ds === 'cancelled' ? 'bg-danger-lt text-danger' : ($ds === 'scheduled' ? 'bg-cyan-lt text-cyan' : 'bg-green-lt text-green') ?>">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= date('d M Y', strtotime($d['delivery_date'])) ?><?= ! empty($d['delivery_time']) ? ' &bull; ' . esc($d['delivery_time']) . ' WIB' : '' ?></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
              <span class="badge bg-primary-lt font-monospace px-2 py-1"><?= esc($d['delivery_number']) ?></span>
              <span class="badge <?= $dsb['badge'] ?>"><?= esc($ds === 'delivered' ? 'Terkirim dari Gudang' : $dsb['label']) ?></span>
            </div>
            <p class="text-secondary mb-0">Pengiriman barang dicatat melalui surat jalan <strong><?= esc($d['delivery_number']) ?></strong>.</p>
            <?php if ($ds === 'cancelled' && ! empty($d['cancel_reason'])): ?>
              <div class="text-danger small mt-1"><strong>Alasan Batal:</strong> <?= esc($d['cancel_reason']) ?></div>
            <?php endif; ?>
          </div>
        </div>
      </li>
      <?php endforeach; endif; ?>
      <?php if ($so['status'] === 'delivered'): ?>
      <li class="timeline-event">
        <div class="timeline-event-icon bg-success-lt text-success">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 12l2 2l4 -4"/><path d="M12 3a9 9 0 1 0 0 18a9 9 0 0 0 0 -18z"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= ! empty($so['updated_at']) ? date('d M Y H:i', strtotime($so['updated_at'])) : '-' ?></div>
            <h4 class="mb-1 text-success">Pesanan Terkirim &amp; Selesai</h4>
            <p class="text-secondary mb-0">Seluruh item pesanan telah selesai dikirim ke pelanggan.</p>
          </div>
        </div>
      </li>
      <?php elseif ($so['status'] === 'cancelled'): ?>
      <li class="timeline-event">
        <div class="timeline-event-icon bg-danger-lt text-danger">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
        </div>
        <div class="card timeline-event-card">
          <div class="card-body">
            <div class="text-secondary float-end small"><?= ! empty($so['updated_at']) ? date('d M Y H:i', strtotime($so['updated_at'])) : '-' ?></div>
            <h4 class="mb-1 text-danger">Pesanan Dibatalkan</h4>
            <p class="text-secondary mb-0">Proses pesanan jual dihentikan dan dibatalkan.</p>
          </div>
        </div>
      </li>
      <?php endif; ?>
    </ul>
  </div>
</div>

<!-- Modal Konfirmasi & Catatan Pembatalan Pesanan Jual -->
<div class="modal modal-blur fade" id="modalCancelSO" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <form action="/sales-orders/cancel/<?= esc($so['id']) ?>" method="post">
        <div class="modal-header">
          <h5 class="modal-title text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-danger" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
            Konfirmasi Pembatalan Pesanan
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-secondary mb-3">
            Apakah Anda yakin ingin membatalkan pesanan jual <strong><?= esc($so['so_number']) ?></strong>? Tindakan ini akan menghentikan proses penjualan dan tidak dapat diubah kembali.
          </p>
          <div class="mb-3">
            <label class="form-label required">Alasan Pembatalan</label>
            <textarea name="cancel_reason" class="form-control" rows="3" placeholder="Sebutkan alasan pembatalan (misal: Pelanggan membatalkan pesanan, stok tidak mencukupi, dsb.)" required></textarea>
            <div class="form-text">Alasan pembatalan dan nama staf akan tersimpan secara permanen pada catatan pesanan.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Kembali</button>
          <button type="submit" class="btn btn-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
            Ya, Batalkan Pesanan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
