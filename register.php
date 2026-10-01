<?php
$pageTitle = "Create account";
require_once __DIR__ . '/config/config.php';

if (Auth::check()) {
    redirect(BASE_URL . '/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();
    $res = Auth::register($_POST);
    if ($res['success']) {
        setFlash('success', 'Your account is ready.');
        redirect(BASE_URL . '/index.php');
    }
    $errors = $res['errors'] ?? [];
}

require_once __DIR__ . '/includes/header.php';

/** Render an inline field error linked to its input for screen readers. */
function fieldError(array $errors, string $key): string {
    if (!isset($errors[$key])) return '';
    return '<p class="form-error" id="err-' . e($key) . '">' . icon('alert', 'icon-sm') . '<span>' . e($errors[$key]) . '</span></p>';
}
function fieldClass(array $errors, string $key): string {
    return isset($errors[$key]) ? ' has-error' : '';
}
function fieldAria(array $errors, string $key): string {
    return isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="err-' . e($key) . '"' : '';
}
?>

<div class="container page">
    <div class="card card-lg" style="max-width:520px;margin-inline:auto">
        <div style="text-align:center;margin-bottom:var(--space-8)">
            <h1 class="page-title">Create an account</h1>
            <p class="page-sub">Track orders and save your delivery details</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" role="alert" style="margin-bottom:var(--space-5)">
                <?= icon('alert') ?><span>Check the highlighted fields below.</span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?= csrfField() ?>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="first_name">First name</label>
                    <input type="text" id="first_name" name="first_name" autocomplete="given-name" required autofocus
                           class="form-control<?= fieldClass($errors, 'first_name') ?>"<?= fieldAria($errors, 'first_name') ?>
                           value="<?= e($_POST['first_name'] ?? '') ?>">
                    <?= fieldError($errors, 'first_name') ?>
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_name">Last name</label>
                    <input type="text" id="last_name" name="last_name" autocomplete="family-name" required
                           class="form-control<?= fieldClass($errors, 'last_name') ?>"<?= fieldAria($errors, 'last_name') ?>
                           value="<?= e($_POST['last_name'] ?? '') ?>">
                    <?= fieldError($errors, 'last_name') ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" id="email" name="email" autocomplete="email" required placeholder="you@example.com"
                       class="form-control<?= fieldClass($errors, 'email') ?>"<?= fieldAria($errors, 'email') ?>
                       value="<?= e($_POST['email'] ?? '') ?>">
                <?= fieldError($errors, 'email') ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone <span class="opt">(optional)</span></label>
                <input type="tel" id="phone" name="phone" autocomplete="tel" placeholder="01XXXXXXXXX"
                       class="form-control<?= fieldClass($errors, 'phone') ?>"<?= fieldAria($errors, 'phone') ?>
                       value="<?= e($_POST['phone'] ?? '') ?>">
                <?= fieldError($errors, 'phone') ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="field-password">
                    <input type="password" id="password" name="password" autocomplete="new-password" required minlength="6"
                           class="form-control<?= fieldClass($errors, 'password') ?>"<?= fieldAria($errors, 'password') ?>>
                    <button type="button" class="pw-toggle" aria-label="Show password">
                        <?= icon('eye') ?>
                    </button>
                </div>
                <?= fieldError($errors, 'password') ?>
                <p class="form-hint">At least 6 characters.</p>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm password</label>
                <div class="field-password">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           autocomplete="new-password" required
                           class="form-control<?= fieldClass($errors, 'password_confirmation') ?>"<?= fieldAria($errors, 'password_confirmation') ?>>
                    <button type="button" class="pw-toggle" aria-label="Show password">
                        <?= icon('eye') ?>
                    </button>
                </div>
                <?= fieldError($errors, 'password_confirmation') ?>
            </div>

            <label class="check" style="margin-top:var(--space-4)">
                <input type="checkbox" name="accept_terms" value="1" required>
                <span>
                    I agree to the <a href="<?= BASE_URL ?>/terms.php" style="color:var(--primary)">terms</a>
                    and <a href="<?= BASE_URL ?>/privacy.php" style="color:var(--primary)">privacy policy</a>
                </span>
            </label>

            <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:var(--space-5)">Create account</button>
        </form>

        <p class="t-sm t-subtle t-center" style="margin-top:var(--space-8)">
            Already have an account? <a href="<?= BASE_URL ?>/login.php" style="color:var(--primary);font-weight:600">Sign in</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
