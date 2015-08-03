<?php
  require_once('includes/application_top.php');
  if ($session_started == false) {
    tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
  }
  $error = false;
  if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
    $email_address = tep_db_prepare_input($_POST['email_address']);
    $password = tep_db_prepare_input($_POST['password']);
    $check_customer_query = tep_db_query("select customers_id, abo_id, customers_firstname, customers_password, customers_email_address, customers_username, customers_default_address_id, status, customers_group from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' OR customers_username = '".tep_db_input($email_address)."'");
    if (!tep_db_num_rows($check_customer_query)) {
      $error = true;
    } else {
      $check_customer = tep_db_fetch_array($check_customer_query);
      if (!tep_validate_password($password, $check_customer['customers_password'])) {
        $error = true;
      } else {
		if ($check_customer['status']=='0') {
			$active_error = true;
		} else {
			if (SESSION_RECREATE == 'True') {
			  tep_session_recreate();
			}
			$check_country_query = tep_db_query("select entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$check_customer['customers_id'] . "' and address_book_id = '" . (int)$check_customer['customers_default_address_id'] . "'");
			$check_country = tep_db_fetch_array($check_country_query);
			$customer_id = $check_customer['customers_id'];
			$abo_id = $check_customer['abo_id'];
			$customer_default_address_id = $check_customer['customers_default_address_id'];
			$customer_first_name = $check_customer['customers_firstname'];
			$customer_country_id = $check_country['entry_country_id'];
			$customer_zone_id = $check_country['entry_zone_id'];
			$customer_group = $check_customer['customers_group'];
			$customers_email_address = $check_customer['customers_email_address'];
			$customers_username = $check_customer['customers_username'];
			tep_session_register('customer_id');
			tep_session_register('abo_id');
			tep_session_register('customer_default_address_id');
			tep_session_register('customer_first_name');
			tep_session_register('customer_country_id');
			tep_session_register('customer_zone_id');
			tep_session_register('customer_group');
			tep_session_register('customers_email_address');
			tep_session_register('customers_username');
			/*autologin*/
			$cookie_url_array = parse_url((ENABLE_SSL == true ? HTTPS_SERVER : HTTP_SERVER) . substr(DIR_WS_CATALOG, 0, -1));
			$cookie_path = $cookie_url_array['path'];	
			if ((ALLOW_AUTOLOGON == 'false') || ($_POST['remember_me'] == '')) {
				setcookie("email_address", "", time() - 3600, $cookie_path);   // Delete email_address cookie
				setcookie("password", "", time() - 3600, $cookie_path);	       // Delete password cookie
			} else {
				setcookie('email_address', $email_address, time()+ (365 * 24 * 3600), $cookie_path, '', ((getenv('HTTPS') == 'on') ? 1 : 0));
				setcookie('password', $check_customer['customers_password'], time()+ (365 * 24 * 3600), $cookie_path, '', ((getenv('HTTPS') == 'on') ? 1 : 0));
			}
			/*autologin*/
			tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");
			$cart->restore_contents();
			
			/*FORUM*/
			if ((FORUM_ACTIVE=='true') && (FORUM_CROSS_LOGIN=='true')) {
				$user->session_begin();
				$auth->acl($user->data);
				
				$get_forum_username_query = tep_db_query("SELECT username_clean FROM ".FORUM_DB_DATABASE.".users WHERE user_email = '".$_POST['email_address']."'");
				$get_forum_username = tep_db_fetch_array($get_forum_username_query);
				
				if ($_POST['remember_me']=='on') {
					$remember = 'true';
				} else {
					$remember = 'false';	
				}
				$auth->login($get_forum_username['username_clean'], $_POST['password'], $remember, 1, 0);
			}
			/*FORUM*/
			// navigation history
			$extra_link_data = '';
			if(isset($_GET['language'])) {
				$extra_link_data = 'language='.$_GET['language'];
			}

			if (sizeof($navigation->snapshot) > 0 && !strstr($navigation->snapshot['page'], 'login.php')) {
			  $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())).'&'.$extra_link_data, $navigation->snapshot['mode']);
			  tep_redirect($origin_href);
			} else if (sizeof($navigation->path) > 0 && !strstr($navigation->path[$last]['page'], 'login.php')) {
				$last = sizeof($navigation->path) - 1;
				$origin_href = tep_href_link($navigation->path[$last]['page'], tep_array_to_string($navigation->path[$last]['get'], array(tep_session_name())).'&'.$extra_link_data, $navigation->path[$last]['mode']);
			        tep_redirect($origin_href);
			} else {
				if ( (strstr($_SERVER['HTTP_REFERER'], 'logoff.php' )) ) {
					tep_redirect(tep_href_link(FILENAME_DEFAULT, $extra_link_data));
				} else { 
					tep_redirect(tep_href_link(FILENAME_DEFAULT, $extra_link_data));
				}
			}
		}
      }
   }
}
if ($error == true) {
    $messageStack->add('login', Translate('Fout: er kon niet ingelogd worden met het ingegeven e-mailadres en wachtwoord. Gelieve opnieuw te proberen'));
}
  if ($active_error == true) {
    $messageStack->add('login', Translate('Uw account werd nog niet geactiveerd.'));
  }
