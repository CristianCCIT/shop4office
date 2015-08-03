<?php
require_once('includes/application_top.php');
if ( isset($_GET['filename']) ) {
	$filename = "../temp/" . $_GET['filename'];
	if ( is_file($filename) ) {
		$fcontents = file ($filename); 
		for($i=0; $i<sizeof($fcontents); $i++) { 
			$line = trim($fcontents[$i]); 
			if ( $line == "" ) continue;
			$arr = explode(";", $line); 
		    $query = tep_db_query("SELECT * FROM " . products . " WHERE products_model ='" . $arr[0] . "'");
			$dir = '../images/foto/thumbs/';
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != '.' && $file != '..') {
						if (strstr($file, $arr[0].'_')) {
							echo $file.' deleted<br />';
							unlink($dir.$file);
						} else if ($file == $arr[0].'.jpg' || $file == $arr[0].'.gif' || $file == $arr[0].'.png') {
							echo $file.' deleted<br />';
							unlink($dir.$file);
						}
					}
				}
				closedir($handle);
			}
			if ( tep_db_num_rows($query) > 0) {
				$query = "UPDATE " . products . " SET products_image_1='".$arr[1]."',products_image_2='".$arr[2]."',products_image_3='".$arr[3]."',products_image_4='".$arr[4]."' WHERE products_model ='" . $arr[0] . "'";
				if (tep_db_query($query) ) {
					echo "Record Updated for the product='" . $arr[0] . "'<br>";
				}
			}
		}
	} else {
		die("The filename you have specified does not exist");
	}
} else {
	die('Please specify a valid filename to load data from');
}
?>