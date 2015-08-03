<?
if (isset($_GET['products_model'])) {
	$dir = '../images/foto/thumbs/';
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				if (strstr($file, $_GET['products_model'].'_')) {
					echo $file.'<br />';
					unlink($dir.$file);
				} else if ($file == $_GET['products_model'].'.jpg' || $file == $_GET['products_model'].'.gif' || $file == $_GET['products_model'].'.png') {
					echo $file.'<br />';
					unlink($dir.$file);
				}
			}
		}
		closedir($handle);
	}
} else {
	echo 'Error: geen products model meegegeven!!!';
}
?>