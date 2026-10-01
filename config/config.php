<?php
/**
 * Application Global Configuration
 */

// Error Reporting (adjust for production)
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't expose PHP errors directly to users
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../error.log');

// Define Base Paths
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');
}

// Ensure Upload Subdirectories Exist
$uploadDirs = [
    UPLOAD_PATH,
    UPLOAD_PATH . DIRECTORY_SEPARATOR . 'products',
    UPLOAD_PATH . DIRECTORY_SEPARATOR . 'categories',
    UPLOAD_PATH . DIRECTORY_SEPARATOR . 'banners',
    UPLOAD_PATH . DIRECTORY_SEPARATOR . 'avatars',
];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Session Configuration & Security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');

    // Serve cookies over HTTPS only when the request itself is HTTPS, so this
    // hardens production without breaking local http://localhost.
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }

    // On multi-instance hosts local session files are not shared between
    // requests. SESSION_DRIVER=database moves them into MySQL instead.
    if (getenv('SESSION_DRIVER') === 'database') {
        require_once ROOT_PATH . '/classes/Database.php';
        require_once ROOT_PATH . '/classes/DbSessionHandler.php';
        session_set_save_handler(new DbSessionHandler(), true);
    }

    session_start();
}

// Detect Dynamic Base URL
// Platforms like Vercel terminate TLS at the edge, so $_SERVER['HTTPS'] is unset
// on the PHP side. Trust the forwarded-proto header when present, otherwise the
// generated BASE_URL would be http:// and every asset would be mixed content.
$forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? 80) == 443
        || strtolower($forwardedProto) === 'https';
$protocol = $isHttps ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = dirname($scriptName);

// Clean path separator for Windows
$scriptDir = str_replace('\\', '/', $scriptDir);
// Find project base URL relative to document root
$appFolder = '';
if (strpos($scriptDir, '/admin') !== false) {
    $appFolder = substr($scriptDir, 0, strpos($scriptDir, '/admin'));
} elseif (strpos($scriptDir, '/api') !== false) {
    $appFolder = substr($scriptDir, 0, strpos($scriptDir, '/api'));
} else {
    $appFolder = ($scriptDir === '/' || $scriptDir === '.') ? '' : $scriptDir;
}

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim($protocol . $host . $appFolder, '/'));
}
if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', BASE_URL . '/admin');
}
if (!defined('UPLOAD_URL')) {
    define('UPLOAD_URL', BASE_URL . '/uploads');
}

// App Constants
if (!defined('APP_NAME')) define('APP_NAME', 'NovaMart');
if (!defined('CURRENCY_SYMBOL')) define('CURRENCY_SYMBOL', '৳');
if (!defined('CURRENCY_CODE')) define('CURRENCY_CODE', 'BDT');

// Autoload Classes
spl_autoload_register(function ($className) {
    $classFile = ROOT_PATH . '/classes/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Require Common Functions
require_once ROOT_PATH . '/includes/icons.php';
require_once ROOT_PATH . '/includes/functions.php';
