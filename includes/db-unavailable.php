<?php
/**
 * Shown instead of a blank 500 when the database cannot be reached.
 *
 * Self-contained on purpose: it cannot use includes/header.php, because that
 * bootstraps the cart and category models and would fail for the same reason.
 * Styling is inlined so it renders even if assets are unavailable.
 */
http_response_code(503);
header('Content-Type: text/html; charset=UTF-8');
header('Retry-After: 300');

$logo      = (defined('BASE_URL') ? BASE_URL : '') . '/assets/images/logo.png';
$configured = (bool) getenv('DB_HOST');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>Setting up | NovaMart</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{--primary:#2563EB;--text:#0F172A;--muted:#475569;--subtle:#64748B;--border:#E2E8F0;--bg:#F8FAFC}
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;
       background:var(--bg);color:var(--text);line-height:24px;
       min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
  .card{background:#fff;border:1px solid var(--border);border-radius:12px;
        max-width:560px;width:100%;padding:48px 40px;text-align:center}
  .logo{height:32px;width:auto;margin:0 auto 32px}
  h1{font-size:32px;line-height:40px;font-weight:700;letter-spacing:-.02em;margin-bottom:12px}
  p{color:var(--muted);margin-bottom:16px}
  .steps{text-align:left;background:var(--bg);border-radius:8px;padding:20px 20px 20px 40px;margin:24px 0}
  .steps li{color:var(--muted);font-size:14px;line-height:20px;margin-bottom:8px}
  .steps li:last-child{margin-bottom:0}
  code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;
       background:#EFF6FF;color:#1D4ED8;padding:2px 6px;border-radius:4px}
  .btn{display:inline-flex;align-items:center;justify-content:center;height:44px;
       padding:0 24px;background:var(--primary);color:#fff;font-size:15px;font-weight:600;
       border-radius:8px;text-decoration:none}
  .note{font-size:12px;line-height:16px;color:var(--subtle);margin-top:24px;margin-bottom:0}
</style>
</head>
<body>
  <div class="card">
    <img class="logo" src="<?= htmlspecialchars($logo, ENT_QUOTES) ?>" alt="NovaMart">
    <h1>Almost there</h1>
    <p>
      The site is deployed and running, but it isn't connected to a database yet,
      so there are no products to show.
    </p>

    <ol class="steps">
      <li>Create a free MySQL database (Aiven or TiDB Cloud, no card needed).</li>
      <li>Import <code>database/novamart-full.sql</code>.</li>
      <li>Set <code>DB_HOST</code>, <code>DB_PORT</code>, <code>DB_NAME</code>,
          <code>DB_USER</code>, <code>DB_PASS</code>, <code>DB_SSL=1</code>.</li>
      <li>Redeploy.</li>
    </ol>

    <a class="btn" href="/_health.php">Check connection status</a>

    <p class="note">
      <?= $configured
            ? 'A database host is configured but could not be reached. Check the credentials and that your IP allow-list permits Vercel.'
            : 'No database host is configured yet. Full instructions are in DEPLOYMENT.md.' ?>
    </p>
  </div>
</body>
</html>
