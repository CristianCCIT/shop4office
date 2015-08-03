<?php
chdir('../../../../../');//root shop
require_once('includes/application_top.php');
$order_ids_query = tep_db_query('SELECT orders_id FROM temp_orders WHERE last_modified < DATE_SUB(NOW(), INTERVAL 30 DAY)');
while($orders_id = tep_db_fetch_array($order_ids_query)) {
	tep_db_query('DELETE FROM temp_orders_total WHERE orders_id = "'.$orders_id['orders_id'].'"');
	tep_db_query('DELETE FROM temp_orders_steps WHERE orders_id = "'.$orders_id['orders_id'].'"');
	tep_db_query('DELETE FROM temp_orders_status_history WHERE orders_id = "'.$orders_id['orders_id'].'"');
	tep_db_query('DELETE FROM temp_orders_products_download WHERE orders_id = "'.$orders_id['orders_id'].'"');
	tep_db_query('DELETE FROM temp_orders_products_attributes WHERE orders_id = "'.$orders_id['orders_id'].'"');
	tep_db_query('DELETE FROM temp_orders_products WHERE orders_id = "'.$orders_id['orders_id'].'"');
	tep_db_query('DELETE FROM temp_orders WHERE orders_id = "'.$orders_id['orders_id'].'"');
	echo 'Order '.$orders_id['orders_id'].' Deleted<br />';
}
$tep_db_query('DELETE FROM analytics_user WHERE date < DATE_SUB(NOW(), INTERVAL 3 DAY)');
?>