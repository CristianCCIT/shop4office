<?php
/*
  $Id: checkout_new_address.php,v 1.4 2003/06/09 22:49:57 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  if (!isset($process)) $process = false;
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td class="main"><?php echo Translate('Voornaam'); ?></td>
    <td class="main"><?php echo tep_draw_input_field('firstname') . ' <span class="inputRequirement">*</span>'; ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo Translate('Achternaam'); ?></td>
    <td class="main"><?php echo tep_draw_input_field('lastname') . ' <span class="inputRequirement">*</span>'; ?></td>
  </tr>
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
  <tr>
    <td class="main"><?php echo Translate('Bedrijfsnaam'); ?></td>
    <td class="main"><?php echo tep_draw_input_field('company'); ?></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="main"><?php echo Translate('Straat en huisnummer'); ?></td>
    <td class="main"><?php echo tep_draw_input_field('street_address') . ' <span class="inputRequirement">*</span>'; ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo Translate('Postcode'); ?></td>
    <td class="main"><?php echo tep_draw_input_field('postcode') . ' <span class="inputRequirement">*</span>'; ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo Translate('Woonplaats'); ?></td>
    <td class="main"><?php echo tep_draw_input_field('city') . ' <span class="inputRequirement">*</span>'; ?></td>
  </tr>
  <tr>
    <td class="main"><?php echo Translate('Land'); ?></td>
    <td class="main"><?php echo tep_get_country_list('country') . ' <span class="inputRequirement">*</span>'; ?></td>
  </tr>
</table>
