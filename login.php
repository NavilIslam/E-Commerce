<?php
$pageTitle = "Sign in";
require_once __DIR__ . '/config/config.php';

if (Auth::check()) {
    redirect(BASE_URL . '/index.php');
}

$redirect = $_GET['redirect'] ?? (BASE_URL . '/index.php');
$formError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $res = Auth::login($email, $password);
    if ($res['success']) {
        setFlash('success', 'Welcome back, ' . explode(' ', Auth::name())[0] . '.');
        // Honour an explicit redirect even for staff, so "add to cart then sign in"
        // returns the customer to what they were doing.
        if (!empty($_GET['redirect'])) {
            redirect($redirect);
        }
        redirect(Auth::isAdminOrStaff() ? ADMIN_URL . '/index.php' : $redirect);
    }
    // Shown inline beside the fields rather than as a page-level banner.
    $formError = $res['message'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <div class="card card-lg" style="max-width:440px;margin-inline:auto">
        <div style="text-align:center;margin-bottom:var(--space-8)">
            <h1 class="page-title">Sign in</h1>
            <p class="page-sub">Access your orders, wishlist and addresses</p>
        </div>

        <?php if ($formError): ?>
            <div class="alert alert-error" role="alert" style="margin-bottom:var(--space-5)">
                <?= icon('alert') ?><span><?= e($formError) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control<?= $formError ? ' has-error' : '' ?>"
                       placeholder="you@example.com" autocomplete="email" required autofocus
                       value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <div class="row-between" style="margin-bottom:var(--space-2)">
                    <label class="form-label" for="password" style="margin-bottom:0">Password</label>
                    <a href="<?= BASE_URL ?>/forgot-password.php" class="t-sm" style="color:var(--primary)">Forgot password?</a>
                </div>
                <div class="field-password">
                    <input type="password" id="password" name="password"
                           class="form-control<?= $formError ? ' has-error' : '' ?>"
                           autocomplete="current-password" required>
                    <button type="button" class="pw-toggle" aria-label="Show password">
                        <?= icon('eye') ?>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:var(--space-6)">Sign in</button>
        </form>

        <p class="t-sm t-subtle t-center" style="margin-top:var(--space-8)">
            New here? <a href="<?= BASE_URL ?>/register.php" style="color:var(--primary);font-weight:600">Create an account</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
