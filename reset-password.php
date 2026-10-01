<?php
$pageTitle = "Choose a new password";
require_once __DIR__ . '/config/config.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = null;
$done  = false;

$db = Database::getInstance();

/** Look up a live, unused, unexpired token by its hash. */
function findResetRow(Database $db, string $token): ?array {
    if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    return $db->fetchOne(
        "SELECT pr.*, u.email
           FROM password_resets pr
           JOIN users u ON u.id = pr.user_id
          WHERE pr.token_hash = :th
            AND pr.used_at IS NULL
            AND pr.expires_at > NOW()
          LIMIT 1",
        [':th' => hash('sha256', $token)]
    );
}

$row = findResetRow($db, $token);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirmation'] ?? '';

    if (!$row) {
        $error = 'This reset link has expired or already been used.';
    } elseif (strlen($password) < 6) {
        $error = 'Choose a password of at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'The two passwords do not match.';
    } else {
        (new User())->updatePassword((int)$row['user_id'], $password);

        // Burn the token so the link cannot be replayed.
        $db->query("UPDATE password_resets SET used_at = NOW() WHERE id = :id", [':id' => $row['id']]);

        $done = true;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <div class="card card-lg" style="max-width:440px;margin-inline:auto">
        <?php if ($done): ?>
            <div style="text-align:center">
                <div class="empty-icon" style="color:var(--success);background:var(--success-bg)"><?= icon('check') ?></div>
                <h1 class="page-title">Password updated</h1>
                <p class="t-sm t-muted" style="margin-top:var(--space-3)">You can now sign in with your new password.</p>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-block" style="margin-top:var(--space-6)">Sign in</a>
            </div>

        <?php elseif (!$row): ?>
            <div style="text-align:center">
                <div class="empty-icon" style="color:var(--danger);background:var(--danger-bg)"><?= icon('alert') ?></div>
                <h1 class="page-title">This link has expired</h1>
                <p class="t-sm t-muted" style="margin-top:var(--space-3)">
                    Reset links are valid for one hour and can only be used once.
                </p>
                <a href="<?= BASE_URL ?>/forgot-password.php" class="btn btn-primary btn-block" style="margin-top:var(--space-6)">
                    Request a new link
                </a>
            </div>

        <?php else: ?>
            <div style="text-align:center;margin-bottom:var(--space-8)">
                <h1 class="page-title">Choose a new password</h1>
                <p class="page-sub">For <?= e($row['email']) ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" role="alert" style="margin-bottom:var(--space-5)">
                    <?= icon('alert') ?><span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <div class="form-group">
                    <label class="form-label" for="password">New password</label>
                    <div class="field-password">
                        <input type="password" id="password" name="password" class="form-control" required
                               minlength="6" autocomplete="new-password" autofocus>
                        <button type="button" class="pw-toggle" aria-label="Show password">
                            <?= icon('eye') ?>
                        </button>
                    </div>
                    <p class="form-hint">At least 6 characters.</p>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm new password</label>
                    <div class="field-password">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" required autocomplete="new-password">
                        <button type="button" class="pw-toggle" aria-label="Show password">
                            <?= icon('eye') ?>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:var(--space-5)">
                    Update password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
