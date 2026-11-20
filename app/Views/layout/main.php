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
<link href="<?= base_url('css/app.css') ?>" rel="stylesheet">
</head>
<body>
<?php if (session()->get('user_id')): ?>
<?php $role = session()->get('user_role'); ?>
<?php
$currentPath = uri_string();
$activeGroup = '';
if (preg_match('#^(categories|products|suppliers|customers|users|roles)(/|$)#', $currentPath)) $activeGroup = 'master';
$navActive = static function (string $prefix) use ($currentPath): string {
    return $prefix === $currentPath || str_starts_with($currentPath, $prefix . '/') ? 'active' : '';
};
$backSegments = explode('/', trim($currentPath, '/'));
$backUrl = count($backSegments) > 1 ? '/' . $backSegments[0] : null;
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
?>
<div class="page">
  <aside class="navbar navbar-vertical navbar-expand-lg">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle sidebar navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="navbar-brand">
        <a href="/dashboard" aria-label="PT Nusantara Distribusi">
          <img src="<?= base_url('img/logo.jpg') ?>" width="32" height="32" alt="PT Nusantara Distribusi" class="navbar-brand-image">
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
              <?php if ($role === 'admin'): ?><a class="dropdown-item <?= $navActive('roles') ?>" href="/roles">Peran</a><?php endif; ?>
            </div>
          </li>
        </ul>
        <div class="navbar-side">
          <div class="d-flex align-items-center px-3 py-2">
            <span class="avatar avatar-sm me-2" style="background-color: #0f3d47; color: #fff;"><?= esc($initials) ?></span>
            <div class="lh-1">
              <div class="text-white"><?= esc($userName) ?></div>
              <div class="mt-1 small text-white-50"><?= esc($roleLabel) ?></div>
            </div>
          </div>
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
    <div class="page-header d-print-none">
      <div class="container-xl">
        <div class="row g-2 align-items-center">
          <div class="col">
            <?php if ($backUrl): ?><a href="<?= $backUrl ?>" class="btn btn-ghost-secondary btn-sm mb-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0"/><path d="M5 12l6 6"/><path d="M5 12l6 -6"/></svg> Kembali</a><?php endif; ?>
            <h1 class="page-title"><?= esc($title ?? 'Mini ERP') ?></h1>
          </div>
        </div>
      </div>
    </div>
    <div class="page-body">
      <div class="container-xl">
        <?php if (session()->getFlashdata('success')): ?><div class="alert alert-important alert-success alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M9 12l2 2l4 -4"/></svg></div><div><?= esc(session()->getFlashdata('success')) ?></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div><?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?><div class="alert alert-important alert-danger alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg></div><div><?= esc(session()->getFlashdata('error')) ?></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div><?php endif; ?>
        <?php $verrors = \Config\Services::validation()->getErrors(); if (! empty($verrors)): ?>
        <div class="alert alert-important alert-danger alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg></div><div><ul class="mb-0"><?php foreach ($verrors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div>
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
      <img src="<?= base_url('img/logo.jpg') ?>" width="64" height="64" alt="PT Nusantara Distribusi">
      <div class="mt-2 h3 mb-0">PT Nusantara Distribusi</div>
      <div class="text-secondary">Mini ERP</div>
    </div>
<div class="row justify-content-center">
      <div class="col-md-6">
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-important alert-success alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M9 12l2 2l4 -4"/></svg></div><div><?= esc(session()->getFlashdata('success')) ?></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-important alert-danger alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg></div><div><?= esc(session()->getFlashdata('error')) ?></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div><?php endif; ?>
    <?php $verrors = \Config\Services::validation()->getErrors(); if (! empty($verrors)): ?>
    <div class="alert alert-important alert-danger alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg></div><div><ul class="mb-0"><?php foreach ($verrors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div>
    <?php endif; ?>
      </div>
    </div>
        <?= $this->renderSection('content') ?>
  </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.7.5/dist/js/tabler.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