$breadcrumb->add(Translate('Inloggen'), tep_href_link(FILENAME_LOGIN, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
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
    <td width="100%" valign="top"><?php echo tep_draw_form('login', tep_href_link(FILENAME_LOGIN, 'action=process', 'SSL')); ?>
    <?php
	if ($messageStack->size('login') > 0)
	{
	echo $messageStack->output('login');
	}
	?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
	<tr>
	   <td width="49%" height="100%" valign="top">
			<table border="0" width="100%" height="100%" cellspacing="5" cellpadding="5" class="loginpage_boxes">
				<tr>
					<td><h2><?php echo Translate('Nieuwe bezoeker'); ?></h2></td>
				</tr>
				<tr>
					<td valign="top">
						<?php echo Translate('Ik ben een nieuwe bezoeker') . '.<br /><br />' . Translate('Door een account aan te maken kunt u sneller winkelen, up to date blijven omtrent uw order status en bijhouden wat u voordien al besteld heeft.'); ?>
					</td>
				</tr>
				<tr>
					<td height="10"></td>
				</tr>
				<tr>
					<td align="right"><a href="<?php echo tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'); ?>" class="button-a"><span><?php echo Translate('Account aanmaken'); ?></span></a></td>
				</tr>
			</table>
		<td width="2%"></td>
		<td width="49%" height="100%" valign="top">
			<table border="0" width="100%" height="100%" cellspacing="5" cellpadding="5" class="loginpage_boxes">
				<tr>
					<td colspan="2"><h2><?php echo Translate('Terugkerende klant'); ?></h2></td>
				</tr>
				<tr>
					<td colspan="2" valign="top"><?php echo Translate('Ik ben een terugkerende klant'); ?>.</td>
				</tr>
				<tr>
					<td colspan="2" height="10"></td>
				</tr>
				<tr>
					<td valign="top"><label for="email_address"><?php echo Translate('E-mailadres'); ?></label>: </td>
					<td valign="top"><?php echo tep_draw_input_field('email_address', '', 'id="email_address"'); ?></td>
				</tr>
				<tr>
					<td valign="top"><label for="password"><?php echo Translate('Wachtwoord'); ?></label>: </td>
					<td valign="top"><?php echo tep_draw_password_field('password', '', 'id="password"'); ?><br /><?php echo '<a href="' . tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . Translate('Wachtwoord vergeten?') . '</a>'; ?></td>
				</tr>
				<tr>
					<td colspan="2" height="10"></td>
				</tr>
				<tr>
					<td colspan="2" align="right">
						<?php 
						/*autologon*/
						if((ALLOW_AUTOLOGON != 'false') && ($cookies_on == true)) {
							echo tep_draw_checkbox_field('remember_me','on', (($password == '') ? false : true)) . '&nbsp;' . Translate('Automatisch inloggen bij elk bezoek');
						}
						/*autologin*/
						?>
                     </td>
				</tr>
				<tr>
					<td colspan="2" height="10"></td>
				</tr>
				<tr>
					<td align="left"><input type="submit" value="<?php echo Translate('Inloggen'); ?>" class="formbutton button-a" /></td>
                    <td align="left"></td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form></td>
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