<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title ?? 'Mini ERP') ?> - Mini ERP PT Nusantara Distribusi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php if (session()->get('user_id')): ?>
<?php $role = session()->get('user_role'); ?>
<?php $roleLabel = ['admin' => 'Admin', 'purchasing' => 'Staff Purchasing', 'gudang' => 'Staff Gudang', 'sales' => 'Staff Sales', 'manajemen' => 'Manajemen'][$role] ?? $role; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="/dashboard">Mini ERP PT Nusantara Distribusi</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Master Data</a>
          <ul class="dropdown-menu">
            <?php if ($role === 'admin'): ?><li><a class="dropdown-item" href="/categories">Kategori</a></li><?php endif; ?>
            <li><a class="dropdown-item" href="/products">Produk</a></li>
            <?php if (in_array($role, ['admin', 'purchasing', 'gudang', 'manajemen'], true)): ?><li><a class="dropdown-item" href="/suppliers">Supplier</a></li><?php endif; ?>
            <?php if (in_array($role, ['admin', 'sales', 'gudang', 'manajemen'], true)): ?><li><a class="dropdown-item" href="/customers">Customer</a></li><?php endif; ?>
            <?php if ($role === 'admin'): ?><li><a class="dropdown-item" href="/users">Pengguna</a></li><?php endif; ?>
          </ul>
        </li>
      </ul>
      <span class="navbar-text text-white me-3"><?= esc(session()->get('user_name')) ?> (<?= esc($roleLabel) ?>)</span>
      <a class="btn btn-outline-light btn-sm" href="/auth/logout">Logout</a>
    </div>
  </div>
</nav>
<?php else: ?>
<nav class="navbar navbar-dark bg-primary"><div class="container-fluid"><span class="navbar-brand">Mini ERP PT Nusantara Distribusi</span></div></nav>
<?php endif; ?>
<div class="container mt-4 mb-5">
  <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
  <?php $verrors = \Config\Services::validation()->getErrors(); if (! empty($verrors)): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($verrors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <?= $this->renderSection('content') ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
