<?php
require_once('includes/application_top.php');
$filename = $_GET['localfile'];
$tempDir = 'temp/';
$seperator = ";";
$languages = tep_get_languages();
if (preg_match("/^address\d{4}([\.])txt$/", $filename)) { //verwerking address.txt
	$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
	$headertags = array();
	foreach (explode($seperator,$lines[0]) as $title) { //create list of table names
		$headertags[] = $title;
	}
	unset($lines[0]); //delete first row == titles
	$address_book_fields = tep_get_table_fields('address_book');
	foreach ($lines as $key=>$value) { //go through all lines in file
		$data = array();
		foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line
			$data[$headertags[$count]] = trim($waarde);
		}
		$address_book_data = array();
		if (($customers_id = tep_get_customers_id($data['abo_id'])) == '') { //check if products_model exists
			echo '<span style="color:#ff0000;font-weight:bold;">Geen klant gevonden met het id '.$data['abo_id'].'</span><br />';
		} else {
			$address_book_data['customers_id'] = $customers_id;
			foreach ($address_book_fields as $field) { //go through all products fields
				if ($field == 'entry_country_id') {
						$country = $data['country'];
						$country_id = tep_get_country_id($country);
						$data[$field] = $country_id;
					}
				if (isset($data[$field])) { //if field exists in data, add it
					$address_book_data[$field] = $data[$field];
				}
			}
			tep_db_perform('address_book', $address_book_data);
			echo '<span style="color:#6DB207;font-weight:bold;">Adres toegevoegd voor klant '.$data['abo_id'].'</span><br />';
			echo '<hr />';
		}
	}
} else {
	echo 'geen geldige filename: '.$filename;
}
//FUNCTIONS
function tep_get_table_fields($table) {
	$field_list = array();
	$table_fields_query = tep_db_query('SHOW COLUMNS FROM '.$table);
	while ($table_fields = tep_db_fetch_array($table_fields_query)) {
		$field_list[] = $table_fields['Field'];
	}
	return $field_list;
}
function tep_get_customers_id($abo_id) {
	$cid_query = tep_db_query('SELECT customers_id FROM customers WHERE abo_id = "'.$abo_id.'"');
	$cid = tep_db_fetch_array($cid_query);
	return $cid['customers_id'];
}
function tep_get_country_id($country) {
	$country_query = tep_db_query('SELECT countries_id FROM countries WHERE countries_name LIKE "'.$country.'"');
	$country = tep_db_fetch_array($country_query);
	return $country['countries_id'];
}
?>