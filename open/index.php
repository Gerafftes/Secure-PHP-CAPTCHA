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

/* ===== Reset ===== */
if (isset($_GET['reset'])) {
  unset($_SESSION['captcha_expected'], $_SESSION['captcha_entered'], $_SESSION['csrf_token']);
  header('Location: index.php'); exit;
}

/* ===== Fingerprint & Rotation ===== */
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

/* ===== Multi‑Step 1..6 ===== */
$step = isset($_POST['step']) ? max(1, min(7, (int)$_POST['step'])) : 1;

/* Neu initialisieren */
if ($step===1 && empty($_POST) && empty($_SESSION['captcha_expected'])) {
  $_SESSION['captcha_expected']=[]; $_SESSION['captcha_entered']=[];
}

/* Vorherige Eingabe speichern */
if (!empty($_POST)) {
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400); die('CSRF ungültig.');
  }
  $prev = $step - 1;
  if ($prev>=1 && $prev<=6) {
    $_SESSION['captcha_entered'][$prev] = mb_substr(trim((string)($_POST['captcha_input'] ?? '')),0,1);
  }
}

/* Fertig → prüfen */
if ($step===7) { header('Location: verify.php'); exit; }

/* Neues Zeichen für aktuellen Schritt */
$rand = random_int(1,36);
$char = ($rand<=26) ? chr(64+$rand) : (string)($rand-27);
$_SESSION['captcha_expected'][$step] = $char;

$isLast  = ($step===6);
$btnText = $isLast ? 'SUBMIT' : 'NEXT';
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DDoS Protection – Schritt <?php echo $step; ?>/6</title>
<style>
:root{
  --bg:#1c1e24; --panel:#262a33; --text:#e8edf6; --muted:#9aa3b3;
  --slot:#353a45; --slotActive:#e45757; --accent:#e45757; --accent2:#8b5cf6;
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
  font-size:1.25rem; letter-spacing:.5px;
}
.slot.disabled{opacity:.5}
.slot.active{ outline:2px solid var(--slotActive); }
.slot input{
  width:100%; height:100%; background:transparent; border:none; color:#fff;
  font:inherit; text-align:center; outline:none;
}
.circle{
  width:140px; height:140px; border-radius:50%; overflow:hidden; border:6px solid #ef5353;
  margin:0 auto 14px; box-shadow:0 10px 30px rgba(239,83,83,.35);
}
.circle img{display:block; width:100%; height:100%}
.btnrow{display:flex; gap:12px; margin-top:12px}
.btn{
  flex:1; padding:12px 16px; font-weight:700; letter-spacing:.3px;
  background:#ef5353; border:none; border-radius:9px; color:#fff; cursor:pointer;
  box-shadow:0 8px 20px rgba(239,83,83,.35);
}
.btn.secondary{background:#7c3aed; box-shadow:0 8px 20px rgba(124,58,237,.35)}
.btn:disabled{opacity:.6; cursor:not-allowed}
.center{text-align:center}
.timer{color:#c9d2e2; font-weight:600; letter-spacing:.35em; margin-top:6px}
.brand{position:absolute; top:12px; font-weight:800; color:#fff; letter-spacing:.4px}
</style>
</head>
<body>
<div class="card">

  <h1 class="center">DDoS Protection</h1>
  <div class="sub center">Select each text box and enter the letter or number you see within the circle below.</div>

  <!-- 6 Felder: nur aktuelles ist editierbar -->
  <div class="slots">
    <?php for($i=1;$i<=6;$i++): ?>
      <?php if ($i === $step): ?>
        <div class="slot active">
          <input type="text" name="dummy" value="" disabled>
        </div>
      <?php else: ?>
        <div class="slot disabled">
          <?php
            $val = $_SESSION['captcha_entered'][$i] ?? '';
            echo htmlspecialchars($val);
          ?>
        </div>
      <?php endif; ?>
    <?php endfor; ?>
  </div>

  <!-- Captcha-Kreis -->
  <div class="circle">
    <img src="generate.php?step=<?php echo $step; ?>&r=<?php echo random_int(1,999999); ?>" alt="Captcha">
  </div>
  <div class="center timer"><?php echo $step; ?> / 6</div>

  <!-- Formular: ein einziges aktives Eingabefeld -->
  <form method="post" action="index.php" class="center" style="margin-top:14px">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <input type="hidden" name="step" value="<?php echo $step+1; ?>">

    <div style="display:flex; justify-content:center; margin-bottom:12px">
      <div class="slot active" style="width:120px;height:56px">
        <input type="text" name="captcha_input" maxlength="1" autofocus required>
      </div>
    </div>

    <div class="btnrow">
      <button class="btn" type="submit"><?php echo $btnText; ?></button>
      <a class="btn secondary" href="?reset=1" style="text-decoration:none; display:inline-grid; place-items:center">RESET</a>
    </div>
  </form>
</div>
</body>
</html>
