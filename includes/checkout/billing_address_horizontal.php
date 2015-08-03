<table cellpadding="0" cellspacing="0">
	<tr>
        <td>
            <label class="formLabel" for="billing_firstname"><?php echo Translate('Voornaam'); ?></label>
            <?php echo tep_draw_input_field('billing_firstname', (isset($billingAddress) ? $billingAddress['firstname'] : ''), 'class="inputbox required" id="billing_firstname" condition="1"'); ?>
        </td>
		<?php
        if (!tep_session_is_registered('customer_id')){
        ?>
        <td>
            <label class="formLabel" for="billing_lastname"><?php echo Translate('Achternaam'); ?></label>
            <?php echo tep_draw_input_field('billing_lastname', (isset($billingAddress) ? $billingAddress['lastname'] : ''), 'class="inputbox required" id="billing_lastname" condition="1"'); ?>
        </td>
        <?php
        }
        ?>
    </tr>
    <?php
    if (ACCOUNT_GENDER == 'true' && !tep_session_is_registered('customer_id')) {
        $gender = $billingAddress['entry_gender'];
        if (isset($gender)) {
            $male = ($gender == 'm') ? true : false;
            $female = ($gender == 'f') ? true : false;
        } else {
            $male = false;
            $female = false;
        }
        ?>
        <tr>
            <td colspan="2"><?php echo Translate('Geslacht'); ?>&nbsp;<?php echo tep_draw_radio_field('billing_gender', 'm', $male) . '&nbsp;&nbsp;' . Translate('man') . '&nbsp;&nbsp;' . tep_draw_radio_field('billing_gender', 'f', $female) . '&nbsp;&nbsp;' . Translate('vrouw'); ?></td>
        </tr>
	<?php
    }
	if (ACCOUNT_COMPANY == 'true') {
	?>
	<tr>
		<td>
			<?php
			if (!tep_session_is_registered('customer_id')){
			?>
			<label class="formLabel" for="billing_company"><?php echo Translate('Bedrijf'); ?></label>
			<?php
				echo tep_draw_input_field('billing_company', (isset($billingAddress) ? $billingAddress['company'] : ''), 'class="inputbox" id="billing_company" condition="2"');
			} else {
				if (isset($billingAddress['company']) && !empty($billingAddress['company'])) {
			?>
			<label class="formLabel" for="billing_company"><?php echo Translate('Bedrijf'); ?></label>
			<?php
					echo '<span class="inputbox">'.$billingAddress['company'].'</span>';
				}
			}
			?>
		</td>
		<td>
			<?php
			if (!tep_session_is_registered('customer_id')){
			?>
			<label class="formLabel" for="btwnr"><?php echo Translate('BTW nummer'); ?></label>
			<?php
				echo tep_draw_input_field('btwnr', (isset($billingAddress) ? $billingAddress['btwnr'] : ''), 'class="inputbox" id="btwnr"  mask="bv. BE 0000 000 000" condition="btw"');
			} else {
				if (isset($billingAddress['btwnr']) && !empty($billingAddress['btwnr'])) {
			?>
			<label class="formLabel" for="billing_company"><?php echo Translate('BTW nummer'); ?></label>
			<?php
					echo '<span class="inputbox">'.$billingAddress['btwnr'].'</span>';
				}
			}
			?>
		</td>
	</tr>
	<?php
	}
	if(!tep_session_is_registered('customer_id')) {
	?>
	<tr>
        <td>
			<label class="formLabel" for="billing_telephone"><?php echo  Translate('Tel.'); ?></label>
			<?php
			if(ONEPAGE_TELEPHONE == 'True')
				echo tep_draw_input_field('billing_telephone', (isset($customerAddress) ? $customerAddress['telephone'] : ''), 'class="inputbox required" id="billing_telephone" condition="2"'); 
			else
				echo tep_draw_input_field('billing_telephone', (isset($customerAddress) ? $customerAddress['telephone'] : ''), 'class="inputbox" id="billing_telephone" condition="2"'); 
			?>
        </td>
		<td>
			<div id="newAccountEmail">
				<label class="formLabel" for="billing_email_address"><?php echo Translate('email-adres'); ?></label>
				<?php echo tep_draw_input_field('billing_email_address', (isset($customerAddress) ? $customerAddress['email_address'] : ''), 'class="inputbox required" id="billing_email_address" condition="email_required"'); ?>
			</div>
		</td>
    </tr>
	<?php
	}
    if (ACCOUNT_DOB == 'true' && !tep_session_is_registered('customer_id')) {
        ?>
        <tr>
            <td colspan="2">
                <label class="formLabel" for="billing_dob"><?php echo Translate('Geboortedatum'); ?></label>
                <?php echo tep_draw_input_field('billing_dob', (isset($customerAddress) ? $customerAddress['dob'] : ''), 'class="inputbox" id="billing_dob"'); ?>
            </td>
        </tr>
		<?php
    }
    ?>
    <tr>
    	<td colspan="2">
        	<table cellpadding="0" cellspacing="0" width="100%">
            	<tr>
                    <td>
                        <label class="formLabel" for="billing_street_address"><?php echo Translate('Straat'); ?></label>
                        <?php echo tep_draw_input_field('billing_street_address', (isset($billingAddress) ? $billingAddress['street_address'] : ''), 'class="inputbox required" id="billing_street_address" condition="1"'); ?>
                    </td>
                    <?php
                    if (ACCOUNT_SUBURB == 'true') {
                        ?>
                        <td>
                            <label class="formLabel" for="billing_suburb"><?php echo Translate('Wijk'); ?></label>
                            <?php echo tep_draw_input_field('billing_suburb', (isset($billingAddress) ? $billingAddress['suburb'] : ''), 'class="inputbox" id="billing_suburb"'); ?>
                        </td>
                        <?php
                    }
                    ?>
                    <td>
                        <label class="formLabel" for="billing_zipcode"><?php echo Translate('Postcode'); ?></label>
                        <?php echo tep_draw_input_field('billing_zipcode', (isset($billingAddress) ? $billingAddress['postcode'] : ''), 'class="inputbox required" id="billing_zipcode" condition="1"'); ?>
                    </td>
                    <td>
                        <label class="formLabel" for="billing_city"><?php echo Translate('Gemeente'); ?></label>
                        <?php echo tep_draw_input_field('billing_city', (isset($billingAddress) ? $billingAddress['city'] : ''), 'class="inputbox required" id="billing_city" condition="1"'); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
		<?php
        if (ACCOUNT_STATE == 'true') {
            $defaultCountry = (isset($billingAddress) && tep_not_null($billingAddress['country_id']) ? $billingAddress['country_id'] : ONEPAGE_DEFAULT_COUNTRY);
            ?>
            <td>
            <?php echo Translate('Staat'); ?>
            <div id="stateCol_billing">
                <?php echo $onePageCheckout->getAjaxStateField($defaultCountry);?>
                <div <?php if(tep_not_null($billingAddress['zone_id']) || tep_not_null($billingAddress['state'])){ ?>class= "success_icon ui-icon-green ui-icon-circle-check" <?php }else{?> class="required_icon ui-icon-red ui-icon-gear" <?php } ?> style="margin-left: 3px; margin-top: 1px; float: left;" title="Required" /></div>
            </div>
            </td>
        <?php
        }
        ?>
        <td>
            <label class="formLabel" for="billing_country"><?php echo Translate('Land'); ?></label>
            <?php
			if (!tep_session_is_registered('customer_id')) {
				echo tep_get_country_list('billing_country', '', 'class="inputbox required" id="billing_country" condition="select_i0" type="select-one"');
			} else {
				echo tep_get_country_list('billing_country', (isset($billingAddress) && tep_not_null($billingAddress['country_id']) ? $billingAddress['country_id'] : ONEPAGE_DEFAULT_COUNTRY), 'class="inputbox required" id="billing_country" condition="select_i0"');
			}
			?>
        </td>
    </tr>
