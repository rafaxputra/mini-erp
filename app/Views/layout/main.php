<!DOCTYPE html>
<html lang="id" data-bs-navbar-theme="primary">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title ?? 'Mini ERP') ?> - Mini ERP PT Nusantara Distribusi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.7.5/dist/css/tabler.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head>
<body>
<?php if (session()->get('user_id')): ?>
<?php $role = session()->get('user_role'); ?>
<?php
$currentPath = uri_string();
$activeGroup = '';
if (preg_match('#^(categories|units|products|suppliers|customers|warehouses)(/|$)#', $currentPath)) $activeGroup = 'master';
elseif (preg_match('#^(purchase-orders|goods-receipts)(/|$)#', $currentPath)) $activeGroup = 'pembelian';
elseif (preg_match('#^(sales-orders|deliveries)(/|$)#', $currentPath)) $activeGroup = 'penjualan';
elseif (preg_match('#^(inventory)(/|$)#', $currentPath)) $activeGroup = 'persediaan';
elseif (preg_match('#^(users|roles)(/|$)#', $currentPath)) $activeGroup = 'pengaturan';

$navActive = static function (string $prefix) use ($currentPath): string {
    if ($prefix === 'inventory') {
        return $currentPath === 'inventory' ? 'active' : '';
    }
    return ($prefix === $currentPath || str_starts_with($currentPath, $prefix . '/')) ? 'active' : '';
};
$backSegments = explode('/', trim($currentPath, '/'));
$backUrl = count($backSegments) > 1 ? '/' . $backSegments[0] : null;

$moduleLabels = [
    'categories' => 'Kategori',
    'units' => 'Satuan',
    'products' => 'Produk',
    'suppliers' => 'Pemasok',
    'customers' => 'Pelanggan',
    'warehouses' => 'Gudang',
    'users' => 'Pengguna',
    'roles' => 'Peran',
    'purchase-orders' => 'Pesanan Pembelian',
    'goods-receipts' => 'Penerimaan Barang',
    'sales-orders' => 'Pesanan Penjualan',
    'deliveries' => 'Pengiriman Barang',
    'inventory' => 'Persediaan',
    'reports' => 'Laporan',
];

$breadcrumbs = [];
if ($currentPath !== '' && $currentPath !== 'dashboard') {
    $breadcrumbs[] = ['title' => 'Dashboard', 'url' => '/dashboard', 'active' => false];
    $mod = $backSegments[0] ?? '';
    $modLabel = $moduleLabels[$mod] ?? ucfirst($mod);

    if (count($backSegments) === 1) {
        $breadcrumbs[] = [
            'title' => $modLabel,
            'url' => '/' . $mod,
            'active' => true,
        ];
    } else {
        $breadcrumbs[] = [
            'title' => $modLabel,
            'url' => '/' . $mod,
            'active' => false,
        ];

        $pageTitle = $title ?? '';
        if ($pageTitle === '' || $pageTitle === 'Mini ERP' || $pageTitle === $modLabel) {
            $action = $backSegments[1] ?? '';
            $actionMap = ['create' => 'Tambah', 'edit' => 'Ubah', 'show' => 'Detail'];
            $pageTitle = ($actionMap[$action] ?? ucfirst($action)) . ' ' . $modLabel;
            if (isset($backSegments[2]) && is_numeric($backSegments[2])) {
                $pageTitle .= ' #' . $backSegments[2];
            }
        }

        $breadcrumbs[] = [
            'title' => $pageTitle,
            'url' => '',
            'active' => true,
        ];
    }
}
?>
<?php $roleLabel = ['admin' => 'Admin', 'purchasing' => 'Staff Purchasing', 'gudang' => 'Staff Gudang', 'sales' => 'Staff Sales', 'manajemen' => 'Manajemen'][$role] ?? $role; ?>
<?php
$userName = (string) (session()->get('user_name') ?? 'Pengguna');
$nameParts = array_filter(preg_split('/\s+/', trim($userName)));
$initials = '';
foreach (array_slice($nameParts, 0, 2) as $part) {
    $initials .= mb_strtoupper(mb_substr($part, 0, 1));
}
if ($initials === '') {
    $initials = 'U';
}

