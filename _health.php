<?php
/**
 * Deployment health check.
 *
 * Tests each layer independently so a failure points at the right thing.
 * Notably it connects to the database WITHOUT booting config.php, because
 * config.php starts the session — and with SESSION_DRIVER=database that would
 * itself fail on a bad database, masking the real cause.
 *
 * Never prints credentials: only the exception class and message.
 */
header('Content-Type: application/json');

$root = __DIR__;
$out  = [
    'php'        => PHP_VERSION,
    'runtime'    => 'ok',
    'pdo_mysql'  => extension_loaded('pdo_mysql'),
    'https'      => ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') ?: ($_SERVER['HTTPS'] ?? 'off'),
    'session_driver' => getenv('SESSION_DRIVER') ?: 'files',
];

// --- Layer 1: database, tested in isolation -------------------------------
$cfg = require $root . '/config/database.php';
$out['db_host_set'] = $cfg['host'] !== '127.0.0.1';   // has DB_HOST been provided?
$out['db_ssl']      = (bool) getenv('DB_SSL');

try {
    $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);

    $out['db']       = 'connected';
    $out['tables']   = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables
                                          WHERE table_schema = DATABASE()")->fetchColumn();
    $out['products'] = (int) $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    // The app cannot serve requests without these two.
    foreach (['sessions', 'settings'] as $t) {
        $out['has_' . $t] = (bool) $pdo->query("SHOW TABLES LIKE " . $pdo->quote($t))->fetchColumn();
    }
} catch (Throwable $e) {
    $out['db']       = 'error';
    $out['db_error'] = get_class($e) . ': ' . $e->getMessage();
    http_response_code(503);
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// --- Layer 2: full application bootstrap ----------------------------------
try {
    require_once $root . '/config/config.php';
    $out['app']      = 'booted';
    $out['base_url'] = BASE_URL;
    $out['site']     = getSetting('site_name', APP_NAME);
} catch (Throwable $e) {
    $out['app']       = 'error';
    $out['app_error'] = get_class($e) . ': ' . $e->getMessage();
    http_response_code(503);
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
