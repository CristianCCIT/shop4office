<?php
/*
  $Id: account_history.php,v 1.63 2003/06/09 23:03:52 hpdl Exp $
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
  $breadcrumb->add(Translate('Mijn account'), tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(Translate('Bestelgeschiedenis'), tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
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
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="orders">
	<tr>
		<td><h1><?php echo Translate('Bestelgeschiedenis'); ?></h1></td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
	<?php
    if ((SOAP_STATUS=='true')&&(SOAP_ACCOUNT_HISTORY=='true')) {
	?>
    <tr>
    	<td>
        <div class="box order-list">
        <?php echo ListDocRequest(); ?>
        </div>
        </td>
    </tr>
    <?php
	} else {
$orders_total = tep_count_customer_orders();
if ($orders_total > 0) {

////////////////////////////////////////////////////////////////// #1178 - 29-05-2013
	$history_query_raw = "select o.orders_id, o.date_purchased, o.delivery_name, o.billing_name, ot.class, ot.text as order_total, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = ot.orders_id and (ot.class = 'ot_total' or ot.class = 'order_total')  and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' order by orders_id DESC";
////////////////////////////////////////////////////////////////// #1178 - 29-05-2013

	$history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_ORDER_HISTORY);
	$history_query = tep_db_query($history_split->sql_query);
	$i = 0;
	while ($history = tep_db_fetch_array($history_query)) {
		$products_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$history['orders_id'] . "'");
		$products = tep_db_fetch_array($products_query);
		if (tep_not_null($history['delivery_name'])) {
			$order_type = Translate('Verzonden naar').' :';
			$order_name = $history['delivery_name'];
		} else {
			$order_type = Translate('Gefactureerd aan').' :';
			$order_name = $history['billing_name'];
		}
		if ($i%2) {
			$class="odd";
		} else {
			$class="even";  
		}
?>
<tr class="<?php echo $class;?>">
	<td>
		<table cellspacing="0" cellpadding="0" border="0" width="100%" class="table_block">
			<tr class="title_block">
				<td class="padding3"><?php echo '<strong>'.Translate('Bestelnummer').'</strong> ' . $history['orders_id']; ?></td>
				<td class="padding3" align="right"><?php echo '<strong>'.Translate('Status').'</strong> ' . $history['orders_status_name']; ?></td>
			</tr>
		</table>
		<table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
			<tr class="infoBoxContents">
				<td>
					<table border="0" width="100%" cellspacing="2" cellpadding="4">
						<tr>
							<td class="main" width="50%" valign="top"><?php echo '<b>'.Translate('Datum').'</b> ' . utf8_encode(tep_date_long($history['date_purchased'])) . '<br><b>' . $order_type . '</b> ' . tep_output_string_protected($order_name); ?></td>
							<td class="main" width="30%" valign="top"><?php echo '<b>'.Translate('Producten').'</b> ' . $products['count'] . '<br><b>'.Translate('Bedrag').'</b> ' . strip_tags($history['order_total']); ?></td>
							<td class="main" align="right" width="20%"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'order_id=' . $history['orders_id'], 'SSL') . '" class="button-a"><span>' . Translate('Bekijken') . '</span></a>'; ?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<table border="0" width="100%" cellspacing="0" cellpadding="2">
			<tr>
				<td height="10"></td>
			</tr>
		</table>
	</td>
</tr>
<?php
	$i++;
	}
} else {
?>
          <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
            <tr class="infoBoxContents">
              <td><table border="0" width="100%" cellspacing="2" cellpadding="4">
                <tr>
                  <td class="main"><?php echo Translate('Geen bestellingen'); ?></td>
                </tr>
              </table></td>
            </tr>
          </table>
<?php
  }
?>
<?php
  if ($orders_total > 0) {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText" valign="top"><?php echo $history_split->display_count(Translate('Bestelling <b>%d</b> tot <b>%d</b> (van <b>%d</b> bestellingen)')); ?></td>
            <td class="smallText" align="right"><?php echo Translate("Paginas") . ' ' . $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
      <tr>
        <td height="10"></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '" class="button-a"><span>' . Translate('Terug') . '</span></a>'; ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <?php
	} ?>
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
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
