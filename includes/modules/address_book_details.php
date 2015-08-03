<?php
  if (!isset($process)) $process = false;
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="inputRequirement" align="right"><?php echo Translate('* Verplicht veld'); ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
      <tr class="infoBoxContents">
        <td><table border="0" cellspacing="2" cellpadding="2">
          <tr>
            <td class="main"><?php echo Translate('Naam'); ?>:</td>
            <td class="main"><?php echo tep_draw_input_field('firstname', $entry['entry_firstname']) . '<span class="inputRequirement">' . ' *' . '</span>'; ?></td>
          </tr>
          <!--<tr>
            <td class="main"><?php //echo Translate('Achternaam'); ?>:</td>
            <td class="main"><?php //echo tep_draw_input_field('lastname', $entry['entry_lastname']) . '<span class="inputRequirement">' . ' *' . '</span>'; ?></td>
          </tr>-->
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
<?php
if (ACCOUNT_COMPANY == 'true') {
?>
          <tr>
            <td class="main"><?php echo Translate('Bedrijfsnaam'); ?>:</td>
            <td class="main">
				<?php
				if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
					echo $entry['entry_company'];
				} else {
					echo tep_draw_input_field('company', $entry['entry_company']);
				}
				?>
			</td>
          </tr>
          <tr>
            <td class="main"><?php echo Translate('BTW nummer'); ?>:</td>
            <td class="main">
				<?php
				if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
					echo $entry['billing_tva_intracom'];
				} else {
					echo tep_draw_input_field('btwnr', $entry['billing_tva_intracom']);
				}
				?>
			</td>
          </tr>	  
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
<?php
}
?>
          <tr>
            <td class="main"><?php echo Translate('Straat en huisnummer'); ?>:</td>
            <td class="main"><?php echo tep_draw_input_field('street_address', $entry['entry_street_address']) . '<span class="inputRequirement">' . ' *' . '</span>'; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo Translate('Postcode'); ?>:</td>
            <td class="main"><?php echo tep_draw_input_field('postcode', $entry['entry_postcode']) . '<span class="inputRequirement">' . ' *' . '</span>'; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo Translate('Woonplaats'); ?>:</td>
            <td class="main"><?php echo tep_draw_input_field('city', $entry['entry_city'])  . '<span class="inputRequirement">' . ' *' . '</span>'; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo Translate('Land'); ?>:</td>
            <td class="main"><?php echo tep_get_country_list('country', $entry['entry_country_id']) . '<span class="inputRequirement">' . ' *' . '</span>'; ?></td>
          </tr>
<?php
  if ((isset($HTTP_GET_VARS['edit']) && ($customer_default_address_id != $HTTP_GET_VARS['edit'])) || (isset($HTTP_GET_VARS['edit']) == false) ) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
            <td colspan="2" class="main"><?php echo tep_draw_checkbox_field('primary', 'on', false, 'id="primary"') . ' ' . Translate('Markeer als uw primaire adres'); ?></td>
          </tr>
<?php
  }
?>
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
