<?php

include "phpqrcode/phpqrcode.php";

$domain = $_SERVER["HTTP_HOST"];
$merchant_id = isset($_GET['m']) ? intval($_GET['m']) : 1;

$value="http://{$domain}/order.php?m={$merchant_id}";
$filename = "qrcode/qrcode_{$merchant_id}.png";
$errorCorrectionLevel = "L";
$matrixPointSize = "8";
QRcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize);

?>
<img src="<?php echo $filename; ?>">
