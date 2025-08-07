<?php session_start();if(empty($_SESSION[base64_decode('Y2FwdGNoYV9leHBlY3RlZA==')])||count($_SESSION[base64_decode('Y2FwdGNoYV9leHBlY3RlZA==')])!==6){$_SESSION[base64_decode('Y2FwdGNoYV9leHBlY3RlZA==')]=[];for($_e66c3671=1;$_e66c3671<=6;$_e66c3671++){$_6c09ff9d=random_int(1,36);$_SESSION[base64_decode('Y2FwdGNoYV9leHBlY3RlZA==')][$_e66c3671]=($_6c09ff9d<=26)?chr(64+$_6c09ff9d):(string)($_6c09ff9d-27);}$_SESSION[base64_decode('Y2FwdGNoYV9lbnRlcmVk')]=[];$_SESSION[base64_decode('Y3VycmVudF9zdGVw')]=1;}if(empty($_SESSION[base64_decode('Y3NyZl90b2tlbg==')])){$_SESSION[base64_decode('Y3NyZl90b2tlbg==')]=bin2hex(random_bytes(32));}$_a71cb7ce=$_SESSION[base64_decode('Y3VycmVudF9zdGVw')];if($_SERVER[base64_decode('UkVRVUVTVF9NRVRIT0Q=')]===base64_decode('UE9TVA==')){if(!isset($_POST[base64_decode('Y3NyZl90b2tlbg==')])||!hash_equals($_SESSION[base64_decode('Y3NyZl90b2tlbg==')],$_POST[base64_decode('Y3NyZl90b2tlbg==')])){die(base64_decode('Q1NSRiB0b2tlbiB1bmfDvGx0aWc='));}$_d82832d7=isset($_POST[base64_decode('Y2FwdGNoYV9pbnB1dA==')])?mb_substr(trim($_POST[base64_decode('Y2FwdGNoYV9pbnB1dA==')]),0,1):'';if($_d82832d7!==''){$_SESSION[base64_decode('Y2FwdGNoYV9lbnRlcmVk')][$_a71cb7ce]=$_d82832d7;if($_a71cb7ce<6){$_SESSION[base64_decode('Y3VycmVudF9zdGVw')]++;}else{header(base64_decode('TG9jYXRpb246IHZlcmlmeS5waHA='));exit;}}$_a71cb7ce=$_SESSION[base64_decode('Y3VycmVudF9zdGVw')];}?>
    <!DOCTYPE html>
<html lang="de"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>CAPTCHA Schritt <?php echo $c;?> / 6</title>
<style>
body{font-family:system-ui,Arial,sans-serif;background:#1c1e24;color:#e8edf6;display:flex;justify-content:center;align-items:center;height:100vh}
.container{background:#262a33;padding:28px;border-radius:14px;width:420px;box-shadow:0 20px 60px rgba(0,0,0,.45);text-align:center}
.slots{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:18px}
.slot{height:56px;border-radius:9px;background:#353a45;color:#dfe6ef;font-size:1.5rem;line-height:56px;user-select:none}
.slot.current{background:#e45757;font-weight:700}
.circle{margin:0 auto 16px;width:140px;height:140px;border-radius:50%;border:6px solid #ef5353;box-shadow:0 10px 30px rgba(239,83,83,.35);overflow:hidden}
.circle img{width:100%;height:100%;display:block}
input[type=text]{font-size:2rem;width:100%;height:56px;border-radius:9px;border:none;text-align:center;outline:none;box-sizing:border-box}
button{margin-top:12px;background:#ef5353;border:none;color:#fff;font-weight:700;font-size:1rem;padding:12px 0;width:100%;border-radius:9px;cursor:pointer;box-shadow:0 8px 20px rgba(239,83,83,.35)}
</style>
</head><body>
<div class="container">
<h1>CAPTCHA Schritt <?php echo $c;?> / 6</h1>
<div class="slots"><?php for($i=1;$i<=6;$i++):?><div class="slot <?php echo $i===$c?'current':'';?>"><?php echo htmlspecialchars($_SESSION['y'][$i]??'');?></div><?php endfor;?></div>
<div class="circle"><img src="generate.php?step=<?php echo $c;?>&r=<?php echo random_int(1,999999);?>" alt="Captcha Zeichen"/></div>
<form method="post" autocomplete="off">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['t']);?>"/>
<input type="text" name="captcha_input" maxlength="1" autofocus required autocomplete="off"/>
<button type="submit"><?php echo ($c===6)?'Absenden':'Weiter';?></button>
</form>
</div>
</body></html>
