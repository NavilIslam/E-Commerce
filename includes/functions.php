<?php
/**
 * Global Utility Functions
 */

// Escape HTML for XSS Protection
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Format Price with Currency Symbol
function formatPrice(float|int|string $amount): string {
    $num = (float) $amount;
    return CURRENCY_SYMBOL . number_format($num, 2);
}

// Get Setting Value from Database with Cache
function getSetting(string $key, ?string $default = null): ?string {
    static $settingsCache = null;

    if ($settingsCache === null) {
        try {
            $db = Database::getInstance();
            $rows = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
            $settingsCache = [];
            foreach ($rows as $row) {
                $settingsCache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            $settingsCache = [];
        }
    }

    return $settingsCache[$key] ?? $default;
}

// JSON Response Helper for APIs
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// JSON Error Helper
function jsonError(string $message, array $errors = [], int $statusCode = 400): void {
    jsonResponse([
        'success' => false,
        'error'   => $message,
        'errors'  => $errors,
    ], $statusCode);
}

// JSON Success Helper
function jsonSuccess(string $message = 'Success', array $data = [], array $extra = [], int $statusCode = 200): void {
    $payload = array_merge([
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ], $extra);
    jsonResponse($payload, $statusCode);
}

// Slug Generator
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'item-' . time() : $text;
}

// Flash Message Set
function setFlash(string $type, string $message): void {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][$type] = $message;
}

