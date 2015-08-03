<div class="box_title"><?php echo Translate('Leveringsadres');?></div>
<table cellpadding="0" cellspacing="0">
	<tr>
        <td>
            <label class="formLabel" for="shipping_firstname"><?php echo Translate('Naam'); ?></label>
            <?php echo tep_draw_input_field('shipping_firstname', $shippingAddress['firstname'], 'class="inputbox required" id="shipping_firstname" condition="2"'); ?>
        </td>
		<?php
		if (ACCOUNT_COMPANY == 'true') {
		?>
			<td>
			<label class="formLabel" for="shipping_company"><?php echo Translate('Bedrijf'); ?></label>
			<?php echo tep_draw_input_field('shipping_company', '', 'class="inputbox" id="shipping_company" condition="2"');?>
			</td>
		<?php
		}
		?>
    </tr>
    <tr>
    	<td colspan="2">
        	<table cellpadding="0" cellspacing="0" width="100%">
            	<tr>
                	<td>
                        <label class="formLabel" for="shipping_street_address"><?php echo Translate('Straat'); ?></label>
                        <?php echo tep_draw_input_field('shipping_street_address', $shippingAddress['street_address'], 'class="inputbox required" id="shipping_street_address" condition="2"'); ?>
                    </td>
					<?php
                    if (ACCOUNT_SUBURB == 'true') {
                    ?>
                        <td>
                        <label class="formLabel" for="shipping_suburb"><?php echo Translate('Wijk'); ?></label>
                        <?php echo tep_draw_input_field('shipping_suburb', $shippingAddress['suburb'], 'class="inputbox" id="shipping_suburb"'); ?>
                        </td>
                    <?php
                    }
                    ?>
                    <td>
                    	<label class="formLabel" for="shipping_zipcode"><?php echo Translate('Postcode'); ?></label>
						<?php echo tep_draw_input_field('shipping_zipcode', $shippingAddress['postcode'], 'class="inputbox required" id="shipping_zipcode" condition="3"'); ?>
                    </td>
                    <td>
                        <label class="formLabel" for="shipping_city"><?php echo Translate('Gemeente'); ?></label>
						<?php echo tep_draw_input_field('shipping_city', $shippingAddress['city'], 'class="inputbox required" id="shipping_city" condition="2"'); ?>
                    </td>
					<?php
                    if (ACCOUNT_STATE == 'true') {
                        $defaultCountry = (isset($shippingAddress) && tep_not_null($shippingAddress['country_id']) ? $shippingAddress['country_id'] : ONEPAGE_DEFAULT_COUNTRY);
                        ?>
                        <td>
                        <label class="formLabel" for="shipping_firstname"><?php echo Translate('Staat'); ?></label>
                        <div id="stateCol_delivery">
                            <?php echo $onePageCheckout->getAjaxStateField($defaultCountry, 'delivery');?>
                            <div <?php if(tep_not_null($shippingAddress['zone_id']) || tep_not_null($shippingAddress['state'])){ ?>class= "success_icon ui-icon-green ui-icon-circle-check" <?php }else{?> class="required_icon ui-icon-red ui-icon-gear" <?php } ?> style="margin-left: 1px; margin-top: 1px; float: left;" title="Required" /></div>
                        </div>
                        </td>
                    <?php
                    }
                    ?>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <label class="formLabel" for="shipping_country"><?php echo Translate('Land'); ?></label>
            <?php
			if (!tep_session_is_registered('customer_id')) {
				echo tep_get_country_list('shipping_country', '', 'class="inputbox required" id="shipping_country" condition="select_i0" type="select-one"');
			} else {
				echo tep_get_country_list('shipping_country', (isset($shippingAddress['country_id']) ? $shippingAddress['country_id'] : ONEPAGE_DEFAULT_COUNTRY), 'class="inputbox required" id="shipping_country" condition="select_i0"');
			}
			?>
        </td>
    </tr>
</table>
<div class="indent-small"></div>