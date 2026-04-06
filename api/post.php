<?php
ini_set('display_errors', '0');
// api/post.php — GET: einzelner Post per slug

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { http_response_code(400); exit('{"error":"slug required"}'); }

$db   = getDB();
$stmt = $db->prepare("
    SELECT id, slug, title_de, title_en, body_de, body_en,
           category, attachments, published_at, created_at
    FROM insights
    WHERE slug = ? AND published_at IS NOT NULL AND published_at <= GETUTCDATE()
");
$stmt->execute([$slug]);
$row = $stmt->fetch();

if (!$row) { http_response_code(404); exit('{"error":"not found"}'); }

$row['attachments'] = $row['attachments']
    ? json_decode($row['attachments'], true)
    : [];
$row['published_at'] = (new DateTime($row['published_at']))->format('c');

echo json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
