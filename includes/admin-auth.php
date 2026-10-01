<?php
/**
 * Admin / Staff Authorization Middleware
 */

require_once __DIR__ . '/../config/config.php';

if (!Auth::check()) {
    setFlash('error', 'Administrator login required.');
    redirect(ADMIN_URL . '/login.php');
}

if (!Auth::isAdminOrStaff()) {
    setFlash('error', 'Access denied. You do not have administrative privileges.');
    redirect(BASE_URL . '/index.php');
}

// Function to check permission on specific admin pages
function requirePermission(string $permission): void {
    if (!Auth::can($permission)) {
        setFlash('error', 'Permission denied for this administrative action.');
        redirect(ADMIN_URL . '/index.php');
    }
}
