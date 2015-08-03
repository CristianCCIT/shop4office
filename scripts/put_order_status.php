<?php
/*
  $Id: index.php,v 1.1 2003/06/11 17:37:59 hpdl Exp $
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2003 osCommerce
  Released under the GNU General Public License
*/
  require('includes/application_top.php');
// the following cPath references come from application_top.php
  require(DIR_WS_LANGUAGES . $language . '/orders.php' );
  include(DIR_WS_INCLUDES . 'filenames.php');
  include(DIR_WS_CLASSES . 'order.php');
  require(DIR_WS_CLASSES . 'currencies.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php');?>
<!-- header_eof //-->
<!-- body //-->
<!-- body_text //-->
<table>
<tr bgcolor="#808080"> 
 <td>Order Id </td> <td>Original Status </td> <td> New Status </td>
</tr>
<?php
$row = 1;
$orders_statuses = array();
$orders_status_array = array();
$orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "'");
while ($orders_status = tep_db_fetch_array($orders_status_query)) {
	$orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                               'text' => $orders_status['orders_status_name']);
	$orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
}
$oID = $_GET['order_id'];
$status= $_GET['status_id'];
$comments = Translate('De status van uw order is aangepast.');
$order_updated = false;
$check_status_query = tep_db_query("select customers_name, customers_email_address, orders_status, date_purchased from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
$check_status = tep_db_fetch_array($check_status_query);
if ($check_status['orders_status'] != $status) {
	tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . tep_db_input($status) . "', last_modified = now() where orders_id = '" . (int)$oID . "'");
	//Prepare variables for html email//
	$Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
	$Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
	$Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
	$Vartable3 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#ffffff">';
	$VarTitle = '<h1>'.Translate('Update bestelstatus').'</h1>';
	$Vartext1 = ' <b>'.Translate('Beste').' ' . $check_status['customers_name'] .' </b><br>';
	$Vartext2 = Translate('Bestelnummer').': <STRONG> '.$oID.'</STRONG><br>'.Translate('Besteldatum').': <strong>'.strftime(DATE_FORMAT_LONG).'</strong><br><a href="'.HTTP_SERVER.DIR_WS_CATALOG.'account_history_info.php?order_id='.$oID.'">'.Translate('Gedetailleerd overzicht').'</a>' ; 
	
	$Varbody = Translate('Uw order is aangepast naar de volgende status.').'<br />'.Translate('Nieuwe status').': <b>'.$orders_status_array[$status].'</b><br /><br />'.Translate('Reageer a.u.b. op deze email als u nog vragen heeft.');
	
	$Varcopyright = Translate('Copyright © 2010');
	$Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door &eacute;&eacute;n van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
	//Check if HTML emails is set to true
	if (EMAIL_USE_HTML == 'true') {	
		//Prepare HTML email
		require(DIR_WS_MODULES . 'email/html_orders.php');
		$email = $html_email_orders;
	} else {		
		//Send text email
		$email = STORE_NAME."\n -==================================- \n".Translate('Bestelnummer').': <STRONG> '.$oID.'</STRONG>'."\n".Translate('Besteldatum').': <strong>'.strftime(DATE_FORMAT_LONG).'</strong>'."\n".'<a href="'.HTTP_SERVER.DIR_WS_CATALOG.'account_history_info.php?order_id='.$oID.'">'.Translate('Gedetailleerd overzicht').'</a>'."\n\n".Translate('Uw order is aangepast naar de volgende status.')."\n".Translate('Nieuwe status').': <b>'.$orders_status_array[$status].'</b>'."\n\n".Translate('Reageer a.u.b. op deze email als u nog vragen heeft.');
	}
	//END SEND HTML MAIL//
	tep_mail($check_status['customers_name'], $check_status['customers_email_address'], EMAIL_TEXT_SUBJECT, $email, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
	$customer_notified = '1';
	tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$oID . "', '" . tep_db_input($status) . "', now(), '" . tep_db_input($customer_notified) . "', '" . tep_db_input($comments)  . "')");
	$message =  "<tr> <td><b><i>" . (int)$oID . "</td><td><b><i>" .  $orders_status_array[$check_status['orders_status'] ]. "</i></b></td><td><b><i>" .$orders_status_array[$status]. "</i></b></td></tr>";
	echo $message;
} else {
	$message = "<tr> <td>" . (int)$oID . "</td><td>" .  $orders_status_array[$check_status['orders_status'] ]. "</td><td>" .$orders_status_array[$status]. "</td></tr>";
	echo $message;
}
?>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>