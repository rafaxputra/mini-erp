<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<form action="/purchase-orders/store" method="post">
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Pemasok</label><select name="supplier_id" class="form-select" required><option value="">Pilih pemasok</option><?php foreach ($suppliers as $s): ?><option value="<?= esc($s['id']) ?>"><?= esc($s['code']) ?> - <?= esc($s['name']) ?></option><?php endforeach; ?></select><small class="form-hint">Pemasok yang memasok barang untuk pesanan ini.</small></div>
    <div class="col-md-6 mb-3"><label class="form-label">Tanggal PO</label><input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
  </div>
  <div class="mb-3"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" placeholder="Contoh: Pesanan rutin bulanan, kirim ke gudang utama"></textarea></div>
  <h5>Detail Produk</h5>
  <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Produk</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th><th>Aksi</th></tr></thead>
  <tbody id="detailRows"><tr>
    <td><select name="product_id[]" class="form-select product-select" required><option value="">Pilih produk</option><?php foreach ($products as $p): ?><option value="<?= esc($p['id']) ?>" data-price="<?= esc($p['purchase_price']) ?>"><?= esc($p['code']) ?> - <?= esc($p['name']) ?></option><?php endforeach; ?></select></td>
    <td><input type="number" name="qty[]" class="form-control qty" min="1" value="1" required></td>
    <td><div class="input-group"><span class="input-group-text">Rp</span><input type="text" inputmode="numeric" name="unit_price[]" class="form-control price js-rupiah" placeholder="Contoh: 15000" required></div></td>
    <td class="subtotal">Rp 0</td>
    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove(); recalc();">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
      Hapus
    </button></td>
  </tr></tbody></table></div>
  <button type="button" class="btn btn-outline-secondary mb-3" id="addRowBtn">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
    Tambah Baris
  </button>
  <p><strong>Total: <span id="totalAmount">Rp 0</span></strong></p>
  <div class="mt-2">
    <button type="submit" class="btn btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h8l4 4v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
      Simpan PO
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
function bindPrice(input) {
  input.addEventListener('input', function () { formatRupiahInput(input); recalc(); });
}
function addRow() {
  const tbody = document.getElementById('detailRows');
  const row = document.createElement('tr');
  let opts = '<option value="">Pilih produk</option>';
  products.forEach(function (p) { opts += '<option value="' + p.id + '" data-price="' + p.price + '">' + p.code + ' - ' + p.name + '</option>'; });
  row.innerHTML = '<td><select name="product_id[]" class="form-select product-select" required>' + opts + '</select></td>'
    + '<td><input type="number" name="qty[]" class="form-control qty" min="1" value="1" required></td>'
    + '<td><div class="input-group"><span class="input-group-text">Rp</span><input type="text" inputmode="numeric" name="unit_price[]" class="form-control price js-rupiah" placeholder="Contoh: 15000" required></div></td>'
    + '<td class="subtotal">Rp 0</td>'
    + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove(); recalc();">Hapus</button></td>';
  tbody.appendChild(row);
  row.querySelector('.product-select').addEventListener('change', function () {
    const sel = this.options[this.selectedIndex];
    const priceEl = row.querySelector('.price');
    priceEl.value = sel.getAttribute('data-price') || '';
    formatRupiahInput(priceEl);
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
    recalc();
  });
});
recalc();
</script>
<?= $this->endSection() ?>
