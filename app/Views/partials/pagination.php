<?php
if (! isset($pager)) {
    return;
}
$query = $query ?? [];
$label = $label ?? 'Data';
$currentPerPage = (int) ($pager['perPage'] ?? 8);
$perPageOptions = [7, 8, 10, 15, 25, 50];
if (! in_array($currentPerPage, $perPageOptions, true)) {
    $perPageOptions[] = $currentPerPage;
    sort($perPageOptions);
}
$cleanQuery = array_filter($query, static fn ($v) => $v !== '' && $v !== null);
$baseQuery = array_merge($cleanQuery, ['per_page' => $currentPerPage]);
?>
<div class="card-footer d-flex flex-wrap justify-content-between align-items-center py-2 bg-transparent gap-2">
  <div class="d-flex flex-wrap align-items-center gap-3">
    <span class="text-muted small">Menampilkan <?= esc($pager['from']) ?> sampai <?= esc($pager['to']) ?> dari <?= esc($pager['total']) ?> <?= esc($label) ?></span>
    <form method="get" class="d-inline-flex align-items-center gap-1 m-0">
      <?php foreach ($cleanQuery as $qk => $qv): ?>
        <input type="hidden" name="<?= esc($qk) ?>" value="<?= esc($qv) ?>">
      <?php endforeach; ?>
      <input type="hidden" name="page" value="1">
      <span class="text-muted small">Baris:</span>
      <select name="per_page" class="form-select form-select-sm" style="width: 72px;" aria-label="Jumlah baris per halaman" onchange="this.form.submit()">
        <?php foreach ($perPageOptions as $opt): ?>
          <option value="<?= $opt ?>" <?= $currentPerPage === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <?php if (($pager['lastPage'] ?? 1) > 1): ?>
    <ul class="pagination pagination-sm m-0">
      <li class="page-item <?= $pager['page'] <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => max(1, $pager['page'] - 1)])) ?>">&laquo;</a>
      </li>
      <?php
      $pages = [];
      for ($p = 1; $p <= $pager['lastPage']; $p++) {
          if ($p === 1 || $p === $pager['lastPage'] || abs($p - $pager['page']) <= 1) {
              $pages[] = $p;
          }
      }
      $pages = array_values(array_unique($pages));
      $prev = 0;
      foreach ($pages as $p):
          if ($p - $prev > 1): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
          <?php endif; ?>
          <li class="page-item <?= $p === $pager['page'] ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => $p])) ?>"><?= esc($p) ?></a>
          </li>
      <?php $prev = $p; endforeach; ?>
      <li class="page-item <?= $pager['page'] >= $pager['lastPage'] ? 'disabled' : '' ?>">
        <a class="page-link" href="?<?= http_build_query(array_merge($baseQuery, ['page' => min($pager['lastPage'], $pager['page'] + 1)])) ?>">&raquo;</a>
      </li>
    </ul>
  <?php endif; ?>
</div>
