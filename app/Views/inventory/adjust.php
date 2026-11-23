<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent py-3">
        <h3 class="card-title m-0 fw-bold">Penyesuaian (Koreksi) Stok Produk</h3>
      </div>
      <div class="card-body">
        <form action="/inventory/adjust" method="post" id="adjustForm">
          <!-- 1. Pilih Gudang Terlebih Dahulu -->
          <div class="mb-3">
            <label class="form-label required fw-semibold">1. Pilih Gudang Lokasi Barang</label>
            <select name="warehouse_id" id="adjustWarehouse" class="form-select" required autofocus>
              <option value="">-- Pilih Gudang Terlebih Dahulu --</option>
              <?php foreach ($warehouses as $w): ?>
                <option value="<?= esc($w['id']) ?>" <?= $w['is_default'] ? 'selected' : '' ?>><?= esc($w['name']) ?> (<?= esc($w['code']) ?>) <?= $w['is_default'] ? '- [Gudang Utama]' : '' ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Pilih gudang fisik tempat stok barang ini berada.</div>
          </div>

          <!-- 2. Pilih Produk dengan Opsi Filter -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label required fw-semibold mb-0">2. Pilih Produk yang Akan Dikoreksi</label>
              <label class="form-check form-check-inline mb-0 small text-secondary">
                <input class="form-check-input" type="checkbox" id="filterAvailableOnly">
                <span class="form-check-label">Hanya produk yang memiliki stok di gudang ini (> 0)</span>
              </label>
            </div>
            <select name="product_id" id="adjustProduct" class="form-select" required>
              <option value="">-- Pilih Gudang Di Atas Terlebih Dahulu --</option>
            </select>
            <div class="form-text">Daftar produk otomatis menampilkan stok aktual yang tersedia pada gudang yang dipilih.</div>
          </div>

          <!-- Kartu Info Stok Interaktif -->
          <div id="stockInfoCard" class="card bg-body-tertiary border mb-3 d-none">
            <div class="card-body p-3">
              <div class="row g-2 align-items-center text-center text-sm-start">
                <div class="col-sm-4 border-end-sm">
                  <div class="text-secondary small">Stok di Gudang Ini:</div>
                  <div class="fs-2 fw-bold text-primary font-monospace mt-1" id="currentWhStock">0</div>
                  <div class="text-muted small" id="stockWhName">Gudang</div>
                </div>
                <div class="col-sm-4 border-end-sm">
                  <div class="text-secondary small">Total Seluruh Gudang:</div>
                  <div class="fs-2 fw-bold text-dark font-monospace mt-1" id="totalGlobalStock">0</div>
                  <div class="text-muted small">Semua cabang gabungan</div>
                </div>
                <div class="col-sm-4">
                  <div class="text-secondary small">Proyeksi Setelah Koreksi:</div>
                  <div class="fs-2 fw-bold font-monospace mt-1" id="projectedWhStock">0</div>
                  <div class="text-muted small" id="projectionStatus">Stok tetap</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Peringatan Jika Stok di Gudang Kosong & Dipilih OUT -->
          <div id="stockWarningAlert" class="alert alert-warning mb-3 d-none" role="alert">
            <div class="d-flex align-items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon text-warning" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
              <div>
                <div class="fw-bold">Stok di Gudang Ini Kosong (0 unit)</div>
                <div class="small">Anda tidak bisa mengurangi stok (Keluar) dari gudang ini. Silakan ubah arah koreksi menjadi <strong>Masuk (tambah stok)</strong> atau pilih gudang lain yang memiliki saldo barang.</div>
              </div>
            </div>
          </div>

          <!-- 3. Arah Koreksi dan Jumlah -->
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label required fw-semibold">3. Arah Koreksi</label>
              <select name="direction" id="adjustDirection" class="form-select" required>
                <option value="IN">Masuk (Tambah stok ke gudang ini)</option>
                <option value="OUT">Keluar (Kurangi stok dari gudang ini)</option>
              </select>
              <div class="form-text">Pilih <strong>Masuk</strong> jika barang bertambah, <strong>Keluar</strong> jika barang berkurang (rusak/hilang).</div>
            </div>
            <div class="col-md-6">
              <label class="form-label required fw-semibold" id="qtyLabel">Jumlah Unit yang Dikoreksi</label>
              <input type="number" name="qty" id="adjustQty" class="form-control" min="1" placeholder="1" required>
              <div class="form-text" id="qtyHelpText">Jumlah unit fisik yang ditambah atau dikurangi.</div>
            </div>
          </div>

          <!-- 4. Alasan Koreksi -->
          <div class="mb-3">
            <label class="form-label required fw-semibold">4. Alasan Koreksi</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Selisih stock opname fisik, barang rusak saat bongkar, atau mutasi fisik antar rak" required></textarea>
            <div class="form-text">Alasan wajib dicatat agar histori mutasi stok dapat diaudit sewaktu-waktu.</div>
          </div>

          <div class="d-flex align-items-center gap-2 pt-2 border-top">
            <button type="submit" id="submitBtn" class="btn btn-primary d-inline-flex align-items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 4h8l4 4v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2"/><path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M14 4l0 4l-6 0l0 -4"/></svg>
              Simpan Koreksi Stok
            </button>
            <a class="btn btn-outline-secondary" href="/inventory">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const products = <?= json_encode($products) ?>;
  const warehouseStocks = <?= json_encode($warehouseStocks ?? []) ?>;

  const whSelect = document.getElementById('adjustWarehouse');
  const prodSelect = document.getElementById('adjustProduct');
  const filterAvailable = document.getElementById('filterAvailableOnly');
  const dirSelect = document.getElementById('adjustDirection');
  const qtyInput = document.getElementById('adjustQty');
  const stockInfoCard = document.getElementById('stockInfoCard');
  const warningAlert = document.getElementById('stockWarningAlert');
  const submitBtn = document.getElementById('submitBtn');

  const elCurrentWhStock = document.getElementById('currentWhStock');
  const elTotalGlobalStock = document.getElementById('totalGlobalStock');
  const elProjectedWhStock = document.getElementById('projectedWhStock');
  const elStockWhName = document.getElementById('stockWhName');
  const elProjectionStatus = document.getElementById('projectionStatus');
  const elQtyLabel = document.getElementById('qtyLabel');

  function getStockInWh(prodId, whId) {
    if (!warehouseStocks[whId]) return 0;
    return parseInt(warehouseStocks[whId][prodId] || 0, 10);
  }

  function getProduct(prodId) {
    return products.find(p => parseInt(p.id, 10) === parseInt(prodId, 10));
  }

  function updateProductDropdown() {
    const whId = parseInt(whSelect.value, 10);
    const selectedProdId = parseInt(prodSelect.value, 10);
    prodSelect.innerHTML = '';

    if (!whId) {
      prodSelect.innerHTML = '<option value="">-- Pilih Gudang Di Atas Terlebih Dahulu --</option>';
      stockInfoCard.classList.add('d-none');
      warningAlert.classList.add('d-none');
      return;
    }

    const whText = whSelect.options[whSelect.selectedIndex].text.split('(')[0].trim();
    let opts = '<option value="">-- Pilih Produk --</option>';
    let availableCount = 0;

    products.forEach(function (p) {
      const stockInThisWh = getStockInWh(p.id, whId);
      const isSelected = selectedProdId === parseInt(p.id, 10);
      const onlyAvailable = filterAvailable.checked;

      if (stockInThisWh > 0) availableCount++;
      if (onlyAvailable && stockInThisWh <= 0) return;

      const badgeText = stockInThisWh > 0 
        ? `[Stok di ${whText}: ${stockInThisWh} ${p.unit}] (Total: ${p.stock_qty})`
        : `[Stok di ${whText}: 0 ${p.unit}] (Total: ${p.stock_qty})`;

      opts += `<option value="${p.id}" ${isSelected ? 'selected' : ''}>${p.code} - ${p.name} - ${badgeText}</option>`;
    });

    if (filterAvailable.checked && availableCount === 0) {
      opts = `<option value="">-- Tidak ada produk dengan stok > 0 di ${whText} --</option>`;
    }

    prodSelect.innerHTML = opts;
    updateInfoCard();
  }

  function updateInfoCard() {
    const whId = parseInt(whSelect.value, 10);
    const prodId = parseInt(prodSelect.value, 10);
    const direction = dirSelect.value;
    const qty = parseInt(qtyInput.value || 0, 10);

    if (!whId || !prodId) {
      stockInfoCard.classList.add('d-none');
      warningAlert.classList.add('d-none');
      submitBtn.disabled = false;
      return;
    }

    const prod = getProduct(prodId);
    if (!prod) return;

    const whText = whSelect.options[whSelect.selectedIndex].text.split('(')[0].trim();
    const currentWhStock = getStockInWh(prodId, whId);
    const totalGlobal = parseInt(prod.stock_qty || 0, 10);

    stockInfoCard.classList.remove('d-none');
    elStockWhName.textContent = whText;
    elCurrentWhStock.textContent = `${currentWhStock} ${prod.unit}`;
    elTotalGlobalStock.textContent = `${totalGlobal} ${prod.unit}`;

    if (direction === 'OUT') {
      elQtyLabel.textContent = `Jumlah Keluar (Maksimal: ${currentWhStock} ${prod.unit})`;
      qtyInput.max = currentWhStock;

      if (currentWhStock <= 0) {
        warningAlert.classList.remove('d-none');
        submitBtn.disabled = true;
      } else {
        warningAlert.classList.add('d-none');
        submitBtn.disabled = false;
      }

      const projected = currentWhStock - qty;
      elProjectedWhStock.textContent = `${Math.max(0, projected)} ${prod.unit}`;
      if (projected < 0) {
        elProjectedWhStock.className = 'fs-2 fw-bold font-monospace mt-1 text-danger';
        elProjectionStatus.textContent = 'Melebihi saldo gudang!';
        submitBtn.disabled = true;
      } else {
        elProjectedWhStock.className = 'fs-2 fw-bold font-monospace mt-1 text-warning';
        elProjectionStatus.textContent = `Berkurang ${qty} ${prod.unit}`;
      }
    } else {
      elQtyLabel.textContent = 'Jumlah Masuk (Tambah ke Gudang)';
      qtyInput.removeAttribute('max');
      warningAlert.classList.add('d-none');
      submitBtn.disabled = false;

      const projected = currentWhStock + qty;
      elProjectedWhStock.textContent = `${projected} ${prod.unit}`;
      elProjectedWhStock.className = 'fs-2 fw-bold font-monospace mt-1 text-success';
      elProjectionStatus.textContent = `Bertambah ${qty} ${prod.unit}`;
    }
  }

  whSelect.addEventListener('change', updateProductDropdown);
  filterAvailable.addEventListener('change', updateProductDropdown);
  prodSelect.addEventListener('change', updateInfoCard);
  dirSelect.addEventListener('change', updateInfoCard);
  qtyInput.addEventListener('input', updateInfoCard);

  // Inisialisasi awal saat halaman dibuka
  if (whSelect.value) {
    updateProductDropdown();
  }
});
</script>

<?= $this->endSection() ?>
