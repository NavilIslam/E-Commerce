<?php
/**
 * Customer Authentication Middleware
 */

require_once __DIR__ . '/../config/config.php';

if (!Auth::check()) {
    $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
    setFlash('error', 'Please log in to your account to continue.');
    redirect(BASE_URL . '/login.php?redirect=' . urlencode($currentUri));
}
