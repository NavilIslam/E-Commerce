<?php
/**
 * Front controller for serverless hosting.
 *
 * Vercel creates one serverless function per entry point, and the Hobby plan
 * allows 12. This app has 40+ PHP entry points, so every request is routed
 * through this single file, which resolves the URL to the real script and
 * includes it. Apache/XAMPP never uses this — it serves the .php files directly.
 *
 * The included script still sees the SCRIPT_NAME it expects, so BASE_URL
 * detection and the active-nav helper behave exactly as they do locally.
 */

$root = __DIR__;

// Maintenance and internal entry points are never routable from the web.
$blocked = ['setup.php', 'download_images.php', '_router.php'];

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = trim(rawurldecode($uri), '/');

// "/" and "/admin/" resolve to their directory index.
if ($path === '') {
    $path = 'index.php';
} elseif (is_dir($root . '/' . $path)) {
    $path = rtrim($path, '/') . '/index.php';
} elseif (!str_ends_with($path, '.php')) {
    // Allow extensionless URLs (/contact -> /contact.php) without redirecting.
    if (is_file($root . '/' . $path . '.php')) {
        $path .= '.php';
    }
}

$target = realpath($root . '/' . $path);

/**
 * Only genuine public entry points are routable. Support directories hold
 * config, model classes and view partials: they are meant to be included by a
 * page, never requested directly, so requesting one is always a 404.
 */
$publicEntryPoint = static function (string $relative): bool {
    $relative = ltrim(str_replace('\\', '/', $relative), '/');
    $segments = explode('/', $relative);

    // Root-level pages: index.php, products.php, cart.php ...
    if (count($segments) === 1) return true;

    // JSON endpoints: api/cart/add.php
    if ($segments[0] === 'api') return true;

    // Admin pages, but not admin/includes/*
    if ($segments[0] === 'admin' && count($segments) === 2) return true;

    return false;
};

$relative = $target === false
    ? ''
    : str_replace('\\', '/', substr($target, strlen($root) + 1));

$isSafe = $target !== false
    && str_starts_with($target, $root . DIRECTORY_SEPARATOR)   // no traversal outside the app
    && is_file($target)
    && strtolower(pathinfo($target, PATHINFO_EXTENSION)) === 'php'
    && !in_array(basename($target), $blocked, true)
    && $publicEntryPoint($relative);

if (!$isSafe) {
    http_response_code(404);
    $pageTitle = 'Page not found';
    require_once $root . '/includes/header.php';
    ?>
    <div class="container page">
        <div class="empty">
            <div class="empty-icon"><?= icon('search') ?></div>
            <h1 class="empty-title">We can't find that page</h1>
            <p class="empty-text">The link may be out of date, or the page may have moved.</p>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary">Go to homepage</a>
        </div>
    </div>
    <?php
    require_once $root . '/includes/footer.php';
    exit;
}

// Present the request to the included script as if it had been hit directly.
$_SERVER['SCRIPT_NAME']     = '/' . $relative;
$_SERVER['PHP_SELF']        = '/' . $relative;
$_SERVER['SCRIPT_FILENAME'] = $target;

chdir(dirname($target));

/**
 * A database outage would otherwise surface as a blank 500. Catch it here and
 * explain the state instead — for API endpoints as JSON, for pages as HTML.
 * Only connection-level failures are handled; everything else rethrows so real
 * bugs stay visible in the logs.
 */
try {
    require $target;
} catch (Throwable $e) {
    $isDbFailure = $e instanceof PDOException
        || str_contains($e->getMessage(), 'Database connection failed');

    if (!$isDbFailure) {
        throw $e;
    }

    error_log('[router] database unavailable: ' . $e->getMessage());

    if (str_starts_with($relative, 'api/')) {
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error'   => 'The store is not connected to a database yet.',
        ]);
        exit;
    }

    require $root . '/includes/db-unavailable.php';
    exit;
}
