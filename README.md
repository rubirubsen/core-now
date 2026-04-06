# CORENOW Website

Firmenwebsite von CORENOW Software-Lösungen — mehrsprachiger PHP-Onepager mit integriertem Insights-Blog und Admin-Panel.

<img width="1904" height="933" alt="grafik" src="https://github.com/user-attachments/assets/c7899664-7f44-4549-b3c5-c6de2be295a0" />


## Stack

- **PHP 8.2** (PHP-FPM Docker Container)
- **MSSQL** (via PDO_SQLSRV / Microsoft ODBC Driver 18)
- **Vanilla JS** — kein Framework, kein Build-System, kein npm
- **CSS Custom Properties** — kein Preprocessor

## Struktur

```
index.php               Onepager (HTML-Template)
mail.php                Kontaktformular-Handler (POST → mail())
db.php                  MSSQL-Verbindung (PDO)
insights_setup.sql      DB-Migration (einmalig ausführen)
admin/
  config.php            ← NICHT committen (Credentials + PW-Hash)
  index.php             Login
  dashboard.php         Post-Übersicht
  edit.php              Post erstellen/bearbeiten
  delete.php            Post löschen
  upload.php            Bild-Upload (AJAX)
  auth.php              Session-Check
api/
  posts.php             GET published posts (JSON)
  post.php              GET einzelner Post per slug (JSON)
insights/
  index.php             Übersicht aller Insights
  post.php              Einzelner Post
assets/
  css/style.css         Alle Stile (Onepager)
  css/admin.css         Admin-Panel Stile
  css/insights.css      Insights-Seiten Stile
  js/translations.js    i18n: const T = { de, en, es, nl, fr }
  js/i18n.js            setLang(), Auto-Detect
  js/nav.js             Scroll-Theme, Hamburger-Menü
  js/reveal.js          IntersectionObserver Animationen
  js/tabs.js            KI-Sektion Tab-Switching
  js/insights.js        Onepager: Fetch + Render Insights-Cards
  js/form.js            Kontaktformular Fetch-Submit
  img/insights/         Hochgeladene Post-Bilder (nicht in Git)
```

## Setup (einmalig)

### 1. PHP sqlsrv Extension installieren (im PHP-FPM Container)

```bash
curl -sSL https://packages.microsoft.com/keys/microsoft.asc \
  | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg

echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-prod.gpg] \
https://packages.microsoft.com/debian/11/prod bullseye main" \
> /etc/apt/sources.list.d/mssql-release.list

apt-get update && ACCEPT_EULA=Y apt-get install -y msodbcsql18 unixodbc-dev
pecl install sqlsrv-5.12.0 pdo_sqlsrv-5.12.0

echo "extension=sqlsrv.so"     > /usr/local/etc/php/conf.d/sqlsrv.ini
echo "extension=pdo_sqlsrv.so" > /usr/local/etc/php/conf.d/pdo_sqlsrv.ini
kill -USR2 1
```

### 2. Datenbanktabelle anlegen

```bash
sqlcmd -S localhost -U sa -P 'passwort' -d corenow -i insights_setup.sql
```

### 3. Admin-Konfiguration anlegen

```bash
# Passwort-Hash erzeugen
docker exec <container> php -r "echo password_hash('deinPasswort', PASSWORD_DEFAULT);"
```

Dann `admin/config.php` nach diesem Schema anlegen (liegt nicht in Git):

```php
<?php
if (!defined('ADMIN_PASS_HASH'))    define('ADMIN_PASS_HASH', '$2y$10$...');
if (!defined('ADMIN_SESSION_KEY')) define('ADMIN_SESSION_KEY', 'cn_admin');

return [
    'host' => '127.0.0.1',
    'port' => '1433',
    'name' => 'corenow',
    'user' => 'sa',
    'pass' => 'passwort',
];
```

### 4. Upload-Verzeichnis anlegen

```bash
docker exec <container> mkdir -p /var/www/html/assets/img/insights
docker exec <container> chmod 775 /var/www/html/assets/img/insights
```

### 5. Admin absichern (NGINX)

```nginx
location = /admin/config.php { deny all; }
```

## Sprachen

DE · EN · ES · NL · FR — alle Strings in `assets/js/translations.js`.  
Blog-Posts: DE (Pflicht) + EN (optional). Andere Sprachen → EN-Fallback.

## Admin-Panel

Erreichbar unter `/admin/` — Single-User, Session-Auth.  
Posts können mit Titel, Text, Kategorie, Veröffentlichungsdatum und Anhängen (Bild, Bild-URL, YouTube, Link, Social) gepflegt werden.  
Bilder per Drag & Drop, Datei-Dialog oder Strg+V (Clipboard) einfügbar.

## Deployment

Dateien direkt per FTP/SFTP/rsync auf den PHP-fähigen Webserver.  
Kein Build-Schritt, keine Dependencies.
