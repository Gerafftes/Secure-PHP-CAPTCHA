<?php
session_start();

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if (!isset($_SESSION['captcha_expected'][$step])) {
    // Falls Step ungültig → Notfallausgabe
    $char = '?';
} else {
    $char = $_SESSION['captcha_expected'][$step];
}

/* Zielgröße für die Ausgabe */
$W_final = 160; 
$H_final = 160;

/* Arbeitsfläche in kleiner Auflösung */
$scaleFactor = 0.5; // 50% Auflösung
$W = (int)($W_final * $scaleFactor);
$H = (int)($H_final * $scaleFactor);

/* Output & No-Cache */
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

/* Basisbild */
$im = imagecreatetruecolor($W,$H);
$bg  = imagecolorallocate($im, 205,205,205); // hellgrau
$cg  = imagecolorallocate($im, 155,155,155); // grauer Kreis
imagefilledrectangle($im, 0,0, $W,$H, $bg);
imagefilledellipse($im, $W/2, $H/2, $W-4, $H-4, $cg);

/* --- Rauschen --- */
$noiseCount = (int)($W*$H*0.25);
for ($i=0; $i<$noiseCount; $i++) {
    $c = imagecolorallocate($im, random_int(0,255), random_int(0,255), random_int(0,255));
    imagesetpixel($im, random_int(0,$W-1), random_int(0,$H-1), $c);
}

/* Störlinien */
$lines = 25; // Anzahl der Linien
for ($i = 0; $i < $lines; $i++) {
    imagesetthickness($im, random_int(1, 3));

    // 40% Chance auf Blauton 80,115,166 – sonst komplett zufällig
    if (random_int(1, 100) <= 40) {
        $lc = imagecolorallocate($im, 80, 115, 166);
    } else {
        $lc = imagecolorallocate($im, random_int(0, 255), random_int(0, 255), random_int(0, 255));
    }

    imageline($im, rand(0, $W), rand(0, $H), rand(0, $W), rand(0, $H), $lc);
}



/* Text */
$textColor = imagecolorallocate($im, 80, 115, 166); // RGB: Blau

$angle = random_int(-18, 18);

$fontPath = __DIR__.'/arial.ttf';
if (file_exists($fontPath)) {
    $fontSize = 27; // kleiner, weil kleinere Arbeitsfläche
    $bbox = imagettfbbox($fontSize, $angle, $fontPath, $char);
    $tw = max($bbox[2],$bbox[4]) - min($bbox[0],$bbox[6]);
    $th = max($bbox[1],$bbox[3]) - min($bbox[5],$bbox[7]);

    /* Zufällige Position statt exakt mittig */
    $offsetX = random_int(-10, 10); // X-Verschiebung
    $offsetY = random_int(-10, 10); // Y-Verschiebung

    $x = (int)(($W - $tw)/2) + $offsetX;
    $y = (int)(($H + $th)/2) + $offsetY;

    imagettftext($im, $fontSize, $angle, $x, $y, $textColor, $fontPath, $char);
} else {
    $font = 3;
    $tw=imagefontwidth($font)*strlen($char);
    $th=imagefontheight($font);
    $offsetX = random_int(-5, 5);
    $offsetY = random_int(-5, 5);
    $tx=(int)(($W-$tw)/2) + $offsetX; 
    $ty=(int)(($H-$th)/2) + $offsetY;
    imagestring($im,$font,$tx,$ty,$char,$textColor);
}

/* Blur */
if (function_exists('imagefilter')) {
    @imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);
}

/* --- Hochskalieren ohne Antialiasing (Pixel-Effekt) --- */
$out = imagecreatetruecolor($W_final, $H_final);
imagecopyresized($out, $im, 0, 0, 0, 0, $W_final, $H_final, $W, $H);

imagedestroy($im);
imagepng($out);
imagedestroy($out);
