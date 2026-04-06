<?php
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../db.php';

$db   = getDB();
$post = null;
$id   = (int)($_GET['id'] ?? 0);

if ($id) {
    $stmt = $db->prepare("SELECT * FROM insights WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) { header('Location: /admin/dashboard.php'); exit; }
}

$attachments = $post ? (json_decode($post['attachments'] ?? '[]', true) ?: []) : [];

// ── SAVE ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title_de = trim($_POST['title_de'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $body_de  = trim($_POST['body_de']  ?? '');
    $body_en  = trim($_POST['body_en']  ?? '');
    $category = trim($_POST['category'] ?? '');
    $pub_raw  = trim($_POST['published_at'] ?? '');
    $pub      = $pub_raw
        ? (new DateTime($pub_raw, new DateTimeZone('Europe/Berlin')))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d H:i:s')
        : null;

    // Slug: aus Titel generieren oder manuell
    $slug = trim($_POST['slug'] ?? '');
    if (!$slug) {
        $slug = strtolower($title_de);
        $slug = str_replace(['ä','ö','ü','Ä','Ö','Ü','ß'], ['ae','oe','ue','ae','oe','ue','ss'], $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug = substr($slug, 0, 110);
    }

    // Attachments sammeln
    $atts = [];
    $types   = $_POST['att_type']    ?? [];
    $urls    = $_POST['att_url']     ?? [];
    $caps    = $_POST['att_caption'] ?? [];
    foreach ($types as $i => $type) {
        $url = trim($urls[$i] ?? '');
        if (!$url && $type !== 'image') continue;
        $atts[] = [
            'type'    => htmlspecialchars($type),
            'url'     => htmlspecialchars($url),
            'caption' => htmlspecialchars(trim($caps[$i] ?? '')),
        ];
    }

    // Bild-Upload
    if (!empty($_FILES['att_images']['name'][0])) {
        foreach ($_FILES['att_images']['tmp_name'] as $i => $tmp) {
            if (!is_uploaded_file($tmp)) continue;
            $orig = $_FILES['att_images']['name'][$i];
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) continue;
            if ($_FILES['att_images']['size'][$i] > 5 * 1024 * 1024) continue;
            $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = __DIR__ . '/../assets/img/insights/' . $name;
            if (move_uploaded_file($tmp, $dest)) {
                $atts[] = [
                    'type'    => 'image',
                    'url'     => '/assets/img/insights/' . $name,
                    'caption' => htmlspecialchars($_POST['att_img_caption'][$i] ?? ''),
                ];
            }
        }
    }

    $attsJson = $atts ? json_encode($atts, JSON_UNESCAPED_UNICODE) : null;

    if ($id) {
        $stmt = $db->prepare("
            UPDATE insights SET
                slug=?, title_de=?, title_en=?, body_de=?, body_en=?,
                category=?, attachments=?, published_at=?
            WHERE id=?
        ");
        $stmt->execute([$slug,$title_de,$title_en,$body_de,$body_en,
                        $category,$attsJson,$pub,$id]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO insights
                (slug,title_de,title_en,body_de,body_en,category,attachments,published_at)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$slug,$title_de,$title_en,$body_de,$body_en,
                        $category,$attsJson,$pub]);
    }

    header('Location: /admin/dashboard.php?saved=1');
    exit;
}

