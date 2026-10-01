<?php
$pageTitle = "Reset your password";
require_once __DIR__ . '/config/config.php';

if (Auth::check()) {
    redirect(BASE_URL . '/index.php');
}

$sent  = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        $db   = Database::getInstance();
        $user = $db->fetchOne("SELECT id, first_name, email FROM users WHERE email = :e AND is_active = 1", [':e' => $email]);

        if ($user) {
            // Raw token goes to the customer; only its hash is stored.
            $token     = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expires   = date('Y-m-d H:i:s', time() + 3600); // one hour

            // Invalidate any outstanding tokens for this account.
            $db->query("UPDATE password_resets SET used_at = NOW() WHERE user_id = :uid AND used_at IS NULL",
                [':uid' => $user['id']]);

            $db->query(
                "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:uid, :th, :exp)",
                [':uid' => $user['id'], ':th' => $tokenHash, ':exp' => $expires]
            );

            $resetLink = BASE_URL . '/reset-password.php?token=' . $token;
            $subject   = 'Reset your ' . getSetting('site_name', APP_NAME) . ' password';
            $body      = "Hello " . $user['first_name'] . ",\n\n"
                       . "Use the link below to choose a new password. It expires in one hour.\n\n"
                       . $resetLink . "\n\n"
                       . "If you did not request this, you can ignore this email.\n";
            $headers   = 'From: ' . getSetting('site_email', 'support@novamart.com');

            // If the host has no mail transport this returns false; we log the
            // failure for an operator rather than pretending the mail was sent.
            if (!@mail($user['email'], $subject, $body, $headers)) {
                error_log('[password-reset] mail() failed for user ' . $user['id'] . ' — link: ' . $resetLink);
            }
        }

        // Always the same response, so this page cannot be used to discover
        // which email addresses have accounts.
        $sent = true;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <div class="card card-lg" style="max-width:440px;margin-inline:auto">
        <?php if ($sent): ?>
            <div style="text-align:center">
                <div class="empty-icon" style="color:var(--success);background:var(--success-bg)"><?= icon('mail') ?></div>
                <h1 class="page-title">Check your email</h1>
                <p class="t-sm t-muted" style="margin-top:var(--space-3)">
                    If an account exists for that address, we've sent a link to reset your password.
                    The link expires in one hour.
                </p>
                <p class="t-sm t-subtle" style="margin-top:var(--space-5)">
                    Didn't get it? Check your spam folder, or
                    <a href="<?= BASE_URL ?>/contact.php" style="color:var(--primary)">contact support</a>.
                </p>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-secondary btn-block" style="margin-top:var(--space-6)">
                    Back to sign in
                </a>
            </div>
        <?php else: ?>
            <div style="text-align:center;margin-bottom:var(--space-8)">
                <h1 class="page-title">Reset your password</h1>
                <p class="page-sub">We'll email you a link to choose a new one</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" role="alert" style="margin-bottom:var(--space-5)">
                    <?= icon('alert') ?><span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control<?= $error ? ' has-error' : '' ?>"
                           placeholder="you@example.com" autocomplete="email" required autofocus
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:var(--space-5)">
                    Send reset link
                </button>
            </form>

            <p class="t-sm t-subtle t-center" style="margin-top:var(--space-8)">
                <a href="<?= BASE_URL ?>/login.php" style="color:var(--primary);font-weight:600">Back to sign in</a>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
