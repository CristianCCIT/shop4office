<?php
  require_once('includes/application_top.php');
	if (CREATE_ACCOUNT_MODE!='Direct access') {
	  $breadcrumb->add(Translate('Account aanvragen'));
	} else {
	  $breadcrumb->add(Translate('Account aanmaken'));
	}
  $breadcrumb->add(Translate('Success'));

  if (sizeof($navigation->snapshot) > 0) {
    $origin_href = tep_href_link($navigation->snapshot['page'], tep_array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
    $navigation->clear_snapshot();
  } else {
    $origin_href = tep_href_link(FILENAME_DEFAULT);
  }
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
                <td>
                <?php if ((CREATE_ACCOUNT_MODE=='Request account') || (CREATE_ACCOUNT_MODE=='Moderated access')) { ?>
                	<h1><?php echo Translate('Account succesvol aangevraagd'); ?></h1>
                <?php } else { ?>
                	<h1><?php echo Translate('Account succesvol aangemaakt'); ?></h1>
                <?php } ?>
                </td>
              </tr>
              <tr>
                <td height="10"></td>
              </tr>
              <tr>
                <td class="main">
                <?php if ((CREATE_ACCOUNT_MODE=='Request account') || (CREATE_ACCOUNT_MODE=='Moderated access')) { ?>
					<?php echo Translate("Uw nieuwe account is succesvol aangevraagd. <br />Een beheerder zal uw gegevens nazien. Wij contacteren u zo snel mogelijk."); ?>
                <?php } else { ?>
					<?php echo sprintf(Translate("Gefeliciteerd. Uw nieuwe account is succesvol aangemaakt. <br />Wij wensen u veel plezier met winkelen. Voor vragen kunt u altijd <a href='%s'>hier</a> contact met ons opnemen."), tep_href_link(FILENAME_CONTACT_US, tep_get_all_get_params(), 'SSL')); ?>
                <?php } ?>
                </td>
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
                <td align="right"><a href="<?php echo $origin_href;?>" class="button-a"><span><?php echo Translate('Ga verder');?></span></a></td>
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
