<?php
  require_once('includes/application_top.php');

  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// needs to be included earlier to set the success message in the messageStack

  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    $password_current = tep_db_prepare_input($_POST['password_current']);
    $password_new = tep_db_prepare_input($_POST['password_new']);
    $password_confirmation = tep_db_prepare_input($_POST['password_confirmation']);

    $error = false;

    if (strlen($password_current) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_password', Translate('Het huidige wachtwoord bestaat uit meer karakters'));
    } elseif (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_password', Translate('Het nieuwe wachtwoord is niet lang genoeg'));
    } elseif ($password_new != $password_confirmation) {
      $error = true;
      $messageStack->add('account_password', Translate('Wachtwoord en wachtwoord bevestigen zijn niet gelijk'));
    }

    if ($error == false) {
      $check_customer_query = tep_db_query("select customers_email_address, customers_password from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
      $check_customer = tep_db_fetch_array($check_customer_query);

      if (tep_validate_password($password_current, $check_customer['customers_password'])) {
		  
		/*autologin*/
		$new_encrypted_password = tep_encrypt_password($password_new);
		
		tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . $new_encrypted_password . "' where customers_id = '" . (int)$customer_id . "'");
		tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_encrypt_password($password_new) . "' where customers_id = '" . (int)$customer_id . "'");
		tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int)$customer_id . "'");
		
		/*FORUM*/
		if ((FORUM_ACTIVE=='true') && (FORUM_SYNC_USERS=='true')) {
			tep_db_query("UPDATE ".FORUM_DB_DATABASE.".users set user_password = '".phpbb_hash($password_new)."', user_passchg = '".time()."' WHERE user_email = '".$check_customer['customers_email_address']."'");
		}
		/*FORUM*/
		
		if (tep_not_null($_COOKIE['password'])) {   //Autologon, Was it enabled?
			$cookie_url_array = parse_url((ENABLE_SSL == true ? HTTPS_SERVER : HTTP_SERVER) . substr(DIR_WS_CATALOG, 0, -1));
			$cookie_path = $cookie_url_array['path'];	
			setcookie('password', $new_encrypted_password, time()+ (365 * 24 * 3600), $cookie_path, '', ((getenv('HTTPS') == 'on') ? 1 : 0));
		}
		/*autologin*/	

        $messageStack->add_session('account', Translate('Uw paswoord is succesvol aangepast.'), 'success');

        tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
      } else {
        $error = true;
        $messageStack->add('account_password', Translate('Uw huidig wachtwoord is niet correct ingegeven.'));
      }
    }
  }

  $breadcrumb->add(Translate('Mijn account'), tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(Translate('Wachtwoord wijzigen'), tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'));
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
    <td width="100%" valign="top"><?php echo tep_draw_form('account_password', tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'), 'post', 'onSubmit="return check_form(account_password);"') . tep_draw_hidden_field('action', 'process'); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><h1><?php echo Translate('Wachtwoord wijzigen'); ?></h1></td>
                <td class="inputRequirement" align="right"><?php echo Translate('* Verplicht veld'); ?></td>
              </tr>
            </table></td>
      </tr>
      <tr>
        <td height="10"></td>
      </tr>
<?php
  if ($messageStack->size('account_password') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('account_password'); ?></td>
      </tr>
      <tr>
        <td height="10"></td>
      </tr>
<?php
  }
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td></td>
          </tr>
          <tr>
            <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
              <tr class="infoBoxContents">
                <td><table border="0" cellspacing="2" cellpadding="2">
                  <tr>
                    <td class="main"><?php echo Translate('Huidig wachtwoord'); ?></td>
                    <td class="main"><?php echo tep_draw_password_field('password_current', '', 'class="inputbox"') . '&nbsp;' . (tep_not_null('*') ? '<span class="inputRequirement">*</span>': ''); ?></td>
                  </tr>
                  <tr>
                    <td colspan="2" height="10"></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo Translate('Nieuw wachtwoord'); ?></td>
                    <td class="main"><?php echo tep_draw_password_field('password_new', '', 'class="inputbox"') . '&nbsp;' . (tep_not_null('*') ? '<span class="inputRequirement">*</span>': ''); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo Translate('Bevestig nieuw wachtwoord'); ?></td>
                    <td class="main"><?php echo tep_draw_password_field('password_confirmation', '', 'class="inputbox"') . '&nbsp;' . (tep_not_null('*') ? '<span class="inputRequirement">*</span>': ''); ?></td>
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
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '" class="button-a"><span>' . Translate('Terug') . '</span></a>'; ?></td>
                <td align="right"><input type="submit" value="<?=Translate('Wachtwoord wijzigen')?>" class="formbutton button-a" /></td>
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
