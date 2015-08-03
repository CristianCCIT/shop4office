<table cellpadding="0" cellspacing="0" border="0" width="400">
 <tr>
  <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
   <tr>
    <td class="main"><b><?php echo Translate('Pas het adres aan') . tep_draw_hidden_field('action', 'saveAddress') . tep_draw_hidden_field('address_id', $address['address_book_id']); ?></b></td>
   </tr>
  </table></td>
 </tr>
 <tr>
  <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
   <tr class="infoBoxContents">
    <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
     <tr>
      <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
      <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
       <tr>
        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if (ACCOUNT_GENDER == 'true') {
      $gender = $address['entry_gender'];
      if (isset($gender)) {
          $male = ($gender == 'm') ? true : false;
          $female = ($gender == 'f') ? true : false;
      } else {
          $male = false;
          $female = false;
      }
?>
         <tr>
          <td class="main"><?php echo Translate('Geslacht'); ?></td>
          <td class="main"><?php echo tep_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . Translate('man') . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . Translate('vrouw') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  }
?>
         <tr>
          <td class="main"><?php echo Translate('Naam'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('firstname', $address['entry_firstname']) . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
         <!--<tr>
          <td class="main"><?php echo Translate('Familienaam'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('lastname', $address['entry_lastname']) . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>-->
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
         <tr>
          <td class="main"><?php echo Translate('Bedrijf'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('company', $address['entry_company']) . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  }
?>
         <tr>
          <td class="main"><?php echo Translate('Straat'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('street_address', $address['entry_street_address']) . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
         <tr>
          <td class="main"><?php echo Translate('Wijk'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('suburb', $address['entry_suburb']) . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  }
?>
         <tr>
          <td class="main"><?php echo Translate('Gemeente'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('city', $address['entry_city']) . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  if (ACCOUNT_STATE == 'true') {
      if (tep_not_null($address['entry_zone_id'])){
          $zones_array = array();
          $zones_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_country_id = '" . $address['entry_country_id'] . "' order by zone_code");
          while ($zones_values = tep_db_fetch_array($zones_query)) {
              $zones_array[] = array('id' => $zones_values['zone_code'], 'text' => $zones_values['zone_code']);
          }
          
          $QzoneName = tep_db_query('select zone_code from ' . TABLE_ZONES . ' where zone_id = "' . $address['entry_zone_id'] . '"');
          $zoneName = tep_db_fetch_array($QzoneName);
          $input = tep_draw_pull_down_menu('state', $zones_array, $zoneName['zone_code']);
      }else{
          $input = tep_draw_input_field('state', $address['entry_state']);
      }
?>
         <tr>
          <td class="main"><?php echo Translate('Staat'); ?></td>
          <td class="main" id="stateCol"><?php echo $input . '&nbsp;<span class="inputRequirement">*</span>';?></td>
         </tr>
<?php
  }
?>
         <tr>
          <td class="main"><?php echo Translate('Postcode'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('postcode', $address['entry_postcode']) . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
         <tr>
          <td class="main"><?php echo Translate('Land'); ?></td>
          <td class="main"><?php echo tep_get_country_list('country', $address['entry_country_id']) . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
        </table></td>
        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
       </tr>
      </table></td>
      <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
     </tr>
    </table></td>
   </tr>
  </table></td>
 </tr>
</table>