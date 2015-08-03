<?php
require('includes/application_top.php');
require('includes/database_tables.php');

$filename = $_GET['filename'];
$file = file('../temp/'.$filename.'');


$lines = array();
// Loop through our array, show HTML source as HTML source; and line numbers too.
foreach ($file as $line_num => $line) {
    //echo "Line #<b>{$line_num}</b> : " . htmlspecialchars($line) . "<br />\n";
	$lines[$line_num] = $line;
}
$delete_first = array_shift($lines);
//print_r($lines);
foreach ($lines as $key=>$value) {
	$cell = explode('|', $value);
	$put_catspecs_query = tep_db_query('SELECT specification_group FROM '.TABLE_CATEGORIES.' WHERE categories_id = "'.$cell[0].'"');
	$put_catspecs = tep_db_fetch_array($put_catspecs_query);
	if ($put_catspecs['specification_group'] != $cell[1]) {
		tep_db_query('UPDATE '.TABLE_CATEGORIES.' SET specification_group = "'.$cell[1].'" WHERE categories_id = "'.$cell[0].'"');
		echo 'categorie: '.$cell[0].' <b style="color: Green;">Updated</b><br />';
	}
}
?>