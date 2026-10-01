<?php
$pageTitle   = "Addresses";
$accountPage = 'addresses';
require_once __DIR__ . '/includes/auth.php';

$userId    = Auth::id();
$userModel = new User();
$errors    = [];
$editing   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_address') {
    CSRF::requireValid();
    $addressId = !empty($_POST['address_id']) ? (int)$_POST['address_id'] : null;

    $data = [
        'label'         => trim($_POST['label'] ?? 'Home'),
        'full_name'     => trim($_POST['full_name'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'address_line1' => trim($_POST['address_line1'] ?? ''),
        'address_line2' => trim($_POST['address_line2'] ?? ''),
        'city'          => trim($_POST['city'] ?? 'Dhaka'),
        'area'          => trim($_POST['area'] ?? ''),
        'postal_code'   => trim($_POST['postal_code'] ?? ''),
        'is_default'    => !empty($_POST['is_default']) ? 1 : 0,
    ];

    if ($data['full_name'] === '')     $errors['full_name']     = 'Enter the recipient name';
    if ($data['phone'] === '')         $errors['phone']         = 'Enter a contact phone number';
    if ($data['address_line1'] === '') $errors['address_line1'] = 'Enter the street address';
    if ($data['city'] === '')          $errors['city']          = 'Enter the city';

    if (empty($errors)) {
        $userModel->saveAddress($userId, $data, $addressId);
        setFlash('success', $addressId ? 'Address updated.' : 'Address saved.');
        redirect(BASE_URL . '/addresses.php');
    }
    $editing = array_merge($data, ['id' => $addressId]);
}

if (isset($_GET['delete'])) {
    CSRF::requireValid();
    $userModel->deleteAddress($userId, (int)$_GET['delete']);
    setFlash('success', 'Address removed.');
    redirect(BASE_URL . '/addresses.php');
}

// Editing an existing address via ?edit=
if (isset($_GET['edit']) && !$editing) {
    foreach ($userModel->getAddresses($userId) as $a) {
        if ((int)$a['id'] === (int)$_GET['edit']) { $editing = $a; break; }
    }
}

$addresses = $userModel->getAddresses($userId);
$showForm  = $editing !== null || isset($_GET['new']) || empty($addresses);

require_once __DIR__ . '/includes/header.php';

function aErr(array $errors, string $k): string {
    return isset($errors[$k])
        ? '<p class="form-error" id="err-' . e($k) . '">' . icon('alert', 'icon-sm') . '<span>' . e($errors[$k]) . '</span></p>'
        : '';
}
function aCls(array $errors, string $k): string { return isset($errors[$k]) ? ' has-error' : ''; }
?>

<div class="container page">
    <div class="page-head">
        <h1 class="page-title">Addresses</h1>
        <p class="page-sub">Used to prefill checkout</p>
    </div>

    <div class="account-layout">
        <?php require __DIR__ . '/includes/account-nav.php'; ?>

        <div class="stack-6">

            <?php if (!empty($addresses)): ?>
                <section>
                    <div class="row-between" style="margin-bottom:var(--space-4)">
                        <h2 class="card-title">Saved addresses</h2>
                        <?php if (!$showForm): ?>
                            <a href="?new=1#address-form" class="btn btn-secondary btn-sm"><?= icon('plus', 'icon-sm') ?> Add address</a>
                        <?php endif; ?>
                    </div>

                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:var(--space-4)">
                        <?php foreach ($addresses as $addr): ?>
                            <div class="card">
                                <div class="row-between" style="margin-bottom:var(--space-3)">
                                    <span class="pill pill-neutral"><?= e($addr['label']) ?></span>
                                    <?php if (!empty($addr['is_default'])): ?>
                                        <span class="pill pill-info">Default</span>
                                    <?php endif; ?>
                                </div>
                                <p class="t-semi t-sm"><?= e($addr['full_name']) ?></p>
                                <p class="t-sm t-muted" style="margin-top:var(--space-1)"><?= e($addr['phone']) ?></p>
                                <p class="t-sm t-muted" style="margin-top:var(--space-2);line-height:1.6">
                                    <?= e($addr['address_line1']) ?>
                                    <?= !empty($addr['address_line2']) ? '<br>' . e($addr['address_line2']) : '' ?>
                                    <br><?= e($addr['city']) ?><?= !empty($addr['area']) ? ', ' . e($addr['area']) : '' ?>
                                    <?= e($addr['postal_code'] ?? '') ?>
                                </p>
                                <div class="row" style="gap:var(--space-2);margin-top:var(--space-4)">
                                    <a href="?edit=<?= (int)$addr['id'] ?>#address-form" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="?delete=<?= (int)$addr['id'] ?>&csrf_token=<?= e(CSRF::getToken()) ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirmDelete(event, this)">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($showForm): ?>
                <section class="card" id="address-form">
                    <div class="card-head">
                        <h2 class="card-title"><?= $editing && !empty($editing['id']) ? 'Edit address' : 'Add an address' ?></h2>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error" role="alert" style="margin-bottom:var(--space-5)">
                            <?= icon('alert') ?><span>Check the highlighted fields below.</span>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="save_address">
                        <?php if ($editing && !empty($editing['id'])): ?>
                            <input type="hidden" name="address_id" value="<?= (int)$editing['id'] ?>">
                        <?php endif; ?>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="label">Label</label>
                                <input type="text" id="label" name="label" class="form-control" placeholder="Home"
                                       value="<?= e($editing['label'] ?? 'Home') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="full_name">Recipient name</label>
                                <input type="text" id="full_name" name="full_name" required autocomplete="name"
                                       class="form-control<?= aCls($errors, 'full_name') ?>"
                                       value="<?= e($editing['full_name'] ?? '') ?>">
                                <?= aErr($errors, 'full_name') ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" required autocomplete="tel" placeholder="01XXXXXXXXX"
                                   class="form-control<?= aCls($errors, 'phone') ?>"
                                   value="<?= e($editing['phone'] ?? '') ?>">
                            <?= aErr($errors, 'phone') ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="address_line1">Street address</label>
                            <input type="text" id="address_line1" name="address_line1" required autocomplete="address-line1"
                                   class="form-control<?= aCls($errors, 'address_line1') ?>"
                                   value="<?= e($editing['address_line1'] ?? '') ?>">
                            <?= aErr($errors, 'address_line1') ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="address_line2">Apartment or floor <span class="opt">(optional)</span></label>
                            <input type="text" id="address_line2" name="address_line2" class="form-control"
                                   autocomplete="address-line2" value="<?= e($editing['address_line2'] ?? '') ?>">
                        </div>

                        <div class="form-grid-3">
                            <div class="form-group">
                                <label class="form-label" for="city">City</label>
                                <input type="text" id="city" name="city" required autocomplete="address-level2"
                                       class="form-control<?= aCls($errors, 'city') ?>"
                                       value="<?= e($editing['city'] ?? 'Dhaka') ?>">
                                <?= aErr($errors, 'city') ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="area">Area <span class="opt">(optional)</span></label>
                                <input type="text" id="area" name="area" class="form-control"
                                       value="<?= e($editing['area'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="postal_code">Postcode <span class="opt">(optional)</span></label>
                                <input type="text" id="postal_code" name="postal_code" class="form-control"
                                       autocomplete="postal-code" value="<?= e($editing['postal_code'] ?? '') ?>">
                            </div>
                        </div>

                        <label class="check">
                            <input type="checkbox" name="is_default" value="1" <?= !empty($editing['is_default']) ? 'checked' : '' ?>>
                            <span>Use as my default delivery address</span>
                        </label>

                        <div class="row" style="gap:var(--space-3);margin-top:var(--space-5)">
                            <button type="submit" class="btn btn-primary">Save address</button>
                            <?php if (!empty($addresses)): ?>
                                <a href="<?= BASE_URL ?>/addresses.php" class="btn btn-secondary">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
async function confirmDelete(e, link) {
    e.preventDefault();
    const ok = await confirmAction({
        title: 'Delete this address?',
        text: 'It will be removed from your saved addresses.',
        confirmLabel: 'Delete',
        danger: true,
    });
    if (ok) window.location.href = link.href;
    return false;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
