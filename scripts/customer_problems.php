<?php
require_once('includes/application_top.php');
if ($_GET['mode'] == 'resetAboId') {
	tep_db_query('UPDATE customers SET abo_id = 0');
	echo 'abo_id is gereset!';
} else if ($_GET['mode'] == 'nonActiveCustomers') {
	echo '<table cellspacing="0" cellpadding="5" border="0">';
	echo '<tr><td></td><td>customer_id</td><td>abo_id</td><td>customer_firstname</td><td>customer_email_addresss</td></tr>';
	$type = 'even';
	$count = 0;
	$customers_query = tep_db_query('SELECT customers_id, abo_id, customers_email_address, customers_firstname FROM customers WHERE customers_password = ""');
	while ($customer = tep_db_fetch_array($customers_query)) {
		if ($type == 'odd') {$type = 'even';} else { $type = 'odd';}
		$count++;
		if ($_GET['action'] == 'delete') {
			tep_db_query('DELETE FROM address_book WHERE customers_id = "'.$customer['customers_id'].'"');
			tep_db_query('DELETE FROM customers_info WHERE customers_info_id = "'.$customer['customers_id'].'"');
			tep_db_query('DELETE FROM customers WHERE customers_id = "'.$customer['customers_id'].'"');
		}
		echo '<tr style="background:'.($type=='odd'?'#ccc':'').'"><td>'.$count.'</td><td>'.$customer['customers_id'].'</td><td>'.$customer['abo_id'].'</td><td>'.$customer['customers_firstname'].'</td><td>'.$customer['customers_email_address'].'</td></tr>';
	}
	echo '</table>';
} else if ($_GET['mode'] == 'doubles') {
	$count = 0;
	$customers_query = tep_db_query('SELECT DISTINCT customers_email_address FROM customers');
	while ($customer = tep_db_fetch_array($customers_query)) {
		$type = 'even';
		$doubles_query = tep_db_query('SELECT customers_id, abo_id, customers_email_address, customers_firstname, customers_password FROM customers WHERE customers_email_address = "'.$customer['customers_email_address'].'"');
		if (tep_db_num_rows($doubles_query) > 1) {
			$count++;
			echo '<table cellspacing="0" cellpadding="5" border="1">';
			echo '<tr><td>'.$count.'</td><td>customers_id</td><td>abo_id</td><td>customers_firstname</td><td>customers_email_address</td><td>customers_password</td><td>orders_ids</td><td>Delete?</td></tr>';
			while ($doubles = tep_db_fetch_array($doubles_query)) {
				if ($type == 'odd') {$type = 'even';} else { $type = 'odd';}
				echo '<tr style="background:'.($type=='odd'?'#ccc':'').'">';
				echo '<td>&nbsp;</td>';
				echo '<td>'.$doubles['customers_id'].'</td>';
				echo '<td>'.$doubles['abo_id'].'</td>';
				echo '<td>'.$doubles['customers_firstname'].'</td>';
				echo '<td>'.$doubles['customers_email_address'].'</td>';
				echo '<td>'.$doubles['customers_password'].'&nbsp;</td>';
				echo '<td>'.get_orders_ids($doubles['customers_id']).'</td>';
				echo '<td><a href="'.tep_href_link('customer_problems.php', 'mode=deleteCustomer&customers_id='.$doubles['customers_id']).'" target="_blank">Delete</a></td>';
				echo '</tr>';
			}
			echo '</table>';
			echo '<div style="margin-bottom:20px;clear:both;"></div>';
		}
	}
} else if ($_GET['mode'] == 'deleteCustomer') {
	tep_db_query('DELETE FROM address_book WHERE customers_id = "'.$_GET['customers_id'].'"');
	tep_db_query('DELETE FROM customers_info WHERE customers_info_id = "'.$_GET['customers_id'].'"');
	tep_db_query('DELETE FROM customers WHERE customers_id = "'.$_GET['customers_id'].'"');
	tep_db_query('DELETE FROM customers_basket WHERE customers_id = "'.$_GET['customers_id'].'"');
	tep_db_query('DELETE FROM customers_basket_attributes WHERE customers_id = "'.$_GET['customers_id'].'"');
	echo 'customer '.$_GET['customers_id'].' is deleted!';
} else {
	echo 'Kies een optie:<br />';
	?>
	<ul>
		<li><a href="<?php echo tep_href_link('customer_problems.php', 'mode=resetAboId');?>">Reset abo ids</a></li>
		<li><a href="<?php echo tep_href_link('customer_problems.php', 'mode=nonActiveCustomers');?>">Lijst van niet actieve gebruikers (klanten zonder paswoord)</a>
			<ul>
				<li><a href="<?php echo tep_href_link('customer_problems.php', 'mode=nonActiveCustomers&action=delete');?>">Verwijder niet actieve gebruikers (klanten zonder paswoord)</a></li>
			</ul>
		</li>
		<li><a href="<?php echo tep_href_link('customer_problems.php', 'mode=doubles');?>">Lijst met dubbele klanten</a></li>
	</ul>
	<?php
}

function get_orders_ids($customers_id) {
	$orders_ids = '';
	$query = tep_db_query('SELECT DISTINCT orders_id FROM orders WHERE customers_id = "'.$customers_id.'"');
	while ($order_id = tep_db_fetch_array($query)) {
		$orders_ids .= $order_id['orders_id'].', ';
	}
	if (strlen($orders_ids) > 0) {
		return substr($orders_ids, 0, -2);
	} else {
		return '&nbsp;';
	}
}
?>