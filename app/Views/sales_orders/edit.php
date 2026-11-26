<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="alert alert-info mb-3 border-info shadow-sm">
  <div class="d-flex align-items-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon text-info" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12.01" y2="8"/><polyline points="11 12 12 12 12 16 13 16"/></svg>
    <div class="small">
      Anda sedang merevisi <strong><?= esc($so['so_number']) ?></strong> (Status: <span class="badge bg-azure-lt"><?= esc(status_label($so['status'])) ?></span>). Saat disimpan, alokasi stok lama otomatis dikembalikan dan dihitung ulang sesuai daftar barang baru.
    </div>
  </div>
</div>

<form action="/sales-orders/update/<?= esc($so['id']) ?>" method="post" id="soForm">
  <!-- Kartu Informasi Pesanan -->
  <div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
      <h3 class="card-title m-0 fw-bold">Revisi Informasi Pesanan Jual</h3>
      <span class="badge bg-dark-lt font-monospace fs-4"><?= esc($so['so_number']) ?></span>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label required">Pelanggan</label>
          <select name="customer_id" class="form-select js-searchable" required>
            <option value="">-- Ketik / Pilih Pelanggan --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= esc($c['id']) ?>" <?= (int) old('customer_id', $so['customer_id']) === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['code']) ?> - <?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label required">Tanggal Pesanan</label>
          <input type="date" name="order_date" class="form-control" value="<?= esc(old('order_date', $so['order_date'])) ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label required">Gudang Pengirim</label>
          <select name="warehouse_id" id="warehouseSelect" class="form-select" required>
            <?php foreach ($warehouses as $w): ?>
              <option value="<?= esc($w['id']) ?>" <?= (int) old('warehouse_id', $so['warehouse_id']) === (int) $w['id'] ? 'selected' : '' ?>><?= esc($w['name']) ?> (<?= esc($w['code']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Catatan Tambahan</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Revisi pesanan pelanggan, tambah/ganti item"><?= esc(old('notes', $so['notes'] ?? '')) ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Kartu Daftar Item Produk -->
  <div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
      <h3 class="card-title m-0 fw-bold">Daftar Produk yang Dijual</h3>
      <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
        Tambah Baris Produk <kbd class="erp-kbd ms-1">Alt+T</kbd>
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-vcenter table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Pilih Produk</th>
            <th class="text-center" style="width: 120px;">Stok Gudang</th>
            <th class="text-center" style="width: 120px;">Stok Total</th>
            <th class="text-center" style="width: 140px;">Jumlah</th>
            <th style="width: 200px;">Harga Satuan Jual</th>
            <th class="text-end" style="width: 170px;">Subtotal</th>
            <th style="width: 60px;" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody id="detailRows">
          <?php foreach ($items as $it): ?>
          <tr>
            <td>
              <select name="product_id[]" class="form-select product-select js-searchable" required>
                <option value="">-- Ketik / Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= esc($p['id']) ?>" data-price="<?= (int) $p['selling_price'] ?>" <?= (int) $it['product_id'] === (int) $p['id'] ? 'selected' : '' ?>>
                    <?= esc($p['code']) ?> - <?= esc($p['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td>
              <input type="text" class="form-control text-center readonly-stat-box wh-stock-box" value="-" readonly tabindex="-1" title="Stok tersedia di gudang terpilih">
            </td>
            <td>
              <input type="text" class="form-control text-center readonly-stat-box total-stock-box" value="-" readonly tabindex="-1" title="Total stok di seluruh gudang">
            </td>
            <td><input type="number" name="qty[]" class="form-control qty text-center" min="1" value="<?= esc((int) $it['qty']) ?>" style="min-width: 80px; font-weight: 600; font-size: 0.95rem;" required></td>
            <td>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" inputmode="numeric" name="unit_price[]" class="form-control price font-monospace js-rupiah" value="<?= number_format((float) $it['unit_price'], 0, ',', '.') ?>" placeholder="0" required>
              </div>
            </td>
            <td class="subtotal text-end font-monospace">Rp 0</td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this);" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-transparent d-flex justify-content-end align-items-center gap-2">
      <span class="text-secondary">Total Nilai Pesanan:</span>
      <strong class="fs-4 text-primary font-monospace" id="totalAmount">Rp 0</strong>
    </div>
  </div>

  <div class="d-flex align-items-center gap-2 pt-2 border-top">
    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h8l4 4v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
      Simpan Revisi SO
    </button>
    <a class="btn btn-outline-secondary" href="/sales-orders/show/<?= esc($so['id']) ?>">Batal</a>
  </div>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const products = <?= json_encode(array_map(function ($p) { return ['id' => (int) $p['id'], 'code' => $p['code'], 'name' => $p['name'], 'price' => (int) $p['selling_price'], 'stock' => (int) $p['stock_qty']]; }, $products)) ?>;
const warehouseStocks = <?= json_encode($warehouseStocks ?? []) ?>;

function getWarehouseStock(whId, prodId) {
  if (warehouseStocks[whId] && warehouseStocks[whId][prodId] !== undefined) {
    return parseInt(warehouseStocks[whId][prodId]) || 0;
  }
  return 0;
}

function getSelectedWarehouseInfo() {
  const whSelect = document.getElementById('warehouseSelect');
  const whId = parseInt(whSelect.value) || 0;
  const opt = whSelect.options[whSelect.selectedIndex];
  const whName = opt ? opt.text.split('(')[0].trim() : 'Gudang Terpilih';
  return { id: whId, name: whName };
}

function buildProductOptionText(p, wh) {
  return `${p.code} - ${p.name}`;
}

function updateRowProductInfo(row) {
  const sel = row.querySelector('.product-select');
  const whBox = row.querySelector('.wh-stock-box');
  const totalBox = row.querySelector('.total-stock-box');
  if (!sel || !whBox || !totalBox) return;

  whBox.classList.remove('is-stock-ok', 'is-stock-empty');
  if (!sel.value) {
    whBox.value = '-';
    totalBox.value = '-';
    return;
  }
  const wh = getSelectedWarehouseInfo();
  const pid = parseInt(sel.value);
  const prod = products.find(p => p.id === pid);
  if (!prod) {
    whBox.value = '-';
    totalBox.value = '-';
    return;
  }
  const whStock = getWarehouseStock(wh.id, pid);
  whBox.value = whStock.toLocaleString('id-ID');
  whBox.classList.add(whStock > 0 ? 'is-stock-ok' : 'is-stock-empty');
  totalBox.value = (parseInt(prod.stock, 10) || 0).toLocaleString('id-ID');
}

function checkQtyStockWarning(row) {
  const sel = row.querySelector('.product-select');
  const qtyInput = row.querySelector('.qty');
  if (!sel || !qtyInput || !sel.value) return;
  const wh = getSelectedWarehouseInfo();
  const pid = parseInt(sel.value);
  const whStock = getWarehouseStock(wh.id, pid);
  const qty = parseFloat(qtyInput.value) || 0;

  let warnEl = row.querySelector('.stock-warning');
  if (!warnEl) {
    warnEl = document.createElement('div');
    warnEl.className = 'stock-warning text-danger small mt-1';
    qtyInput.parentNode.appendChild(warnEl);
  }

  if (qty > whStock) {
    const shortage = qty - Math.max(0, whStock);
    warnEl.className = 'stock-warning text-warning small mt-1 fw-semibold';
    warnEl.textContent = `Stok di ${wh.name} hanya ${whStock}. Kekurangan ${shortage} unit akan otomatis dikirim sebagai notif pengadaan ke Purchasing.`;
    qtyInput.classList.remove('is-invalid');
  } else {
    warnEl.textContent = '';
    qtyInput.classList.remove('is-invalid');
  }
}

function syncAllProductStockLabels() {
  const wh = getSelectedWarehouseInfo();
  document.querySelectorAll('#detailRows .product-select').forEach(function (sel) {
    Array.from(sel.options).forEach(function (opt) {
      if (!opt.value) return;
      const pid = parseInt(opt.value);
      const prod = products.find(p => p.id === pid);
      if (!prod) return;
      const whStock = getWarehouseStock(wh.id, pid);
      opt.text = buildProductOptionText(prod, wh);
      opt.setAttribute('data-stock', whStock);
    });

    const row = sel.closest('tr');
    if (row) {
      updateRowProductInfo(row);
      checkQtyStockWarning(row);
    }
  });
}

function formatRupiahInput(el) {
  let val = (el.value || '').toString().trim();
  if (val.indexOf('.') !== -1 && val.indexOf(',') === -1) {
    const parts = val.split('.');
    if (parts.length === 2 && (parts[1] === '00' || parts[1] === '0')) {
      val = parts[0];
    }
  }
  const digits = val.replace(/\D/g, '');
  el.value = digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
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
    checkQtyStockWarning(row);
  });
  document.getElementById('totalAmount').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
function bindPrice(input) {
  input.addEventListener('input', function () { formatRupiahInput(input); recalc(); });
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

      if (selectedValues.includes(opt.value) && opt.value !== currentVal) {
        opt.disabled = true;
        opt.hidden = true;
      } else {
        opt.disabled = false;
        opt.hidden = false;
      }
    });
    if (sel.tomselect) {
      sel.tomselect.sync();
    }
  });

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

