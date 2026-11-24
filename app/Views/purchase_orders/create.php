<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php if (! empty($existingActivePo)): ?>
  <div class="alert alert-warning mb-3 border-warning shadow-sm">
    <div class="d-flex align-items-center">
      <div class="me-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-warning" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
      </div>
      <div>
        <div class="fw-bold">Peringatan: Produk Ini Sudah Ada Pesanan Berjalan (Double Order Guard)</div>
        <div class="small">Produk yang Anda pilih sudah terdaftar di pesanan <strong><?= esc($existingActivePo['po_number']) ?></strong> (Status: <span class="badge bg-warning-lt fw-bold"><?= esc(ucfirst($existingActivePo['status'])) ?></span>, Jumlah: <strong><?= esc($existingActivePo['ordered_qty']) ?> unit</strong>). Pastikan pesanan tambahan ini memang diperlukan. <a href="/purchase-orders/show/<?= esc($existingActivePo['id']) ?>" class="fw-semibold text-primary" target="_blank">Buka PO Terkait &rarr;</a></div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if (empty($suppliers) || empty($products)): ?>
  <div class="alert alert-warning mb-3">
    <div class="d-flex">
      <div>
        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
      </div>
      <div>
        <div class="fw-bold">Data Master Belum Lengkap</div>
        <div class="text-secondary small">
          <?php if (empty($suppliers)): ?>
            Anda belum mendaftarkan data Pemasok. Silakan buat pemasok di menu <a href="/suppliers/create">Tambah Pemasok</a> terlebih dahulu.<br>
          <?php endif; ?>
          <?php if (empty($products)): ?>
            Anda belum memiliki produk terdaftar. Silakan buat produk di menu <a href="/products/create">Tambah Produk</a> terlebih dahulu.
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<form action="/purchase-orders/store" method="post" id="poForm">
  <!-- Kartu Informasi Pesanan -->
  <div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-transparent py-3">
      <h3 class="card-title m-0 fw-bold">Informasi Pesanan Beli</h3>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-7">
          <label class="form-label required">Pemasok</label>
          <select name="supplier_id" class="form-select" required>
            <option value="">-- Pilih Pemasok --</option>
            <?php foreach ($suppliers as $s): ?>
              <option value="<?= esc($s['id']) ?>"><?= esc($s['code']) ?> - <?= esc($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Pemasok yang menyediakan barang untuk pesanan ini.</div>
        </div>
        <div class="col-md-5">
          <label class="form-label required">Tanggal Pesanan</label>
          <input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          <div class="form-text text-muted"><span class="badge bg-secondary-lt">Info</span> Gudang penyimpanan fisik ditentukan oleh petugas saat Penerimaan Barang (GR).</div>
        </div>
        <div class="col-12">
          <label class="form-label">Catatan Tambahan</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Pesanan rutin berkala ke vendor"></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Kartu Daftar Item Produk -->
  <div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
      <h3 class="card-title m-0 fw-bold">Daftar Produk yang Dipesan</h3>
      <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
        Tambah Baris Produk
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-vcenter table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Pilih Produk</th>
            <th style="width: 130px;">Jumlah</th>
            <th style="width: 220px;">Harga Satuan Beli</th>
            <th class="text-end" style="width: 200px;">Subtotal</th>
            <th style="width: 60px;" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody id="detailRows">
          <?php
            $initProductId = $selectedProductId ?? 0;
            $initQty = $suggestedQty ?? 1;
            $initPrice = 0;
            if ($initProductId) {
                foreach ($products as $p) {
                    if ((int)$p['id'] === (int)$initProductId) {
                        $initPrice = (float) $p['purchase_price'];
                        break;
                    }
                }
            }
            $initSubtotal = $initPrice * $initQty;
          ?>
          <tr>
            <td>
              <select name="product_id[]" class="form-select product-select" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= esc($p['id']) ?>" 
                    data-price="<?= esc($p['purchase_price']) ?>"
                    data-unit="<?= esc($p['unit']) ?>"
                    data-stock="<?= (int) $p['stock_qty'] ?>"
                    data-min-stock="<?= (int) $p['min_stock'] ?>"
                    data-max-stock="<?= (int) $p['max_stock'] ?>"
                    data-on-order="<?= (int) $p['on_order_qty'] ?>"
                    <?= ($initProductId && (int)$p['id'] === (int)$initProductId) ? 'selected' : '' ?>>
                    <?= esc($p['code']) ?> - <?= esc($p['name']) ?> (Fisik: <?= esc($p['stock_qty']) ?><?= $p['max_stock'] > 0 ? ' / Max: ' . esc($p['max_stock']) : '' ?><?= $p['on_order_qty'] > 0 ? ', On-Order: ' . esc($p['on_order_qty']) : '' ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="product-info-note small mt-1 text-muted"></div>
            </td>
            <td style="vertical-align: top;">
              <div class="input-group">
                <input type="number" name="qty[]" class="form-control qty text-center" min="1" value="<?= esc($initQty) ?>" required>
                <span class="input-group-text unit-label text-muted small">unit</span>
              </div>
              <div class="qty-warning small text-danger mt-1 fw-semibold" style="display:none;"></div>
            </td>
            <td style="vertical-align: top;">
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" inputmode="numeric" name="unit_price[]" class="form-control price js-rupiah" placeholder="0" value="<?= $initPrice > 0 ? number_format($initPrice, 0, ',', '.') : '' ?>" required>
              </div>
            </td>
            <td class="text-end fw-bold subtotal text-dark" style="vertical-align: top; padding-top: 12px;"><?= $initSubtotal > 0 ? 'Rp ' . number_format($initSubtotal, 0, ',', '.') : 'Rp 0' ?></td>
            <td class="text-center" style="vertical-align: top;">
              <button type="button" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus baris" onclick="removeRow(this)">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
              </button>
            </td>
          </tr>
        </tbody>
        <tfoot class="table-light">
          <tr>
            <td colspan="3" class="text-end fw-bold fs-3">Total Estimasi Nilai PO:</td>
            <td class="text-end fw-bold fs-2 text-primary" id="totalAmount"><?= $initSubtotal > 0 ? 'Rp ' . number_format($initSubtotal, 0, ',', '.') : 'Rp 0' ?></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="d-flex align-items-center gap-2">
    <button type="submit" class="btn btn-primary px-4">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h8l4 4v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
      Simpan & Buat Pesanan Beli
    </button>
    <a class="btn btn-secondary" href="/purchase-orders">Batal</a>
  </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const products = <?= json_encode(array_map(function ($p) { return ['id' => $p['id'], 'code' => $p['code'], 'name' => $p['name'], 'price' => (float) $p['purchase_price']]; }, $products)) ?>;

function formatRupiahInput(el) {
  const digits = el.value.replace(/\D/g, '');
  el.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function priceValue(el) {
  return parseFloat((el.value || '').replace(/\./g, '')) || 0;
}

function recalc() {
  let total = 0;
  document.querySelectorAll('#detailRows tr').forEach(function (row) {
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const price = priceValue(row.querySelector('.price'));
    const sub = qty * price;
    row.querySelector('.subtotal').textContent = 'Rp ' + sub.toLocaleString('id-ID');
    total += sub;
  });
  document.getElementById('totalAmount').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

function updateProductDropdowns() {
  const selects = document.querySelectorAll('#detailRows .product-select');
  const selectedValues = [];

  selects.forEach(function (sel) {
    if (sel.value) {
      selectedValues.push(sel.value);
    }
  });

  selects.forEach(function (sel) {
    const currentVal = sel.value;
    Array.from(sel.options).forEach(function (opt) {
      if (!opt.value) {
        opt.disabled = false;
        opt.hidden = false;
        return;
      }

      // Sembunyikan dan matikan opsi yang sudah dipilih di baris lain
      if (selectedValues.includes(opt.value) && opt.value !== currentVal) {
        opt.disabled = true;
        opt.hidden = true;
      } else {
        opt.disabled = false;
        opt.hidden = false;
      }
    });
  });

  // Kunci tombol tambah jika seluruh produk katalog sudah dimasukkan
  const addBtn = document.getElementById('addRowBtn');
  if (addBtn) {
    const totalCatalogProducts = products.length;
    if (selects.length >= totalCatalogProducts) {
      addBtn.disabled = true;
      addBtn.title = 'Seluruh produk katalog sudah dipilih';
    } else {
      addBtn.disabled = false;
      addBtn.title = '';
    }
  }
}

function validateRowStock(row) {
  const sel = row.querySelector('.product-select');
  const opt = sel.options[sel.selectedIndex];
  const infoEl = row.querySelector('.product-info-note');
  const warnEl = row.querySelector('.qty-warning');
  const unitLabel = row.querySelector('.unit-label');
  const qtyInput = row.querySelector('.qty');

  if (!opt || !opt.value) {
    if (infoEl) infoEl.innerHTML = '';
    if (warnEl) { warnEl.style.display = 'none'; warnEl.textContent = ''; }
    if (unitLabel) unitLabel.textContent = 'unit';
    qtyInput.classList.remove('is-invalid');
    return;
  }

  const unit = opt.getAttribute('data-unit') || 'unit';
  const stock = parseInt(opt.getAttribute('data-stock') || '0', 10);
  const minStock = parseInt(opt.getAttribute('data-min-stock') || '0', 10);
  const maxStock = parseInt(opt.getAttribute('data-max-stock') || '0', 10);
  const onOrder = parseInt(opt.getAttribute('data-on-order') || '0', 10);
  const qty = parseInt(qtyInput.value || '0', 10);

  if (unitLabel) unitLabel.textContent = unit;

  let infoHtml = `Stok fisik: <strong>${stock} ${unit}</strong>`;
  if (maxStock > 0) {
    infoHtml += ` &bull; Kapasitas max: <strong>${maxStock} ${unit}</strong>`;
  }
  if (onOrder > 0) {
    infoHtml += ` &bull; <span class="badge bg-warning-lt">PO Aktif: ${onOrder} ${unit}</span>`;
  }
  if (infoEl) infoEl.innerHTML = infoHtml;

  if (maxStock > 0) {
    const totalProjected = stock + onOrder;
    const availableQuota = Math.max(0, maxStock - totalProjected);

    if (totalProjected >= maxStock) {
      if (warnEl) {
        warnEl.style.display = 'block';
        warnEl.textContent = `Kapasitas penuh! Sudah ${totalProjected} / ${maxStock} ${unit}. Tidak dapat dipesan.`;
      }
      qtyInput.classList.add('is-invalid');
    } else if (qty > availableQuota) {
      if (warnEl) {
        warnEl.style.display = 'block';
        warnEl.textContent = `Melebihi max stok! Sisa kuota pemesanan: ${availableQuota} ${unit}.`;
      }
      qtyInput.classList.add('is-invalid');
    } else {
      if (warnEl) { warnEl.style.display = 'none'; warnEl.textContent = ''; }
      qtyInput.classList.remove('is-invalid');
    }
  } else {
    if (warnEl) { warnEl.style.display = 'none'; warnEl.textContent = ''; }
    qtyInput.classList.remove('is-invalid');
  }
}

function bindRowEvents(row) {
  const sel = row.querySelector('.product-select');
  const priceInput = row.querySelector('.price');
  const qtyInput = row.querySelector('.qty');

  sel.addEventListener('change', function () {
    const opt = sel.options[sel.selectedIndex];
    const defPrice = opt ? (opt.getAttribute('data-price') || '0') : '0';
    priceInput.value = parseInt(defPrice, 10).toLocaleString('id-ID').replace(/,/g, '.');
    updateProductDropdowns();
    validateRowStock(row);
    recalc();
  });

  priceInput.addEventListener('input', function () {
    formatRupiahInput(priceInput);
    recalc();
  });

  qtyInput.addEventListener('input', function () {
    validateRowStock(row);
    recalc();
  });

  validateRowStock(row);
}

function removeRow(btn) {
  const tbody = document.getElementById('detailRows');
  if (tbody.querySelectorAll('tr').length > 1) {
    btn.closest('tr').remove();
    updateProductDropdowns();
    recalc();
  } else {
    alert('Pesanan harus memiliki minimal 1 produk.');
  }
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('#detailRows tr').forEach(bindRowEvents);
  updateProductDropdowns();
  recalc();

  document.getElementById('addRowBtn').addEventListener('click', function () {
    const tbody = document.getElementById('detailRows');
    const totalCatalogProducts = products.length;
    if (tbody.querySelectorAll('tr').length >= totalCatalogProducts) {
      alert('Semua produk dalam katalog sudah dimasukkan ke dalam pesanan.');
      return;
    }

    const firstRow = tbody.querySelector('tr');
    const newRow = firstRow.cloneNode(true);

    newRow.querySelector('.product-select').value = '';
    newRow.querySelector('.qty').value = '1';
    newRow.querySelector('.qty').classList.remove('is-invalid');
    newRow.querySelector('.price').value = '';
    newRow.querySelector('.subtotal').textContent = 'Rp 0';
    newRow.querySelector('.product-info-note').innerHTML = '';
    const warn = newRow.querySelector('.qty-warning');
    if (warn) { warn.style.display = 'none'; warn.textContent = ''; }

    tbody.appendChild(newRow);
    bindRowEvents(newRow);
    updateProductDropdowns();
  });

  document.getElementById('poForm').addEventListener('submit', function (e) {
    const hasInvalid = document.querySelector('#detailRows .is-invalid');
    if (hasInvalid) {
      e.preventDefault();
      alert('Terdapat jumlah pemesanan yang melebihi batas stok maksimum produk. Silakan sesuaikan jumlah pesanan terlebih dahulu.');
      return false;
    }

    document.querySelectorAll('.js-rupiah').forEach(function (inp) {
      inp.value = inp.value.replace(/\./g, '');
    });
  });
});
</script>
<?= $this->endSection() ?>
