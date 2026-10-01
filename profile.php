<?php
$pageTitle   = "Profile";
$accountPage = 'profile';
require_once __DIR__ . '/includes/auth.php';

$userId    = Auth::id();
$userModel = new User();
$user      = $userModel->findById($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    CSRF::requireValid();
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');

    if ($firstName === '' || $lastName === '') {
        setFlash('error', 'First and last name are both required.');
    } else {
        $userModel->updateProfile($userId, [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'phone'      => $phone,
        ]);
        setFlash('success', 'Your details have been saved.');
        redirect(BASE_URL . '/profile.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    CSRF::requireValid();
    $current = $_POST['current_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $fullUser = Database::getInstance()->fetchOne(
        "SELECT password_hash FROM users WHERE id = :id", [':id' => $userId]
    );

    if (!password_verify($current, $fullUser['password_hash'])) {
        setFlash('error', 'Your current password is not correct.');
    } elseif (strlen($newPass) < 6) {
        setFlash('error', 'Choose a new password of at least 6 characters.');
    } elseif ($newPass !== $confirm) {
        setFlash('error', 'The two new passwords do not match.');
    } else {
        $userModel->updatePassword($userId, $newPass);
        setFlash('success', 'Your password has been changed.');
        redirect(BASE_URL . '/profile.php');
    }
}

require_once __DIR__ . '/includes/header.php';

$pwField = function (string $id, string $name, string $label, string $autocomplete) {
    ?>
    <div class="form-group">
        <label class="form-label" for="<?= e($id) ?>"><?= e($label) ?></label>
        <div class="field-password">
            <input type="password" id="<?= e($id) ?>" name="<?= e($name) ?>" class="form-control" required
                   autocomplete="<?= e($autocomplete) ?>">
            <button type="button" class="pw-toggle" aria-label="Show password">
                <?= icon('eye') ?>
            </button>
        </div>
    </div>
    <?php
};
?>

<div class="container page">
    <div class="page-head">
        <h1 class="page-title">Profile</h1>
        <p class="page-sub"><?= e($user['email']) ?></p>
    </div>

    <div class="account-layout">
        <?php require __DIR__ . '/includes/account-nav.php'; ?>

        <div class="stack-6">
            <section class="card">
                <div class="card-head">
                    <h2 class="card-title">Your details</h2>
                </div>

                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="first_name">First name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" required
                                   autocomplete="given-name" value="<?= e($user['first_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="last_name">Last name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" required
                                   autocomplete="family-name" value="<?= e($user['last_name']) ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="email_display">Email</label>
                            <input type="email" id="email_display" class="form-control" value="<?= e($user['email']) ?>" disabled>
                            <p class="form-hint">To change your email, <a href="<?= BASE_URL ?>/contact.php" style="color:var(--primary)">contact support</a>.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                   autocomplete="tel" value="<?= e($user['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save changes</button>
                </form>
            </section>

            <section class="card">
                <div class="card-head">
                    <h2 class="card-title">Password</h2>
                </div>

                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="change_password">

                    <?php $pwField('current_password', 'current_password', 'Current password', 'current-password'); ?>

                    <div class="form-grid">
                        <?php $pwField('new_password', 'new_password', 'New password', 'new-password'); ?>
                        <?php $pwField('confirm_password', 'confirm_password', 'Confirm new password', 'new-password'); ?>
                    </div>

                    <button type="submit" class="btn btn-secondary">Update password</button>
                </form>
            </section>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
