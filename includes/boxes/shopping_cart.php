<div id="shopping_cart">
<?php
if (CanShop() == 'true') {
// Start - CREDIT CLASS Gift Voucher Contribution
// CREDIT CLASS script moved for compatibility with STS
?>
<script language="javascript">
function couponpopupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
<?php
// End - CREDIT CLASS Gift Voucher Contribution
	echo '<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '" class="box-title">'.Translate('Winkelwagen').'</a>';
	echo '<div class="box-content">';
	if (!strstr($_SERVER['PHP_SELF'], 'checkout')) {
		echo '<span id="cart-items">'.Translate('Items').': '.$cart->count_contents().'</span>';
		echo '<span id="cart-total">'.Translate('Totaal').': '.$currencies->format($cart->show_total()).'</span>';
// Start - CREDIT CLASS Gift Voucher Contribution
		if (tep_session_is_registered('customer_id')) {
			$gv_query = tep_db_query("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . $customer_id . "'");
			$gv_result = tep_db_fetch_array($gv_query);
			if ($gv_result['amount'] > 0 ) {
				//echo '<span id="shoppingcartbox_voucher">'.Translate('Cadeaubon').': '.$currencies->format($gv_result['amount']).'</span>';
				//echo '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext"><a href="'. tep_href_link(FILENAME_GV_SEND) . '">' . BOX_SEND_TO_FRIEND . '</a></td></tr></table>';
			}
		}
		if (tep_session_is_registered('gv_id')) {
			$gv_query = tep_db_query("select coupon_amount from " . TABLE_COUPONS . " where coupon_id = '" . $gv_id . "'");
			$coupon = tep_db_fetch_array($gv_query);
			echo tep_draw_separator();
			echo '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="smalltext">' . VOUCHER_REDEEMED . '</td><td class="smalltext" align="right" valign="bottom">' . $currencies->format($coupon['coupon_amount']) . '</td></tr></table>';
		}
		if (tep_session_is_registered('cc_id') && $cc_id) {
			$coupon_query = tep_db_query("select * from " . TABLE_COUPONS . " where coupon_id = '" . $cc_id . "'");
			$coupon = tep_db_fetch_array($coupon_query);
			$coupon_desc_query = tep_db_query("select * from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id = '" . $cc_id . "' and language_id = '" . $languages_id . "'");
			$coupon_desc = tep_db_fetch_array($coupon_desc_query);
			$text_coupon_help = sprintf("%s",$coupon_desc['coupon_name']);
			echo tep_draw_separator();
			echo '<table cellpadding="0" width="100%" cellspacing="0" border="0"><tr><td class="infoBoxContents">' . CART_COUPON . $text_coupon_help . '<br>' . '</td></tr></table>';
		}  
// End - CREDIT CLASS Gift Voucher Contribution

	} else {
		echo Translate('Bezig met verwerking...');
	}
	echo '</div>';
}
?>
</div>