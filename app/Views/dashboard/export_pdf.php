<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan PDF — <?= esc($period['periodLabel']) ?> | PT Nusantara Distribusi</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 14mm 14mm 16mm 14mm;
    }
    * { box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
      font-size: 11px;
      line-height: 1.45;
      color: #0f172a;
      background: #e2e8f0;
      margin: 0;
      padding: 20px;
    }
    .toolbar {
      max-width: 210mm;
      margin: 0 auto 14px auto;
      background: #0f172a;
      color: #ffffff;
      padding: 10px 16px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
    }
    .toolbar-actions { display: flex; gap: 8px; }
    .btn {
      border: none;
      border-radius: 6px;
      padding: 7px 14px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-print { background: #2563eb; color: #fff; }
    .btn-close { background: #334155; color: #e2e8f0; }

    .sheet {
      max-width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      background: #ffffff;
      padding: 18mm 16mm;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
      border-radius: 4px;
    }

    /* Kop Surat */
    .letterhead {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 3px solid #0f172a;
      padding-bottom: 12px;
      margin-bottom: 16px;
      position: relative;
    }
    .letterhead::after {
      content: "";
      position: absolute;
      left: 0;
      right: 0;
      bottom: -6px;
      height: 1.5px;
      background: #f59e0b;
    }
    .company-name {
      font-size: 18px;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: 0.3px;
      margin: 0 0 2px 0;
    }
    .company-sub {
      font-size: 10px;
      color: #475569;
      margin: 0;
    }
    .doc-meta {
      text-align: right;
      font-size: 10px;
      color: #334155;
    }
    .doc-title {
      font-size: 13px;
      font-weight: 800;
      color: #1d4ed8;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin: 0 0 4px 0;
    }

    .note-box {
      background: #f8fafc;
      border-left: 3px solid #2563eb;
      padding: 8px 12px;
      margin-bottom: 14px;
      font-size: 10.5px;
      color: #334155;
    }

    .section {
      margin-bottom: 18px;
      page-break-inside: avoid;
    }
    .section-title {
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.4px;
      color: #0f172a;
      border-left: 4px solid #f59e0b;
      padding-left: 8px;
      margin: 0 0 8px 0;
    }

    /* KPI Grid */
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
    }
    .kpi-box {
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      padding: 9px 10px;
      background: #f8fafc;
    }
    .kpi-label {
      font-size: 9px;
      text-transform: uppercase;
      font-weight: 700;
      color: #64748b;
      margin-bottom: 3px;
    }
    .kpi-value {
      font-size: 13.5px;
      font-weight: 800;
      color: #0f172a;
      font-variant-numeric: tabular-nums;
    }
    .kpi-sub {
      font-size: 9.5px;
      color: #475569;
      margin-top: 2px;
    }

    /* Corporate Tables */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10.2px;
      font-variant-numeric: tabular-nums;
    }
    thead th {
      background: #0f172a;
      color: #ffffff;
      font-weight: 700;
      text-align: left;
      padding: 6px 8px;
      border: 1px solid #0f172a;
      font-size: 9.5px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    tbody td {
      padding: 5.5px 8px;
      border: 1px solid #cbd5e1;
      vertical-align: middle;
    }
    tbody tr:nth-child(even) {
      background: #f8fafc;
    }
    tfoot td {
      background: #eff6ff;
      font-weight: 800;
      padding: 6.5px 8px;
      border: 1px solid #93c5fd;
      color: #0f172a;
    }
    .text-end { text-align: right; }
    .text-center { text-align: center; }
    .mono { font-family: Consolas, monospace; }
    .badge {
      display: inline-block;
      padding: 1.5px 6px;
      border-radius: 4px;
      font-size: 9px;
      font-weight: 700;
      background: #e2e8f0;
      color: #1e293b;
    }
    .badge-warn { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-ok { background: #dcfce7; color: #166534; }

    /* Footer */
    .doc-footer {
      margin-top: 20px;
      padding-top: 8px;
      border-top: 1px dashed #cbd5e1;
      font-size: 9px;
      color: #64748b;
      display: flex;
      justify-content: space-between;
    }

    @media print {
      body { background: #ffffff; padding: 0; }
      .toolbar { display: none !important; }
      .sheet { box-shadow: none; padding: 0; margin: 0; width: 100%; min-height: auto; }
    }
  </style>
</head>
<body>
  <div class="toolbar">
    <div>
      <strong>Pratinjau Laporan PDF</strong> &mdash; Periode: <?= esc($period['periodLabel']) ?>
    </div>
    <div class="toolbar-actions">
      <button type="button" class="btn btn-print" onclick="window.print();">Simpan PDF / Cetak</button>
      <button type="button" class="btn btn-close" onclick="window.close();">Tutup</button>
    </div>
  </div>

  <div class="sheet">
    <div class="letterhead">
      <div>
        <h1 class="company-name">PT NUSANTARA DISTRIBUSI</h1>
        <p class="company-sub">Sistem Informasi Distribusi &amp; Manajemen Persediaan (Mini ERP)</p>
      </div>
      <div class="doc-meta">
        <div class="doc-title">Laporan Operasional &amp; Bisnis</div>
        <div><strong>Periode:</strong> <?= esc($period['periodLabel']) ?> (<?= esc($period['startDate']) ?> s/d <?= esc($period['endDate']) ?>)</div>
        <div><strong>Disiapkan oleh:</strong> <?= esc($userName) ?> (<?= esc(ucfirst($role)) ?>)</div>
        <div><strong>Tanggal Cetak:</strong> <?= date('d/m/Y H:i') ?> WIB</div>
      </div>
    </div>

    <?php if ($reportNote !== ''): ?>
      <div class="note-box">
        <strong>Catatan Laporan:</strong> <?= esc($reportNote) ?>
      </div>
    <?php endif; ?>

    <?php if (in_array('summary', $sections, true) && !empty($summary)): ?>
      <div class="section">
        <h2 class="section-title">1. Ringkasan Eksekutif</h2>
        <div class="kpi-grid">
          <?php if ($role === 'manajemen'): ?>
            <div class="kpi-box">
              <div class="kpi-label">Total Penjualan (SO)</div>
              <div class="kpi-value">Rp <?= number_format((float) $summary['so_total'], 0, ',', '.') ?></div>
              <div class="kpi-sub"><?= number_format((int) $summary['so_count']) ?> pesanan pelanggan</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-label">Total Pembelian (PO)</div>
              <div class="kpi-value">Rp <?= number_format((float) $summary['po_total'], 0, ',', '.') ?></div>
              <div class="kpi-sub"><?= number_format((int) $summary['po_count']) ?> pesanan pemasok</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-label">Nilai Aset Persediaan</div>
              <div class="kpi-value">Rp <?= number_format((float) $summary['inv_value'], 0, ',', '.') ?></div>
              <div class="kpi-sub"><?= number_format((int) $summary['total_units']) ?> unit total stok</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-label">Pengiriman Barang (DO)</div>
              <div class="kpi-value"><?= number_format((int) $summary['do_count']) ?> Surat Jalan</div>
              <div class="kpi-sub"><?= number_format((int) $summary['do_qty']) ?> unit terkirim</div>
            </div>
          <?php elseif ($role === 'sales'): ?>
            <div class="kpi-box">
              <div class="kpi-label">Total Penjualan (SO)</div>
              <div class="kpi-value">Rp <?= number_format((float) $summary['so_total'], 0, ',', '.') ?></div>
              <div class="kpi-sub"><?= number_format((int) $summary['so_count']) ?> pesanan pelanggan</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-label">Pengiriman Barang (DO)</div>
              <div class="kpi-value"><?= number_format((int) $summary['do_count']) ?> Surat Jalan</div>
              <div class="kpi-sub"><?= number_format((int) $summary['do_qty']) ?> unit terkirim</div>
            </div>
          <?php elseif ($role === 'purchasing'): ?>
            <div class="kpi-box">
              <div class="kpi-label">Total Pembelian (PO)</div>
              <div class="kpi-value">Rp <?= number_format((float) $summary['po_total'], 0, ',', '.') ?></div>
              <div class="kpi-sub"><?= number_format((int) $summary['po_count']) ?> pesanan pemasok</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-label">Nilai Aset Persediaan</div>
              <div class="kpi-value">Rp <?= number_format((float) $summary['inv_value'], 0, ',', '.') ?></div>
              <div class="kpi-sub"><?= number_format((int) $summary['total_units']) ?> unit total stok</div>
            </div>
          <?php else: ?>
            <div class="kpi-box">
              <div class="kpi-label">Total Jenis Produk</div>
              <div class="kpi-value"><?= number_format((int) $summary['sku_count']) ?> Produk</div>
              <div class="kpi-sub">Katalog aktif gudang</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-label">Total Stok Fisik</div>
              <div class="kpi-value"><?= number_format((int) $summary['total_units']) ?> Unit</div>
              <div class="kpi-sub">Seluruh gudang</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-label">Pengiriman Barang (DO)</div>
              <div class="kpi-value"><?= number_format((int) $summary['do_count']) ?> Surat Jalan</div>
              <div class="kpi-sub"><?= number_format((int) $summary['do_qty']) ?> unit terkirim</div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if (in_array('sales', $sections, true)): ?>
      <div class="section">
        <h2 class="section-title">Rincian Transaksi Penjualan (SO)</h2>
        <table>
          <thead>
            <tr>
              <th style="width: 32px;" class="text-center">No</th>
              <th style="width: 115px;">No. SO</th>
              <th style="width: 82px;">Tanggal</th>
              <th>Pelanggan</th>
              <th style="width: 55px;" class="text-center">Qty</th>
              <th style="width: 95px;" class="text-center">Status</th>
              <th style="width: 120px;" class="text-end">Total Nilai</th>
            </tr>
          </thead>
          <tbody>
            <?php $grandSales = 0; $grandSalesQty = 0; ?>
            <?php if (empty($salesRows)): ?>
              <tr><td colspan="7" class="text-center">Tidak ada transaksi penjualan pada periode ini.</td></tr>
            <?php else: foreach ($salesRows as $i => $r): $grandSales += (float) $r['total_amount']; $grandSalesQty += (int) $r['total_qty']; ?>
              <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td class="mono"><?= esc($r['so_number']) ?></td>
                <td><?= esc(date('d/m/Y', strtotime($r['order_date']))) ?></td>
                <td><?= esc($r['customer_name']) ?></td>
                <td class="text-center"><?= number_format((int) $r['total_qty']) ?></td>
                <td class="text-center"><span class="badge"><?= esc(status_label($r['status'])) ?></span></td>
                <td class="text-end">Rp <?= number_format((float) $r['total_amount'], 0, ',', '.') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <?php if (!empty($salesRows)): ?>
          <tfoot>
            <tr>
              <td colspan="4" class="text-end">TOTAL PENJUALAN PERIODE INI</td>
              <td class="text-center"><?= number_format($grandSalesQty) ?></td>
              <td></td>
              <td class="text-end">Rp <?= number_format($grandSales, 0, ',', '.') ?></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    <?php endif; ?>

    <?php if (in_array('purchase', $sections, true)): ?>
      <div class="section">
        <h2 class="section-title">Rincian Transaksi Pembelian (PO)</h2>
        <table>
          <thead>
            <tr>
              <th style="width: 32px;" class="text-center">No</th>
              <th style="width: 115px;">No. PO</th>
              <th style="width: 82px;">Tanggal</th>
              <th>Pemasok</th>
              <th style="width: 55px;" class="text-center">Qty</th>
              <th style="width: 95px;" class="text-center">Status</th>
              <th style="width: 120px;" class="text-end">Total Nilai</th>
            </tr>
          </thead>
          <tbody>
            <?php $grandPo = 0; $grandPoQty = 0; ?>
            <?php if (empty($poRows)): ?>
              <tr><td colspan="7" class="text-center">Tidak ada transaksi pembelian pada periode ini.</td></tr>
            <?php else: foreach ($poRows as $i => $r): $grandPo += (float) $r['total_amount']; $grandPoQty += (int) $r['total_qty']; ?>
              <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td class="mono"><?= esc($r['po_number']) ?></td>
                <td><?= esc(date('d/m/Y', strtotime($r['order_date']))) ?></td>
                <td><?= esc($r['supplier_name']) ?></td>
                <td class="text-center"><?= number_format((int) $r['total_qty']) ?></td>
                <td class="text-center"><span class="badge"><?= esc(status_label($r['status'])) ?></span></td>
                <td class="text-end">Rp <?= number_format((float) $r['total_amount'], 0, ',', '.') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
          <?php if (!empty($poRows)): ?>
          <tfoot>
            <tr>
              <td colspan="4" class="text-end">TOTAL PEMBELIAN PERIODE INI</td>
              <td class="text-center"><?= number_format($grandPoQty) ?></td>
              <td></td>
              <td class="text-end">Rp <?= number_format($grandPo, 0, ',', '.') ?></td>
            </tr>
          </tfoot>
          <?php endif; ?>
        </table>
      </div>
    <?php endif; ?>

    <?php if (in_array('top_products', $sections, true)): ?>
      <div class="section">
        <h2 class="section-title">Produk Terlaris Periode Ini</h2>
        <table>
          <thead>
            <tr>
              <th style="width: 32px;" class="text-center">No</th>
              <th style="width: 90px;">Kode</th>
              <th>Nama Produk</th>
              <th style="width: 90px;" class="text-center">Terjual</th>
              <th style="width: 130px;" class="text-end">Nilai Penjualan</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($topRows)): ?>
              <tr><td colspan="5" class="text-center">Belum ada data penjualan produk pada periode ini.</td></tr>
            <?php else: foreach ($topRows as $i => $r): ?>
              <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td class="mono"><?= esc($r['code']) ?></td>
                <td><?= esc($r['name']) ?></td>
                <td class="text-center"><?= number_format((int) $r['total_qty']) ?> <?= esc($r['unit']) ?></td>
                <td class="text-end">Rp <?= number_format((float) $r['total_sales'], 0, ',', '.') ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if (in_array('stock', $sections, true)): ?>
      <div class="section">
        <h2 class="section-title">Daftar Stok Produk &amp; Persediaan</h2>
        <table>
          <thead>
            <tr>
              <th style="width: 32px;" class="text-center">No</th>
              <th style="width: 85px;">Kode</th>
              <th>Nama Produk</th>
              <th>Kategori</th>
              <th style="width: 75px;" class="text-center">Stok</th>
              <?php if (!$isGudang): ?>
                <th style="width: 105px;" class="text-end">Harga Modal</th>
                <th style="width: 120px;" class="text-end">Nilai Aset</th>
              <?php else: ?>
                <th style="width: 80px;" class="text-center">Min. Stok</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php $totalAsset = 0; $totalStockUnits = 0; ?>
            <?php foreach ($stockRows as $i => $p):
              $lineVal = (int) $p['stock_qty'] * (float) $p['purchase_price'];
              $totalAsset += $lineVal;
              $totalStockUnits += (int) $p['stock_qty'];
            ?>
              <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td class="mono"><?= esc($p['code']) ?></td>
                <td><?= esc($p['name']) ?></td>
                <td><?= esc($p['category_name'] ?? '-') ?></td>
                <td class="text-center"><?= number_format((int) $p['stock_qty']) ?> <?= esc($p['unit']) ?></td>
                <?php if (!$isGudang): ?>
                  <td class="text-end">Rp <?= number_format((float) $p['purchase_price'], 0, ',', '.') ?></td>
                  <td class="text-end">Rp <?= number_format($lineVal, 0, ',', '.') ?></td>
                <?php else: ?>
                  <td class="text-center"><?= number_format((int) $p['min_stock']) ?> <?= esc($p['unit']) ?></td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="text-end">TOTAL PERSEDIAAN</td>
              <td class="text-center"><?= number_format($totalStockUnits) ?></td>
              <?php if (!$isGudang): ?>
                <td></td>
                <td class="text-end">Rp <?= number_format($totalAsset, 0, ',', '.') ?></td>
              <?php else: ?>
                <td></td>
              <?php endif; ?>
            </tr>
          </tfoot>
        </table>
      </div>
    <?php endif; ?>

    <?php if (in_array('low_stock', $sections, true)): ?>
      <div class="section">
        <h2 class="section-title">Peringatan Stok Menipis / Habis</h2>
        <table>
          <thead>
            <tr>
              <th style="width: 32px;" class="text-center">No</th>
              <th style="width: 85px;">Kode</th>
              <th>Nama Produk</th>
              <th style="width: 90px;" class="text-center">Stok Kini</th>
              <th style="width: 90px;" class="text-center">Batas Min.</th>
              <th style="width: 110px;" class="text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($lowRows)): ?>
              <tr><td colspan="6" class="text-center">Seluruh stok produk dalam kondisi aman.</td></tr>
            <?php else: foreach ($lowRows as $i => $p): ?>
              <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td class="mono"><?= esc($p['code']) ?></td>
                <td><?= esc($p['name']) ?></td>
                <td class="text-center"><?= number_format((int) $p['stock_qty']) ?> <?= esc($p['unit']) ?></td>
                <td class="text-center"><?= number_format((int) $p['min_stock']) ?> <?= esc($p['unit']) ?></td>
                <td class="text-center">
                  <?php if ((int) $p['stock_qty'] <= 0): ?>
                    <span class="badge badge-danger">Habis</span>
                  <?php else: ?>
                    <span class="badge badge-warn">Menipis</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <div class="doc-footer">
      <span>PT Nusantara Distribusi &mdash; Sistem Distribusi &amp; Persediaan</span>
      <span>Dicetak: <?= date('d/m/Y H:i') ?> WIB</span>
    </div>
  </div>

  <script>
    window.addEventListener('load', function () {
      setTimeout(function () { window.print(); }, 350);
    });
  </script>
</body>
</html>
