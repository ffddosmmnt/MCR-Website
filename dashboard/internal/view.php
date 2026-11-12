<main class="halaman">
<header class="judul-halaman"><div><h1>MCR Admin · <?= e($title) ?></h1><p class="text-body-secondary mb-0">Administrator</p></div><div class="d-flex gap-2 flex-wrap"><a class="btn btn-primary" <?= $action === 'create' ? 'data-dialog-opener' : '' ?> href="<?= e($url(['action' => 'create'])) ?>">+ Add record</a><a class="btn btn-outline-secondary" href="../index.php">View website</a></div></header>
<nav aria-label="Data modules" class="mb-3"><ul class="nav nav-pills gap-2 flex-wrap"><?php foreach ($modules as $name => $module): ?><li class="nav-item"><a class="nav-link <?= $table === $name ? 'active' : '' ?>" <?= $table === $name ? 'aria-current="page"' : '' ?> href="index.php?module=<?= e($name) ?>"><?= e($module[0]) ?></a></li><?php endforeach ?></ul></nav>
<?php if ($success): ?><div class="alert alert-success" role="status"><?= e($success) ?></div><?php endif ?>
<section class="daftar-data" aria-label="<?= e($title) ?> records">
<div class="card-header p-3"><form method="get" action="index.php" class="row g-3 align-items-end">
<input type="hidden" name="module" value="<?= e($table) ?>"><input type="hidden" name="sort" value="<?= e($sort) ?>"><input type="hidden" name="direction" value="<?= e($direction) ?>">
<div class="col-md-4"><label class="form-label" for="search">Search records</label><input class="form-control" id="search" name="search" type="search" maxlength="200" value="<?= e($search) ?>"></div>
<?php if ($filterKey): ?><div class="col-md-3"><label class="form-label" for="filter"><?= e($fields[$filterKey][0]) ?></label><select class="form-select" name="filter" id="filter"><option value="">All</option><?php foreach ($options[$filterKey] as $parentId => $label): ?><option value="<?= (int) $parentId ?>" <?= $filter === (string) $parentId ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach ?></select></div><?php endif ?>
<div class="col-md-2"><label class="form-label" for="limit">Rows per page</label><select class="form-select" id="limit" name="limit"><?php foreach ([5, 10, 15] as $size): ?><option <?= $limit === $size ? 'selected' : '' ?> value="<?= $size ?>"><?= $size ?></option><?php endforeach ?></select></div>
<div class="col-md-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Apply</button><a class="btn btn-outline-secondary" href="index.php?module=<?= e($table) ?>">Reset</a></div>
</form></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr>
<?php foreach (['id' => ['ID'], ...$fields] as $column => $field): ?><th scope="col" <?= $sort === $column ? 'aria-sort="' . ($direction === 'asc' ? 'ascending' : 'descending') . '"' : '' ?>><a class="sort-link" href="<?= e($url(['sort' => $column, 'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc', 'page' => 1])) ?>"><?= e($field[0]) ?> <?= $sort === $column ? ($direction === 'asc' ? '↑' : '↓') : '' ?></a></th><?php endforeach ?><th scope="col">Actions</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr><td><?= (int) $row['id'] ?></td>
<?php foreach ($fields as $key => $field): ?><td class="text-break"><?php
    $display = $row[$key];
    if ($field[1] === 'relation') {
        $display = $options[$key][(string) $display] ?? '#' . $display;
    } elseif ($field[1] === 'timestamp' && $display !== null) {
        $display = date_label($display, true);
    }
    if ($field[1] === 'textarea' && $display !== null): ?>
