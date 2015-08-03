<?php
include_once('includes/application_top.php');

if ($_GET['size'] == 'medium') {
	$watermarkimage="medium.png";
} else {
	$watermarkimage="small.png";
}
$file=$_GET['img'];

$image = DIR_FS_CATALOG."/".$file;
$watermark = DIR_FS_CATALOG."/images/watermerk/".$watermarkimage;

$im = imagecreatefrompng($watermark);

$ext = substr($image, -3);

if (strtolower($ext) == "gif") {
if (!$im2 = imagecreatefromgif($image)) {
echo "Error opening $image!"; exit;
}
} else if(strtolower($ext) == "jpg") {
if (!$im2 = imagecreatefromjpeg($image)) {
echo "Error opening $image!"; exit;
}
} else if(strtolower($ext) == "png") {
if (!$im2 = imagecreatefrompng($image)) {
echo "Error opening $image!"; exit;
}
} else {
die;
}
imagefilledrectangle($im2, 0 , (imagesy($im2))-(imagesy($im)) , imagesx($im2) , imagesy($im2) , imagecolorallocatealpha($im2, 255, 255, 255, 127) );
imagecopy($im2, $im, (0), ((imagesy($im2))-(imagesy($im))), 0, 0, imagesx($im), imagesy($im));

$last_modified = gmdate('D, d M Y H:i:s T', filemtime ($image));

header("Last-Modified: $last_modified");
header("Content-Type: image/jpeg");
imagejpeg($im2,NULL,95);
imagedestroy($im);
imagedestroy($im2);
?>