$navBadges = ['so_revision' => 0, 'po_draft' => 0, 'do_scheduled' => 0, 'restock_open' => 0, 'low_stock' => 0];
try {
    $navDb = \Config\Database::connect();
    if (in_array($role, ['admin', 'sales', 'manajemen'], true)) {
        $navBadges['so_revision'] = (int) ($navDb->query("SELECT COUNT(DISTINCT so_number) AS c FROM sales_orders WHERE status = 'revision'")->getRow('c') ?? 0);
    }
    if (in_array($role, ['admin', 'purchasing', 'manajemen'], true)) {
        $navBadges['po_draft'] = (int) ($navDb->query("SELECT COUNT(DISTINCT po_number) AS c FROM purchase_orders WHERE status = 'draft'")->getRow('c') ?? 0);
        $navBadges['restock_open'] = (int) ($navDb->query("SELECT COUNT(*) AS c FROM restock_alerts WHERE status = 'open'")->getRow('c') ?? 0);
    }
    if (in_array($role, ['admin', 'gudang', 'manajemen'], true)) {
        $navBadges['do_scheduled'] = (int) ($navDb->query("SELECT COUNT(DISTINCT delivery_number) AS c FROM deliveries WHERE status = 'scheduled'")->getRow('c') ?? 0);
    }
    if (in_array($role, ['admin', 'gudang', 'purchasing', 'manajemen'], true)) {
        $navBadges['low_stock'] = (int) ($navDb->query("SELECT COUNT(*) AS c FROM products WHERE stock_qty <= min_stock")->getRow('c') ?? 0);
    }
} catch (\Throwable $e) {
    // Abaikan jika tabel belum tersedia
}
$totalNotifCount = $navBadges['so_revision'] + $navBadges['po_draft'] + $navBadges['do_scheduled'] + $navBadges['restock_open'] + ($navBadges['low_stock'] > 0 ? 1 : 0);
?>
<script>
if (localStorage.getItem('erp_sidebar_collapsed') === '1' && window.innerWidth >= 992) {
  document.body.classList.add('sidebar-collapsed');
}
</script>
<div class="page">
  <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle sidebar navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="navbar-brand">
        <a href="/dashboard" aria-label="PT Nusantara Distribusi">
          <img src="<?= base_url('img/logo.png') ?>" width="32" height="32" alt="PT Nusantara Distribusi" class="navbar-brand-image">
          <span class="navbar-brand-text">PT Nusantara Distribusi</span>
        </a>
      </div>
      <div class="collapse navbar-collapse" id="sidebar-menu">
        <ul class="navbar-nav pt-lg-2">
          <li class="nav-item">
            <a class="nav-link <?= ($currentPath === 'dashboard' || $currentPath === '') ? 'active' : '' ?>" href="/dashboard" title="Dashboard">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg></span>
              <span class="nav-link-title">Dashboard</span>
            </a>
          </li>

          <li class="sidebar-section-label">Data Master</li>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('products') ?>" href="/products" title="Produk">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/><path d="M12 12l8 -4.5"/><path d="M12 12l0 9"/><path d="M12 12l-8 -4.5"/></svg></span>
              <span class="nav-link-title">Produk</span>
            </a>
          </li>
          <?php if ($role === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('categories') ?>" href="/categories" title="Kategori">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h6v6h-6z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6h-6z"/><path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/></svg></span>
              <span class="nav-link-title">Kategori</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('units') ?>" href="/units" title="Satuan">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 3l4 4l-14 14l-4 -4z"/><path d="M16 7l-1.5 -1.5"/><path d="M13 10l-1.5 -1.5"/><path d="M10 13l-1.5 -1.5"/><path d="M7 16l-1.5 -1.5"/></svg></span>
              <span class="nav-link-title">Satuan</span>
            </a>
          </li>
          <?php endif; ?>
          <?php if (in_array($role, ['admin', 'purchasing', 'manajemen'], true)): ?>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('suppliers') ?>" href="/suppliers" title="Pemasok">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0"/><path d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l2 4"/><path d="M5 21l0 -10.15"/><path d="M19 21l0 -10.15"/></svg></span>
              <span class="nav-link-title">Pemasok</span>
            </a>
          </li>
          <?php endif; ?>
          <?php if (in_array($role, ['admin', 'sales', 'manajemen'], true)): ?>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('customers') ?>" href="/customers" title="Pelanggan">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg></span>
              <span class="nav-link-title">Pelanggan</span>
            </a>
          </li>
          <?php endif; ?>
          <?php if (in_array($role, ['admin', 'gudang', 'manajemen'], true)): ?>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('warehouses') ?>" href="/warehouses" title="Gudang">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21v-13l9 -4l9 4v13"/><path d="M13 13h4v8h-10v-6h6"/><path d="M13 21v-9a1 1 0 0 0 -1 -1h-2a1 1 0 0 0 -1 1v3"/></svg></span>
              <span class="nav-link-title">Gudang</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($role === 'gudang'): ?>
            <li class="sidebar-section-label">Operasional Gudang</li>
            <li class="nav-item">
              <a class="nav-link <?= $navActive('goods-receipts') ?>" href="/goods-receipts" title="Terima Barang (GR)">
                <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3v12"/><path d="M8 11l4 4l4 -4"/><path d="M8 5h-4a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2 -2v-10a2 2 0 0 0 -2 -2h-4"/></svg></span>
                <span class="nav-link-title">Terima Barang (GR)</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $navActive('deliveries') ?>" href="/deliveries" title="Kirim Barang (DO)">
                <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"/></svg></span>
                <span class="nav-link-title d-flex align-items-center justify-content-between w-100">
                  <span>Kirim Barang (DO)</span>
                  <?php if ($navBadges['do_scheduled'] > 0): ?><span class="badge bg-azure text-white ms-2" title="Dijadwalkan"><?= $navBadges['do_scheduled'] ?></span><?php endif; ?>
                </span>
              </a>
            </li>
          <?php else: ?>
            <?php if (in_array($role, ['admin', 'purchasing', 'manajemen'], true)): ?>
              <li class="sidebar-section-label">Pembelian</li>
              <li class="nav-item">
                <a class="nav-link <?= $navActive('purchase-orders') ?>" href="/purchase-orders" title="Pesanan Pembelian (PO)">
                  <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg></span>
                  <span class="nav-link-title d-flex align-items-center justify-content-between w-100">
                    <span>Pesanan Pembelian (PO)</span>
                    <?php if ($navBadges['po_draft'] > 0): ?><span class="badge bg-amber text-dark ms-2"><?= $navBadges['po_draft'] ?></span><?php endif; ?>
                  </span>
                </a>
              </li>
              <?php if (in_array($role, ['admin', 'manajemen'], true)): ?>
              <li class="nav-item">
                <a class="nav-link <?= $navActive('goods-receipts') ?>" href="/goods-receipts" title="Terima Barang (GR)">
                  <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3v12"/><path d="M8 11l4 4l4 -4"/><path d="M8 5h-4a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2 -2v-10a2 2 0 0 0 -2 -2h-4"/></svg></span>
                  <span class="nav-link-title">Terima Barang (GR)</span>
                </a>
              </li>
              <?php endif; ?>
            <?php endif; ?>

            <?php if (in_array($role, ['admin', 'sales', 'manajemen'], true)): ?>
              <li class="sidebar-section-label">Penjualan</li>
              <li class="nav-item">
                <a class="nav-link <?= $navActive('sales-orders') ?>" href="/sales-orders" title="Pesanan Penjualan (SO)">
                  <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304"/><path d="M9 11v-5a3 3 0 0 1 6 0v5"/></svg></span>
                  <span class="nav-link-title d-flex align-items-center justify-content-between w-100">
                    <span>Pesanan Penjualan (SO)</span>
                    <?php if ($navBadges['so_revision'] > 0): ?><span class="badge bg-warning text-dark ms-2" title="Butuh Revisi"><?= $navBadges['so_revision'] ?></span><?php endif; ?>
                  </span>
                </a>
              </li>
              <?php if (in_array($role, ['admin', 'manajemen'], true)): ?>
              <li class="nav-item">
                <a class="nav-link <?= $navActive('deliveries') ?>" href="/deliveries" title="Kirim Barang (DO)">
                  <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M5 17h-2v-11a1 1 0 0 1 1 -1h9v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5"/></svg></span>
                  <span class="nav-link-title d-flex align-items-center justify-content-between w-100">
                    <span>Kirim Barang (DO)</span>
                    <?php if ($navBadges['do_scheduled'] > 0): ?><span class="badge bg-azure text-white ms-2" title="Dijadwalkan"><?= $navBadges['do_scheduled'] ?></span><?php endif; ?>
                  </span>
                </a>
              </li>
              <?php endif; ?>
            <?php endif; ?>
          <?php endif; ?>

          <li class="sidebar-section-label">Persediaan</li>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('inventory') ?>" href="/inventory" title="Stok Produk">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h16v16h-16z"/><path d="M4 12h16"/><path d="M12 4v16"/></svg></span>
              <span class="nav-link-title">Stok Produk</span>
            </a>
          </li>
          <?php if (in_array($role, ['admin', 'gudang', 'purchasing', 'manajemen'], true)): ?>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('inventory/movements') ?>" href="/inventory/movements" title="Mutasi Stok">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8v4l3 3"/><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5"/></svg></span>
              <span class="nav-link-title">Mutasi Stok</span>
            </a>
          </li>
          <?php endif; ?>

          <?php if ($role === 'admin'): ?>
          <li class="sidebar-section-label">Pengaturan</li>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('users') ?>" href="/users" title="Pengguna">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg></span>
              <span class="nav-link-title">Pengguna</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $navActive('roles') ?>" href="/roles" title="Peran &amp; Akses">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/></svg></span>
              <span class="nav-link-title">Peran &amp; Akses</span>
            </a>
          </li>
          <?php endif; ?>
        </ul>
        <div class="navbar-side mt-auto pt-2 border-top border-secondary border-opacity-25">
          <ul class="navbar-nav pb-2">
            <li class="nav-item">
              <a class="nav-link text-danger-subtle" href="/auth/logout">
                <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" > <path stroke="none" d="M0 0h24v24H0z" fill="none" /> <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /> <path d="M9 12h12l-3 -3" /> <path d="M18 15l3 -3" /> </svg></span>
                <span class="nav-link-title">Keluar (Logout)</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </aside>
  <div class="page-wrapper">
    <!-- Sticky Glass Top Utility Bar -->
    <header class="erp-topbar d-print-none">
      <div class="container-xl d-flex align-items-center justify-content-between gap-2 px-0">
        <div class="d-flex align-items-center gap-2">
          <button type="button" id="sidebarCollapseBtn" class="btn btn-sm btn-ghost-secondary d-none d-lg-inline-flex align-items-center justify-content-center" title="Ciutkan / Lebarkan Sidebar" style="width:34px;height:34px;padding:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
          </button>
          <span class="d-none d-sm-inline-block text-secondary small fw-semibold">
            <?= esc($roleLabel) ?> &middot; <span class="text-dark">PT Nusantara Distribusi</span>
          </span>
        </div>

        <div class="d-flex align-items-center gap-3 ms-auto">
          <?php
            $cmdSearchShort = match ($role) {
                'sales'      => 'Cari Produk, SO, Pelanggan...',
                'purchasing' => 'Cari Produk, PO, Pemasok...',
                'gudang'     => 'Cari Produk, DO, GR...',
                default      => 'Cari Produk, SO, PO...',
            };
            $cmdSearchLong = match ($role) {
                'sales'      => 'Ketik nama produk, nomor SO, atau nama pelanggan...',
                'purchasing' => 'Ketik nama produk, nomor PO, atau nama pemasok...',
                'gudang'     => 'Ketik nama produk, nomor pengiriman (DO), atau barang masuk (GR)...',
                default      => 'Ketik nama produk, nomor SO/PO, pelanggan, atau pemasok...',
            };
          ?>
          <button type="button" id="erpCmdTrigger" class="erp-cmd-trigger" data-bs-toggle="modal" data-bs-target="#erpCommandModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <span class="flex-grow-1 text-start d-none d-md-inline"><?= esc($cmdSearchShort) ?></span>
            <span class="flex-grow-1 text-start d-inline d-md-none">Cari...</span>
          </button>

          <div class="d-none d-sm-flex align-items-center gap-2 ps-3 border-start">
            <span class="avatar avatar-sm avatar-user-badge"><?= esc($initials) ?></span>
            <div class="lh-1">
              <div class="small fw-bold text-dark"><?= esc($userName) ?></div>
              <div class="role-badge-text mt-1"><?= esc($roleLabel) ?></div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <?php if ($currentPath !== '' && $currentPath !== 'dashboard'): ?>
    <div class="page-header d-print-none mb-3">
      <div class="container-xl">
        <div class="row g-2 align-items-center">
          <div class="col">
            <?php if (! empty($breadcrumbs)): ?>
            <ol class="breadcrumb breadcrumb-arrows mb-2" aria-label="breadcrumbs">
              <?php foreach ($breadcrumbs as $b): ?>
                <?php if ($b['active']): ?>
                  <li class="breadcrumb-item active" aria-current="page"><span class="text-dark fw-bold"><?= esc($b['title']) ?></span></li>
                <?php else: ?>
                  <li class="breadcrumb-item"><a href="<?= $b['url'] ?>" class="text-secondary"><?= esc($b['title']) ?></a></li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ol>
            <?php endif; ?>
            <h1 class="page-title text-dark fw-bold"><?= esc($title ?? 'Mini ERP') ?></h1>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <div class="page-body">
      <div class="container-xl">
        <?= $this->renderSection('content') ?>
      </div>
    </div>
  </div>
</div>
<?php else: ?>
<div class="page page-center">
  <div class="container py-4">
    <div class="text-center mb-4">
      <img src="<?= base_url('img/logo.png') ?>" width="64" height="64" alt="PT Nusantara Distribusi">
      <div class="mt-2 h3 mb-0">PT Nusantara Distribusi</div>
      <div class="text-secondary">Mini ERP</div>
    </div>
    <?= $this->renderSection('content') ?>
  </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.7.5/dist/js/tabler.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Helper global untuk mengubah select panjang menjadi dropdown searchable (ketik manual & pilih)
window.initSearchableSelect = function (el) {
  if (!el || typeof TomSelect === 'undefined' || el.tomselect) return null;
  let emptyOpt = el.querySelector('option[value=""]');
  if (!emptyOpt) {
    const zeroOpt = el.querySelector('option[value="0"]');
    if (zeroOpt && /^(Semua|--)/i.test(zeroOpt.textContent.trim())) {
      emptyOpt = zeroOpt;
      emptyOpt.value = '';
    }
  }
  const placeholderText = emptyOpt ? emptyOpt.textContent.trim() : 'Ketik untuk mencari...';
  if (emptyOpt) {
    emptyOpt.textContent = '';
  }
  return new TomSelect(el, {
    create: false,
    allowEmptyOption: false,
    placeholder: placeholderText,
    dropdownParent: 'body',
    sortField: { field: 'text', direction: 'asc' },
    render: {
      no_results: function () {
        return '<div class="no-results p-2 text-muted small">Data tidak ditemukan</div>';
      }
    }
  });
};

// Ganti alert bawaan browser dengan modal SweetAlert2 yang lebih elegan
window.alert = function (message) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: 'warning',
      title: 'Perhatian',
      html: '<div style="font-size: 1.05rem; color: #1e293b; font-weight: 500; line-height: 1.65;">' + message + '</div>',
      confirmButtonText: 'Mengerti',
      confirmButtonColor: '#1d4ed8'
    });
  } else {
    window.originalAlert ? window.originalAlert(message) : console.warn(message);
  }
};

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('select.js-searchable').forEach(function (el) {
    window.initSearchableSelect(el);
  });
  <?php $isAuthPage = ! session()->get('user_id') || ($currentPath === 'auth/login' || $currentPath === 'auth'); ?>
  <?php if (! $isAuthPage): ?>
  // Notifikasi Flash message (khusus halaman internal setelah login)
  <?php $verrors = session()->getFlashdata('errors') ?? \Config\Services::validation()->getErrors(); ?>
  <?php if (session()->getFlashdata('success')): ?>
  const Toast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
  Toast.fire({
    icon: 'success',
    title: <?= json_encode(session()->getFlashdata('success')) ?>
  });
  <?php elseif (session()->getFlashdata('info')): ?>
  const InfoToast = Swal.mixin({
    toast: true,
    position: 'top',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer);
      toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
  });
  InfoToast.fire({
    icon: 'info',
    title: <?= json_encode(session()->getFlashdata('info')) ?>
  });
  <?php elseif (session()->getFlashdata('error')): ?>
  Swal.fire({
    icon: 'error',
    title: 'Tindakan Ditolak',
    html: '<div style="font-size: 1.05rem; color: #1e293b; font-weight: 500; line-height: 1.65; padding: 4px 0;">' + <?= json_encode(session()->getFlashdata('error')) ?> + '</div>',
    confirmButtonText: 'Saya Mengerti',
    confirmButtonColor: '#1d4ed8'
  });
  <?php elseif (! empty($verrors)): ?>
  Swal.fire({
    icon: 'warning',
    title: 'Periksa Kembali Input Form',
    html: '<div class="text-start px-2 py-1" style="font-size: 1.02rem; color: #1e293b; line-height: 1.7;"><ul class="mb-0 ps-3">' + <?= json_encode(implode('', array_map(static fn($e) => '<li>' . esc($e) . '</li>', $verrors))) ?> + '</ul></div>',
    confirmButtonText: 'Perbaiki',
    confirmButtonColor: '#f59e0b'
  });
  <?php endif; ?>
  <?php endif; ?>

  // Interseptor Form Konfirmasi Pengganti confirm() bawaan browser
  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!form || form.tagName !== 'FORM') return;

    const confirmMsg = form.getAttribute('data-confirm');
    if (confirmMsg && !form.dataset.confirmed) {
      e.preventDefault();
      e.stopImmediatePropagation();

      const type = form.getAttribute('data-confirm-type') || 'danger';
      const isDanger = type === 'danger';
      const isWarning = type === 'warning';
      const isPrimary = type === 'primary' || type === 'success';

      const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
      const btnText = form.getAttribute('data-confirm-btn') || (isDanger ? 'Ya, Hapus' : 'Ya, Lanjutkan');
      const icon = form.getAttribute('data-confirm-icon') || (isDanger ? 'warning' : (isPrimary ? 'question' : 'info'));
      const confirmColor = isDanger ? '#d63939' : (isWarning ? '#f59e0b' : '#1d4ed8');

      Swal.fire({
        title: title,
        html: '<div style="font-size: 1.05rem; color: #1e293b; font-weight: 500; line-height: 1.65;">' + confirmMsg + '</div>',
        icon: icon,
        showCancelButton: true,
        confirmButtonText: btnText,
        cancelButtonText: 'Batal',
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#6c757d',
        reverseButtons: true
      }).then(function (result) {
        if (result.isConfirmed) {
          form.dataset.confirmed = 'true';
          form.submit();
        }
      });
      return false;
    }
  }, true);

  document.querySelectorAll('.js-table-search').forEach(function (input) {
    input.addEventListener('input', function () {
      const q = this.value.toLowerCase().trim();
      const targetSelector = this.dataset.target || 'table tbody';
      const tbody = document.querySelector(targetSelector);
      if (!tbody) return;

      const rows = tbody.querySelectorAll('tr:not(.no-search-results)');
      let matchCount = 0;
      rows.forEach(function (row) {
        const text = row.innerText.toLowerCase();
        const matches = text.includes(q);
        row.style.display = matches ? '' : 'none';
        if (matches) matchCount++;
      });

      let emptyRow = tbody.querySelector('.no-search-results');
      if (matchCount === 0 && rows.length > 0) {
        if (!emptyRow) {
          emptyRow = document.createElement('tr');
          emptyRow.className = 'no-search-results';
          emptyRow.innerHTML = '<td colspan="100%" class="text-center text-secondary py-4"><div>Data tidak ditemukan untuk pencarian <strong>"' + q.replace(/</g, '&lt;') + '"</strong></div></td>';
          tbody.appendChild(emptyRow);
        } else {
          emptyRow.style.display = '';
          emptyRow.querySelector('td').innerHTML = '<div>Data tidak ditemukan untuk pencarian <strong>"' + q.replace(/</g, '&lt;') + '"</strong></div>';
        }
      } else if (emptyRow) {
        emptyRow.style.display = 'none';
      }
    });
  });

  function formatRupiah(val) {
    let str = String(val || '').trim();
    if (str.indexOf('.') !== -1 && str.indexOf(',') === -1) {
      const parts = str.split('.');
      if (parts.length === 2 && (parts[1] === '00' || parts[1] === '0')) {
        str = parts[0];
      }
    }
    const clean = str.replace(/\D/g, '');
    return clean ? clean.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
  }

  document.querySelectorAll('.js-rupiah').forEach(function (inp) {
    if (inp.value) {
      inp.value = formatRupiah(inp.value);
    }
    inp.addEventListener('input', function () {
      this.value = formatRupiah(this.value);
    });
  });

  document.querySelectorAll('form').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      // 1. Jika validasi form HTML5 belum lengkap, batalkan (jangan ubah tombol)
      if (f.checkValidity && !f.checkValidity()) {
        return;
      }

      // 2. Jika pengguna menekan "Cancel" pada dialog konfirmasi apapun, batalkan (tombol tetap utuh)
      if (e.defaultPrevented) {
        return;
      }

      f.querySelectorAll('.js-rupiah').forEach(function (inp) {
        inp.value = inp.value.replace(/\./g, '');
      });

      // 3. Jangan ubah tombol pada form aksi kecil di dalam tabel (Hapus, Toggle Aktif/Nonaktif)
      if (f.closest('.table') || f.classList.contains('no-disable')) {
        return;
      }

      // 4. Hanya ubah tombol pada form pengisian data utama saat benar-benar dikirim
      if (f.method.toLowerCase() === 'post') {
        const btn = f.querySelector('button[type="submit"]');
        if (btn) {
          setTimeout(function () {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
          }, 10);
        }
      }
    });
  });

  // Sidebar Collapse Toggle & Collapsed Flyout Handling
  const sidebarBtn = document.getElementById('sidebarCollapseBtn');
  if (sidebarBtn) {
    sidebarBtn.addEventListener('click', function () {
      document.body.classList.toggle('sidebar-collapsed');
      document.querySelectorAll('.navbar-vertical .nav-item.dropdown.flyout-open').forEach(function (el) {
        el.classList.remove('flyout-open');
      });
      localStorage.setItem('erp_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
      window.dispatchEvent(new Event('resize'));
    });
  }

  document.querySelectorAll('.navbar-vertical .nav-item.dropdown > .nav-link').forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
      if (document.body.classList.contains('sidebar-collapsed') && window.innerWidth >= 992) {
        e.preventDefault();
        e.stopPropagation();
        const parentItem = toggle.closest('.nav-item.dropdown');
        const wasOpen = parentItem.classList.contains('flyout-open');
        document.querySelectorAll('.navbar-vertical .nav-item.dropdown.flyout-open').forEach(function (el) {
          el.classList.remove('flyout-open');
        });
        if (!wasOpen) {
          parentItem.classList.add('flyout-open');
        }
      }
    });
  });

  document.addEventListener('click', function (e) {
    if (document.body.classList.contains('sidebar-collapsed') && !e.target.closest('.navbar-vertical')) {
      document.querySelectorAll('.navbar-vertical .nav-item.dropdown.flyout-open').forEach(function (el) {
        el.classList.remove('flyout-open');
      });
    }
  });

  // Command Palette (Ctrl+K / Cmd+K) - 100% reliable via trigger button
  const cmdModalEl = document.getElementById('erpCommandModal');
  const cmdTrigger = document.getElementById('erpCmdTrigger');
  const cmdInput = document.getElementById('erpCmdInput');
  const cmdResults = document.getElementById('erpCmdResults');
  if (cmdModalEl && cmdInput && cmdResults) {
    const defaultHtml = cmdResults.innerHTML;
    let cmdTimer = null;

    window.addEventListener('keydown', function (e) {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        e.stopPropagation();
        if (cmdModalEl.classList.contains('show')) {
          const closeBtn = cmdModalEl.querySelector('[data-bs-dismiss="modal"]');
          if (closeBtn) closeBtn.click();
        } else if (cmdTrigger) {
          cmdTrigger.click();
        }
      }
    }, true);

    cmdModalEl.addEventListener('shown.bs.modal', function () {
      cmdInput.value = '';
      cmdResults.innerHTML = defaultHtml;
      setTimeout(function () { cmdInput.focus(); }, 30);
    });

    cmdInput.addEventListener('input', function () {
      const q = this.value.trim();
      clearTimeout(cmdTimer);
      if (q.length < 2) {
        cmdResults.innerHTML = defaultHtml;
        return;
      }
      cmdTimer = setTimeout(function () {
        cmdResults.innerHTML = '<div class="text-center py-3 text-secondary small">Mencari...</div>';
        fetch('/dashboard/quick-search?q=' + encodeURIComponent(q))
          .then(r => r.json())
          .then(data => {
            if (!data.items || data.items.length === 0) {
              cmdResults.innerHTML = '<div class="text-center py-4 text-secondary small">Tidak ada hasil untuk "' + q.replace(/</g, '&lt;') + '"</div>';
              return;
            }
            cmdResults.innerHTML = data.items.map(item => `
              <a href="${item.url}" class="erp-cmd-result-item">
                <div>
                  <div class="fw-semibold small">${item.title}</div>
                  <div class="text-secondary" style="font-size:0.75rem;">${item.subtitle}</div>
                </div>
                <span class="badge bg-secondary-lt">${item.type}</span>
              </a>
            `).join('');
          })
          .catch(() => {
            cmdResults.innerHTML = '<div class="text-center py-3 text-danger small">Gagal memuat hasil pencarian.</div>';
          });
      }, 220);
    });
  }
  // Global Auto-Trim: hapus spasi di awal/akhir pada seluruh input teks/email/textarea (kecuali password)
  const trimField = function (el) {
    if (!el || typeof el.value !== 'string') return;
    const type = (el.getAttribute('type') || 'text').toLowerCase();
    if (['password', 'file', 'checkbox', 'radio', 'hidden', 'date', 'number'].includes(type)) return;
    let val = el.value.trim();
    if (el.getAttribute('name') === 'code') {
      val = val.replace(/\s+/g, '');
    }
    if (el.value !== val) {
      el.value = val;
    }
  };
  document.addEventListener('focusout', function (e) {
    if (e.target && e.target.matches('input, textarea')) {
      trimField(e.target);
    }
  });
  document.addEventListener('submit', function (e) {
    if (e.target && e.target.matches('form')) {
      e.target.querySelectorAll('input, textarea').forEach(trimField);
    }
  }, true);
});
</script>

