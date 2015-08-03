<?php
/*
  $Id: account_history_info.php,v 1.100 2003/06/09 23:03:52 hpdl Exp $
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2003 osCommerce
  Released under the GNU General Public License
*/
require('includes/application_top.php');
if (!tep_session_is_registered('customer_id')) {
	$navigation->set_snapshot();
	tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}
if (!isset($_GET['order_id']) || (isset($_GET['order_id']) && !is_numeric($_GET['order_id']))) {
	tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
}
if ((SOAP_STATUS!='true')||(SOAP_ACCOUNT_HISTORY!='true')) {
	$customer_info_query = tep_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '". (int)$_GET['order_id'] . "'");
	$customer_info = tep_db_fetch_array($customer_info_query);
	if ($customer_info['customers_id'] != $customer_id) {
		tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
	}
}
$breadcrumb->add(Translate('Mijn account'), tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(Translate('Bestelgeschiedenis'), tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
$breadcrumb->add(sprintf(NAVBAR_TITLE_3, $_GET['order_id']), tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $_GET['order_id'], 'SSL'));
require(DIR_WS_CLASSES . 'order.php');
$order = new order($_GET['order_id']);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
<td width="100%" valign="top">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td><h1><?php echo Translate('Informatie over bestelling'); ?></h1></td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
    <?php
	if ((SOAP_STATUS=='true')&&(SOAP_ACCOUNT_HISTORY=='true')) {
		?>
        <tr>
        	<td>
            <div class="box order-detail">
            <?php echo ViewDocRequest($_GET['order_id']); ?>
            </div>
            </td>
        </tr>
		<?php
	} else {
	?>
	<tr>
		<td>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td class="main" colspan="2">
						<b><?php echo sprintf(Translate('Bestelnummer'), $_GET['order_id']) . ' <small>(' . $order->info['orders_status'] . ')</small>'; ?></b>
					</td>
					<td class="smallText"><?php echo Translate('Datum') . ' ' . utf8_encode(tep_date_long($order->info['date_purchased'])); ?></td>
            		<td class="smallText" align="right"><?php echo Translate('Bedrag') . ' ' . $order->info['total']; ?></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
	<tr>
		<td valign="top">
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
				<tr>
					<td>
						<table border="0" width="100%" cellspacing="0" cellpadding="2" class="orders_history">
							<tr class="title">
								<td class="main"><b><?php echo Translate('Item'); ?></b></td>
								<td class="smallText"><b><?php echo Translate('Productnummer'); ?></b></td>
								<?php
								if (USE_PRICES_TO_QTY == 'true') {
								?>
								<td class="smallText"><b><?php echo Translate('Maat'); ?></b></td>
								<?php
								}
								?>
								<td class="smallText"><b><?php echo Translate('Aantal'); ?></b></td>
								<?php
								if (sizeof($order->info['tax_groups']) > 1) {
								?>
								<td class="smallText" align="right"><b><?php echo Translate('BTW'); ?></b></td>
								<?php
								}
								?>
								<td class="smallText" align="right"><b><?php echo Translate('Totaal'); ?></b></td>
							</tr>
						<?php
						for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
							if ($i%2) {
								echo '<tr class="even">'."\n";
							} else {
								echo '<tr class="odd">' . "\n";
							}
							echo '<td class="main" valign="top">';
							if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
								$attributen = '';
								for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
									if (USE_PRICES_TO_QTY == 'true') {
										if ($order->products[$i]['attributes'][$j]['option'] != Translate('Maat')) {
											$attributen .= '<nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr><br />';
										}
									} else {
										$attributen .= '<nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr><br />';
									}
								}
								if ($attributen == '') {
									echo $order->products[$i]['name'];
								} else {
									echo '<span id="show_more'.$order->products[$i]['id'].'" class="pointer"><span class="plus"></span>'.$order->products[$i]['name'].'</span><br />';
									echo '<span class="textshow_more'.$order->products[$i]['id'].'" style="display:none;">'.$attributen.'</span>';
								}
							} else {
								echo $order->products[$i]['name'];
							}
							echo '</td>'."\n";
							echo '<td class="main" valign="top">'.$order->products[$i]['model'].'</td>'."\n";
							if (USE_PRICES_TO_QTY == 'true') {
								$maat = '';
								if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
									for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
										if ($order->products[$i]['attributes'][$j]['option'] == Translate('Maat')) {
											$maat = $order->products[$i]['attributes'][$j]['value'];
										}
									}
								}
								echo '<td class="main" valign="top">'.$maat.'</td>'."\n";
							}
							echo '<td class="main" valign="top">'.$order->products[$i]['qty'].'</td>'."\n";
							if (sizeof($order->info['tax_groups']) > 1) {
								echo '<td class="main" valign="top" align="right">'.tep_display_tax_value($order->products[$i]['tax']).'%</td>'."\n";
							}
							echo '<td class="main" align="right" valign="top">'.$currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']).'</td>'."\n";
							echo '</tr>'."\n";
						}
						?>
						</table>
						<script type="text/javascript">
							$(document).ready(function() {
								$('span[id^=show_more]').click(function() {										  
									var $this = $(this);
									var x = $this.attr("id");
									$('.text' + x).each(function(i, elem) {
										$(elem).slideToggle('400');
									});
									$(this).children('span').toggleClass('plus');
									$(this).children('span').toggleClass('min');
									return false;
								});
							});
						</script>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td height="5"></td>
	</tr>
	<tr>
		<td><hr class="hr" /></td>
	</tr>
	<tr>
		<td height="5"></td>
	</tr>
	<tr>
		<td width="70%" valign="top">
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
			<?php
			for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
				echo '              <tr>' . "\n" .
					'                <td class="main" align="right" width="100%">' . $order->totals[$i]['title'] . '</td>' . "\n" .
					'                <td class="main" align="right">' . $order->totals[$i]['text'] . '</td>' . "\n" .
					'              </tr>' . "\n";
			}
			?>
            </table>
		</td>
	</tr>
	<tr>
		<td height="30"></td>
	</tr>
	<tr>
		<td>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<!-- Verzend adres -->
					<td valign="top">
						<?php
						if ($order->delivery != false) {
						?>
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr>
								<td class="main"><b><?php echo Translate('Leveradres'); ?></b></td>
							</tr>
							<tr>
								<td class="main"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>'); ?></td>
							</tr>
						</table>
						<?php
						}
						?>
					</td>
					<td width="10"></td>
					<!-- Verzend methode -->
					<td valign="top">
						<?php
						if ($order->delivery != false) {
							if (tep_not_null($order->info['shipping_method'])) {
						?>
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr>
								<td class="main"><b><?php echo Translate('Verzendmethode');?></b></td>
							</tr>
							<tr>
								<td class="main"><?php echo $order->info['shipping_method']; ?></td>
							</tr>
						</table>
							<?php
							}
							?>
						<?php
						}
						?>
					</td>
					<td width="10"></td>
					<!-- Factuur adres -->
					<td valign="top">
						<table border="0" width="100%" cellspacing="0" cellpadding="2">
							<tr>
								<td class="main"><b><?php echo Translate('Factuuradres'); ?></b></td>
							</tr>
							<tr>
								<td class="main"><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>'); ?></td>
							</tr>
						</table>
					</td>
					<td width="10"></td>
					<!-- betaal methode -->
					<td valign="top">
						<table cellspacing="0" cellpadding="0" border="0" width="100%">
							<tr>
								<td class="main"><b><?php echo Translate('Betaalmethode'); ?></b></td>
							</tr>
							<tr>
								<td class="main"><?php echo $order->info['payment_method']; ?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>	
	<tr>
		<td height="30"></td>
	</tr>
	<tr>
		<td class="main"><b><?php echo Translate('Bestelgeschiedenis'); ?></b></td>
	</tr>
	<tr>
		<td height="5"></td>
	</tr>
	
	<tr>
		<td valign="top">
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
			<?php
			$statuses_query = tep_db_query("select os.orders_status_name, osh.date_added, osh.comments from " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh where osh.orders_id = '" . (int)$_GET['order_id'] . "' and osh.orders_status_id = os.orders_status_id and os.language_id = '" . (int)$languages_id . "' order by osh.date_added");
			while ($statuses = tep_db_fetch_array($statuses_query)) {
				echo '              <tr>' . "\n" .
					'                <td class="main" valign="top" width="70">' . tep_date_short($statuses['date_added']) . '</td>' . "\n" .
					'                <td class="main" valign="top" width="70">' . $statuses['orders_status_name'] . '</td>' . "\n" .
					'                <td class="main" valign="top">' . (empty($statuses['comments']) ? '&nbsp;' : nl2br(tep_output_string_protected($statuses['comments']))) . '</td>' . "\n" .
					'              </tr>' . "\n";
			}
			?>
			</table>
		</td>
	</tr>
	<?php
	if (DOWNLOAD_ENABLED == 'true') include(DIR_WS_MODULES . 'downloads.php');
	?>
    <?php
	}
	?>
	<tr>
		<td height="20"></td>
	</tr>
	<tr>
		<td>
			<?php echo '<a href="'.tep_href_link(FILENAME_ACCOUNT_HISTORY, tep_get_all_get_params(array('order_id')), 'SSL').'" class="button-a"><span>'.Translate('Terug').'</span></a>'; ?>
		</td>
	</tr>
</table>
</td>
<!-- body_text_eof //-->
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>