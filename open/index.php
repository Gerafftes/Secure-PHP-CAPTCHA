<?php
/* ===== Session-Hardening ===== */
$useHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
ini_set('session.use_trans_sid','0');
ini_set('session.use_only_cookies','1');
ini_set('session.use_strict_mode','1');
session_set_cookie_params([
  'lifetime'=>0,'path'=>'/','domain'=>'',
  'secure'=>$useHttps,'httponly'=>true,'samesite'=>'Lax'
]);
if ($useHttps) header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
session_start();
if (empty($_SESSION['captcha_expected']) || count($_SESSION['captcha_expected']) !== 6) {
    $_SESSION['captcha_expected'] = [];
    for ($i = 1; $i <= 6; $i++) {
        $r = random_int(1, 36);
        $_SESSION['captcha_expected'][$i] = ($r <= 26) 
            ? chr(64 + $r)     // A–Z
            : (string)($r - 27); // 0–9
    }
}

/* ===== Reset ===== */
if (isset($_GET['reset'])) {
  unset($_SESSION['captcha_expected'], $_SESSION['captcha_entered'], $_SESSION['csrf_token']);
  header('Location: index.php'); exit;
}

/* ===== Fingerprint & Rotation (optional, wie gehabt) ===== */
$ip=$_SERVER['REMOTE_ADDR']??''; $ip24=preg_replace('~(\d+\.\d+\.\d+)\.\d+~','$1.0',$ip);
$ua=$_SERVER['HTTP_USER_AGENT']??''; $fp=hash('sha256',$ip24.'|'.$ua);
if (empty($_SESSION['__init'])) {
  $_SESSION['__init']=time(); $_SESSION['__fprint']=$fp; $_SESSION['__last_rotate']=time();
  session_regenerate_id(true);
} else {
  if (!hash_equals($_SESSION['__fprint']??'',$fp)) { $_SESSION=[]; session_destroy(); session_start(); }
  if (($_SESSION['__last_rotate']??0)+300<time()) { session_regenerate_id(true); $_SESSION['__last_rotate']=time(); }
}

/* ===== CSRF ===== */
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));

/* ===== Step-Logik =====
   - $currentStep ist das „aktive“ Kästchen (1..6)
   - Klick auf ein anderes Kästchen kommt als POST[goto]=Ziel
   - Beim Klick speichern wir zuerst die Eingabe des aktuellen Kästchens
*/
$hasPost = ($_SERVER['REQUEST_METHOD']==='POST');
$currentStep = 1;

/* Startzustand */
if (empty($_SESSION['captcha_expected']) || empty($_SESSION['captcha_entered'])) {
  $_SESSION['captcha_expected'] = [];
  $_SESSION['captcha_entered']  = [];
}

/* Eingabe des bisherigen aktiven Kästchens speichern + Ziel bestimmen */
if ($hasPost) {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400); die('CSRF ungültig.');
  }

  $prev = isset($_POST['current']) ? max(1, min(6, (int)$_POST['current'])) : 1;

  // Eingabe (ein Zeichen) sichern
  $val = isset($_POST['captcha_input']) ? mb_substr(trim((string)$_POST['captcha_input']), 0, 1) : '';
  if ($val !== '') {
    $_SESSION['captcha_entered'][$prev] = $val;
  }

  // Ziel bestimmen (nächstes/anderes Kästchen oder Submit)
  if (isset($_POST['goto'])) {
    $goto = (int)$_POST['goto'];
    if ($goto === 7) {  // SUBMIT
      header('Location: verify.php'); exit;
    }
    $currentStep = max(1, min(6, $goto));
  } else {
    $currentStep = $prev; // Falls irgendwas ohne goto kam, bleib auf gleichem Feld
  }
} else {
  // Erste Anzeige: beginne bei 1
  $currentStep = 1;
}

/* Für das aktuelle Feld das Zeichen nur erzeugen, wenn es noch nicht existiert */
if (empty($_SESSION['captcha_expected']) || count($_SESSION['captcha_expected']) !== 6) {
    $_SESSION['captcha_expected'] = [];
    for ($i = 1; $i <= 6; $i++) {
        $r = random_int(1, 36);
        $_SESSION['captcha_expected'][$i] = ($r <= 26) ? chr(64 + $r) : (string)($r - 27);
    }
}

/* Eingaben-Array sicherstellen (aber nicht überschreiben) */
if (isset($_GET['reset'])) {
  unset($_SESSION['captcha_expected'], $_SESSION['captcha_entered'], $_SESSION['csrf_token']);
  header('Location: index.php'); exit;
}