function addRow() {
  const tbody = document.getElementById('detailRows');
  const row = document.createElement('tr');
  const wh = getSelectedWarehouseInfo();
  let opts = '<option value="">-- Pilih Produk --</option>';
  products.forEach(function (p) {
    const whStock = getWarehouseStock(wh.id, p.id);
    const label = buildProductOptionText(p, wh);
    opts += '<option value="' + p.id + '" data-price="' + Math.round(p.price) + '" data-stock="' + whStock + '">' + label + '</option>';
  });
  row.innerHTML = '<td><select name="product_id[]" class="form-select product-select js-searchable" required>' + opts + '</select></td>'
    + '<td><input type="text" class="form-control text-center readonly-stat-box wh-stock-box" value="-" readonly tabindex="-1" title="Stok tersedia di gudang terpilih"></td>'
    + '<td><input type="text" class="form-control text-center readonly-stat-box total-stock-box" value="-" readonly tabindex="-1" title="Total stok di seluruh gudang"></td>'
    + '<td><input type="number" name="qty[]" class="form-control qty text-center" min="1" value="1" style="min-width: 80px; font-weight: 600; font-size: 0.95rem;" required></td>'
    + '<td><div class="input-group"><span class="input-group-text">Rp</span><input type="text" inputmode="numeric" name="unit_price[]" class="form-control price font-monospace js-rupiah" placeholder="0" required></div></td>'
    + '<td class="subtotal text-end font-monospace">Rp 0</td>'
    + '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this);" title="Hapus baris"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg></button></td>';
  tbody.appendChild(row);
  const newSelect = row.querySelector('.product-select');
  if (typeof window.initSearchableSelect === 'function') {
    window.initSearchableSelect(newSelect);
  }
  row.querySelector('.product-select').addEventListener('change', function () {
    const val = this.value;
    const sel = Array.from(this.options).find(function (o) { return o.value === val; });
    const priceEl = row.querySelector('.price');
    const rawPrice = sel ? (sel.getAttribute('data-price') || '0') : '0';
    priceEl.value = parseInt(rawPrice, 10) || 0;
    formatRupiahInput(priceEl);
    updateRowProductInfo(row);
    updateProductDropdowns();
    recalc();
  });
  row.querySelectorAll('.qty').forEach(function (el) { el.addEventListener('input', recalc); });
  bindPrice(row.querySelector('.price'));
  updateProductDropdowns();
  recalc();
  if (newSelect && newSelect.tomselect) {
    newSelect.tomselect.focus();
  }
}

