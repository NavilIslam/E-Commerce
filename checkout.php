<?php
$pageTitle    = "Checkout";
$checkoutMode = true;   // strips the shopping nav from header/footer
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

$userId      = Auth::id();
$userModel   = new User();
$addresses   = $userModel->getAddresses($userId);
$defaultAddr = $userModel->getDefaultAddress($userId);
$userProfile = $userModel->findById($userId);

$cart    = new Cart($userId);
$details = $cart->getDetails();

if (empty($details['items'])) {
    setFlash('warning', 'Your cart is empty. Add something before checking out.');
    redirect(BASE_URL . '/cart.php');
}
?>

<div class="container page">
    <div class="page-narrow" style="max-width:none">

        <ol class="steps" aria-label="Checkout progress">
            <li class="step is-done"><span class="step-num"><?= icon('check', 'icon-sm') ?></span> <span class="step-label">Cart</span></li>
            <span class="step-sep" aria-hidden="true"></span>
            <li class="step is-current" aria-current="step"><span class="step-num">2</span> <span class="step-label">Delivery &amp; payment</span></li>
            <span class="step-sep" aria-hidden="true"></span>
            <li class="step"><span class="step-num">3</span> <span class="step-label">Confirmation</span></li>
        </ol>

        <form id="checkout-form" novalidate>
            <div class="checkout-layout">
                <div class="stack-6">

                    <!-- Delivery -->
                    <section class="card">
                        <h2 class="card-title" style="margin-bottom:var(--space-5)">Delivery address</h2>

                        <?php if (!empty($addresses)): ?>
                            <div class="form-group">
                                <label class="form-label" for="saved-address">Use a saved address</label>
                                <select id="saved-address" class="form-control" onchange="fillSavedAddress(this)">
                                    <option value="">Enter a new address</option>
                                    <?php foreach ($addresses as $addr): ?>
                                        <option value="<?= (int)$addr['id'] ?>"
                                                data-name="<?= e($addr['full_name']) ?>"
                                                data-phone="<?= e($addr['phone']) ?>"
                                                data-line1="<?= e($addr['address_line1']) ?>"
                                                data-line2="<?= e($addr['address_line2'] ?? '') ?>"
                                                data-city="<?= e($addr['city']) ?>"
                                                data-area="<?= e($addr['area'] ?? '') ?>"
                                                data-postal="<?= e($addr['postal_code'] ?? '') ?>"
                                                <?= ($defaultAddr && $defaultAddr['id'] == $addr['id']) ? 'selected' : '' ?>>
                                            <?= e($addr['label']) ?> - <?= e($addr['address_line1']) ?>, <?= e($addr['city']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="full_name">Full name</label>
                                <input type="text" id="full_name" name="full_name" class="form-control" required
                                       autocomplete="name"
                                       aria-describedby="err-full_name"
                                       value="<?= e($defaultAddr['full_name'] ?? trim(($userProfile['first_name'] ?? '') . ' ' . ($userProfile['last_name'] ?? ''))) ?>">
                                <p class="form-error" id="err-full_name" hidden></p>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" class="form-control" required
                                       autocomplete="tel" placeholder="01XXXXXXXXX"
                                       aria-describedby="err-phone"
                                       value="<?= e($defaultAddr['phone'] ?? $userProfile['phone'] ?? '') ?>">
                                <p class="form-error" id="err-phone" hidden></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="address_line1">Street address</label>
                            <input type="text" id="address_line1" name="address_line1" class="form-control" required
                                   autocomplete="address-line1" placeholder="House 12, Road 4"
                                   aria-describedby="err-address_line1"
                                   value="<?= e($defaultAddr['address_line1'] ?? '') ?>">
                            <p class="form-error" id="err-address_line1" hidden></p>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="address_line2">Apartment or floor <span class="opt">(optional)</span></label>
                            <input type="text" id="address_line2" name="address_line2" class="form-control"
                                   autocomplete="address-line2" placeholder="Flat 4B"
                                   value="<?= e($defaultAddr['address_line2'] ?? '') ?>">
                        </div>

                        <div class="form-grid-3">
                            <div class="form-group">
                                <label class="form-label" for="city">City</label>
                                <input type="text" id="city" name="city" class="form-control" required
                                       autocomplete="address-level2"
                                       aria-describedby="err-city"
                                       value="<?= e($defaultAddr['city'] ?? 'Dhaka') ?>">
                                <p class="form-error" id="err-city" hidden></p>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="area">Area <span class="opt">(optional)</span></label>
                                <input type="text" id="area" name="area" class="form-control" placeholder="Banani"
                                       value="<?= e($defaultAddr['area'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="postal_code">Postcode <span class="opt">(optional)</span></label>
                                <input type="text" id="postal_code" name="postal_code" class="form-control" placeholder="1213"
                                       autocomplete="postal-code"
                                       value="<?= e($defaultAddr['postal_code'] ?? '') ?>">
                            </div>
                        </div>

                        <label class="check">
                            <input type="checkbox" name="save_address" value="1" checked>
                            <span>Save this address for next time</span>
                        </label>
                    </section>

                    <!-- Payment -->
                    <section class="card">
                        <h2 class="card-title" style="margin-bottom:var(--space-3)">Payment</h2>
                        <p class="t-sm t-subtle" style="margin-bottom:var(--space-5)">
                            Online card and wallet payments are not enabled on this store yet.
                            Choose how you would like to pay and we will confirm the details with you.
                        </p>

                        <div class="stack" style="gap:var(--space-3)">
                            <label class="option-card">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <span class="grow">
                                    <span class="option-title"><?= icon('cash') ?> Cash on delivery</span>
                                    <span class="option-desc">Pay the courier when your order arrives.</span>
                                </span>
                            </label>

                            <label class="option-card">
                                <input type="radio" name="payment_method" value="bkash">
                                <span class="grow">
                                    <span class="option-title"><?= icon('wallet') ?> bKash or Nagad</span>
                                    <span class="option-desc">We will send you payment instructions after you place the order.</span>
                                </span>
                            </label>

                            <label class="option-card">
                                <input type="radio" name="payment_method" value="sslcommerz">
                                <span class="grow">
                                    <span class="option-title"><?= icon('card') ?> Card or bank transfer</span>
                                    <span class="option-desc">We will contact you with transfer details before dispatch.</span>
                                </span>
                            </label>
                        </div>

                        <div class="form-group" style="margin-top:var(--space-5);margin-bottom:0">
                            <label class="form-label" for="notes">Delivery notes <span class="opt">(optional)</span></label>
                            <textarea id="notes" name="notes" rows="2" class="form-control"
                                      placeholder="Call before arriving"></textarea>
                        </div>
                    </section>
                </div>

                <!-- Summary -->
                <div class="summary">
                    <div class="card">
                        <h2 class="card-title" style="margin-bottom:var(--space-4)">
                            Your order (<?= (int)$details['item_count'] ?>)
                        </h2>

                        <div class="review-list">
                            <?php foreach ($details['items'] as $item): ?>
                                <div class="review-item">
                                    <img src="<?= getImageUrl($item['image']) ?>" alt="" width="44" height="44">
                                    <span class="grow">
                                        <span class="cart-name" style="display:block"><?= e($item['name']) ?></span>
                                        <span class="review-qty"><?= (int)$item['quantity'] ?> &times; <?= formatPrice($item['effective_unit_price']) ?></span>
                                    </span>
                                    <span class="num t-semi t-sm"><?= formatPrice($item['line_total']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="sum-row">
                            <span>Subtotal</span>
                            <span><?= formatPrice($details['items_subtotal']) ?></span>
                        </div>

                        <?php if ($details['offer_discount'] > 0): ?>
                            <div class="sum-row is-credit">
                                <span>Offers</span>
                                <span>&minus;<?= formatPrice($details['offer_discount']) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($details['coupon_discount'] > 0): ?>
                            <div class="sum-row is-credit">
                                <span>Coupon (<?= e($details['coupon']['code']) ?>)</span>
                                <span>&minus;<?= formatPrice($details['coupon_discount']) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="sum-row">
                            <span>Delivery</span>
                            <span>
                                <?php if ($details['is_free_shipping']): ?>
                                    <strong style="color:var(--success)">Free</strong>
                                <?php else: ?>
                                    <?= formatPrice($details['shipping_fee']) ?>
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="sum-total">
                            <span>Total</span>
                            <span><?= formatPrice($details['total_amount']) ?></span>
                        </div>

                        <button type="submit" id="place-order" class="btn btn-primary btn-lg btn-block" style="margin-top:var(--space-5)">
                            Place order
                        </button>

                        <p class="t-xs t-subtle t-center" style="margin-top:var(--space-3)">
                            By placing this order you agree to our
                            <a href="<?= BASE_URL ?>/terms.php" style="color:var(--primary)">terms</a> and
                            <a href="<?= BASE_URL ?>/returns.php" style="color:var(--primary)">returns policy</a>.
                        </p>

                        <p style="margin-top:var(--space-4);text-align:center">
                            <a href="<?= BASE_URL ?>/cart.php" class="btn btn-tertiary btn-sm">Back to cart</a>
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function fillSavedAddress(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;
    const map = {
        full_name: 'name', phone: 'phone', address_line1: 'line1',
        address_line2: 'line2', city: 'city', area: 'area', postal_code: 'postal'
    };
    Object.entries(map).forEach(([id, key]) => {
        const el = document.getElementById(id);
        if (el) el.value = opt.dataset[key] || '';
    });
}

const REQUIRED = {
    full_name: 'Enter the recipient name',
    phone: 'Enter a phone number we can reach you on',
    address_line1: 'Enter the street address',
    city: 'Enter the city',
};

function showFieldError(id, message) {
    const input = document.getElementById(id);
    const err = document.getElementById('err-' + id);
    if (!input || !err) return;
    input.classList.add('has-error');
    input.setAttribute('aria-invalid', 'true');
    err.textContent = message;
    err.hidden = false;
}

function clearFieldError(id) {
    const input = document.getElementById(id);
    const err = document.getElementById('err-' + id);
    if (!input || !err) return;
    input.classList.remove('has-error');
    input.removeAttribute('aria-invalid');
    err.hidden = true;
}

Object.keys(REQUIRED).forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', () => clearFieldError(id));
});

