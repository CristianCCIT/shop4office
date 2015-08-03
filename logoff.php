<?php
require('includes/application_top.php');
/*autologin*/
$cookie_url_array = parse_url((ENABLE_SSL == true ? HTTPS_SERVER : HTTP_SERVER) . substr(DIR_WS_CATALOG, 0, -1));
$cookie_path = $cookie_url_array['path'];	
tep_session_unregister('autologon_executed'); 
tep_session_unregister('autologon_link'); 
/*autologin*/

/*FORUM*/
if ((FORUM_ACTIVE=='true') && (FORUM_CROSS_LOGIN=='true')) {
	$user->session_kill();
	$user->session_begin();
}
/*FORUM*/

$breadcrumb->add(Translate('Uitloggen'));
setcookie('temp_orders_id', '', time() - 3600, '/');
tep_session_unregister('customer_id');
tep_session_unregister('customer_default_address_id');
tep_session_unregister('customer_first_name');
tep_session_unregister('customer_country_id');
tep_session_unregister('customer_zone_id');
tep_session_unregister('comments');
// Start - CREDIT CLASS Gift Voucher Contribution
tep_session_unregister('gv_id');
tep_session_unregister('cot_gv');
tep_session_unregister('cc_id');
// End - CREDIT CLASS Gift Voucher Contribution
tep_session_unregister('customer_postcode');
tep_session_unregister('customer_vendors_id');
tep_session_unregister('vendors_affiliate_id');
tep_session_unregister('abo_id');
tep_session_unregister('customer_group');
tep_session_unregister('customers_email_address');
tep_session_unregister('customers_username');
$cart->reset();
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td><h1><?php echo Translate('Uitloggen'); ?></h1></td>
              </tr>
              <tr>
                <td height="10"></td>
              </tr>
              <tr>
                <td class="main"><?php echo Translate('U bent nu uitgelogd.<br /><br />Uw winkelwagen is bewaard voor de volgende keer.'); ?></td>
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
                <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '" class="button-a"><span>' . Translate('Ga verder') . '</span></a>'; ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
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
