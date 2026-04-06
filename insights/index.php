<?php
require_once __DIR__ . '/../db.php';

$db   = getDB();
$stmt = $db->query("
    SELECT slug, title_de, title_en, body_de, body_en, category, published_at
    FROM insights
    WHERE published_at IS NOT NULL AND published_at <= GETUTCDATE()
    ORDER BY published_at DESC
");
$posts = $stmt->fetchAll();

$hasEn = array_filter($posts, fn($p) => !empty($p['title_en']));

function teaser(string $text, int $len = 200): string {
    $t = strip_tags($text);
    return mb_strlen($t) > $len ? mb_substr($t, 0, $len) . '…' : $t;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Insights – CORENOW</title>
  <meta name="description" content="Trends, Erfahrungen und Werkzeuge aus dem CORENOW-Alltag."/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Figtree:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/assets/css/style.css"/>
  <link rel="stylesheet" href="/assets/css/insights.css"/>
</head>
<body>

<nav id="nav" class="on-dark scrolled">
  <a href="/" class="nav-logo">CORE<em>NOW</em></a>
  <a href="/" class="nav-back">← Zurück zur Website</a>
</nav>

<section class="insights-page s-white">
  <div class="inner">
    <div class="insights-page-header">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px">
        <div>
          <span class="label">Insights</span>
          <h1 class="s-heading">Gedanken &amp;<br><em>Einblicke.</em></h1>
          <div class="rule"></div>
          <p class="s-body">Trends, Erfahrungen und Werkzeuge aus unserem Arbeitsalltag – kompakt und ohne Umwege.</p>
        </div>
        <?php if ($hasEn): ?>
        <div class="lang-toggle" style="margin-top:8px;flex-shrink:0">
          <button class="lt-btn active" data-lang="de">🇩🇪</button>
          <button class="lt-btn" data-lang="en">🇬🇧</button>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$posts): ?>
      <p style="color:var(--ink-muted);text-align:center;padding:60px 0">Noch keine Beiträge veröffentlicht.</p>
    <?php else: ?>
    <div class="insights-list">
      <?php foreach ($posts as $p):
        $date = (new DateTime($p['published_at']))->format('d. F Y');
      ?>
      <a href="/insights/post.php?slug=<?= urlencode($p['slug']) ?>" class="insight-row">
        <div class="insight-row-meta">
          <?php if ($p['category']): ?>
            <span class="insight-tag"><?= htmlspecialchars($p['category']) ?></span>
          <?php endif; ?>
          <span class="insight-date"><?= $date ?></span>
        </div>
        <h2 class="insight-row-title">
          <span class="lv lv-de"><?= htmlspecialchars($p['title_de']) ?></span>
          <?php if (!empty($p['title_en'])): ?>
            <span class="lv lv-en" hidden><?= htmlspecialchars($p['title_en']) ?></span>
          <?php endif; ?>
        </h2>
        <p class="insight-row-teaser">
          <span class="lv lv-de"><?= htmlspecialchars(teaser($p['body_de'])) ?></span>
          <?php if (!empty($p['body_en'])): ?>
            <span class="lv lv-en" hidden><?= htmlspecialchars(teaser($p['body_en'])) ?></span>
          <?php endif; ?>
        </p>
        <span class="insight-arrow lv lv-de">Weiterlesen →</span>
        <span class="insight-arrow lv lv-en" hidden>Read more →</span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<footer id="footer" data-theme="dark">
  <div class="ft-inner">
    <div class="ft-brand">CORE<em>NOW</em></div>
    <div class="ft-copy">© <?= date('Y') ?> CORENOW. Alle Rechte vorbehalten.</div>
  </div>
</footer>

<script>
document.querySelectorAll('.lt-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const lang = btn.dataset.lang;
    document.querySelectorAll('.lt-btn').forEach(b => b.classList.toggle('active', b.dataset.lang === lang));
    document.querySelectorAll('.lv').forEach(el => {
      el.hidden = !el.classList.contains('lv-' + lang);
    });
  });
});
</script>
</body>
</html>
