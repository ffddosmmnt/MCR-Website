<?php
declare(strict_types=1);
require_once __DIR__ . '/internal/admin.php';
admin_session();
try {
    admin_user();
    if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
        header('Allow: GET, HEAD');
        admin_fail(405, 'Method not allowed.');
    }
    $kind = admin_string($_GET, 'kind', 'contact');
    if (!in_array($kind, ['contact', 'subscribe'], true)) throw new InvalidArgumentException();
    $page = admin_string($_GET, 'page', '1');
    if (!preg_match('/\A[1-9][0-9]{0,5}\z/', $page)) throw new InvalidArgumentException();
    $table = $kind === 'contact' ? 'contact_messages' : 'newsletter_subscriptions';
    $columns = $kind === 'contact' ? 'name,email,subject,message,created_at' : 'email,created_at';
    $query = database()->prepare("SELECT $columns FROM $table ORDER BY id DESC LIMIT 21 OFFSET :offset");
    $query->bindValue(':offset', ((int) $page - 1) * 20, PDO::PARAM_INT);
    $query->execute();
    $rows = $query->fetchAll();
} catch (InvalidArgumentException) { admin_fail(400, 'Invalid request.'); }
catch (Throwable $error) {
    error_log('[mcr] Inbox read failed (' . get_class($error) . ').');
    admin_fail(503, 'Messages are temporarily unavailable.');
}
admin_head('Inbox');
?>
<main class="halaman"><header class="judul-halaman"><h1>Inbox</h1><a class="btn btn-outline-secondary" href="../dashboard/">Back to dashboard</a></header>
<nav class="d-flex gap-3 mb-3" aria-label="Inbox"><a href="?kind=contact">Contact messages</a><a href="?kind=subscribe">Newsletter subscribers</a><a href="../dashboard/index.php?module=fan_messages">Published fan messages</a></nav>
<?php if (!$rows): ?><p>No records yet.</p><?php endif ?>
<?php foreach (array_slice($rows, 0, 20) as $row): ?><article class="card p-3 mb-3 text-break">
<?php if ($kind === 'contact'): ?><h2 class="h4"><?= e($row['subject']) ?></h2><p><?= e($row['name']) ?> · <?= e($row['email']) ?></p><p><?= nl2br(e($row['message'])) ?></p>
<?php else: ?><p><?= e($row['email']) ?></p><?php endif ?>
<small>Received (UTC): <?= e($row['created_at']) ?></small></article><?php endforeach ?>
<nav class="d-flex gap-3" aria-label="Pagination"><?php if ((int) $page > 1): ?><a href="?kind=<?= e($kind) ?>&amp;page=<?= (int) $page - 1 ?>">Previous</a><?php endif ?><?php if (count($rows) > 20): ?><a href="?kind=<?= e($kind) ?>&amp;page=<?= (int) $page + 1 ?>">Next</a><?php endif ?></nav></main></body></html>
