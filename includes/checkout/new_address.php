<table cellpadding="0" cellspacing="0" border="0" width="400">
<?php 
  if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) {
?>
 <tr>
  <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
   <tr>
    <td class="main"><b><?php echo Translate('Nieuw adres') . tep_draw_hidden_field('action', 'addNewAddress'); ?></b></td>
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
          <td class="main"><?php echo tep_draw_input_field('firstname') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
         <!--<tr>
          <td class="main"><?php echo Translate('Familienaam'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('lastname') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>-->
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
         <tr>
          <td class="main"><?php echo Translate('Bedrijf'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('company') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  }
?>
         <tr>
          <td class="main"><?php echo Translate('Straat'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('street_address') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
         <tr>
          <td class="main"><?php echo Translate('Bedrijf'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('suburb') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  }
?>
         <tr>
          <td class="main"><?php echo Translate('Gemeente'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('city') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  if (ACCOUNT_STATE == 'true') {
?>
         <tr>
          <td class="main"><?php echo Translate('Staat'); ?></td>
          <td class="main" id="stateCol"><?php echo tep_draw_input_field('state') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
<?php
  }
?>
         <tr>
          <td class="main"><?php echo Translate('Postcode'); ?></td>
          <td class="main"><?php echo tep_draw_input_field('postcode') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
         </tr>
         <tr>
          <td class="main"><?php echo Translate('Land'); ?></td>
          <td class="main"><?php echo tep_get_country_list('country') . '&nbsp;<span class="inputRequirement">*</span>'; ?></td>
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
<?php
  }    
?>   
</table>