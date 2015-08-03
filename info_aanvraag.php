<?php
/*
  $Id: contact_us.php,v 1.42 2003/06/12 12:17:07 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  $error = false;
  if (isset($_GET['action']) && ($_GET['action'] == 'send')) {
	$name = tep_db_prepare_input($_POST['name']);
	$email_address = tep_db_prepare_input($_POST['email']);
	$enquiry = tep_db_prepare_input($_POST['enquiry']);
	$onderwerp = tep_db_prepare_input($_POST['reason']);
	
	$cmessage = '<table cellspacing="0" cellpadding="0" border="0">';
	$cmessage .= '<tr><td valign="top">Onderwerp: &nbsp;</td><td valign="top">'.$onderwerp.'</td></tr>'."\n";
	$cmessage .= '<tr><td valign="top">Naam: &nbsp;</td><td valign="top">'.$name.'</td></tr>'."\n";
	$cmessage .= '<tr><td valign="top">Bericht: &nbsp;</td><td valign="top">'.$enquiry.'</td></tr>'."\n";
	$cmessage .= '</table>';
	// $cmessage = 'Telefoon : ' . $phone . " \n" . 'Adres : ' . $adres . "\n" . 'Vraag : ' . "\n " . $enquiry . "\n";
	$emailsubject = tep_db_prepare_input($_POST['reason']) . ' ' . Translate('Bericht voor').' '.STORE_NAME;
	if (tep_validate_email($email_address)) {
		
		$Varlogo = '' ;
		$Vartable1 = ''  ;
		$Vartable2 = ''  ;
		$Vartextmail = $cmessage;
		$Vartrcolor = '' ;
		$Varmailfooter = '';
		require(DIR_WS_MODULES . 'email/html_standard.php');
		$email_text = $html_email_text ;
		if (EMAIL_USE_HTML == 'true') {
		$email_text;
		} 
		else
		{
		$email_text = $cmessage;
		}
	  tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $emailsubject, $email_text, $name, $email_address);
	  tep_redirect(tep_href_link(FILENAME_CONTACT_US, 'action=success'));
	} else {
	  $error = true;
	
	  $messageStack->add('contact', Translate('Geef a.u.b. een bestaand e-mail adres!'));
	}
  }

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_CONTACT_US));
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
    <td width="100%" valign="top">
		<?php echo tep_draw_form('contact_us', tep_href_link(FILENAME_CONTACT_US, 'action=send'), 'POST', ' id="contact_us_form"'); ?>
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td><h1><?php echo Translate('Informatie aanvraag');?></h1></td>
			</tr>
<?php
  if ($messageStack->size('contact') > 0) {
?>
			<tr>
				<td><?php echo $messageStack->output('contact'); ?></td>
			</tr>
			<tr>
				<td height="10"></td> 
			</tr>
<?php
  }

  if (isset($_GET['action']) && ($_GET['action'] == 'success')) {
?>
      <tr>
        <td><?php echo Translate('Uw vraag/opmerking is succesvol naar ' . STORE_NAME . ' verzonden. U krijgt zo spoedig mogelijk antwoord.'); ?></td>
      </tr>
      <tr>
        <td height="10"></td> 
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2">
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10" height="10"></td>
                <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '" class="blue_button">'.Translate('Ga verder').'</a>'; ?></td>
                <td width="10" height="10"></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
  } else {
?>
		<tr>
			<td>
				<?php echo Translate('Gelieve uw gegevens zo nauwkeurig mogelijk in te vullen.<br /><br />Selecteer voor welk onderdeel u verdere informatie wenst, en geef eventueel op in welke sector u actief bent, zo kunnen we u specifiekere documentatie bezorgen.');?>
			</td>
		</tr>
		<tr>
			<td height="10"></td>
		</tr>
		<tr>
        	<td style="vertical-align: top;">
				<table border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td><?php echo Translate('Firma');?> </td>
						<td><?php echo Translate('Contactpersoon');?> </td>
					</tr>
					<tr>
						<td><?php echo tep_draw_input_field('company', '', 'size="45" class="input_field" style="width: 300px;"'); ?></td>
						<td><?php echo tep_draw_input_field('contact_person', '', 'size="45" class="input_field" style="width: 300px;"'); ?></td>
					</tr>
					<tr>
						<td colspan="2" height="10"></td>
					</tr>
					<tr>
						<td><?php echo Translate('E-mailadres');?> </td>
						<td><?php echo Translate('Telefoon nummer');?> </td>
					</tr>
					<tr>
						<td><?php echo tep_draw_input_field('email', '', 'size="45" class="input_field" style="width: 300px;"'); ?></td>
						<td><?php echo tep_draw_input_field('phone', '', 'size="45" class="input_field" style="width: 300px;"'); ?></td>
					</tr>
					<tr>
						<td height="10"></td>
					</tr>
					<tr>
						<td valign="top" height="15"><?php echo Translate('Sector');?> </td>
						<td valign="top" rowspan="2">
							<table cellspacing="0" cellpadding="0" border="0" width="100%">
								<tr>
									<td valign="top" style="width: 70px;">
										<?php echo Translate('Onderdeel');?>
									</td>
									<td>
										<ul class="ia_list">
											<li><input type="checkbox" name="onderdeel" value="boekhouding" /> <?php echo Translate('Boekhouding');?></li>
											<li><input type="checkbox" name="onderdeel" value="facturatie" /> <?php echo Translate('Facturatie');?></li>
											<li><input type="checkbox" name="onderdeel" value="kassabeheer" /> <?php echo Translate('Kassabeheer');?></li>
											<li><input type="checkbox" name="onderdeel" value="website" /> <?php echo Translate('Website/webshop');?></li>
										</ul>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td valign="top"><?php echo tep_draw_input_field('sector', '', 'size="45" class="input_field" style="width: 300px;"'); ?></td>
					</tr>
					<tr>
						<td height="10"></td>
					</tr>
					<tr>
						<td colspan="2" height="10"></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo Translate('Bericht:');?></td>
					</tr>
					<tr>
						<td style="padding-right: 5px;" colspan="2"><?php echo tep_draw_textarea_field('enquiry', 'soft', 40, 5, '', ' class="input_field" style="width: 645px;"'); ?></td>
					</tr>			
					<tr>
						<td height="5"></td> 
					</tr>
					<tr>
						<td align="right" colspan="2"><input type="submit" class="blue_button" value="<?php echo Translate('Verzenden');?>" /></td>
					</tr>
				</table>
			</td>
      	</tr>
      	<tr>
        	<td height="10"></td> 
      	</tr>
<?php
  }
?>
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
