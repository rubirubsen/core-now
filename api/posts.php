<?php
ini_set('display_errors', '0');
// api/posts.php — GET: published posts als JSON
// Parameter: ?limit=3&offset=0

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db.php';

$limit  = min((int)($_GET['limit']  ?? 10), 50);
$offset = max((int)($_GET['offset'] ?? 0),  0);

$db   = getDB();
$stmt = $db->prepare("
    SELECT id, slug, title_de, title_en, body_de, body_en,
           category, attachments, published_at
    FROM insights
    WHERE published_at IS NOT NULL AND published_at <= GETUTCDATE()
    ORDER BY published_at DESC
    OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY
");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

foreach ($rows as &$row) {
    $row['attachments'] = $row['attachments']
        ? json_decode($row['attachments'], true)
        : [];
    $row['published_at'] = $row['published_at']
        ? (new DateTime($row['published_at']))->format('c')
        : null;
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
