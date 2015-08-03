<?php
$selection = $payment_modules->selection();
// *** BEGIN GOOGLE CHECKOUT ***
// Skips Google Checkout as a payment option on the payments page since that option
// is provided in the checkout page.
for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
	if ($selection[$i]['id'] == 'googlecheckout') {
		array_splice($selection, $i, 1);
		break;
	}
}
// *** END GOOGLE CHECKOUT ***
for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
	if ($selection[$i]['id'] == 'ccerr') {
		array_splice($selection, $i, 1);
		break;
	}
}
$paymentMethod = '';
if (tep_session_is_registered('onepage')){
	$paymentMethod = $onepage['info']['payment_method'];
}
if ($paymentMethod == ''){
	$paymentMethod = ONEPAGE_DEFAULT_PAYMENT;
}
if (($order->delivery['postcode']=='') || ($onepage['info']['shipping_method']['id']=='')) {
	?>
    <div class="disabled-overflow"></div>
    <?php
}
if (sizeof($selection) > 1) {
	?>
    <p><?php echo Translate('Selecteer a.u.b. een betaalmethode voor deze bestelling.'); ?></p>
	<?php
} else {
	?>
    <p><?php echo Translate('Dit is momenteel de enige betaalmethode die u kan kiezen voor uw bestelling.'); ?></p>
	<?php
}
$radio_buttons = 0;
?>
<ul class="formatted-list">
<?php
for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
	?>
    <li class="moduleRow paymentRow<?php echo ($selection[$i]['id'] == $paymentMethod ? ' moduleRowSelected' : '');?>">
    <?php
	if (sizeof($selection) > 1) {
		echo tep_draw_radio_field('payment', $selection[$i]['id'], ($selection[$i]['id'] == $paymentMethod));
	} else {
		echo tep_draw_hidden_field('payment', $selection[$i]['id']);
	}
	?>
    <span class="method-name"><?php echo $selection[$i]['module']; ?></span>
	<?php
    if (isset($selection[$i]['error'])) {
		echo $selection[$i]['error'];
	} elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields']) && ($selection[$i]['id'] == $paymentMethod)) {
		for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
			echo $selection[$i]['fields'][$j]['title'];
			echo $selection[$i]['fields'][$j]['field'];
		}
	}
	?>
    </li>
    <?php
	$radio_buttons++;
}
?>
</ul>
<?php
// Start - CREDIT CLASS Gift Voucher Contribution
if(MODULE_ORDER_TOTAL_GV_STATUS == 'true')
if (tep_session_is_registered('customer_id')) {
	$gv_query = tep_db_query("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . $customer_id . "'");
  	$gv_result = tep_db_fetch_array($gv_query);
    if ($gv_result['amount']>0){
		echo '<div onclick="clearRadeos()">';
		echo $order_total_modules->sub_credit_selection();
		echo $currencies->format($gv_result['amount']).' '.Translate('tegoed van cadeaubonnen kan gebruikt worden.');
		?>
		<script type="text/javascript">
		<!--
		<?php
		// Start - CREDIT CLASS Gift Voucher Contribution
		if (MODULE_ORDER_TOTAL_COUPON_STATUS == 'true'){
			if (MODULE_ORDER_TOTAL_INSTALLED) {
				$temp=$order_total_modules->process();
			}

			$temp=$temp[count($temp)-1];
			$temp=$temp['value'];
		
			$gv_query = tep_db_query("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . $customer_id . "'");
			$gv_result = tep_db_fetch_array($gv_query);
		
			if ($gv_result['amount']>=$temp){
				$coversAll=true;
				?>
				function clearRadeos(){
					document.checkout.cot_gv.checked=!document.checkout.cot_gv.checked;
					for (counter = 0; counter < document.checkout.payment.length; counter++) {
						// If a radio button has been selected it will return true
						// (If not it will return false)
						if (document.checkout.cot_gv.checked){
							document.checkout.payment[counter].checked = false;
							document.checkout.payment[counter].disabled=true;
						} else {
							document.checkout.payment[counter].disabled=false;
						}
					}
				}
		<?php
			} else {
				$coversAll=false;?>
				function clearRadeos(){
					document.checkout.cot_gv.checked=!document.checkout.cot_gv.checked;
				}
		<?php
			}
		}
		?>
		//-->
		</script>
		<?php
		echo '</div>';
	}
}
// End - CREDIT CLASS Gift Voucher Contribution
if (is_array($buysafe_result) && $buysafe_result['IsBuySafeEnabled'] == 'true') {
    $buysafe_module->draw_payment_page();
}
//BOF Points/Rewards
if ((USE_POINTS_SYSTEM == 'true') && (USE_REDEEM_SYSTEM == 'true')) {
	echo points_selection();
	if (tep_not_null(USE_REFERRAL_SYSTEM) && (tep_count_customer_orders() == 0)) {
		echo referral_input();
	}
}
//EOF Points/Rewards
?>