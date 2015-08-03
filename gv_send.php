<?php
/*
  $Id: gv_send.php,v 1.1.2.3 2003/05/12 22:57:20 wilt Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 - 2003 osCommerce

  Gift Voucher System v1.0
  Copyright (c) 2001, 2002 Ian C Wilson
  http://www.phesis.org

  Released under the GNU General Public License
*/
require('includes/application_top.php');
require('includes/classes/http_client.php');

// if the customer is not logged on, redirect them to the login page
if (!tep_session_is_registered('customer_id')) {
	$navigation->set_snapshot();
	tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}
if (($_POST['back_x']) || ($_POST['back_y'])) {
	$_GET['action'] = '';
}
if ($_GET['action'] == 'process') {
	$error = false;
	if (strlen($_POST['to_name']) < 1) {
		$error = true;
		$messageStack->add('to_name', Translate('Gelieve de naam van de ontvanger in te geven.'));
	}
	if (!tep_validate_email(trim($_POST['email']))) {
		$error = true;
		$messageStack->add('email', Translate('Gelieve een correct email adres in te geven.'));
	}
	$gv_query = tep_db_query("select amount from ".TABLE_COUPON_GV_CUSTOMER." where customer_id = '".$customer_id."'");
	$gv_result = tep_db_fetch_array($gv_query);
	$customer_amount = $gv_result['amount'];
	$gv_amount = trim($_POST['amount']);
	if (ereg('[^0-9/.]', $gv_amount)) {
		$error = true;
		$messageStack->add('amount', Translate('Gelieve een numerieke waarde in te geven voor het bedrag.'));
	}
	if ($gv_amount>$customer_amount || $gv_amount == 0) {
		$error = true; 
		$messageStack->add('amount', Translate('Gelieve een correct bedrag in te geven. Deze mag niet nul zijn of groter dan het beschikbaar bedrag.'));
	}
	if (strlen($_POST['message']) < 1) {
		$error = true;
		$messageStack->add('message', Translate('Gelieve een bericht aan de ontvanger in te geven.'));
	}
	if (!$error) {
		$id1 = create_coupon_code($_POST['email']);
		$new_amount=$gv_result['amount']-$_POST['amount'];
		tep_db_query("update coupon_gv_customer set amount = '".$new_amount."' where customer_id = '".$customer_id."'");
		$gv_query = tep_db_query("select customers_firstname, customers_lastname from customers where customers_id = '".$customer_id."'");
		$gv_customer = tep_db_fetch_array($gv_query);
		tep_db_query("insert into ".TABLE_COUPONS." (coupon_type, coupon_code, date_created, coupon_amount) values ('G', '".$id1."', NOW(), '".$_POST['amount']."')");
		$insert_id = tep_db_insert_id();
		$gv_query=tep_db_query("insert into coupon_email_track (coupon_id, customer_id_sent, sent_firstname, sent_lastname, emailed_to, date_sent) values ('".$insert_id."' ,'".$customer_id."', '".addslashes($gv_customer['customers_firstname'])."', '".addslashes($gv_customer['customers_lastname'])."', '".$_POST['email']."', now())");
		$Varlogo = '<a href="'.HTTP_SERVER.DIR_WS_CATALOG.'"><img src="'.HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
		$Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
		$Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
		$Vartext1 = Translate('Cadeaubon');
		$Vartext2 = '<table cellspacing="3" cellpadding="3" border="0" width="587" bgcolor="#ffffff"><tr><td>';
		$Vartext2 .= Translate('Beste').' '.$_POST['to_name'].',<br /><br />';
		$Vartext2 .= sprintf(Translate('Proficiat, hebt een cadeaubon ontvangen van %s'), $currencies->format($_POST['amount']));
		$Vartext2 .= '</td></tr><tr><td height="5"></td></tr><tr><td>';
		$Vartext2 .= sprintf(Translate('Deze cadeaubon werd u toegezonden door %s'), $gv_customer['customers_firstname']).'<br />';
		$Vartext2 .= Translate('Zijn/haar bericht').': <br />'.$_POST['message'];
		$Vartext2 .= '</td></tr><tr><td height="5"></td></tr><tr><td>';
		$Vartext2 .= Translate('Klik op onderstaande link om deze cadeaubon te valideren.').'<br /><br />';
		$Vartext2 .= '<a href="'.tep_href_link(FILENAME_GV_REDEEM, 'gift='.$id1, 'NONSSL', false).'">'.tep_href_link(FILENAME_GV_REDEEM, 'gift='.$id1, 'NONSSL', false).'</a><br /><br />';
		$Vartext2 .= sprintf(Translate('Houdt ook uw code %s bij, voor het geval u problemen ondervindt.'), $id1);
		$Vartext2 .= '</td></tr></table>';
		$Varcopyright = 'Copyright &copy; '.date('Y');
		$Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door één van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:'.STORE_OWNER_EMAIL_ADDRESS.'">'.STORE_OWNER_EMAIL_ADDRESS.'</a>';
		if (EMAIL_USE_HTML == 'true') {	
			//Prepare HTML email
			require('includes/modules/email/html_password_forgotten.php');
			$emailText = $html_email_text;
		} else {		
			//Send text email
			$emailText = STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $_GET['orders_id'] . "\n" . EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $_GET['orders_id'], 'SSL') . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($check_status['date_purchased']) . "\n\n" . EMAIL_TEXT_COMMENTS_UPDATE . ' ' . $comments . "\n\n" . sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]);
		}
		$gv_email_subject = sprintf(Translate('Een cadeaubon van %s'), stripslashes($gv_customer['customers_firstname']));
		tep_mail($_POST['to_name'], $_POST['email'], $gv_email_subject, nl2br($emailText), STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, '');
	}
}
$breadcrumb->add(Translate('Verzend een cadeaubon'));
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td><h1><?php echo Translate('Verzend een cadeaubon');?></h1></td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
<?php
  if ($_GET['action'] == 'process' && !$error) {
?>
	<tr>
		<td class="main"><?php echo Translate('Proficiat, uw cadeaubon is succesvol verzonden'); ?></td>
	</tr>
	<tr>
		<td align="right"><br><a href="<?php echo tep_href_link(FILENAME_DEFAULT, '', 'NONSSL'); ?>" class="button-a"><?php echo Translate('Ga verder'); ?></a></td>
	</tr>
<?php
  } elseif ($_GET['action']=='' || $error) {
?>
	<tr>
		<td>
			<?php echo Translate('Vul hieronder a.u.b. de gegevens in voor de cadeaubon die u wilt versturen.');?>
		</td>
	</tr>
	<tr>
		<td height="5"></td>
	</tr>
	<tr>
		<td>
			<form action="<?php echo tep_href_link(FILENAME_GV_SEND, 'action=process', 'NONSSL'); ?>" method="post">
			<script type="text/javascript" src="includes/js/form_validation.js"></script>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<td width="110"><?php echo Translate('Naam ontvanger');?>:</td>
					<td colspan="2"><input type="text" name="to_name" value="<?php echo stripslashes($_POST['to_name']);?>" condition="1" class="inputbox<?php if ($messageStack->size('to_name') > 0) { echo ' error';}?>" /></td>
				</tr>
				<tr>
					<td colspan="3" height="5"></td>
				</tr>
				<tr>
					<td width="110"><?php echo Translate('Email ontvanger');?>:</td>
					<td colspan="2">
						<input type="text" name="email" condition="email_required" value="<?php echo stripslashes($_POST['email']);?>" class="inputbox<?php if ($messageStack->size('email') > 0) { echo ' error';}?>" />
					</td>
				</tr>
				<tr>
					<td colspan="3" height="5"></td>
				</tr>
				<tr>
					<?php
					$gv_query = tep_db_query("select amount from coupon_gv_customer where customer_id = '".$customer_id."'");
					$gv_result = tep_db_fetch_array($gv_query);
					?>
					<td width="110"><?php echo Translate('Bedrag');?>:</td>
					<td width="135"><input type="text" name="amount" value="<?php echo stripslashes($_POST['amount']);?>" condition="range0-<?php echo $gv_result['amount'];?>_required" class="inputbox<?php if ($messageStack->size('amount') > 0) { echo ' error';}?>" /><?php if ($error) echo $error_amount;?></td>
					<td>
						<?php
						echo Translate('Beschikbaar').': '.$currencies->format($gv_result['amount']);
						?>
					</td>
				</tr>
				<tr>
					<td colspan="3" height="5"></td>
				</tr>
				<tr>
					<td width="110" valign="top"><?php echo Translate('Bericht');?>:</td>
					<td colspan="2"><textarea cols="50" rows="5" style="height: 150px;" name="message" condition="1" class="inputbox<?php if ($messageStack->size('message') > 0) { echo ' error';}?>"><?php echo  stripslashes($_POST['message']);?></textarea></td>
				</tr>
				<tr>
					<td colspan="3" height="5"></td>
				</tr>
				<tr>
					<td></td>
					<td colspan="2">
						<table cellspacing="0" cellpadding="0" border="0" width="100">
							<tr>
								<td style="padding-right:5px;">
								<?php
								$back = sizeof($navigation->path)-2;
								if (isset($navigation->path[$back]['page']) && $navigation->path[$back]['page'] != '') {
									$link = $navigation->path[$back]['page'];
								} else {
										$link = FILENAME_DEFAULT;
								}
								echo '<a href="' . tep_href_link($link, tep_array_to_string($navigation->path[$back]['get'], array('action'))) . '" class="button-a">'.Translate('Terug').'</a>';
								?>
								</td>
								<td>
									<input type="submit" class="button-a" value="<?php echo Translate('Verzend');?>" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
<?php
  }
?>
</table>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>