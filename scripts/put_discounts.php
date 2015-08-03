<?php
require_once('includes/application_top.php');
$filename = $_GET['filename'];
$tempDir = 'temp/';
$seperator = ";";
$languages = tep_get_languages();
if (preg_match("/^discount\d{4}([\.])txt$/", $filename)) { //verwerking artikel.txt
	$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
	$headertags = array();
	foreach (explode($seperator,$lines[0]) as $title) { //create list of table names
		$headertags[] = $title;
	}
	unset($lines[0]); //delete first row == titles
	$discount_fields = tep_get_table_fields('customers_discount');
	foreach ($lines as $key=>$value) { //go through all lines in file
		$discount_data = array();
		$data = array();
		foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line
			$data[$headertags[$count]] = trim($waarde);
		}
		foreach ($discount_fields as $field) { //go through all products fields
			if (isset($data[$field])) { //if field exists in data, add it
				$discount_data[$field] = $data[$field];
			}
		}
		$check_query = tep_db_query('SELECT customers_discount_id FROM customers_discount WHERE customers_id = "'.$discount_data['customers_id'].'" AND customers_group = "'.substr($discount_data['customers_group'], 0, 2).'" AND products_id = "'.$discount_data['products_id'].'" AND categories_id = "'.$discount_data['categories_id'].'" AND manufacturers_id = "'.$discount_data['manufacturers_id'].'"');
		if (tep_db_num_rows($check_query) > 0) {
			$check = tep_db_fetch_array($check_query);
			tep_db_perform('customers_discount', $discount_data, 'update', 'customers_discount_id = "'.$check['customers_discount_id'].'"'); //update products table for this product
			echo '<span style="color:#6DB207;font-weight:bold;">Discount updated</span><br />';
		} else {
			tep_db_perform('customers_discount', $discount_data, 'insert'); //update products table for this product
			echo '<span style="color:#0289FF;font-weight:bold;">New discount added</span><br />';
		}
	}
}
//functions
function tep_get_table_fields($table) {
	$field_list = array();
	$table_fields_query = tep_db_query('SHOW COLUMNS FROM '.$table);
	while ($table_fields = tep_db_fetch_array($table_fields_query)) {
		$field_list[] = $table_fields['Field'];
	}
	return $field_list;
}
?>