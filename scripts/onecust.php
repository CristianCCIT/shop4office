<?php
require_once('includes/application_top.php');
$dir = '../temp/';
$newfile = $_GET['filename'];
$separator = ';';
$filename = $dir . $newfile;
$lines = array();
$fp = fopen ( $filename , "r" );
$i = 0;
while (( $lines[] = fgetcsv ( $fp ,1000, $separator )) !== FALSE ) {$i++;}
fclose ( $fp );

$data = array();
for ($j=1;$j<count($lines);$j++) {
	if ($lines[$j][0] != '') {
		$customer = array();
		$address_book = array();
		$customers_id = '';
		$abo_id = '';
		for ($i=0;$i<count($lines[0]);$i++) {
			if ($lines[0][$i] == 'customers_id') {
				$customers_id = $lines[$j][$i];
			} else if ($lines[0][$i] == 'abo_id') {
				$abo_id = $lines[$j][$i];
			} else if ($lines[0][$i] == 'customers_firstname') {
				$customer[$lines[0][$i]] = $lines[$j][$i];
				$address_book['entry_firstname'] = $lines[$j][$i];
			} else if ($lines[0][$i] == 'customers_password') {
				$customer[$lines[0][$i]] = tep_encrypt_password($lines[$j][$i]);
			} else 	if (strstr($lines[0][$i], 'entry_')) {
				if ($lines[0][$i] == 'entry_country_id') {
					$country_query = tep_db_query("select countries_id from countries where countries_name = '".$lines[$j][$i]."' OR countries_iso_code_2 = '".$lines[$j][$i]."' OR countries_iso_code_3 = '".$lines[$j][$i]."'");
					if (tep_db_num_rows($country_query) > 0) {
						$country = tep_db_fetch_array($country_query);
						$address_book[$lines[0][$i]] = $country['countries_id'];
					} else {
						$address_book[$lines[0][$i]] = $lines[$j][$i];
					}
				} else {
					$address_book[$lines[0][$i]] = $lines[$j][$i];
				}
			} else {
				$customer[$lines[0][$i]] = $lines[$j][$i];
			}
		}
		$data[$j] = array('customer' => $customer, 'address_book' => $address_book, 'customers_id' => $customers_id, 'abo_id' => $abo_id);
	}
}

foreach($data as $key=>$content) {
	$abo_query = tep_db_query("select customers_id from customers where abo_id = '".$content['abo_id']."'");
	if (tep_db_num_rows($abo_query) > 0) {
		$abo_id = tep_db_fetch_array($abo_query);
		if (count($content['customer']) > 0) {
			tep_db_perform('customers', $content['customer'], 'update', 'customers_id = '.$abo_id['customers_id']);
		}
		if (count($content['address_book']) > 0) {
			tep_db_perform('address_book', $content['address_book'], 'update', 'customers_id = '.$abo_id['customers_id']);
		}
	} else {
		if ($content['customers_id'] != '0') {
			$content['customer']['abo_id'] = $content['abo_id'];
			$osid_query = tep_db_query("select customers_id, customers_default_address_id from customers where customers_id = '".$content['customers_id']."' AND (abo_id = '0' OR abo_id = '')");
			if (tep_db_num_rows($osid_query) > 0) {
				tep_db_perform('customers', $content['customer'], 'update', 'customers_id = '.$content['customers_id']);
				$default_address = tep_db_fetch_array($osid_query);
				if (count($content['address_book']) > 0) {
					tep_db_perform('address_book', $content['address_book'], 'update', 'customers_id = '.$content['customers_id'].' AND address_book_id = "'.$default_address['customers_default_address_id'].'"');
				}
			} else {
				if (count($content['customer']) > 0) {
					$content['customer']['abo_id'] = $content['abo_id'];
					tep_db_perform('customers', $content['customer'], 'insert');
					$new_id = tep_db_insert_id();
					echo $content['abo_id'].':'.$new_id.'<br />';
					$content['address_book']['customers_id'] = $new_id;
					tep_db_perform('address_book', $content['address_book'], 'insert');
					$address_id = tep_db_insert_id();
					tep_db_query('INSERT INTO customers_info (customers_info_id, customers_info_date_account_created) VALUES ("'.$new_id.'", NOW())');
					tep_db_query('UPDATE customers SET customers_default_address_id = "'.$address_id.'" WHERE customers_id = "'.$new_id.'"');
				}
			}
		} else {
			$email_query = tep_db_query("select customers_id, customers_default_address_id from customers where customers_email_address = '".$content['customer']['customers_email_address']."'");
			$content['customer']['abo_id'] = $content['abo_id'];
			if (tep_db_num_rows($email_query) > 0) {
				$email = tep_db_fetch_array($email_query);
				tep_db_perform('customers', $content['customer'], 'update', 'customers_id = '.$email['customers_id']);
				if (count($content['address_book']) > 0) {
					tep_db_perform('address_book', $content['address_book'], 'update', 'customers_id = '.$email['customers_id'].' AND address_book_id = "'.$email['customers_default_address_id'].'"');
				}
			} else {
				if (count($content['customer']) > 0) {
					$content['customer']['abo_id'] = $content['abo_id'];
					tep_db_perform('customers', $content['customer'], 'insert');
					$new_id = tep_db_insert_id();
					echo $content['abo_id'].':'.$new_id.'<br />';
					if (count($content['address_book']) > 0) {
						$content['address_book']['customers_id'] = $new_id;
						tep_db_perform('address_book', $content['address_book'], 'insert');
						$address_id = tep_db_insert_id();
						tep_db_query('UPDATE customers SET customers_default_address_id = "'.$address_id.'" WHERE customers_id = "'.$new_id.'"');
					}
					tep_db_query('INSERT INTO customers_info (customers_info_id, customers_info_date_account_created) VALUES ("'.$new_id.'", NOW())');
				}
			}
		}
	}
}
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>