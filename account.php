<?php
  require('includes/application_top.php');
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  $breadcrumb->add(Translate('Mijn account'), tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
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
	<?php
    if ($messageStack->size('account') > 0)
    {
    echo $messageStack->output('account');
    }
	?>
    <table border="0" width="100%" cellspacing="10" cellpadding="10">
      <tr>
        <td><h1><?php echo Translate('Mijn account'); ?></h1></td>
      </tr>
<?
if ((SOAP_STATUS!='true')||(SOAP_ACCOUNT_HISTORY!='true')) {
  if (tep_count_customer_orders() > 0) {
?>
	<tr>
		<td>
			<div class="orders">
				<h2><?php echo Translate('Overzicht'); ?> <?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . Translate('Laatste bestellingen') . '</a>'; ?></h2>
				<table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
$orders_query = tep_db_query("select o.orders_id, o.date_purchased, o.delivery_name, o.delivery_country, o.billing_name, o.billing_country, ot.text as order_total, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' order by orders_id desc limit 3");
$i=0;
while ($orders = tep_db_fetch_array($orders_query)) {
	if (tep_not_null($orders['delivery_name'])) {
		$order_name = convert_to_entities($orders['delivery_name']);
		$order_country = convert_to_entities($orders['delivery_country']);
	} else {
		$order_name = convert_to_entities($orders['billing_name']);
		$order_country = convert_to_entities($orders['billing_country']);
	}
	if ($i%2) {
		$class = "odd";
	} else {
		$class = "even";
	}
?>
					<tr class="<?php echo $class;?>" onClick="document.location.href='<?php echo tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders['orders_id'], 'SSL'); ?>'">
						<td height="5" colspan="6"></td>
					</tr>
					<tr class="<?php echo $class;?>" onClick="document.location.href='<?php echo tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders['orders_id'], 'SSL'); ?>'">
						<td class="main" width="80"><?php echo tep_date_short($orders['date_purchased']); ?></td>
						<td class="main"><?php echo '#' . $orders['orders_id']; ?></td>
						<td class="main"><?php echo tep_output_string_protected($order_name) . ', ' . $order_country; ?></td>
						<td class="main"><?php echo $orders['orders_status_name']; ?></td>
						<td class="main" align="right"><?php echo $orders['order_total']; ?></td>
						<td class="main" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders['orders_id'], 'SSL') . '" class="button-a"><span>' . Translate('Bekijken') . '</span></a>'; ?></td>
					</tr>
					<tr class="<?php echo $class;?>" onClick="document.location.href='<?php echo tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders['orders_id'], 'SSL'); ?>'">
						<td height="5" colspan="6"></td>
					</tr>
<?php
	$i++;
}
?>
			</table>
			</div>
		</td>
	</tr>
<?php
  }
}
?>
	<tr>
		<td style="padding-left: 10px;">
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td class="main">
						<?php echo '<span class="raquo"><span>&raquo;</span></span> <a href="'.tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL').'">'.Translate('Bekijk of verander mijn account informatie').'</a>'; ?>
					</td>
				</tr>
				<tr>
					<td class="main">
						<?php echo '<span class="raquo"><span>&raquo;</span></span> <a href="'.tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL').'">'.Translate('Bekijk of verander personen uit mijn adreslijst').'</a>'; ?>
					</td>
				</tr>
				<tr>
					<td class="main">
						<?php echo '<span class="raquo"><span>&raquo;</span></span> <a href="'.tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL').'">'.Translate('Verander mijn account wachtwoord').'</a>'; ?>
					</td>
				</tr>
				<?php
				if (ACCOUNT_NEWSLETTER == 'true') {
				?>
				<tr>
					<td class="main">
						<?php echo '<span class="raquo"><span>&raquo;</span></span> <a href="'.tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL').'">'.Translate('Inschrijven/uitschrijven van nieuwsbrief').'</a>'; ?>
					</td>
				</tr>
				<?php
				}
				?>
			</table>
		</td>
	</tr>
	<tr>
		<td class="main"><h2><?php echo Translate('Mijn bestellingen'); ?></h2></td>
	</tr>
	<tr>
		<td class="main" style="padding-left: 10px;">
			<?php echo '<span class="raquo"><span>&raquo;</span></span> <a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . Translate('Bekijk mijn bestellingen') . '</a>'; ?>
		</td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
</table></td>
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
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
