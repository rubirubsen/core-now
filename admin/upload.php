<?php
// admin/upload.php — AJAX-Bild-Upload, gibt JSON zurück
require_once __DIR__ . '/auth.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
    http_response_code(400);
    exit('{"error":"no file"}');
}

$file = $_FILES['file'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
    http_response_code(415);
    exit('{"error":"invalid type"}');
}

if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(413);
    exit('{"error":"too large"}');
}

$name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
$dest = __DIR__ . '/../assets/img/insights/' . $name;

if (!is_dir(dirname($dest))) {
    http_response_code(500);
    exit(json_encode(['error' => 'directory missing: ' . dirname($dest)]));
}

if (!is_writable(dirname($dest))) {
    http_response_code(500);
    exit(json_encode(['error' => 'directory not writable: ' . dirname($dest)]));
}

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    http_response_code(500);
    exit('{"error":"move_uploaded_file failed"}');
}

echo json_encode(['url' => '/assets/img/insights/' . $name]);
