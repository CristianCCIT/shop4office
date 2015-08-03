<?php
require('includes/application_top.php');
if (!tep_session_is_registered('customer_id')) {
	$navigation->set_snapshot();
	tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}
//Begin PHPlist Newsletter add-on
//get user specifics from osC
$current_user_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'"); //current osc-user info
$current_user = tep_db_fetch_array($current_user_query);
$email_address = $current_user['customers_email_address'];
$firstname = $current_user['customers_firstname'];
$lastname = $current_user['customers_lastname'];
$subscription_text = '';
if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
	global $all_lists;
	$lists = PHPLIST_LISTNUMBERS;
	$lists = explode(';', $lists);
	foreach ($lists as $key=>$list) {
		if (isset($_POST['newsletters_'.$list])) {
			$subscription = put_user_in_list($list, 'subscribe', $email_address, $firstname.' '.$lastname);
		} else {
			$subscription = put_user_in_list($list, 'unsubscribe', $email_address, $firstname.' '.$lastname);
		}
		if ($subscription == '1') {
			$subscription_text .= sprintf(Translate('U bent nu ingeschreven op %s.'), getListName($list)).'<br />';
		} else if ($subscription == '2') {
			$subscription_text .= sprintf(Translate('U bent reeds ingeschreven op %s.'), getListName($list)).'<br />';
		} else if ($subscription == '3') {
			$subscription_text .= sprintf(Translate('U bent nu uitgeschreven van %s.'), getListName($list)).'<br />';
		} else {
			$subscription_text .= sprintf(Translate('Dit email adres is niet ingeschreven op %s.'), getListName($list)).'<br />';
		}
	}
	$messageStack->add_session('account', $subscription_text, 'success');
	tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}
$breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL'));
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
		<td width="<?php echo BOX_WIDTH; ?>" valign="top">
			<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
				<!-- left_navigation //-->
				<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
				<!-- left_navigation_eof //-->
			</table>
		</td>
		<!-- body_text //-->
		<td width="100%" valign="top">
		<?php echo tep_draw_form('account_newsletter', tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL')) . tep_draw_hidden_field('action', 'process'); ?>
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td><h1><?php echo Translate('Nieuwsbrief'); ?></h1></td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				<td>
					<table border="0" width="100%" cellspacing="0" cellpadding="2">
						<?php
						if (ACCOUNT_NEWSLETTER == 'true') {
						$lists = PHPLIST_LISTNUMBERS;
						$lists = explode(';', $lists);
						foreach ($lists as $key=>$list) {
							$newsletter = check_if_subscribed($email_address, $list);
							$newsletter = explode('|', $newsletter);
							echo '<tr><td width="20">'.tep_draw_checkbox_field('newsletters_'.$list, '1', (($newsletter[1] == '1') ? true : false)).'</td><td>'.$newsletter[0].'</td></tr>';
						}
						tep_db_list_close();
						tep_db_connect();
						}
						?>
					</table>
				</td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				<td>
					<?php echo Translate('Wens je in de toekomst op de hoogte te zijn van onze nieuwtjes en niet te missen aanbiedingen? Vink dan de gewenste nieuwsbrief aan!');?>
				</td>
			</tr>
			<tr>
				<td height="10"></td>
			</tr>
			<tr>
				<td>
					<table border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td><?php echo '<a href="'.tep_href_link(FILENAME_ACCOUNT, '', 'SSL').'" class="button-a">'.Translate('Terug').'</a>'; ?></td>
							<td style="padding-left:5px;"><input type="submit" value="<?php echo Translate('Pas aan');?>" class="button-a" /></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		</form>
		</td>
		<!-- body_text_eof //-->
		<td width="<?php echo BOX_WIDTH; ?>" valign="top">
			<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
				<!-- right_navigation //-->
				<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
				<!-- right_navigation_eof //-->
			</table>
		</td>
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