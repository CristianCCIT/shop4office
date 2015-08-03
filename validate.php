<?php
require_once('includes/application_top.php');
if (!tep_session_is_registered('customer_id')) {
	$navigation->set_snapshot();
	tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}
if (isset($_GET['gift'])) {
	$error = true;
	$voucher_number=tep_db_prepare_input($_GET['gift']);
	$gv_query = tep_db_query("select c.coupon_id, c.coupon_amount from ".TABLE_COUPONS." c, ".TABLE_COUPON_EMAIL_TRACK." et where coupon_code = '".addslashes($voucher_number)."' and c.coupon_id = et.coupon_id");
	if (tep_db_num_rows($gv_query) > 0) {
		$coupon = tep_db_fetch_array($gv_query);
		$redeem_query = tep_db_query("select coupon_id from ".TABLE_COUPON_REDEEM_TRACK." where coupon_id = '".$coupon['coupon_id']."'");
		if (tep_db_num_rows($redeem_query) == 0 ) {
			if (!tep_session_is_registered('gv_id')) {
				tep_session_register('gv_id');
			}
			$gv_id = $coupon['coupon_id'];
			$error = false;
		} else {
			$error = true;
		}
	}
} else {
	tep_redirect(FILENAME_DEFAULT);
}
if ((!$error) && (tep_session_is_registered('customer_id'))) {
	$gv_query = tep_db_query("insert into ".TABLE_COUPON_REDEEM_TRACK." (coupon_id, customer_id, redeem_date, redeem_ip) values ('".$coupon['coupon_id']."', '".$customer_id."', now(),'".$_SERVER['REMOTE_ADDR']."')");
	$gv_update = tep_db_query("update ".TABLE_COUPONS." set coupon_active = 'N' where coupon_id = '".$coupon['coupon_id']."'");
	tep_gv_account_update($customer_id, $gv_id);
	tep_session_unregister('gv_id');
}
$breadcrumb->add(Translate('Redeem Gift Certificate'));
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td>
			<h1><?php echo Translate('Validate Cadeaubon');?></h1>
		</td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
	<tr>
		<td>
			<?php
			$message = '<div class="messageStackSuccess">'.tep_image(DIR_WS_ICONS.'success.gif').' '.Translate('Proficiat, u hebt een cadeaubon gevalideerd van').' '.$currencies->format($coupon['coupon_amount']).'</div>';
			if ($error) {
				$message = '<div class="messageStackError">'.tep_image(DIR_WS_ICONS.'error.gif').' '.Translate('De cadeaubon code is niet geldig of is al gevalideerd.').'</div>';
			}
			echo $message;
			?>
		</td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
	<tr>
		<td>
			<?php echo '<a href="'.tep_href_link(FILENAME_DEFAULT).'" class="button-a">'.Translate('ga verder').'</a>';?>
		</td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
	<tr>
		<td class="validatePage">
			<?php echo transformText(VALIDATE__TEXT, Translate('Lees meer'));?>
		</td>
	</tr>
</table>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>