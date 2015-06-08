<?php  
include "phpqrcode/phpqrcode.php";  
$value="http://52.24.99.103/order.php";  
$filename = 'qrcode/qrcode.png';
$errorCorrectionLevel = "L";  
$matrixPointSize = "4";  
QRcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize);  
?>
<img src="<?php echo $filename; ?>">
