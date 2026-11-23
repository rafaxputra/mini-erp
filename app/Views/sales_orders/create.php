<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php if (empty($customers) || empty($products)): ?>
  <div class="alert alert-warning mb-3">
    <div class="d-flex">
      <div>
        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
      </div>
      <div>
        <div class="fw-bold">Data Master Belum Lengkap</div>
        <div class="text-secondary small">
          <?php if (empty($customers)): ?>
            Anda belum mendaftarkan data Pelanggan. Silakan buat pelanggan di menu <a href="/customers/create">Tambah Pelanggan</a> terlebih dahulu.<br>
          <?php endif; ?>
          <?php if (empty($products)): ?>
            Anda belum memiliki produk terdaftar. Silakan buat produk di menu <a href="/products/create">Tambah Produk</a> terlebih dahulu.
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<form action="/sales-orders/store" method="post" id="soForm">
  <!-- Kartu Informasi Pesanan -->
  <div class="card mb-3 border-0 shadow-sm">
    <div class="card-header bg-transparent py-3">
      <h3 class="card-title m-0 fw-bold">Informasi Pesanan Jual</h3>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label required">Pelanggan</label>
          <select name="customer_id" class="form-select" required autofocus>
            <option value="">-- Pilih Pelanggan --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= esc($c['id']) ?>"><?= esc($c['code']) ?> - <?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Pelanggan yang membeli barang pada pesanan ini.</div>
        </div>
        <div class="col-md-3">
          <label class="form-label required">Tanggal Pesanan</label>
          <input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label required">Gudang Pengirim</label>
          <select name="warehouse_id" class="form-select" required>
            <?php foreach ($warehouses as $w): ?>
              <option value="<?= esc($w['id']) ?>" <?= $w['is_default'] ? 'selected' : '' ?>><?= esc($w['name']) ?> (<?= esc($w['code']) ?>)</option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Gudang asal barang saat dikirim.</div>
        </div>
        <div class="col-12">
          <label class="form-label">Catatan Tambahan</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Pesanan pelanggan rutin, kirim secepatnya"></textarea>
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
        Tambah Baris Produk
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-vcenter table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Pilih Produk</th>
            <th style="width: 130px;">Jumlah</th>
            <th style="width: 220px;">Harga Satuan Jual</th>
            <th class="text-end" style="width: 200px;">Subtotal</th>
            <th style="width: 60px;" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody id="detailRows">
          <tr>
            <td>
              <select name="product_id[]" class="form-select product-select" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= esc($p['id']) ?>" data-price="<?= esc($p['selling_price']) ?>"><?= esc($p['code']) ?> - <?= esc($p['name']) ?> (stok <?= esc($p['stock_qty']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="number" name="qty[]" class="form-control qty text-center" min="1" value="1" required></td>
            <td>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" inputmode="numeric" name="unit_price[]" class="form-control price font-monospace js-rupiah" placeholder="0" required>
              </div>
            </td>
            <td class="subtotal text-end font-monospace">Rp 0</td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this);" title="Hapus baris">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
              </button>
            </td>
          </tr>
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
      Simpan Pesanan Jual
    </button>
    <a class="btn btn-outline-secondary" href="/sales-orders">Batal</a>
  </div>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const products = <?= json_encode(array_map(function ($p) { return ['id' => $p['id'], 'code' => $p['code'], 'name' => $p['name'], 'price' => (float) $p['selling_price'], 'stock' => (int) $p['stock_qty']]; }, $products)) ?>;
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
  let opts = '<option value="">-- Pilih Produk --</option>';
  products.forEach(function (p) { opts += '<option value="' + p.id + '" data-price="' + p.price + '">' + p.code + ' - ' + p.name + ' (stok ' + p.stock + ')</option>'; });
  row.innerHTML = '<td><select name="product_id[]" class="form-select product-select" required>' + opts + '</select></td>'
    + '<td><input type="number" name="qty[]" class="form-control qty text-center" min="1" value="1" required></td>'
    + '<td><div class="input-group"><span class="input-group-text">Rp</span><input type="text" inputmode="numeric" name="unit_price[]" class="form-control price font-monospace js-rupiah" placeholder="0" required></div></td>'
    + '<td class="subtotal text-end font-monospace">Rp 0</td>'
    + '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this);" title="Hapus baris"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg></button></td>';
  tbody.appendChild(row);
  row.querySelector('.product-select').addEventListener('change', function () {
    const sel = this.options[this.selectedIndex];
    const priceEl = row.querySelector('.price');
    priceEl.value = sel.getAttribute('data-price') || '';
    formatRupiahInput(priceEl);
    updateProductDropdowns();
    recalc();
  });
  row.querySelectorAll('.qty').forEach(function (el) { el.addEventListener('input', recalc); });
  bindPrice(row.querySelector('.price'));
  recalc();
}
document.getElementById('addRowBtn').addEventListener('click', addRow);
document.querySelectorAll('#detailRows .qty').forEach(function (el) { el.addEventListener('input', recalc); });
document.querySelectorAll('#detailRows .price').forEach(function (el) { bindPrice(el); });
document.querySelectorAll('#detailRows .product-select').forEach(function (sel) {
  sel.addEventListener('change', function () {
    const row = this.closest('tr');
    const opt = this.options[this.selectedIndex];
    const priceEl = row.querySelector('.price');
    priceEl.value = opt.getAttribute('data-price') || '';
    formatRupiahInput(priceEl);
    updateProductDropdowns();
    recalc();
  });
});
updateProductDropdowns();
recalc();
</script>
<?= $this->endSection() ?>
