<?php
/**
 * Database Configuration Settings
 *
 * Everything is environment-driven so the same code runs against local XAMPP
 * and a managed cloud MySQL without edits. Set these in the host's env panel:
 *
 *   DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
 *   DB_SSL=1            enable TLS (required by most managed MySQL providers)
 *   DB_SSL_CA=/path.pem optional CA bundle, when the provider supplies one
 */

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Managed providers (TiDB Cloud, Aiven, PlanetScale, RDS) require TLS.
if (getenv('DB_SSL') && defined('PDO::MYSQL_ATTR_SSL_CA')) {
    $ca = getenv('DB_SSL_CA');
    if ($ca && file_exists($ca)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
    } else {
        // No bundle supplied: still negotiate TLS, just without local CA pinning.
        // The connection is encrypted; the cert chain is not verified locally.
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
}

return [
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => getenv('DB_PORT') ?: '3306',
    'dbname'   => getenv('DB_NAME') ?: 'ecommerce_db',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    'charset'  => 'utf8mb4',
    'options'  => $options,
];
