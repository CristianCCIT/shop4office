<?php
require('includes/application_top.php');
$filename = $_GET['filename'];
$file = file('../temp/'.$filename.'');
$lines = array();
foreach ($file as $line_num => $line) {
	$lines[$line_num] = $line;
}
$delete_first = array_shift($lines);
$seperator = "\t";
foreach ($lines as $key=>$value) {
	$cell = explode($seperator, $value);
	$update_stock_query = tep_db_query('UPDATE products SET products_quantity = "'.$cell[1].'" WHERE products_model ="'.$cell[0].'"');
	echo $cell[0].'-'.$cell[1]."&nbsp;&nbsp;&nbsp; -> <b>Updated</b><br />";		
}

$dir = DIR_FS_CATALOG.'tmp';
if ($handle = opendir($dir)) {
	/* This is the correct way to loop over the directory. */
	while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != "..") {
			chmod($dir.'/'.$file, 0777);
			unlink($dir.'/'.$file);
		}
	}			
	closedir($handle);
}
?>