// Flash Message Get and Clear
function getFlash(string $type): ?string {
    if (isset($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

// Has Flash Message
function hasFlash(string $type): bool {
    return isset($_SESSION['flash'][$type]);
}

// Redirect Helper
function redirect(string $url): void {
    header("Location: " . $url);
    exit;
}

// Sanitize String Input
function sanitize(mixed $data): mixed {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return trim(filter_var($data, FILTER_DEFAULT));
}

// CSRF Field Helper for HTML Forms
function csrfField(): string {
    $token = CSRF::getToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

// CSRF Meta Tag Helper for JavaScript fetch requests
function csrfMeta(): string {
    $token = CSRF::getToken();
    return '<meta name="csrf-token" content="' . e($token) . '">';
}

// Get Image URL Helper with Fallback
function getImageUrl(?string $fileName, string $folder = 'products'): string {
    if (!empty($fileName) && file_exists(UPLOAD_PATH . '/' . $folder . '/' . $fileName)) {
        return UPLOAD_URL . '/' . $folder . '/' . $fileName;
    }
    // Fallback placeholder
    return BASE_URL . '/assets/images/placeholder.svg';
}

// True when an image actually exists on disk (used to decide contain vs cover, and
// whether to render an image-led card at all).
function hasImage(?string $fileName, string $folder = 'products'): bool {
    return !empty($fileName) && file_exists(UPLOAD_PATH . '/' . $folder . '/' . $fileName);
}

/**
 * Decide which single badge a product card should carry, if any.
 * Badges only mean something when they are scarce, so we show at most one.
 * Priority: out of stock > meaningful discount > editorial "featured".
 *
 * @param array $prod              Product row
 * @param bool  $suppressFeatured  True inside a section that is already "Featured"
 * @return array{label:string,variant:string}|null
 */
function productBadge(array $prod, bool $suppressFeatured = false): ?array {
    if ((int)($prod['stock_quantity'] ?? 0) <= 0) {
        return ['label' => 'Out of stock', 'variant' => 'muted'];
    }

    $price = (float)($prod['price'] ?? 0);
    $sale  = (float)($prod['sale_price'] ?? 0);
    if ($sale > 0 && $price > 0 && $sale < $price) {
        $pct = (int) round((($price - $sale) / $price) * 100);
        if ($pct >= 15) {
            return ['label' => '-' . $pct . '%', 'variant' => 'sale'];
        }
    }

    if (!$suppressFeatured && !empty($prod['is_featured'])) {
        return ['label' => 'Featured', 'variant' => 'featured'];
    }

    return null;
}

// Render a 5-star rating as SVG stars with an accessible label.
function ratingStars(float $rating, ?int $count = null, string $size = 'sm'): string {
    $rating = max(0, min(5, $rating));
    $full   = (int) floor($rating);
    $half   = ($rating - $full) >= 0.5;
    $out    = '<span class="rating rating-' . e($size) . '">';
    $out   .= '<span class="rating-stars" role="img" aria-label="Rated ' . number_format($rating, 1) . ' out of 5">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full)        $cls = 'is-full';
        elseif ($i === $full + 1 && $half) $cls = 'is-half';
        else                    $cls = 'is-empty';
        $out .= '<span class="star ' . $cls . '">' . icon('star', 'star-icon') . '</span>';
    }
    $out .= '</span>';
    $out .= '<span class="rating-value">' . number_format($rating, 1) . '</span>';
    if ($count !== null) {
        $out .= '<span class="rating-count">(' . (int)$count . ')</span>';
    }
    return $out . '</span>';
}

/**
 * Windowed pagination: Previous, first, ellipsis, a window around the current
 * page, ellipsis, last, Next. Never renders a wall of numbers.
 *
 * @param callable $urlFor  fn(int $page): string
 */
function renderPagination(int $current, int $total, callable $urlFor): string
{
    if ($total < 2) return '';

    $window = 1;
    $pages  = [];
    for ($p = 1; $p <= $total; $p++) {
        if ($p === 1 || $p === $total || abs($p - $current) <= $window) {
            $pages[] = $p;
        }
    }
    $pages = array_values(array_unique($pages));

    $out = '<nav class="pagination" aria-label="Pagination">';

    $out .= $current > 1
        ? '<a class="page-btn" href="' . e($urlFor($current - 1)) . '" rel="prev">' . icon('chevron-l', 'icon-sm') . ' Previous</a>'
        : '<span class="page-btn is-disabled" aria-hidden="true">' . icon('chevron-l', 'icon-sm') . ' Previous</span>';

    $prev = 0;
    foreach ($pages as $p) {
        if ($prev && $p - $prev > 1) {
            $out .= '<span class="page-gap" aria-hidden="true">&hellip;</span>';
        }
        $out .= $p === $current
            ? '<span class="page-btn is-current" aria-current="page">' . $p . '</span>'
            : '<a class="page-btn" href="' . e($urlFor($p)) . '" aria-label="Page ' . $p . '">' . $p . '</a>';
        $prev = $p;
    }

    $out .= $current < $total
        ? '<a class="page-btn" href="' . e($urlFor($current + 1)) . '" rel="next">Next ' . icon('chevron-r', 'icon-sm') . '</a>'
        : '<span class="page-btn is-disabled" aria-hidden="true">Next ' . icon('chevron-r', 'icon-sm') . '</span>';

    return $out . '</nav>';
}

/** "1–12 of 15 products" — a real range, not the page size. */
function resultRange(int $page, int $perPage, int $total, string $noun = 'product'): string
{
    if ($total === 0) return 'No ' . $noun . 's found';
    $from = (($page - 1) * $perPage) + 1;
    $to   = min($page * $perPage, $total);
    return $from . '&ndash;' . $to . ' of ' . $total . ' ' . $noun . ($total === 1 ? '' : 's');
}

// Mark the active primary-nav item so the user can see where they are.
function navActive(string $file, array $extra = []): string {
    $current = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($current === $file) {
        // Some nav entries share products.php but differ by query (e.g. deals).
        foreach ($extra as $key => $val) {
            if (($_GET[$key] ?? null) != $val) return '';
        }
        return ' is-active';
    }
    return '';
}

// Relative Human Time Format
function timeAgo(string $datetime): string {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $timestamp);
}

// Truncate Text
function truncateText(string $text, int $limit = 100): string {
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . '...';
}
