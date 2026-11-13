<?php
declare(strict_types=1);
require_once __DIR__ . '/internal/admin.php';
admin_session();
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'POST'], true)) {
    header('Allow: GET, HEAD, POST');
    admin_fail(405, 'Method not allowed.');
}
$modules = admin_modules();
$error = '';
$record = [];
$options = [];
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_csrf();
    }
    $table = admin_string($_GET, 'module', 'news_articles');
    if (!isset($modules[$table])) {
        admin_fail(404, 'Module not found.');
    }
    [$title, $fields] = $modules[$table];
    $createReadOnly = in_array($table, ['users', 'merch_orders', 'merch_order_items', 'ticket_orders', 'ticket_order_items'], true);
    $listFields = array_filter($fields, fn ($field) => $field[1] !== 'password');
    $action = admin_string($_GET, 'action');
    if (!in_array($action, ['', 'create', 'edit', 'delete'], true)) {
        throw new InvalidArgumentException('Invalid action.');
    }
    if ($createReadOnly && in_array($action, ['edit', 'delete'], true)) {
        admin_fail(404, 'Update and Delete for this module are introduced in WBS 2.3.');
    }
    $id = in_array($action, ['edit', 'delete'], true) ? admin_id(admin_string($_GET, 'id')) : 0;
    $search = admin_string($_GET, 'search');
    if (mb_strlen($search) > 200) {
        throw new InvalidArgumentException('Search is too long.');
    }
    $sort = admin_string($_GET, 'sort', 'id');
    if (!in_array($sort, array_merge(['id'], array_keys($listFields)), true)) {
        throw new InvalidArgumentException('Invalid sort column.');
    }
    $direction = admin_string($_GET, 'direction', 'desc');
    if (!in_array($direction, ['asc', 'desc'], true)) {
        throw new InvalidArgumentException('Invalid sort direction.');
    }
    $limitText = admin_string($_GET, 'limit', '5');
    if (!in_array($limitText, ['5', '10', '15'], true)) {
        throw new InvalidArgumentException('Invalid page size.');
    }
    $limit = (int) $limitText;
    $pageText = admin_string($_GET, 'page', '1');
    if (!ctype_digit($pageText) || strlen($pageText) > 9 || (int) $pageText < 1) {
        throw new InvalidArgumentException('Invalid page.');
    }
    $page = (int) $pageText;
    $db = database();
    foreach ($fields as $key => $field) {
        if ($field[1] !== 'relation') {
            continue;
        }
        // Include every parent, not just the first page of records.
        $parents = $db->query('SELECT * FROM `' . $field[2] . '` ORDER BY id DESC')->fetchAll();
        $options[$key] = [];
        foreach ($parents as $parent) {
            $options[$key][(string) $parent['id']] = admin_label($parent);
        }
    }
    $filters = [];
    $legacyFilter = admin_string($_GET, 'filter');
    if (!$options && $legacyFilter !== '') {
        throw new InvalidArgumentException('Invalid relation filter.');
    }
    foreach ($options as $key => $parents) {
        $filter = admin_string($_GET, 'filter_' . $key, $key === array_key_first($options) ? $legacyFilter : '');
        if ($filter !== '' && !isset($parents[$filter])) {
            throw new InvalidArgumentException('Invalid ' . strtolower($fields[$key][0]) . ' filter.');
        }
        $filters[$key] = $filter;
    }
    $state = ['module' => $table, 'search' => $search, 'sort' => $sort, 'direction' => $direction, 'limit' => $limit, 'page' => $page];
    foreach ($filters as $key => $filter) {
        $state['filter_' . $key] = $filter;
    }
    $url = fn (array $changes = []) => 'index.php?' . http_build_query(array_merge($state, $changes));
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === '') {
            throw new InvalidArgumentException('Select a write action.');
        }
        try {
            admin_save($db, $table, $fields, $action, $id);
            $_SESSION['success'] = $action === 'delete' ? 'Record deleted.' : 'Record saved.';
            admin_redirect($url());
        } catch (PDOException $exception) {
            $code = (int) ($exception->errorInfo[1] ?? 0);
            if ($code === 1062) {
                http_response_code(409);
                $error = 'This unique value is already in use. Choose another value.';
            } elseif (in_array($code, [1451, 1452], true)) {
                http_response_code(409);
                $error = 'Related records prevent this change. Check the relationships first.';
            } else {
                throw $exception;
            }
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            $error = $exception->getMessage();
        } catch (DomainException $exception) {
            admin_fail(409, $exception->getMessage());
        }
    }
    if ($id) {
        $record = admin_record($db, $table, $id);
    }
    // A validation retry must retain its original snapshot, even if another admin saved meanwhile.
    $version = $error ? admin_string($_POST, 'version') : ($record ? admin_version($record) : '');
    $values = $record;
    if ($error && $action !== 'delete') {
        foreach ($fields as $key => $field) {
            $values[$key] = $field[1] === 'password' ? '' : (is_string($_POST[$key] ?? null) ? $_POST[$key] : '');
        }
    } else {
        foreach ($fields as $key => $field) {
            $values[$key] = admin_input_value($record[$key] ?? null, $field[1]);
        }
    }
    $where = [];
    $parameters = [];
    if ($search !== '') {
        $clauses = [];
        $pattern = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        foreach (array_merge(['id'], array_keys($listFields)) as $column) {
            $clauses[] = "CAST(`$column` AS CHAR) LIKE ? ESCAPE '!'";
            $parameters[] = $pattern;
        }
        $where[] = '(' . implode(' OR ', $clauses) . ')';
    }
    foreach ($filters as $key => $filter) {
        if ($filter !== '') {
            $where[] = "`$key` = ?";
            $parameters[] = $filter;
        }
    }
    $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
    $stmt = $db->prepare("SELECT COUNT(*) FROM `$table`" . $whereSql);
    $stmt->execute($parameters);
    $total = (int) $stmt->fetchColumn();
    $pages = max(1, (int) ceil($total / $limit));
    $page = min($page, $pages);
    $state['page'] = $page;
    $offset = ($page - 1) * $limit;
    $stmt = $db->prepare("SELECT * FROM `$table`$whereSql ORDER BY `$sort` $direction, id $direction LIMIT $limit OFFSET $offset");
    $stmt->execute($parameters);
    $rows = $stmt->fetchAll();
} catch (OutOfBoundsException $exception) {
    admin_fail(404, $exception->getMessage());
} catch (InvalidArgumentException $exception) {
    admin_fail(400, $exception->getMessage());
} catch (Throwable $exception) {
    error_log('[mcr] Admin request unavailable (' . get_class($exception) . ').');
    admin_fail(503, 'Dashboard is temporarily unavailable. Please try again later.');
}
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
admin_head($title);
require __DIR__ . '/internal/view.php';
