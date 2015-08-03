<?php
require('includes/application_top.php');
require('includes/database_tables.php');

$languages = array();
$get_language_ids_query = tep_db_query('SELECT languages_id, code FROM languages');
while ($get_language_ids = tep_db_fetch_array($get_language_ids_query)) {
	$languages[$get_language_ids['languages_id']] = $get_language_ids['code'];
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
if ($frans == '') {
	$frans = '97';
}
if ($engels == '') {
	$engels = '98';
}
if ($duits == '') {
	$engels = '99';
}
$filename = $_GET['filename'];
$file = file('../temp/'.$filename.'');
$lines = array();

foreach ($file as $line_num => $line) {
	$lines[$line_num] = $line;
	$cell = explode('|', $line);
	tep_db_query('DELETE FROM `productspecs` WHERE products_model = "'.$cell[0].'"');
}
$delete_first = array_shift($lines);

foreach ($lines as $key=>$value) {
	$cell = explode('|', $value);
	$get_highest_subkenmerk_query = tep_db_query ('SELECT subkenmerk FROM specsdescription ORDER BY subkenmerk DESC LIMIT 1 ');
	$get_highest_subkenmerk = tep_db_fetch_array($get_highest_subkenmerk_query);
	$highest_subkenmerk = $get_highest_subkenmerk['subkenmerk'] + 1;
	if (empty($cell[3])){
		// do nothing
	} else {
		$specs_nl_query = tep_db_query('SELECT value FROM productspecs WHERE products_model = "'.$cell[0].'" AND hoofdkenmerk = "'.$cell[1].'" AND subkenmerk = "'.$cell[2].'"');
		if (tep_db_num_rows($specs_nl_query) > 0) {
			while ($specs_nl = tep_db_fetch_array($specs_nl_query)) {
				$exists_language_query = tep_db_query('SELECT value FROM specsdescription WHERE subkenmerk = "'.$specs_nl['value'].'" AND language_id = "'.$nederlands.'"');
				if (tep_db_num_rows($exists_language_query) > 0) {
					tep_db_query('UPDATE specsdescription set value = "'.$cell[3].'" WHERE subkenmerk = "'.$specs_nl['value'].'" AND language_id = "'.$nederlands.'"');
					echo $cell[0].' value= '.$cell[3].'<b style="color: green;">UPDATED</b><br />';
				} else {
					while($exists_language = tep_db_fetch_array($exists_language_query)) {
						echo $exists_language['value'].' - '.$specs_nl['value'].'<br />';
					}
					echo $specs_nl['value'].'<br />';
					tep_db_query('INSERT INTO specsdescription (subkenmerk, language_id, value) VALUES("'.$specs_nl['value'].'", "'.$nederlands.'", "'.$cell[3].'")');
					echo $cell[0].' value= '.$cell[3].'<b style="color: red;">NEW SPEC</b><br />';
				}
			}
		} else {
			$get_value_query = tep_db_query('SELECT subkenmerk FROM specsdescription WHERE language_id = "'.$nederlands.'" AND value = "'.$cell[3].'"');
			if (tep_db_num_rows($get_value_query) > 0) {
				while ($get_value = tep_db_fetch_array($get_value_query)) {
					$put_product_query = tep_db_query("INSERT INTO productspecs (products_model, hoofdkenmerk, subkenmerk, value) VALUES('".$cell[0]."', '".$cell[1]."', '".$cell[2]."', '".$get_value['subkenmerk']."')");
					echo $cell[0].' value= '.$cell[3].'<b style="color: red;">NEW SPEC</b><br />';
				}
			} else {
				$put_product_query = tep_db_query("INSERT INTO productspecs (products_model, hoofdkenmerk, subkenmerk, value) VALUES('".$cell[0]."', '".$cell[1]."', '".$cell[2]."', '".$highest_subkenmerk."')");
				$sql_nl =  tep_db_query("INSERT INTO specsdescription (subkenmerk, language_id, value) VALUES('".$highest_subkenmerk."', '".$nederlands."', '".$cell[3]."')");
				echo $cell[0].' value= '.$cell[3].'<b style="color: red;">NEW SPEC</b><br />';
			}
		}
	}
	if (empty($cell[4])){
		// do nothing
	} else {
		$specs_nl_query = tep_db_query('SELECT value FROM productspecs WHERE products_model = "'.$cell[0].'" AND hoofdkenmerk = "'.$cell[1].'" AND subkenmerk = "'.$cell[2].'"');
		if (tep_db_num_rows($specs_nl_query) > 0) {
			while ($specs_nl = tep_db_fetch_array($specs_nl_query)) {
				$exists_language_query = tep_db_query('SELECT value FROM specsdescription WHERE subkenmerk = "'.$specs_nl['value'].'" AND language_id = "'.$frans.'"');
				if (tep_db_num_rows($exists_language_query) > 0) {
					tep_db_query('UPDATE specsdescription set value = "'.$cell[4].'" WHERE subkenmerk = "'.$specs_nl['value'].'" AND language_id = "'.$frans.'"');
					echo $cell[0].' value= '.$cell[4].'<b style="color: green;">UPDATED</b><br />';
				} else {
					tep_db_query('INSERT INTO specsdescription (subkenmerk, language_id, value) VALUES("'.$specs_nl['value'].'", "'.$frans.'", "'.$cell[4].'")');
					echo $cell[0].' value= '.$cell[4].'<b style="color: red;">NEW SPEC</b><br />';
				}
			}
		} else {
			$put_product_query = tep_db_query("INSERT INTO productspecs (products_model, hoofdkenmerk, subkenmerk, value) VALUES('".$cell[0]."', '".$cell[1]."', '".$cell[2]."', '".$highest_subkenmerk."')");
			$sql_nl =  tep_db_query("INSERT INTO specsdescription (subkenmerk, language_id, value) VALUES('".$highest_subkenmerk."', '".$frans."', '".$cell[4]."')");
			echo $cell[0].' value= '.$cell[4].'<b style="color: red;">NEW SPEC</b><br />';
		}
	}
	if (empty($cell[5])){
		// do nothing
	} else {
		$specs_nl_query = tep_db_query('SELECT value FROM productspecs WHERE products_model = "'.$cell[0].'" AND hoofdkenmerk = "'.$cell[1].'" AND subkenmerk = "'.$cell[2].'"');
		if (tep_db_num_rows($specs_nl_query) > 0) {
			while ($specs_nl = tep_db_fetch_array($specs_nl_query)) {
				$exists_language_query = tep_db_query('SELECT value FROM specsdescription WHERE subkenmerk = "'.$specs_nl['value'].'" AND language_id = "'.$engels.'"');
				if (tep_db_num_rows($exists_language_query) > 0) {
					tep_db_query('UPDATE specsdescription set value = "'.$cell[5].'" WHERE subkenmerk = "'.$specs_nl['value'].'" AND language_id = "'.$engels.'"');
					echo $cell[0].' value= '.$cell[5].'<b style="color: green;">UPDATED</b><br />';
				} else {
					tep_db_query('INSERT INTO specsdescription (subkenmerk, language_id, value) VALUES("'.$specs_nl['value'].'", "'.$engels.'", "'.$cell[5].'")');
					echo $cell[0].' value= '.$cell[5].'<b style="color: red;">NEW SPEC</b><br />';
				}
			}
		} else {
			$put_product_query = tep_db_query("INSERT INTO productspecs (products_model, hoofdkenmerk, subkenmerk, value) VALUES('".$cell[0]."', '".$cell[1]."', '".$cell[2]."', '".$highest_subkenmerk."')");
			$sql_nl =  tep_db_query("INSERT INTO specsdescription (subkenmerk, language_id, value) VALUES('".$highest_subkenmerk."', '".$engels."', '".$cell[5]."')");
			echo $cell[0].' value= '.$cell[5].'<b style="color: red;">NEW SPEC</b><br />';
		}
	}
	if (empty($cell[6])){
		// do nothing
	} else {
		$specs_nl_query = tep_db_query('SELECT value FROM productspecs WHERE products_model = "'.$cell[0].'" AND hoofdkenmerk = "'.$cell[1].'" AND subkenmerk = "'.$cell[2].'"');
		if (tep_db_num_rows($specs_nl_query) > 0) {
			while ($specs_nl = tep_db_fetch_array($specs_nl_query)) {
				$exists_language_query = tep_db_query('SELECT value FROM specsdescription WHERE subkenmerk = "'.$specs_nl['value'].'" AND language_id = "'.$duits.'"');
				if (tep_db_num_rows($exists_language_query) > 0) {
					tep_db_query('UPDATE specsdescription set value = "'.$cell[6].'" WHERE subkenmerk = "'.$specs_nl['value'].'" AND language_id = "'.$duits.'"');
					echo $cell[0].' value= '.$cell[6].'<b style="color: green;">UPDATED</b><br />';
				} else {
					tep_db_query('INSERT INTO specsdescription (subkenmerk, language_id, value) VALUES("'.$specs_nl['value'].'", "'.$duits.'", "'.$cell[6].'")');
					echo $cell[0].' value= '.$cell[6].'<b style="color: red;">NEW SPEC</b><br />';
				}
			}
		} else {
			$put_product_query = tep_db_query("INSERT INTO productspecs (products_model, hoofdkenmerk, subkenmerk, value) VALUES('".$cell[0]."', '".$cell[1]."', '".$cell[2]."', '".$highest_subkenmerk."')");
			$sql_nl =  tep_db_query("INSERT INTO specsdescription (subkenmerk, language_id, value) VALUES('".$highest_subkenmerk."', '".$duits."', '".$cell[6]."')");
			echo $cell[0].' value= '.$cell[6].'<b style="color: red;">NEW SPEC</b><br />';
		}
	}
}
tep_db_query('DELETE FROM specsdescription WHERE language_id IN ("0", "97", "98", "99")');
$values = array();
$select_values_query = tep_db_query('SELECT DISTINCT value FROM productspecs');
while ($select_values = tep_db_fetch_array($select_values_query)) {
	$values[] = $select_values['value'];
}
$unused = implode(',', $values);
if ($unused!='') {
	tep_db_query('DELETE FROM specsdescription WHERE subkenmerk NOT IN ('.$unused.')');
}
?>