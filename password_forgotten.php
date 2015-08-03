<?php
  require('includes/application_top.php');
  if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
    $email_address = tep_db_prepare_input($_POST['email_address']);
    $check_customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_password, customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
    if (tep_db_num_rows($check_customer_query)) {
      $check_customer = tep_db_fetch_array($check_customer_query);
      $new_password = tep_create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
      $crypted_password = tep_encrypt_password($new_password);
      tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . tep_db_input($crypted_password) . "' where customers_id = '" . (int)$check_customer['customers_id'] . "'");
		/*FORUM*/
		if ((FORUM_ACTIVE=='true') && (FORUM_SYNC_USERS=='true')) {
			tep_db_query("UPDATE ".FORUM_DB_DATABASE.".users set user_password = '".phpbb_hash($new_password)."', user_passchg = '".time()."' WHERE user_email = '".$check_customer['customers_email_address']."'");
		}
		/*FORUM*/
if (EMAIL_USE_HTML == 'true') {
	$Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
	$Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
	$Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
	$Vartext1 = Translate('Nieuw wachtwoord');
	$Vartext2 = '<table cellspacing="3" cellpadding="3" border="0" width="587" bgcolor="#ffffff"><tr><td>';
	$Vartext2 .= Translate('Beste ').' '.$check_customer['customers_firstname'].',';
	$Vartext2 .= '</td></tr><tr><td height="5"></td></tr><tr><td>';
	$Vartext2 .= sprintf(Translate('Uw nieuw wachtwoord is: %s'), $new_password);
	$Vartext2 .= '</td></tr></table>';
	$Varcopyright = Translate('Copyright $copy; 2010');
	$Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door &eacute;&eacute;n van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:'.STORE_OWNER_EMAIL_ADDRESS.'">'.STORE_OWNER_EMAIL_ADDRESS.'</a>';
	require(DIR_WS_MODULES . 'email/html_password_forgotten.php');
	$email_text = $html_email_text ;
} else {
	$email_text = sprintf(Translate('Uw nieuw wachtwoord is: %s'), $new_password);
}




      tep_mail($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $email_address, Translate('Nieuw wachtwoord'), $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      $messageStack->add('password_forgotten', Translate('Een nieuw wachtwoord werd naar uw e-mailadres gestuurd. Gelieve uw inbox te controleren.'), 'success');
    } else {
      $messageStack->add('password_forgotten', Translate('Het e-mailadres kon niet gevonden worden'));
    }
  }
  $breadcrumb->add(Translate('Inloggen'), tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  $breadcrumb->add(Translate('Wachtwoord vergeten'), tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
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
    <td width="100%" valign="top"><?php echo tep_draw_form('password_forgotten', tep_href_link(FILENAME_PASSWORD_FORGOTTEN, 'action=process&member=none', 'SSL')); ?>
    <?php
  if ($messageStack->size('password_forgotten') > 0) {
echo $messageStack->output('password_forgotten');
  }
?>
    <table border="0" width="100%" cellspacing="10" cellpadding="10">
      <tr>
        <td><h1><?php echo Translate('Wachtwoord vergeten'); ?></h1></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" height="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" height="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td height="10"></td>
              </tr>
              <tr>
                <td colspan="2" class="main"><?php echo Translate('Gelieve uw emailadres op te geven. Een nieuw wachtwoord wordt aangemaakt en naar uw inbox gestuurd.'); ?></td>
              </tr>
              <tr>
                <td height="20"></td>
              </tr>
              <tr>
                <td class="main"><?php echo '<b>' . Translate('E-mailadres') . '</b>&nbsp; ' . tep_draw_input_field('email_address', '', 'style="width:400px;" class="searchinputboxtrans"'); ?></td>
              </tr>
              <tr>
                <td height="10"></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
     <tr>
        <td><table border="0" cellspacing="5" cellpadding="5">
             <tr>
                <td>
				<?php echo '<a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '" class="button-a"><span>' . Translate('Terug') . '</span></a>'; ?>
                </td>
                <td align="right">
                <input type="submit" value="<?php echo Translate('Verzenden'); ?>" class="formbutton button-a" />
                </td>
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
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>