<?php
/*
  $Id: account_edit.php,v 1.65 2003/06/09 23:03:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    if (ACCOUNT_GENDER == 'true') $gender = tep_db_prepare_input($_POST['gender']);
    $firstname = tep_db_prepare_input($_POST['firstname']);
    if (ACCOUNT_DOB == 'true') $dob = tep_db_prepare_input($_POST['dob']);
    $email_address = tep_db_prepare_input($_POST['email_address']);
    $telephone = tep_db_prepare_input($_POST['telephone']);
    $fax = tep_db_prepare_input($_POST['fax']);

    $error = false;


    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', Translate('Uw voornaam moet minstens ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' karakters bevatten'));
    }


    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', Translate('Gelieve een geldig e-mailadres in te geven'));
    }

    if (!tep_validate_email($email_address)) {
      $error = true;

      $messageStack->add('account_edit', Translate('Gelieve een geldig e-mailadres in te geven'));
    }

    $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_id != '" . (int)$customer_id . "'");
    $check_email = tep_db_fetch_array($check_email_query);
    if ($check_email['total'] > 0) {
      $error = true;

      $messageStack->add('account_edit', Translate('Er bestaat reeds een klant met dit e-mailadres'));
    }

    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', Translate('Uw telefoonnummer moet minstens ' . ENTRY_TELEPHONE_MIN_LENGTH . ' karakters bevatten'));
    }

    if ($error == false) {


      $sql_data_array = array('customers_firstname' => $firstname,
                              'customers_email_address' => $email_address,
                              'customers_telephone' => $telephone,
                              'customers_fax' => $fax);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);

      tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "'");

      tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int)$customer_id . "'");

      $sql_data_array = array('entry_firstname' => $firstname);

      tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$customer_default_address_id . "'");
		/*autologin*/
		if (tep_not_null($_COOKIE['email_address'])) {   //Does email address exist in Cookie?
			$cookie_url_array = parse_url((ENABLE_SSL == true ? HTTPS_SERVER : HTTP_SERVER) . substr(DIR_WS_CATALOG, 0, -1));
			$cookie_path = $cookie_url_array['path'];	
			setcookie('email_address', $email_address, time()+ (365 * 24 * 3600), $cookie_path, '', ((getenv('HTTPS') == 'on') ? 1 : 0));
		}
		/*autologin*/	
// reset the session variables
      $customer_first_name = $firstname;

      $messageStack->add_session('account', Translate('Uw account is succesvol bijgewerkt'), 'success');

      tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }
  }

  $account_query = tep_db_query("select customers_gender, customers_firstname, customers_dob, customers_email_address, customers_telephone, customers_fax from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
  $account = tep_db_fetch_array($account_query);

  $breadcrumb->add(Translate('Mijn account'), tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(Translate('Account bewerken'), tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<?php require('includes/form_check.js.php'); ?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top">
	<?php echo tep_draw_form('account_edit', tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'), 'post', 'onSubmit="return check_form(account_edit);"') . tep_draw_hidden_field('action', 'process'); ?>
    <?php
	if ($messageStack->size('account_edit') > 0) { echo $messageStack->output('account_edit'); }
	?>

    <table border="0" width="100%" cellspacing="10" cellpadding="10">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><h1><?php echo Translate('Account bewerken'); ?></h1></td>
                <td class="inputRequirement" align="right"><?php echo Translate('* Verplicht veld'); ?></td>
              </tr>
            </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
              <tr class="infoBoxContents">
                <td><table border="0" cellspacing="5" cellpadding="5">
                  <tr>
                    <td class="main"><label for="name"><?php echo Translate('Naam'); ?></label>:</td>
                    <td class="main"><?php echo tep_draw_input_field('firstname', $account['customers_firstname'], 'class="inputbox" id="name"') . ' <span class="inputRequirement">*</span>'; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><label for="email"><?php echo Translate('E-mailadres'); ?></label>:</td>
                    <td class="main"><?php echo tep_draw_input_field('email_address', $account['customers_email_address'], 'class="inputbox" id="email"') . ' <span class="inputRequirement">*</span>'; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><label for="phone"><?php echo Translate('Telefoonnummer'); ?></label>:</td>
                    <td class="main"><?php echo tep_draw_input_field('telephone', $account['customers_telephone'], 'class="inputbox" id="phone"') . ' <span class="inputRequirement">*</span>'; ?></td>
                  </tr>
                  <tr>
                    <td class="main"><label for="fax"><?php echo Translate('Faxnummer'); ?></label>:</td>
                    <td class="main"><?php echo tep_draw_input_field('fax', $account['customers_fax'], 'class="inputbox" id="fac"'); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td height="10"></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" cellspacing="5" cellpadding="5">
              <tr>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '" class="button-a"><span>' . Translate('Terug') . '</span></a>'; ?></td>
                <td align="right"><input type="submit" value="<?=Translate('Ga verder')?>" class="formbutton button-a" /></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></form></td>
<!-- body_text_eof //-->
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
