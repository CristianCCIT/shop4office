<?php
require('includes/application_top.php');
if (isset($_GET['action']) && ($_GET['action'] == 'update')) {
	$notify_string = 'action=notify&';
	$notify = $_POST['notify'];
	if (!is_array($notify)) $notify = array($notify);
	for ($i=0, $n=sizeof($notify); $i<$n; $i++) {
		$notify_string .= 'notify[]=' . $notify[$i] . '&';
	}
	if (strlen($notify_string) > 0) $notify_string = substr($notify_string, 0, -1);
	tep_redirect(tep_href_link(FILENAME_DEFAULT, $notify_string));
}
$breadcrumb->add(Translate('Afrekenen'));
$breadcrumb->add(Translate('Succes'));
$global_query = tep_db_query("select global_product_notifications from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$customer_id . "'");
$global = tep_db_fetch_array($global_query);
/* One Page Checkout - BEGIN */
if (tep_session_is_registered('customers_id')){
	/* One Page Checkout - END */
	if ($global['global_product_notifications'] != '1') {
		$orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by date_purchased desc limit 1");
		$orders = tep_db_fetch_array($orders_query);
		$products_array = array();
		$products_query = tep_db_query("select products_id, products_name from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$orders['orders_id'] . "' order by products_name");
		while ($products = tep_db_fetch_array($products_query)) {
			$products_array[] = array('id' => $products['products_id'],
									'text' => $products['products_name']);
		}
	}
	/* One Page Checkout - BEGIN */
}
/* One Page Checkout - END */
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
echo tep_draw_form('order', tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'action=update', 'SSL'));
?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="10" cellpadding="10">
				<tr>
					<td><h1><?php echo Translate('Bestelling compleet'); ?></h1></td>
				</tr>
				<tr>
					<td valign="top" class="main"><?php echo Translate('Uw bestelling werd geregistreerd. Een volledig overzicht van uw bestelling wordt via mail naar u opgestuurd.'); ?><br><br>
					<h2><?php echo Translate('Bedankt voor uw bestelling'); ?></h2></td>
				</tr>
				<tr>
					<td>
						<div class="button-a"><span><input type="submit" value="<?=Translate('Ga verder')?>" class="formbutton button-a" /></span></div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<?php if (DOWNLOAD_ENABLED == 'true') include(DIR_WS_MODULES . 'downloads.php'); ?>
</table>
</form>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>