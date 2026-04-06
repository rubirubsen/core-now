<?php
// mail.php – CORENOW Kontaktformular
// Einfach auf den Server laden, liegt neben index.html

header('Content-Type: text/plain; charset=utf-8');

// Erlaubte Sprachen für Betreff-Übersetzungen
$subjects = [
  'de' => 'Neue Kontaktanfrage über core-now.com',
  'en' => 'New contact request via core-now.com',
  'es' => 'Nueva solicitud de contacto vía core-now.com',
  'nl' => 'Nieuw contactverzoek via core-now.com',
  'fr' => 'Nouvelle demande de contact via core-now.com',
];

// Empfänger – hier Ihre E-Mail eintragen
$to = 'info@core-now.com';

// Felder validieren
$vorname  = trim(strip_tags($_POST['vorname']  ?? ''));
$nachname = trim(strip_tags($_POST['nachname'] ?? ''));
$email    = trim(strip_tags($_POST['email']    ?? ''));
$leistung = trim(strip_tags($_POST['leistung'] ?? ''));
$nachricht= trim(strip_tags($_POST['nachricht']?? ''));
$lang     = trim(strip_tags($_POST['lang']     ?? 'de'));

// Pflichtfelder prüfen
if (empty($vorname) || empty($nachname) || empty($email) || empty($nachricht)) {
  http_response_code(400);
  echo 'Bitte alle Pflichtfelder ausfüllen.';
  exit;
}

// E-Mail-Adresse validieren
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo 'Ungültige E-Mail-Adresse.';
  exit;
}

// Betreff je nach Sprache
$subject = $subjects[$lang] ?? $subjects['de'];

// Nachrichtentext
$body  = "Name:      $vorname $nachname\n";
$body .= "E-Mail:    $email\n";
$body .= "Leistung:  $leistung\n";
$body .= "Sprache:   $lang\n";
$body .= str_repeat('-', 40) . "\n\n";
$body .= $nachricht . "\n";

// E-Mail-Header
$headers  = "From: noreply@core-now.com\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Senden
if (mail($to, $subject, $body, $headers)) {
  http_response_code(200);
  echo 'ok';
} else {
  http_response_code(500);
  echo 'Fehler beim Senden. Bitte direkt per E-Mail kontaktieren.';
}