<?php

session_start();
echo $_SESSION['test'] ='Captcha';

$randomNumber = random_int(1, 36);
echo $_SESSION['randomNumber'] = $randomNumber;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p>Random number: <?php echo $randomNumber; ?></p>
</body>
</html>
