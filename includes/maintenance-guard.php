<?php
/**
 * Guard for maintenance / installer utilities.
 *
 * These scripts perform privileged operations (schema installation, asset
 * downloads). They must never be reachable by an anonymous visitor.
 *
 * Two independent gates, both required:
 *   1. An install lock file must be ABSENT (config/installed.lock). Once the
 *      site is installed, these tools are off permanently until an operator
 *      removes the lock from the server filesystem.
 *   2. The request must come from a signed-in owner account.
 *
 * Gate 1 alone protects the pre-install window (when no accounts exist yet and
 * auth cannot be checked); gate 2 protects everything afterwards.
 */

require_once __DIR__ . '/../config/config.php';

function maintenanceLockPath(): string
{
    return ROOT_PATH . '/config/installed.lock';
}

function maintenanceDeny(string $reason): void
{
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    // Deliberately terse: do not confirm to a prober that this tool exists.
    echo "Not Found\n";
    error_log('[maintenance-guard] blocked ' . ($_SERVER['SCRIPT_NAME'] ?? '?') . ' - ' . $reason
        . ' - ip=' . ($_SERVER['REMOTE_ADDR'] ?? '?'));
    exit;
}

/**
 * @param bool $allowPreInstall  True for the installer itself, which must be
 *                               usable before any user account exists.
 */
function requireMaintenanceAccess(bool $allowPreInstall = false): void
{
    $locked = file_exists(maintenanceLockPath());

    if ($locked) {
        // Site is installed. Only a signed-in owner may proceed.
        if (!Auth::check() || Auth::role() !== 'owner') {
            maintenanceDeny('installed and requester is not owner');
        }
        return;
    }

    if ($allowPreInstall) {
        // Pre-install window: no lock yet, no accounts yet.
        return;
    }

    if (!Auth::check() || Auth::role() !== 'owner') {
        maintenanceDeny('not owner');
    }
}

/** Write the lock so the installer cannot be re-run from the browser. */
function writeMaintenanceLock(): void
{
    @file_put_contents(
        maintenanceLockPath(),
        "Installed " . date('c') . "\nDelete this file from the server filesystem to re-enable setup.php.\n"
    );
}