document.addEventListener('keydown', function (e) {
  if (e.altKey && (e.key === 't' || e.key === 'T')) {
    e.preventDefault();
    const btn = document.getElementById('addRowBtn');
    if (btn && !btn.disabled) btn.click();
  } else if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
    e.preventDefault();
    const form = document.getElementById('soEditForm') || document.querySelector('form[action*="/sales-orders/update"]');
    if (form) form.requestSubmit();
  }
});

document.getElementById('warehouseSelect').addEventListener('change', function () {
  syncAllProductStockLabels();
  updateProductDropdowns();
  recalc();
});

document.getElementById('addRowBtn').addEventListener('click', addRow);
document.querySelectorAll('#detailRows .qty').forEach(function (el) { el.addEventListener('input', recalc); });
document.querySelectorAll('#detailRows .price').forEach(function (el) { bindPrice(el); });
document.querySelectorAll('#detailRows .product-select').forEach(function (sel) {
  sel.addEventListener('change', function () {
    const row = this.closest('tr');
    const val = this.value;
    const opt = Array.from(this.options).find(function (o) { return o.value === val; });
    const priceEl = row.querySelector('.price');
    const rawPrice = opt ? (opt.getAttribute('data-price') || '0') : '0';
    priceEl.value = parseInt(rawPrice, 10) || 0;
    formatRupiahInput(priceEl);
    updateRowProductInfo(row);
    updateProductDropdowns();
    recalc();
  });
});
syncAllProductStockLabels();
updateProductDropdowns();
recalc();
</script>
<?= $this->endSection() ?>