$cats = ['KI','Webentwicklung','Tooling','Security','DevOps','Allgemein'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?= $id ? 'Post bearbeiten' : 'Neuer Post' ?> – CORENOW Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="/assets/css/admin.css"/>
  <meta name="robots" content="noindex,nofollow"/>
</head>
<body class="admin-page">
  <header class="admin-header">
    <div class="admin-brand">CORE<em>NOW</em> <span><?= $id ? 'Post bearbeiten' : 'Neuer Post' ?></span></div>
    <a href="/admin/dashboard.php" class="admin-btn-ghost">← Zurück</a>
  </header>

  <main class="admin-main">
    <form method="POST" action="/admin/edit.php<?= $id ? "?id=$id" : '' ?>"
          enctype="multipart/form-data" class="edit-form">
      <input type="hidden" name="csrf" value="<?= csrfToken() ?>"/>

      <!-- Kern -->
      <div class="edit-grid">
        <div class="edit-col-main">
          <div class="f-grp">
            <label>Titel (DE) *</label>
            <input type="text" name="title_de" required
                   value="<?= htmlspecialchars($post['title_de'] ?? '') ?>"
                   placeholder="Titel auf Deutsch"/>
          </div>
          <div class="f-grp">
            <label>Titel (EN)</label>
            <input type="text" name="title_en"
                   value="<?= htmlspecialchars($post['title_en'] ?? '') ?>"
                   placeholder="Title in English (optional)"/>
          </div>
          <div class="f-grp">
            <label>Inhalt (DE) *</label>
            <textarea name="body_de" rows="12" required
                      placeholder="Text auf Deutsch …"><?= htmlspecialchars($post['body_de'] ?? '') ?></textarea>
          </div>
          <div class="f-grp">
            <label>Inhalt (EN)</label>
            <textarea name="body_en" rows="12"
                      placeholder="Content in English (optional) …"><?= htmlspecialchars($post['body_en'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="edit-col-side">
          <div class="side-box">
            <h4>Veröffentlichung</h4>
            <div class="f-grp">
              <label>Slug</label>
              <input type="text" name="slug"
                     value="<?= htmlspecialchars($post['slug'] ?? '') ?>"
                     placeholder="auto-generiert aus Titel"/>
            </div>
            <div class="f-grp">
              <label>Kategorie</label>
              <select name="category">
                <option value="">— keine —</option>
                <?php foreach ($cats as $c): ?>
                  <option value="<?= $c ?>"
                    <?= ($post['category'] ?? '') === $c ? 'selected' : '' ?>>
                    <?= $c ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="f-grp">
              <label>Veröffentlichen am</label>
              <input type="datetime-local" name="published_at"
                     value="<?= $post['published_at']
                        ? (new DateTime($post['published_at'], new DateTimeZone('UTC')))
                            ->setTimezone(new DateTimeZone('Europe/Berlin'))
                            ->format('Y-m-d\TH:i')
                        : '' ?>"/>
              <span class="f-hint">Leer lassen = Entwurf</span>
            </div>
            <button type="submit" class="admin-btn-primary w-full">Speichern</button>
          </div>

          <!-- Attachments -->
          <div class="side-box">
            <h4>Anhänge</h4>
            <div id="att-list">
              <?php foreach ($attachments as $i => $att): ?>
              <div class="att-block">
                <select name="att_type[]" class="att-type-sel">
                  <?php foreach (['youtube','url','social','image-url'] as $t): ?>
                    <option value="<?= $t ?>" <?= $att['type']===$t?'selected':'' ?>><?= $t==='image-url'?'Bild-URL':ucfirst($t) ?></option>
                  <?php endforeach; ?>
                </select>
                <input type="url" name="att_url[]"
                       value="<?= htmlspecialchars($att['url']) ?>"
                       placeholder="URL"/>
                <input type="text" name="att_caption[]"
                       value="<?= htmlspecialchars($att['caption'] ?? '') ?>"
                       placeholder="Beschriftung (optional)"/>
                <button type="button" class="att-remove" onclick="this.closest('.att-block').remove()">✕</button>
              </div>
              <?php endforeach; ?>
            </div>

            <div class="att-add-btns">
              <button type="button" class="admin-btn-ghost" onclick="addAtt('youtube')">+ YouTube</button>
              <button type="button" class="admin-btn-ghost" onclick="addAtt('url')">+ Link</button>
              <button type="button" class="admin-btn-ghost" onclick="addAtt('social')">+ Social</button>
              <button type="button" class="admin-btn-ghost" onclick="addAtt('image-url')">+ Bild-URL</button>
            </div>

            <div class="paste-zone" id="pasteZone">
              <span>📋 Bild hier einfügen (Strg+V) oder hineinziehen</span>
              <input type="file" id="pasteFileInput" accept="image/*"
                     style="position:absolute;inset:0;opacity:0;cursor:pointer"/>
            </div>
            <div id="pasteStatus" class="f-hint" style="margin-top:4px"></div>
          </div>
        </div>
      </div>
    </form>
  </main>

  <script>
  function addAtt(type, url, caption) {
    const labels = {
      youtube: 'YouTube-URL',
      url: 'Link-URL',
      social: 'Social-URL',
      'image-url': 'Bild-URL (https://…)'
    };
    const block = document.createElement('div');
    block.className = 'att-block';
    block.innerHTML = `
      <select name="att_type[]" class="att-type-sel">
        <option value="youtube"   ${type==='youtube'   ?'selected':''}>YouTube</option>
        <option value="url"       ${type==='url'       ?'selected':''}>Link</option>
        <option value="social"    ${type==='social'    ?'selected':''}>Social</option>
        <option value="image-url" ${type==='image-url' ?'selected':''}>Bild-URL</option>
      </select>
      <input type="url" name="att_url[]"
             placeholder="${labels[type] || 'URL'}"
             value="${url || ''}"/>
      <input type="text" name="att_caption[]"
             placeholder="Beschriftung (optional)"
             value="${caption || ''}"/>
      <button type="button" class="att-remove"
              onclick="this.closest('.att-block').remove()">✕</button>
    `;
    document.getElementById('att-list').appendChild(block);
  }

  // ── Upload-Helfer ──────────────────────────────────────────────────
  function uploadImage(file) {
    const status = document.getElementById('pasteStatus');
    if (!file || !file.type.startsWith('image/')) return;
    status.textContent = '⏳ Wird hochgeladen …';

    const fd = new FormData();
    fd.append('file', file);

    fetch('/admin/upload.php', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (data.url) {
          addAtt('image-url', window.location.origin + data.url, '');
          status.textContent = '✓ Hochgeladen: ' + data.url;
          setTimeout(() => status.textContent = '', 3000);
        } else {
          status.textContent = '✗ Fehler: ' + (data.error || 'unbekannt');
        }
      })
      .catch(() => { status.textContent = '✗ Upload fehlgeschlagen.'; });
  }

  // ── Strg+V Paste (nur wenn kein Text-Feld fokussiert) ────────────
  document.addEventListener('paste', e => {
    const tag = document.activeElement?.tagName;
    if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;

    const items = e.clipboardData?.items;
    if (!items) return;
    for (const item of items) {
      if (item.type.startsWith('image/')) {
        e.preventDefault();
        uploadImage(item.getAsFile());
        return;
      }
    }
  });

  // ── Drag & Drop / File-Input ───────────────────────────────────────
  const zone  = document.getElementById('pasteZone');
  const finput = document.getElementById('pasteFileInput');

  finput.addEventListener('change', () => {
    if (finput.files[0]) uploadImage(finput.files[0]);
    finput.value = '';
  });

  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const file = e.dataTransfer?.files[0];
    if (file) uploadImage(file);
  });
  </script>
</body>
</html>
