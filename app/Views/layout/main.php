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
if (preg_match('#^(categories|products|suppliers|customers|warehouses)(/|$)#', $currentPath)) $activeGroup = 'master';
elseif (preg_match('#^(purchase-orders|goods-receipts)(/|$)#', $currentPath)) $activeGroup = 'pembelian';
elseif (preg_match('#^(users|roles)(/|$)#', $currentPath)) $activeGroup = 'pengaturan';

$navActive = static function (string $prefix) use ($currentPath): string {
    return $prefix === $currentPath || str_starts_with($currentPath, $prefix . '/') ? 'active' : '';
};
$backSegments = explode('/', trim($currentPath, '/'));
$backUrl = count($backSegments) > 1 ? '/' . $backSegments[0] : null;

$moduleLabels = [
    'categories' => 'Kategori',
    'products' => 'Produk',
    'suppliers' => 'Pemasok',
    'customers' => 'Pelanggan',
    'warehouses' => 'Gudang',
    'users' => 'Pengguna',
    'roles' => 'Peran',
    'purchase-orders' => 'Pesanan Beli',
    'goods-receipts' => 'Barang Masuk',
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
?>
<div class="page">
  <aside class="navbar navbar-vertical navbar-expand-lg">
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
              <span class="nav-link-title">Data Master</span>
            </a>
            <div class="dropdown-menu <?= $activeGroup === 'master' ? 'show' : '' ?>">
              <a class="dropdown-item <?= $navActive('products') ?>" href="/products">Produk</a>
              <?php if ($role === 'admin'): ?><a class="dropdown-item <?= $navActive('categories') ?>" href="/categories">Kategori</a><?php endif; ?>
              <?php if (in_array($role, ['admin', 'purchasing', 'gudang', 'manajemen'], true)): ?><a class="dropdown-item <?= $navActive('suppliers') ?>" href="/suppliers">Pemasok</a><?php endif; ?>
              <?php if (in_array($role, ['admin', 'sales', 'gudang', 'manajemen'], true)): ?><a class="dropdown-item <?= $navActive('customers') ?>" href="/customers">Pelanggan</a><?php endif; ?>
              <?php if (in_array($role, ['admin', 'gudang', 'manajemen'], true)): ?><a class="dropdown-item <?= $navActive('warehouses') ?>" href="/warehouses">Gudang</a><?php endif; ?>
            </div>
          </li>
          <?php if (in_array($role, ['admin', 'purchasing', 'gudang', 'manajemen'], true)): ?>
          <li class="nav-item dropdown <?= $activeGroup === 'pembelian' ? 'show' : '' ?>">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="<?= $activeGroup === 'pembelian' ? 'true' : 'false' ?>">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon" > <path stroke="none" d="M0 0h24v24H0z" fill="none" /> <path d="M4 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /> <path d="M15 19a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /> <path d="M17 17h-11v-14h-2" /> <path d="M6 5l14 1l-1 7h-13" /> </svg></span>
              <span class="nav-link-title">Pembelian</span>
            </a>
            <div class="dropdown-menu <?= $activeGroup === 'pembelian' ? 'show' : '' ?>">
              <a class="dropdown-item <?= $navActive('purchase-orders') ?>" href="/purchase-orders">Pesanan Beli</a>
              <a class="dropdown-item <?= $navActive('goods-receipts') ?>" href="/goods-receipts">Barang Masuk</a>
            </div>
          </li>
          <?php endif; ?>
          <?php if ($role === 'admin'): ?>
          <li class="nav-item dropdown <?= $activeGroup === 'pengaturan' ? 'show' : '' ?>">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="<?= $activeGroup === 'pengaturan' ? 'true' : 'false' ?>">
              <span class="nav-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/></svg></span>
              <span class="nav-link-title">Pengaturan</span>
            </a>
            <div class="dropdown-menu <?= $activeGroup === 'pengaturan' ? 'show' : '' ?>">
              <a class="dropdown-item <?= $navActive('users') ?>" href="/users">Pengguna</a>
              <a class="dropdown-item <?= $navActive('roles') ?>" href="/roles">Peran & Akses</a>
            </div>
          </li>
          <?php endif; ?>
        </ul>
        <div class="navbar-side">
          <div class="d-flex align-items-center px-3 py-2">
            <span class="avatar avatar-sm me-2 avatar-user-badge"><?= esc($initials) ?></span>
            <div class="lh-1">
              <div class="text-white fw-medium"><?= esc($userName) ?></div>
              <div class="mt-1 role-badge-text"><?= esc($roleLabel) ?></div>
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
    <div class="page-body">
      <div class="container-xl">
        <?php if (session()->getFlashdata('success')): ?><div class="alert alert-important alert-success alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M9 12l2 2l4 -4"/></svg></div><div><?= esc(session()->getFlashdata('success')) ?></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div><?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?><div class="alert alert-important alert-danger alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg></div><div><?= esc(session()->getFlashdata('error')) ?></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div><?php endif; ?>
        <?php $verrors = session()->getFlashdata('errors') ?? \Config\Services::validation()->getErrors(); if (! empty($verrors)): ?>
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
      <img src="<?= base_url('img/logo.png') ?>" width="64" height="64" alt="PT Nusantara Distribusi">
      <div class="mt-2 h3 mb-0">PT Nusantara Distribusi</div>
      <div class="text-secondary">Mini ERP</div>
    </div>
<div class="row justify-content-center">
      <div class="col-md-6">
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-important alert-success alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M9 12l2 2l4 -4"/></svg></div><div><?= esc(session()->getFlashdata('success')) ?></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-important alert-danger alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg></div><div><?= esc(session()->getFlashdata('error')) ?></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div><?php endif; ?>
    <?php $verrors = session()->getFlashdata('errors') ?? \Config\Services::validation()->getErrors(); if (! empty($verrors)): ?>
    <div class="alert alert-important alert-danger alert-dismissible" role="alert"><div class="d-flex"><div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon alert-icon"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 8l0 4"/><path d="M12 16l0 .01"/></svg></div><div><ul class="mb-0"><?php foreach ($verrors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div></div><a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a></div>
    <?php endif; ?>
      </div>
    </div>
        <?= $this->renderSection('content') ?>
  </div>
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.7.5/dist/js/tabler.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
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
    const clean = String(val).replace(/\D/g, '');
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
});
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
