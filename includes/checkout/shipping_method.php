<?php
if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
	$pass = false;
    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'both':
        $pass = true;
        break;
    }
    // disable free shipping for Alaska and Hawaii
    $zone_code = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], '');
    if(in_array($zone_code, array('AK', 'HI'))) {
		$pass = false;
    }
    $free_shipping = false;
    if ($pass == true && ($order->info['total'] - $order->info['shipping_cost']) >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
		$free_shipping = true;
		//include(DIR_WS_LANGUAGES . $language . '/modules/order_total/ot_shipping.php');
    }
} else {
	$free_shipping = false;
}
$quotes = $shipping_modules->quote();
if ( !tep_session_is_registered('shipping') || ( tep_session_is_registered('shipping') && ($shipping == false) && (tep_count_shipping_modules() > 1) ) ){
	if (tep_session_is_registered('shipping')){
		tep_session_unregister('shipping');
	}
	tep_session_register('shipping');
	if($free_shipping == false)
		$shipping = $shipping_modules->cheapest();
	else
	{
		$shipping = array(
			  'id' => 'free_free',
			  'title' => Translate('Gratis verzending'),
			  'cost' => '0'
			  );
	}
}
if ($order->delivery['postcode']=='') {
	?>
    <div class="disabled-overflow"></div>
    <?php
}
if (sizeof($quotes) > 1 && sizeof($quotes[0]) > 1) {
?>
    <p><?php echo Translate('Selecteer a.u.b. een verzend methode voor deze bestelling.'); ?></p>
<?php
} elseif ($free_shipping == false) {
?>
	<p><?php echo Translate('Dit is momenteel de enige verzend methode die u kan kiezen voor uw bestelling.'); ?></p>
<?php
}
if ($free_shipping == true) {
	$checked = ($shipping['id'] == 'free_free'?true:false);
	?>
    <p><?php echo Translate('Gratis verzending'); ?>: <?php echo $quotes[$i]['icon']; ?></p>
    <p><?php echo sprintf(Translate('De producten voor deze bestelling worden gratis verzonden.'), $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)); ?>
    <?php echo tep_draw_radio_field('shipping', 'free_free', $checked); ?></p>
	<?php
} 
if(sizeof($quotes) >= 1) {
	?>
    <ul class="formatted-list">
    <?php
	$radio_buttons = 0;
	for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
		?>
        <li>
		<span class="method-name"><?php echo $quotes[$i]['module']; ?></span>
		<?php if (isset($quotes[$i]['icon']) && tep_not_null($quotes[$i]['icon'])) { echo $quotes[$i]['icon']; } ?>
		<?php
		if (isset($quotes[$i]['error'])) {
			echo $quotes[$i]['error'];
		} else {
			?>
            <div id="tbl_<?php echo $quotes[$i]['id']; ?>">
            <table cellpadding="0" cellspacing="0" width="100%">
            <?php
			for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
				// set the radio button to be checked if it is the method chosen
				if ($onepage['info']['shipping_method']['id']!='') {
				$checked = ($quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'] == $shipping['id'] ? true : false);
				}
				if ($quotes[$i]['methods'][$j]['distance']!='' && $j==0) {
				?>
                <tr>
                	<td colspan="3"><h2><?php echo Translate('In mijn buurt'); ?></h2></td>
                </tr>
                <?php	
				} elseif ($quotes[$i]['methods'][$j]['distance']!='' && $j>3 && $title_shown==false) {
					$title_shown=true;
				?>
                <tr>
                	<td colspan="3"><h2><?php echo Translate('Overige'); ?></h2></td>
                </tr>
                <?php
				} 
				?>
				<tr class="moduleRow shippingRow<?php echo ($checked ? ' moduleRowSelected' : ''); if ($j==0) { echo ' first'; } ?>">
					<?php
                    if ( ($n > 1) || ($n2 > 1) ) {
                    ?>
                        <td style="width:20px;"><?php echo tep_draw_radio_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'], $checked, 'id="'.$quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'].'"'); ?></td>
                        <td style="width:200px;"><?php echo $quotes[$i]['methods'][$j]['title']; ?></td>
                        <td style="text-align:right;">
							<?php 
							if ($quotes[$i]['methods'][$j]['cost']!=0) {
								echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)));
							}
							?>
                        </td>
					<?php
                    } else {
						if ($checked) {
							$shipping_actual_tax = $quotes[$i]['tax'] / 100;
							$shipping_tax = $shipping_actual_tax * $quotes[$i]['methods'][$j]['cost'];
							$shipping['cost'] = $quotes[$i]['methods'][$j]['cost'];
							$shipping['shipping_tax_total'] = $shipping_tax;
							if (isset($onepage['info']['shipping_method']['cost'])) {
								$onepage['info']['shipping_method']['cost'] =
								$quotes[$i]['methods'][$j]['cost'];
								$onepage['info']['shipping_method']['shipping_tax_total'] =
								$shipping_tax;
							}
						}
						?>
                        <td style="width:220px;"><?php echo $quotes[$i]['methods'][$j]['title']; ?></td>
                        <td style="text-align:right;">
							<?php 
							if ($quotes[$i]['methods'][$j]['cost']!=0) {
								echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax']));
							}
							echo tep_draw_hidden_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id']);
							?>
                        </td>
					<?php
                    }
                    ?>
                </tr>
				<?php
                $radio_buttons++;
			}
			?>
            </table>
            </div>
        <?php
		}
		?>
		</li>
		<?php

	}
	?>
    </ul>
    <?php
}
?>