</table>
<?php
if(!tep_session_is_registered('customer_id')) { ?>
	<?php if ( (ONEPAGE_ACCOUNT_CREATE != 'required') && (CUSTOMER_ACCOUNTS!='false') ){ ?>
    	<div class="indent-small"></div>
		<p><input type="checkbox" name="billing_show_pw" id="billing_show_pw" value="1">
        <label for="billing_show_pw"><?php echo Translate('Maak een account aan voor uw volgend bezoek'); ?></label></p>
	<?php } ?>
	<table cellpadding="0" cellspacing="0" id="PwFields">
		<tr>
			<td>
				<label class="formLabel" for="password"><?php echo Translate('Wachtwoord'); ?></label>
				<?php echo tep_draw_password_field('password', '', 'autocomplete="off" class="inputbox required" '.'id="password" maxlength="40" condition="1"'); ?>
			</td>
			<td>
				<label class="formLabel" for="confirmation"><?php echo Translate('Wachtwoord bevestigen'); ?></label>
				<?php echo tep_draw_password_field('confirmation', '', 'autocomplete="off" ' . (ONEPAGE_ACCOUNT_CREATE == 'required' ? 'class="inputbox required" maxlength="40" ' : 'class="inputbox" maxlength="40" ') . 'id="confirmation" condition="confirmation_password_required"'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="pstrength_password"></div>
			</td>
		</tr>
	</table>
	<?php
	if (CHECKOUT_NEWSLETTER == 'true') {
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<?php
		$current_user_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
		$current_user = tep_db_fetch_array($current_user_query);
		$email_address = $current_user['customers_email_address'];
		$lists = PHPLIST_LISTNUMBERS;
		$lists = explode(';', $lists);
		foreach ($lists as $key=>$list) {
			$newsletter = check_if_subscribed($email_address, $list);
			$newsletter = explode('|', $newsletter);
			echo '<tr><td width="20">'.tep_draw_checkbox_field('newsletters_'.$list, '1', (($newsletter[1] == '1') ? true : false)).'</td><td>'.$newsletter[0].'</td></tr>';
		}
		tep_db_connect();
		?>
	</table>
	<?php
	}
}
?>