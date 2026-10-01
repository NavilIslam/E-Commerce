<?php
/**
 * Persistent account navigation. Sidebar on desktop, select on mobile.
 * Expects $accountPage to be one of the keys below.
 */
$accountLinks = [
    'profile'   => ['label' => 'Profile',   'url' => BASE_URL . '/profile.php',   'icon' => 'user'],
    'orders'    => ['label' => 'Orders',    'url' => BASE_URL . '/orders.php',    'icon' => 'package'],
    'addresses' => ['label' => 'Addresses', 'url' => BASE_URL . '/addresses.php', 'icon' => 'pin'],
    'wishlist'  => ['label' => 'Wishlist',  'url' => BASE_URL . '/wishlist.php',  'icon' => 'heart'],
];
$accountPage = $accountPage ?? '';
?>
<nav class="account-nav" aria-label="Account">
    <?php foreach ($accountLinks as $key => $link): ?>
        <a href="<?= $link['url'] ?>" class="<?= $accountPage === $key ? 'is-active' : '' ?>"
           <?= $accountPage === $key ? 'aria-current="page"' : '' ?>>
            <?= icon($link['icon']) ?> <?= e($link['label']) ?>
        </a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/logout.php" class="is-danger"><?= icon('logout') ?> Sign out</a>
</nav>

<div class="account-select">
    <label class="sr-only" for="account-jump">Account section</label>
    <select id="account-jump" class="form-control" onchange="if(this.value) window.location.href=this.value">
        <?php foreach ($accountLinks as $key => $link): ?>
            <option value="<?= $link['url'] ?>" <?= $accountPage === $key ? 'selected' : '' ?>><?= e($link['label']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
