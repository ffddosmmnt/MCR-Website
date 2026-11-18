<?php
declare(strict_types=1);

// Content installation is a local maintenance operation, never a public endpoint.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/app/database.php';

if ($argc > 2 || ($argc === 2 && !in_array($argv[1], ['--dry-run', '--help'], true))) {
    fwrite(STDERR, "Use: php database/import.php [--dry-run|--help]\n");
    exit(1);
}
if (($argv[1] ?? '') === '--help') {
    fwrite(STDOUT, "Use: php database/import.php [--dry-run]\nConfigure DB_* and import the original schema plus migrations 001/002/003 first.\nExisting matching records are retained, except known starter images are refreshed. No accounts or orders are imported.\n");
    exit;
}

$db = null;
$locked = false;
$lockName = '';
$exitCode = 0;
try {
    $db = database();
    $name = $db->query('SELECT DATABASE()')->fetchColumn();
    $lockName = 'mcr.content.' . substr(hash('sha256', $name), 0, 40);
    $lock = $db->prepare('SELECT GET_LOCK(?, 10)');
    $lock->execute([$lockName]);
    if ((int) $lock->fetchColumn() !== 1) {
        throw new RuntimeException('Content import is already running.');
    }
    $locked = true;

    $required = ['band_members', 'albums', 'songs', 'news_articles', 'venues', 'concerts',
        'ticket_types', 'categories', 'products', 'product_variants', 'users', 'merch_orders',
        'merch_order_items', 'ticket_orders', 'ticket_order_items', 'checkout_requests',
        'contact_messages', 'newsletter_subscriptions', 'fan_messages'];
    $engines = $db->query('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()')->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($required as $table) {
        if (($engines[$table] ?? '') !== 'InnoDB') {
            throw new RuntimeException('Import schema and migrations 001/002/003 first; InnoDB tables are required.');
        }
    }
    // Only identifiers from this fixed schema allowlist are interpolated.
    $contentTables = ['band_members', 'albums', 'songs', 'news_articles', 'venues', 'concerts',
        'ticket_types', 'categories', 'products', 'product_variants', 'fan_messages'];
    $before = [];
    foreach ($contentTables as $table) {
        $before[$table] = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    }
    $sql = file_get_contents(__DIR__ . '/content.sql');
    if ($sql === false) {
        throw new RuntimeException('Content file is unavailable.');
    }
    // The SQL file also works in SQL clients. PDO owns the transaction here so
    // an error anywhere cannot commit earlier inserts, even with --dry-run.
    $sql = preg_replace('/^START TRANSACTION;\R/m', '', $sql);
    $sql = preg_replace('/^COMMIT;\s*\z/m', '', $sql);
    $db->beginTransaction();
    $db->exec($sql);
    $summary = [];
    foreach ($contentTables as $table) {
        $after = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $summary[$table] = ['before' => $before[$table], 'inserted' => $after - $before[$table], 'after' => $after];
    }
    $dryRun = ($argv[1] ?? '') === '--dry-run';
    if ($dryRun) {
        $db->rollBack();
    } else {
        $db->commit();
    }
    fwrite(STDOUT, json_encode(['database' => $name, 'mode' => $dryRun ? 'rolled_back' : 'committed', 'tables' => $summary], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . "\n");
} catch (Throwable $error) {
    if ($db instanceof PDO && $db->inTransaction()) {
        $db->rollBack();
    }
    // Do not print PDO errors, SQL, credentials, or private record values.
    fwrite(STDERR, "Content import failed. No content inserts were committed. Check DB_* configuration, schema/migrations, write permissions, and duplicate parent names.\n");
    $exitCode = 1;
} finally {
    if ($locked) {
        try {
            $release = $db->prepare('SELECT RELEASE_LOCK(?)');
            $release->execute([$lockName]);
        } catch (Throwable $error) {
            // MySQL also releases connection-scoped locks when the CLI exits.
        }
    }
}
exit($exitCode);
