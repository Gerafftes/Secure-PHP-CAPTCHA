<?php
session_start();

if (empty($_SESSION['captcha_expected']) || empty($_SESSION['captcha_entered'])) {
  echo 'Unvollständig. Bitte neu starten.';
  exit;
}

$ok = true;
$mismatch = [];
for ($i = 1; $i <= 6; $i++) {
  $exp = $_SESSION['captcha_expected'][$i] ?? '';
  $got = $_SESSION['captcha_entered'][$i] ?? '';
  if (strcasecmp((string) $exp, (string) $got) !== 0) {
    $ok = false;
    $mismatch[] = $i;
  }
}

unset($_SESSION['captcha_expected'], $_SESSION['captcha_entered'], $_SESSION['csrf_token']);

header('Content-Type: text/html; charset=utf-8');
if ($ok) {
  echo '<h1>Erfolg</h1><p>Alle 6 Zeichen korrekt.</p>';
} else {
  echo '<h1>Fehler</h1><p>Falsche Eingabe bei Feld: ' . implode(', ', $mismatch) . '.</p>';
  echo '<p><a href="index.php">Nochmal versuchen</a></p>';
}
