<?php
require('includes/application_top.php');
$customers_query = tep_db_query("SELECT customers_id FROM ".TABLE_CUSTOMERS." WHERE abo_id = ".tep_db_input($_GET['abo_id']) );
if (tep_db_num_rows($customers_query) == 0) {
	echo "Not found";
} else {
	$customers = tep_db_fetch_array($customers_query);
	echo $customers['customers_id'];
}
?>