<?php if (session()->get('user_id')): ?>
<!-- Command Palette Modal -->
<div class="modal fade d-print-none" id="erpCommandModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 580px;">
    <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
      <div class="modal-header border-bottom py-2 px-3">
        <div class="d-flex align-items-center gap-2 w-100">
          <span class="d-inline-flex align-items-center justify-content-center text-secondary flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          </span>
          <input type="text" id="erpCmdInput" class="form-control border-0 shadow-none bg-transparent px-1 flex-grow-1" placeholder="<?= esc($cmdSearchLong) ?>" autocomplete="off">
          <button type="button" class="btn-close erp-cmd-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
      </div>
      <div class="modal-body p-2" id="erpCmdResults" style="max-height: 360px; overflow-y: auto;">
        <div class="px-2 py-1 text-uppercase text-secondary fw-bold" style="font-size: 0.7rem; letter-spacing: .05em;">Pintasan Cepat</div>
        <a href="/dashboard" class="erp-cmd-result-item"><span>Dashboard Utama</span><span class="badge bg-primary-lt">Halaman</span></a>
        <a href="/products" class="erp-cmd-result-item"><span>Katalog Produk</span><span class="badge bg-secondary-lt">Master</span></a>
        <a href="/inventory" class="erp-cmd-result-item"><span>Stok Persediaan</span><span class="badge bg-secondary-lt">Gudang</span></a>
        <?php if (in_array($role, ['admin', 'sales', 'manajemen'], true)): ?>
          <a href="/sales-orders" class="erp-cmd-result-item"><span>Daftar Pesanan Penjualan (SO)</span><span class="badge bg-success-lt">Penjualan</span></a>
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'purchasing', 'manajemen'], true)): ?>
          <a href="/purchase-orders" class="erp-cmd-result-item"><span>Daftar Pesanan Pembelian (PO)</span><span class="badge bg-azure-lt">Pembelian</span></a>
        <?php endif; ?>
        <?php if (in_array($role, ['admin', 'gudang', 'manajemen'], true)): ?>
          <a href="/deliveries" class="erp-cmd-result-item"><span>Pengiriman Barang (DO)</span><span class="badge bg-primary-lt">Gudang</span></a>
          <a href="/goods-receipts" class="erp-cmd-result-item"><span>Penerimaan Barang (GR)</span><span class="badge bg-primary-lt">Gudang</span></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->renderSection('scripts') ?>
</body>
</html>
