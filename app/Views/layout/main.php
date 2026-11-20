<!DOCTYPE html>
<html lang="id" data-bs-navbar-theme="primary">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title ?? 'Mini ERP') ?> - Mini ERP PT Nusantara Distribusi</title>
<link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.7.5/dist/css/tabler.min.css" rel="stylesheet">
<link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head>
<body>
<?php if (session()->get('user_id')): ?>
<?php $role = session()->get('user_role'); ?>
<?php
$currentPath = uri_string();
$activeGroup = '';
if (preg_match('#^(categories|products|suppliers|customers|users)(/|$)#', $currentPath)) $activeGroup = 'master';
$navActive = static function (string $prefix) use ($currentPath): string {
    return $prefix === $currentPath || str_starts_with($currentPath, $prefix . '/') ? 'active' : '';
};
?>
<?php $roleLabel = ['admin' => 'Admin', 'purchasing' => 'Staff Purchasing', 'gudang' => 'Staff Gudang', 'sales' => 'Staff Sales', 'manajemen' => 'Manajemen'][$role] ?? $role; ?>
<?php
$dbNotif = \Config\Database::connect();
$lowStockNotif = $dbNotif->query('SELECT code, name, stock_qty, min_stock FROM products WHERE stock_qty <= min_stock ORDER BY stock_qty ASC')->getResultArray();
$lowStockCount = count($lowStockNotif);
$userName = (string) session()->get('user_name');
$nameParts = preg_split('/\s+/', trim($userName));
$initials = strtoupper(substr($nameParts[0] ?? '', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
if ($initials === '') $initials = '?';
?>
<div class="page">
  <aside class="navbar navbar-vertical navbar-expand-lg">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle sidebar navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="navbar-brand">
        <a href="/dashboard" aria-label="PT Nusantara Distribusi">
          <img src="<?= base_url('img/logo-nusantara.webp') ?>" width="32" height="32" alt="PT Nusantara Distribusi" class="navbar-brand-image">
          <span class="navbar-brand-text">PT Nusantara Distribusi</span>
        </a>
      </div>
      <div class="collapse navbar-collapse" id="sidebar-menu">
        <ul class="navbar-nav pt-lg-3">
          <li class="nav-item">
            <a class="nav-link <?= ($currentPath === 'dashboard' || $currentPath === '') ? 'active' : '' ?>" href="/dashboard">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" > <path stroke="none" d="M0 0h24v24H0z" fill="none" /> <path d="M5 12l-2 0l9 -9l9 9l-2 0" /> <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /> <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /> </svg></span>
              <span class="nav-link-title">Dashboard</span>
            </a>
          </li>
          <li class="nav-item dropdown <?= $activeGroup === 'master' ? 'show' : '' ?>">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="<?= $activeGroup === 'master' ? 'true' : 'false' ?>">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" > <path stroke="none" d="M0 0h24v24H0z" fill="none" /> <path d="M4 6a8 3 0 1 0 16 0a8 3 0 1 0 -16 0" /> <path d="M4 6v6a8 3 0 0 0 16 0v-6" /> <path d="M4 12v6a8 3 0 0 0 16 0v-6" /> </svg></span>
              <span class="nav-link-title">Data Utama</span>
            </a>
            <div class="dropdown-menu <?= $activeGroup === 'master' ? 'show' : '' ?>">
              <?php if ($role === 'admin'): ?><a class="dropdown-item <?= $navActive('categories') ?>" href="/categories">Kategori</a><?php endif; ?>
              <a class="dropdown-item <?= $navActive('products') ?>" href="/products">Produk</a>
              <?php if (in_array($role, ['admin', 'purchasing', 'gudang', 'manajemen'], true)): ?><a class="dropdown-item <?= $navActive('suppliers') ?>" href="/suppliers">Pemasok</a><?php endif; ?>
              <?php if (in_array($role, ['admin', 'sales', 'gudang', 'manajemen'], true)): ?><a class="dropdown-item <?= $navActive('customers') ?>" href="/customers">Pelanggan</a><?php endif; ?>
              <?php if ($role === 'admin'): ?><a class="dropdown-item <?= $navActive('users') ?>" href="/users">Pengguna</a><?php endif; ?>
            </div>
          </li>
        </ul>
        <div class="navbar-side">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="/auth/logout">
                <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" > <path stroke="none" d="M0 0h24v24H0z" fill="none" /> <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /> <path d="M9 12h12l-3 -3" /> <path d="M18 15l3 -3" /> </svg></span>
                <span class="nav-link-title">Logout</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </aside>
  <div class="page-wrapper">
    <header class="navbar navbar-expand-md d-print-none">
      <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav flex-row order-md-last">
          <div class="nav-item dropdown d-none d-md-flex me-3">
            <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Notifikasi stok">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 8a6 6 0 0 0 -12 0c0 7 -3 9 -3 9h18s-3 -2 -3 -9"/><path d="M13.73 21a2 2 0 0 1 -3.46 0"/></svg></span>
              <?php if ($lowStockCount > 0): ?><span class="badge bg-red badge-notification badge-pill"><?= $lowStockCount ?></span><?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
              <div class="card">
                <div class="card-header"><h3 class="card-title">Stok Hampir Habis</h3></div>
                <div class="list-group list-group-flush list-group-hoverable">
                  <?php if (empty($lowStockNotif)): ?>
                    <div class="list-group-item"><div class="text-secondary">Tidak ada produk yang stoknya menipis.</div></div>
                  <?php else: ?>
                    <?php foreach ($lowStockNotif as $n): ?>
                    <a href="/dashboard" class="list-group-item">
                      <div class="row align-items-center">
                        <div class="col text-truncate">
                          <span class="text-body d-block"><?= esc($n['code']) ?> <?= esc($n['name']) ?></span>
                          <div class="d-block text-secondary text-truncate mt-n1">Sisa <?= esc($n['stock_qty']) ?>, minimal <?= esc($n['min_stock']) ?></div>
                        </div>
                      </div>
                    </a>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Menu akun">
              <span class="avatar avatar-sm" style="background-color: #0f3d47; color: #fff;"><?= esc($initials) ?></span>
              <div class="d-none d-xl-block ps-2">
                <div><?= esc($userName) ?></div>
                <div class="mt-1 small text-secondary"><?= esc($roleLabel) ?></div>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
              <a href="/auth/logout" class="dropdown-item">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </header>
    <div class="page-header d-print-none">
      <div class="container-xl">
        <div class="row g-2 align-items-center">
          <div class="col">
            <h1 class="page-title"><?= esc($title ?? 'Mini ERP') ?></h1>
          </div>
        </div>
      </div>
    </div>
    <div class="page-body">
      <div class="container-xl">
        <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
        <?php $verrors = \Config\Services::validation()->getErrors(); if (! empty($verrors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($verrors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <?= $this->renderSection('content') ?>
      </div>
    </div>
  </div>
</div>
<?php else: ?>
<div class="page page-center">
  <div class="container py-4">
    <div class="text-center mb-4">
      <img src="<?= base_url('img/logo-nusantara.webp') ?>" width="64" height="64" alt="PT Nusantara Distribusi">
      <div class="mt-2 h3 mb-0">PT Nusantara Distribusi</div>
      <div class="text-secondary">Mini ERP</div>
    </div>
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php $verrors = \Config\Services::validation()->getErrors(); if (! empty($verrors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($verrors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
  </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.7.5/dist/js/tabler.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
