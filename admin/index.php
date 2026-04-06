<?php
require_once __DIR__ . '/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = $_POST['password'] ?? '';
    if (password_verify($pw, ADMIN_PASS_HASH)) {
        session_regenerate_id(true);
        $_SESSION[ADMIN_SESSION_KEY] = true;
        header('Location: /admin/dashboard.php');
        exit;
    }
    $error = 'Falsches Passwort.';
}

if (isLoggedIn()) {
    header('Location: /admin/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>CORENOW Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/assets/css/admin.css"/>
  <meta name="robots" content="noindex,nofollow"/>
</head>
<body class="admin-login">
  <div class="login-box">
    <div class="login-brand">CORE<em>NOW</em></div>
    <p class="login-sub">Admin-Bereich</p>
    <?php if ($error): ?>
      <div class="admin-alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="/admin/">
      <div class="f-grp">
        <label>Passwort</label>
        <input type="password" name="password" autofocus required/>
      </div>
      <button type="submit" class="admin-btn-primary">Anmelden</button>
    </form>
  </div>
</body>
</html>
