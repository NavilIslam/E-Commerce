<?php
/**
 * Shared shell for the static policy pages.
 * Expects $pageTitle, $policyIntro and $policyBody (HTML string).
 */
require_once __DIR__ . '/header.php';
?>
<div class="container page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= BASE_URL ?>/index.php">Home</a>
        <?= icon('chevron-r') ?>
        <span aria-current="page"><?= e($pageTitle) ?></span>
    </nav>

    <div class="page-narrow">
        <div class="page-head">
            <h1 class="page-title"><?= e($pageTitle) ?></h1>
            <?php if (!empty($policyIntro)): ?>
                <p class="page-sub"><?= e($policyIntro) ?></p>
            <?php endif; ?>
        </div>

        <div class="card card-lg">
            <div class="prose"><?= $policyBody ?></div>
        </div>

        <p class="t-sm t-subtle" style="margin-top:var(--space-6)">
            Questions? <a href="<?= BASE_URL ?>/contact.php" style="color:var(--primary)">Contact us</a>.
        </p>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
