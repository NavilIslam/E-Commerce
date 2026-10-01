<?php
/**
 * Automated Database Installer & Setup Wizard
 */

// Installer: usable only before the site is locked, or by a signed-in owner.
require_once __DIR__ . '/includes/maintenance-guard.php';
requireMaintenanceAccess(true);

error_reporting(E_ALL);
ini_set('display_errors', '0');

$configFile = __DIR__ . '/config/database.php';
if (!file_exists($configFile)) {
    die("Database configuration file missing.");
}
$config = require $configFile;

$message = '';
$error = '';
$isInstalled = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['run'])) {
    try {
        // Connect to MySQL server without selecting DB first
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // 1. Create DB
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$config['dbname']}`");

        // 2. Run Schema
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (file_exists($schemaFile)) {
            $schemaSql = file_get_contents($schemaFile);
            $pdo->exec($schemaSql);
        }

        // 3. Run Seed Data
        $seedFile = __DIR__ . '/database/seed.sql';
        if (file_exists($seedFile)) {
            $seedSql = file_get_contents($seedFile);
            $pdo->exec($seedSql);
        }

        $message = "Database schema and demo seed data successfully initialized!";
        $isInstalled = true;

        // Lock the installer so it cannot be replayed from the browser.
        writeMaintenanceLock();
    } catch (PDOException $e) {
        $error = "Database setup failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup Wizard | NovaMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .setup-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            max-width: 620px;
            width: 100%;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            padding: 0.85rem 1.75rem;
            font-weight: 700;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            text-align: center;
            width: 100%;
            transition: all 0.2s;
        }
        .btn:hover { background: #1d4ed8; }
        .btn-outline {
            background: transparent;
            border: 1px solid #475569;
            color: #cbd5e1;
        }
        .btn-outline:hover { background: #334155; color: #fff; }
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .alert-success { background: #064e3b; color: #a7f3d0; border: 1px solid #059669; }
        .alert-danger { background: #7f1d1d; color: #fecaca; border: 1px solid #dc2626; }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 0.9rem;
        }
        .info-table td {
            padding: 0.5rem 0;
            border-bottom: 1px solid #334155;
        }
        .info-table td:first-child { color: #94a3b8; }
        .info-table td:last-child { font-family: monospace; font-weight: 600; }
        .icon { width:20px; height:20px; display:inline-block; vertical-align:-0.2em; fill:currentColor; flex-shrink:0; }
        .brand { height: 34px; width: auto; display: inline-block; }
    </style>
</head>
<body>

<div class="setup-card">
    <div style="text-align:center;margin-bottom:2rem;">
        <div style="margin-bottom:0.75rem;"><?= brandLogo('NovaMart', 'white') ?></div>
        <h1 style="font-size:1.85rem;font-weight:700;">NovaMart E-Commerce Setup</h1>
        <p style="color:#94a3b8;font-size:0.9rem;margin-top:0.25rem;">Automated Database & Demo Content Initializer</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <strong><?= icon('check') ?> Success!</strong> <?= e($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <strong><?= icon('close') ?> Error:</strong> <?= e($error) ?>
        </div>
    <?php endif; ?>

    <table class="info-table">
        <tr><td>Target Host:</td><td><?= e($config['host']) ?>:<?= e($config['port']) ?></td></tr>
        <tr><td>Target Database:</td><td><?= e($config['dbname']) ?></td></tr>
        <tr><td>MySQL Username:</td><td><?= e($config['username']) ?></td></tr>
        <tr><td>Admin Demo Login:</td><td>admin@example.com</td></tr>
        <tr><td>Customer Demo Login:</td><td>customer@example.com</td></tr>
        <tr><td>Default Password:</td><td>Password123!</td></tr>
    </table>

    <?php if ($isInstalled): ?>
        <div style="display:flex;gap:1rem;margin-top:1.5rem;">
            <a href="index.php" class="btn">Launch storefront</a>
            <a href="admin/login.php" class="btn btn-outline">Admin portal</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <button type="submit" class="btn">Initialize database</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
