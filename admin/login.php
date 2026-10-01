<?php
require_once __DIR__ . '/../config/config.php';

if (Auth::check() && Auth::isAdminOrStaff()) {
    redirect(ADMIN_URL . '/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $res = Auth::login($email, $password);
    if ($res['success']) {
        if (!Auth::isAdminOrStaff()) {
            Auth::logout();
            setFlash('error', 'Access denied. You do not have administrator permissions.');
            redirect(ADMIN_URL . '/login.php');
        }
        setFlash('success', 'Logged in as ' . ucfirst(Auth::role()) . '.');
        redirect(ADMIN_URL . '/index.php');
    } else {
        setFlash('error', $res['message']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Sign In | <?= e(getSetting('site_name', APP_NAME)) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            min-height: 100vh;
            padding: 1.5rem;
        }
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 2.25rem;
            border: 1px solid var(--admin-border);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div style="text-align:center;margin-bottom:2rem;">
        <div style="margin-bottom:0.75rem;"><?= brandLogo(getSetting('site_name', APP_NAME), 'color') ?></div>
        <h1 style="font-size:1.25rem;font-weight:700;color:var(--text-dark);letter-spacing:-0.01em;">Staff Sign In</h1>
        <p style="color:var(--text-muted);font-size:0.8rem;margin-top:0.25rem;">Sign in to access the store administration panel</p>
    </div>

    <?php if ($msg = getFlash('error')): ?>
        <div style="background:#fee2e2;color:#991b1b;padding:0.75rem;border:1px solid #fecaca;font-size:0.825rem;margin-bottom:1.25rem;">
            <?= e($msg) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <?= csrfField() ?>

        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="admin@example.com" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding:0.75rem;margin-top:1.5rem;">
            Sign In
        </button>
    </form>

    <div style="margin-top:2rem;padding-top:1rem;border-top:1px solid var(--admin-border);font-size:0.75rem;color:var(--text-muted);text-align:center;">
        <div>Demo Credentials:</div>
        <div style="font-family:var(--font-mono);margin-top:0.25rem;color:var(--text-dark);">
            <strong>admin@example.com</strong> / <strong>Password123!</strong>
        </div>
    </div>
</div>

</body>
</html>