<details><summary><?= e(mb_strimwidth($display, 0, 70, '…')) ?></summary><p class="text-break"><?= nl2br(e($display)) ?></p></details>
<?php else: ?><?= $display === null ? '<span class="text-body-secondary">—</span>' : e((string) $display) ?><?php endif ?></td><?php endforeach ?>
<td><div class="d-flex gap-2"><a class="btn btn-outline-primary btn-sm" <?= $action === 'edit' && $id === (int) $row['id'] ? 'data-dialog-opener' : '' ?> aria-label="Edit record <?= (int) $row['id'] ?>" href="<?= e($url(['action' => 'edit', 'id' => $row['id']])) ?>">Edit</a><a class="btn btn-outline-danger btn-sm" <?= $action === 'delete' && $id === (int) $row['id'] ? 'data-dialog-opener' : '' ?> aria-label="Delete record <?= (int) $row['id'] ?>" href="<?= e($url(['action' => 'delete', 'id' => $row['id']])) ?>">Delete</a></div></td></tr><?php endforeach ?>
<?php if (!$rows): ?><tr><td colspan="<?= count($fields) + 2 ?>" class="text-center py-5 text-body-secondary">No records found.</td></tr><?php endif ?>
</tbody></table></div>
<div class="card-footer p-3 d-flex flex-wrap justify-content-between align-items-center gap-3"><p class="mb-0 text-body-secondary small">Showing <?= $total ? $offset + 1 : 0 ?>–<?= min($offset + $limit, $total) ?> of <?= $total ?> records · Page <?= $page ?> of <?= $pages ?></p>
<nav aria-label="Pagination"><ul class="pagination pagination-sm mb-0 flex-wrap">
<?php foreach (['First' => 1, 'Previous' => max(1, $page - 1)] as $label => $target): ?><li class="page-item <?= $page === 1 ? 'disabled' : '' ?>"><?php if ($page > 1): ?><a class="page-link" href="<?= e($url(['page' => $target])) ?>"><?= e($label) ?></a><?php else: ?><span class="page-link" aria-disabled="true"><?= e($label) ?></span><?php endif ?></li><?php endforeach ?>
<?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?><li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" <?= $p === $page ? 'aria-current="page"' : '' ?> href="<?= e($url(['page' => $p])) ?>"><?= $p ?></a></li><?php endfor ?>
<?php foreach (['Next' => min($pages, $page + 1), 'Last' => $pages] as $label => $target): ?><li class="page-item <?= $page === $pages ? 'disabled' : '' ?>"><?php if ($page < $pages): ?><a class="page-link" href="<?= e($url(['page' => $target])) ?>"><?= e($label) ?></a><?php else: ?><span class="page-link" aria-disabled="true"><?= e($label) ?></span><?php endif ?></li><?php endforeach ?>
</ul></nav>
<form method="get" class="d-flex gap-2 align-items-center"><?php foreach ($state as $key => $value): if ($key === 'page') continue; ?><input type="hidden" name="<?= e($key) ?>" value="<?= e((string) $value) ?>"><?php endforeach ?><label for="page" class="small text-nowrap">Go to page</label><input class="form-control form-control-sm page-input" type="number" id="page" name="page" min="1" max="<?= $pages ?>" value="<?= $page ?>"><button class="btn btn-outline-primary btn-sm">Go</button></form>
</div></section>
<?php if ($action): ?>
<dialog open class="record-modal" id="record-dialog" aria-labelledby="record-title">
<div class="modal-heading"><h2 id="record-title"><?= e(ucfirst($action)) ?> · <?= e($title) ?></h2><a class="modal-close" href="<?= e($url()) ?>" aria-label="Close">&times;</a></div>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif ?>
<form method="post" action="<?= e($url(['action' => $action, 'id' => $id ?: null])) ?>">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="version" value="<?= e($version) ?>">
<?php if ($action === 'delete'): ?><p>Delete <strong><?= e(admin_label($record)) ?></strong>? This cannot be undone. Records with dependencies cannot be deleted.</p>
<?php else: foreach ($fields as $key => [$label, $type, $max]): $optional = $fields[$key][3] ?? false; ?>
<div class="mb-3"><label class="form-label" for="field-<?= e($key) ?>"><?= e($label) ?><?= $optional ? ' (optional)' : '' ?></label>
<?php if ($type === 'relation'): ?><select class="form-select" id="field-<?= e($key) ?>" name="<?= e($key) ?>" required><option value="">Choose…</option><?php foreach ($options[$key] as $parentId => $parentLabel): ?><option value="<?= (int) $parentId ?>" <?= (string) ($values[$key] ?? '') === (string) $parentId ? 'selected' : '' ?>><?= e($parentLabel) ?></option><?php endforeach ?></select>
<?php elseif ($type === 'textarea'): ?><textarea class="form-control" rows="6" id="field-<?= e($key) ?>" name="<?= e($key) ?>" maxlength="65535" <?= $optional ? '' : 'required' ?>><?= e((string) ($values[$key] ?? '')) ?></textarea>
<?php else: $inputType = match ($type) { 'integer', 'money' => 'number', 'timestamp' => 'datetime-local', 'date', 'datetime-local' => $type, default => 'text' }; ?><input class="form-control" id="field-<?= e($key) ?>" name="<?= e($key) ?>" type="<?= e($inputType) ?>" value="<?= e((string) ($values[$key] ?? '')) ?>" <?= $optional ? '' : 'required' ?> <?php if (in_array($type, ['integer', 'money'], true)): ?>min="0" max="<?= $type === 'money' ? '9999999999.99' : $max ?>" step="<?= $type === 'money' ? '0.01' : '1' ?>"<?php elseif (in_array($type, ['datetime-local', 'timestamp'], true)): ?>step="1"<?php else: ?>maxlength="<?= $max ?>"<?php endif ?>><?php endif ?>
<?php if ($type === 'image'): ?><p class="modal-help">Existing local image, for example assets/Blackparade.png.</p><?php endif ?></div>
<?php endforeach; endif ?>
<div class="modal-actions"><a class="btn btn-outline-secondary" href="<?= e($url()) ?>">Cancel</a><button class="btn <?= $action === 'delete' ? 'btn-danger' : 'btn-primary' ?>" type="submit"><?= $action === 'delete' ? 'Yes, delete' : 'Save record' ?></button></div>
</form></dialog>
<script src="dashboard.js" defer></script>
<?php endif ?>
</main></body></html>
