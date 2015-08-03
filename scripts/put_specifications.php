<?php
require('includes/application_top.php');
require('includes/database_tables.php');

$languages = array();
$get_language_ids_query = tep_db_query('SELECT languages_id, code FROM languages');
while ($get_language_ids = tep_db_fetch_array($get_language_ids_query)) {
	$languages[$get_language_ids['languages_id']] = $get_language_ids['code'];
}
if ($_GET['filename'] == 'specs001.txt') {
	mysql_query("TRUNCATE TABLE `specifications`");
}

foreach ($languages as $key=>$value) {
	if ($value == 'nl') {
		$nederlands = $key;
	} else if ($value == 'fr') {
		$frans = $key;
	} else if ($value == 'en') {
		$engels = $key;
	} else if ($value == 'de') {
		$duits = $key;
	}
}

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
$i = 1;
foreach ($lines as $key=>$value) {
	$cell = explode('|', $value);
	$cell[5] = trim($cell[5]);
	if ($nederlands != '') {
		$sql_nl =  'INSERT INTO specifications (hoofdkenmerk, subkenmerk, language_id, value, search, homepage, title) VALUES("'.$cell[0].'", "'.$cell[1].'", "'.$nederlands.'", "'.$cell[2].'", "'.$cell[5].'", "'.$cell[6].'", "'.$cell[7].'")';
		$nl = tep_db_query($sql_nl) or die('nederlands werkt niet');
		echo $i.' '.$cell[0].' '.$cell[1].' NL '.$cell[2]." <b style='color: black;'>New specification</b><br />\n";
	}
	if ($frans != '') {
		$sql_fr =  'INSERT INTO specifications (hoofdkenmerk, subkenmerk, language_id, value, search, homepage, title) VALUES("'.$cell[0].'", "'.$cell[1].'", "'.$frans.'", "'.$cell[3].'", "'.$cell[5].'", "'.$cell[6].'", "'.$cell[7].'")';
		$fr = tep_db_query($sql_fr) or die('frans werkt niet');
		echo $i.' '.$cell[0].' '.$cell[1].' FR '.$cell[3]." <b style='color: black;'>New specification</b><br />\n";
	}
	if ($engels != '') {
		$sql_en =  'INSERT INTO specifications (hoofdkenmerk, subkenmerk, language_id, value, search, homepage, title) VALUES("'.$cell[0].'", "'.$cell[1].'", "'.$engels.'", "'.$cell[4].'", "'.$cell[5].'", "'.$cell[6].'", "'.$cell[7].'")';
		$en = tep_db_query($sql_en) or die('engels werkt niet');
		echo $i.' '.$cell[0].' '.$cell[1].' EN '.$cell[4]." <b style='color: black;'>New specification</b><br />\n";
	}	
	if ($duits != '') {
		$sql_de =  'INSERT INTO specifications (hoofdkenmerk, subkenmerk, language_id, value, search, homepage, title) VALUES("'.$cell[0].'", "'.$cell[1].'", "'.$duits.'", "'.$cell[4].'", "'.$cell[5].'", "'.$cell[6].'", "'.$cell[7].'")';
		$en = tep_db_query($sql_de) or die('duits werkt niet');
		echo $i.' '.$cell[0].' '.$cell[1].' DE '.$cell[4]." <b style='color: black;'>New specification</b><br />\n";
	}	
	$i++;	
}
?>