<?php
require_once __DIR__ . '/auth.php';
requireLogin();
verifyCsrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/dashboard.php');
    exit;
}

require_once __DIR__ . '/../db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM insights WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: /admin/dashboard.php?deleted=1');
exit;
