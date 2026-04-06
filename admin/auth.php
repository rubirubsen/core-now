<?php
// admin/auth.php — wird von jeder Admin-Seite als erstes inkludiert

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Strict']);
    session_start();
}

$cfg = require __DIR__ . '/config.php';

function isLoggedIn(): bool {
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /admin/');
        exit;
    }
}

function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        exit('Ungültiger CSRF-Token.');
    }
}
