<?php
session_start();

if (empty($_SESSION['captcha_expected']) || count($_SESSION['captcha_expected']) !== 6) {
    $_SESSION['captcha_expected'] = [];
    for ($i = 1; $i <= 6; $i++) {
        $r = random_int(1, 36);
        $_SESSION['captcha_expected'][$i] = ($r <= 26) ? chr(64 + $r) : (string)($r - 27);
    }
    $_SESSION['captcha_entered'] = [];
    $_SESSION['current_step'] = 1;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$currentStep = $_SESSION['current_step'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token ungültig');
    }
    $input = isset($_POST['captcha_input']) ? mb_substr(trim($_POST['captcha_input']), 0, 1) : '';
    if ($input !== '') {
        $_SESSION['captcha_entered'][$currentStep] = $input;
        if ($currentStep < 6) {
            $_SESSION['current_step']++;
        } else {
            header('Location: verify.php');
            exit;
        }
    }
    $currentStep = $_SESSION['current_step'];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>CAPTCHA Schritt <?php echo $currentStep; ?> / 6</title>
<style>
  body {
    font-family: system-ui, Arial, sans-serif;
    background: #1c1e24;
    color: #e8edf6;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }
  .container {
    background: #262a33;
    padding: 28px;
    border-radius: 14px;
    width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,.45);
    text-align: center;
  }
  .slots {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    margin-bottom: 18px;
  }
  .slot {
    height: 56px;
    border-radius: 9px;
    background: #353a45;
    color: #dfe6ef;
    font-size: 1.5rem;
    line-height: 56px;
    user-select: none;
  }
  .slot.current {
    background: #e45757;
    font-weight: bold;
  }
  .circle {
    margin: 0 auto 16px;
    width: 140px;
    height: 140px;
    border-radius: 50%;
    border: 6px solid #ef5353;
    box-shadow: 0 10px 30px rgba(239,83,83,.35);
    overflow: hidden;
  }
  .circle img {
    width: 100%;
    height: 100%;
    display: block;
  }
  input[type="text"] {
    font-size: 2rem;
    width: 100%;
    height: 56px;
    border-radius: 9px;
    border: none;
    text-align: center;
    outline: none;
    box-sizing: border-box;
  }
  button {
    margin-top: 12px;
    background: #ef5353;
    border: none;
    color: white;
    font-weight: 700;
    font-size: 1rem;
    padding: 12px 0;
    width: 100%;
    border-radius: 9px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(239,83,83,.35);
  }
</style>
</head>
<body>
  <div class="container">
    <h1>CAPTCHA Schritt <?php echo $currentStep; ?> / 6</h1>
    <div class="slots">
      <?php for ($i=1; $i <=6; $i++): ?>
        <div class="slot <?php echo $i === $currentStep ? 'current' : ''; ?>">
          <?php echo htmlspecialchars($_SESSION['captcha_entered'][$i] ?? ''); ?>
        </div>
      <?php endfor; ?>
    </div>

    <div class="circle">
      <img src="generate.php?step=<?php echo $currentStep; ?>&r=<?php echo random_int(1,999999); ?>" alt="Captcha Zeichen" />
    </div>

    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />
      <input type="text" name="captcha_input" maxlength="1" autofocus required autocomplete="off" />
      <button type="submit"><?php echo $currentStep === 6 ? 'Absenden' : 'Weiter'; ?></button>
    </form>
  </div>
</body>
</html>
/* 
   .--.
   |o_o |
   |:_/ |
   //   \\\
  (\_\  /_)
   ^^   ^^
*/