document.getElementById('checkout-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    // Validate inline, next to each field, before touching the network.
    let firstBad = null;
    Object.entries(REQUIRED).forEach(([id, message]) => {
        const el = document.getElementById(id);
        if (!el.value.trim()) {
            showFieldError(id, message);
            if (!firstBad) firstBad = el;
        } else {
            clearFieldError(id);
        }
    });

    const phone = document.getElementById('phone');
    if (phone.value.trim() && !/^[0-9+\-\s()]{6,20}$/.test(phone.value.trim())) {
        showFieldError('phone', 'Use digits only, for example 01712345678');
        if (!firstBad) firstBad = phone;
    }

    if (firstBad) {
        firstBad.focus();
        firstBad.scrollIntoView({ block: 'center', behavior: 'smooth' });
        showToast('Check the highlighted fields', 'error');
        return;
    }

    const btn = document.getElementById('place-order');
    setBusy(btn, true);

    const payload = {};
    new FormData(e.target).forEach((v, k) => { payload[k] = v; });

    try {
        const res = await apiFetch(`${window.BASE_URL}/api/orders/create.php`, { method: 'POST', body: payload });
        showToast(res.message, 'success');
        if (res.data && res.data.redirect) {
            setTimeout(() => { window.location.href = res.data.redirect; }, 700);
        }
    } catch (err) {
        setBusy(btn, false);
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
