<?php
// db.php — zentrale MSSQL-Verbindung (PDO_SQLSRV)
// Wird von api/ und admin/ inkludiert

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $cfg = require __DIR__ . '/admin/config.php';

    $dsn = sprintf(
        'sqlsrv:Server=%s,%s;Database=%s;Encrypt=0;TrustServerCertificate=1',
        $cfg['host'], $cfg['port'], $cfg['name']
    );

    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        // Kein Stack-Trace nach außen
        http_response_code(503);
        exit('Datenbankverbindung fehlgeschlagen.');
    }

    return $pdo;
}
