<?php
$pageTitle = "Contact us";
require_once __DIR__ . '/config/config.php';

$errors = [];
$values = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if (Auth::check()) {
    $values['name']  = Auth::name();
    $u = (new User())->findById(Auth::id());
    $values['email'] = $u['email'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();
    foreach ($values as $k => $_) {
        $values[$k] = trim($_POST[$k] ?? '');
    }

    if ($values['name'] === '')                                        $errors['name']    = 'Tell us your name';
    if ($values['email'] === '')                                       $errors['email']   = 'We need an email to reply to';
    elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL))      $errors['email']   = 'Enter a valid email address';
    if ($values['message'] === '')                                     $errors['message'] = 'Let us know how we can help';

    if (empty($errors)) {
        Database::getInstance()->insert('contact_messages', [
            'name'    => $values['name'],
            'email'   => $values['email'],
            'subject' => $values['subject'],
            'message' => $values['message'],
        ]);
        setFlash('success', 'Thank you. We have received your message and will reply by email.');
        redirect(BASE_URL . '/contact.php');
    }
}

require_once __DIR__ . '/includes/header.php';

function cErr(array $errors, string $k): string {
    return isset($errors[$k])
        ? '<p class="form-error" id="err-' . e($k) . '">' . icon('alert', 'icon-sm') . '<span>' . e($errors[$k]) . '</span></p>'
        : '';
}
function cCls(array $errors, string $k): string { return isset($errors[$k]) ? ' has-error' : ''; }
function cAria(array $errors, string $k): string {
    return isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . e($k) . '"' : '';
}
?>

<div class="container page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= BASE_URL ?>/index.php">Home</a>
        <?= icon('chevron-r') ?>
        <span aria-current="page">Contact us</span>
    </nav>

    <div class="page-head">
        <h1 class="page-title">Contact us</h1>
        <p class="page-sub">We reply within one working day.</p>
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:var(--space-8);align-items:start"
         class="contact-layout">

        <section class="card card-lg">
            <h2 class="card-title" style="margin-bottom:var(--space-5)">Send a message</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" role="alert" style="margin-bottom:var(--space-5)">
                    <?= icon('alert') ?><span>Check the highlighted fields below.</span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?= csrfField() ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="name">Your name</label>
                        <input type="text" id="name" name="name" required autocomplete="name"
                               class="form-control<?= cCls($errors, 'name') ?>"<?= cAria($errors, 'name') ?>
                               value="<?= e($values['name']) ?>">
                        <?= cErr($errors, 'name') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" required autocomplete="email"
                               class="form-control<?= cCls($errors, 'email') ?>"<?= cAria($errors, 'email') ?>
                               value="<?= e($values['email']) ?>">
                        <?= cErr($errors, 'email') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="subject">Subject <span class="opt">(optional)</span></label>
                    <input type="text" id="subject" name="subject" class="form-control"
                           placeholder="Order number, or what this is about"
                           value="<?= e($values['subject']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="message">Message</label>
                    <textarea id="message" name="message" rows="6" required
                              class="form-control<?= cCls($errors, 'message') ?>"<?= cAria($errors, 'message') ?>><?= e($values['message']) ?></textarea>
                    <?= cErr($errors, 'message') ?>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Send message</button>
            </form>
        </section>

        <aside class="stack-6">
            <div class="card">
                <h2 class="card-title" style="margin-bottom:var(--space-4)">Get in touch</h2>
                <div class="footer-contact" style="color:var(--text-muted)">
                    <div>
                        <?= icon('phone') ?>
                        <span>
                            <a href="tel:<?= e(str_replace(' ', '', getSetting('site_phone', '+8809612000000'))) ?>"
                               style="color:var(--text-main);font-weight:600"><?= e(getSetting('site_phone', '+880 9612-000000')) ?></a>
                            <br><span class="t-xs t-subtle">Saturday to Thursday, 10am&ndash;7pm</span>
                        </span>
                    </div>
                    <div>
                        <?= icon('mail') ?>
                        <a href="mailto:<?= e(getSetting('site_email', 'support@novamart.com')) ?>"
                           style="color:var(--text-main)"><?= e(getSetting('site_email', 'support@novamart.com')) ?></a>
                    </div>
                    <div>
                        <?= icon('pin') ?>
                        <span><?= e(getSetting('site_address', 'Gulshan 2, Dhaka 1212, Bangladesh')) ?></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 class="card-title" style="margin-bottom:var(--space-3);font-size:var(--text-base)">Before you write</h2>
                <p class="t-sm t-muted" style="margin-bottom:var(--space-4)">
                    Many questions are answered already:
                </p>
                <ul class="footer-links" style="color:var(--text-muted)">
                    <li><a href="<?= BASE_URL ?>/faq.php" style="color:var(--primary)">Frequently asked questions</a></li>
                    <li><a href="<?= BASE_URL ?>/shipping.php" style="color:var(--primary)">Delivery times and charges</a></li>
                    <li><a href="<?= BASE_URL ?>/returns.php" style="color:var(--primary)">Returns and refunds</a></li>
                    <?php if (Auth::check()): ?>
                        <li><a href="<?= BASE_URL ?>/orders.php" style="color:var(--primary)">Track your order</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