/* Hilfsfunktion: Stelle sicher, dass zurückgeklickte Felder ihr altes Zeichen behalten.
   (Neue Felder werden beim ersten Betreten erzeugt, s.o.) */

/* UI-Variablen */
$isLast  = ($currentStep===6);
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DDoS Protection – Schritt <?php echo $currentStep; ?>/6</title>
<style>
:root{
  --bg:#1c1e24; --panel:#262a33; --text:#e8edf6; --muted:#9aa3b3;
  --slot:#353a45; --slotActive:#e45757; --accent:#e45757; --accent2:#7c3aed;
}
*{box-sizing:border-box}
body{
  margin:0; min-height:100vh; display:grid; place-items:center;
  background: radial-gradient(60rem 60rem at 20% 10%, #2b2f38 0, #1c1e24 60%);
  color:var(--text); font-family:system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
}
.card{
  width:420px; background:var(--panel); border-radius:14px; padding:28px;
  box-shadow:0 20px 60px rgba(0,0,0,.45); border:1px solid #313543;
}
h1{margin:0 0 8px;font-weight:800;letter-spacing:.3px}
.sub{color:var(--muted);font-size:.95rem;margin-bottom:18px}
.slots{display:grid; grid-template-columns:repeat(6, 1fr); gap:10px; margin-bottom:18px}
.slot{
  width:100%; height:56px; display:grid; place-items:center;
  border-radius:9px; background:var(--slot); border:1px solid #3e4452; color:#dfe6ef;
  font-size:1.25rem; letter-spacing:.5px; text-align:center;
}
.slot.disabled{opacity:.55}
.slot.active{ outline:2px solid var(--slotActive); background:#3a3f4a; }
.slot button{
  width:100%; height:100%; background:transparent; color:inherit; border:none; cursor:pointer; font:inherit;
}
.slot input{
  width:100%; height:100%; background:transparent; border:none; color:#fff;
  font:inherit; text-align:center; outline:none;
}
.circle{
  width:140px; height:140px; border-radius:50%; overflow:hidden; border:6px solid #ef5353;
  margin:6px auto 10px; box-shadow:0 10px 30px rgba(239,83,83,.35);
}
.circle img{display:block; width:100%; height:100%}
.btnrow{display:flex; gap:12px; margin-top:12px}
.btn{
  flex:1; padding:12px 16px; font-weight:700; letter-spacing:.3px;
  background:#ef5353; border:none; border-radius:9px; color:#fff; cursor:pointer;
  box-shadow:0 8px 20px rgba(239,83,83,.35);
}
.btn.secondary{background:#7c3aed; box-shadow:0 8px 20px rgba(124,58,237,.35); text-decoration:none; display:inline-grid; place-items:center}
.center{text-align:center}
.progress{color:#c9d2e2; font-weight:600; letter-spacing:.35em; margin-top:6px}
</style>
</head>
<body>
<div class="card">
  <h1 class="center">DDoS Protection</h1>
  <div class="sub center">Select each box and enter the letter or number you see within the circle below.</div>

  <!-- Ein einziges Formular kapselt alles:
       - Aktives Feld enthält das Input
       - Andere Felder sind Buttons (goto), die beim Klick navigieren -->
  <form method="post" action="index.php" id="form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <input type="hidden" name="current" value="<?php echo $currentStep; ?>">

    <div class="slots">
      <?php for($i=1;$i<=6;$i++): ?>
        <?php if ($i === $currentStep): ?>
          <div class="slot active">
            <input type="text" name="captcha_input" maxlength="1" autofocus>
          </div>
        <?php else: ?>
          <div class="slot disabled">
            <button type="submit" name="goto" value="<?php echo $i; ?>">
              <?php echo htmlspecialchars($_SESSION['captcha_entered'][$i] ?? ''); ?>
            </button>
          </div>
        <?php endif; ?>
      <?php endfor; ?>
    </div>

    <div class="circle">
      <img src="generate.php?step=<?php echo $currentStep; ?>&r=<?php echo random_int(1,999999); ?>" alt="Captcha">
    </div>
    <div class="center progress"><?php echo $currentStep; ?> / 6</div>

    <div class="btnrow">
      <!-- SUBMIT führt zu verify.php (per goto=7), RESET setzt alles zurück -->
      <button class="btn" type="submit" name="goto" value="<?php echo $isLast ? 7 : min(6, $currentStep+1); ?>">
        <?php echo $isLast ? 'SUBMIT' : 'NEXT'; ?>
      </button>
      <a class="btn secondary" href="?reset=1">RESET</a>
    </div>
  </form>
</div>
</body>
</html>
