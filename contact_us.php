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
	
	$cmessage = '<table cellspacing="0" cellpadding="0" border="0">';
	$cmessage .= '<tr><td valign="top">'.Translate('Naam').': &nbsp;</td><td valign="top">'.$name.'</td></tr>'."\n";
	$cmessage .= '<tr><td valign="top">'.Translate('Bericht').': &nbsp;</td><td valign="top">'.$enquiry.'</td></tr>'."\n";
	$cmessage .= '</table>';
	// $cmessage = 'Telefoon : ' . $phone . " \n" . 'Adres : ' . $adres . "\n" . 'Vraag : ' . "\n " . $enquiry . "\n";
	$emailsubject = Translate('Bericht voor').' '.STORE_NAME;
	if (tep_validate_email($email_address)) {
		/*CAPTCHA*/
		//die('session '.$_SESSION['captcha_code'].' post'.$_POST['security_code']);
		if (($_SESSION['captcha_code'] != $_POST['security_code'] || (empty($_SESSION['captcha_code']))) && ((USE_CAPTCHA=='true') && (USE_CAPTCHA_CONTACT=='true'))) {
			$error = true;
			$messageStack->add('contact', Translate('Beveiligingscode werd niet juist overgenomen.'));
		} else {
		/*CAPTCHA*/
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
			} else {
				$email_text = $cmessage;
			}
			tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $emailsubject, $email_text, $name, $email_address);
			if (GA_EVENT_TRACKING=='true') {
			?>
            <script type="text/javascript">
			_gaq.push(['_trackEvent', 'Contact', 'submit', 'contact_sent']);
			</script>
            <?php
			}
			tep_redirect(tep_href_link(FILENAME_CONTACT_US, 'action=success'));
		/*CAPTCHA*/
		}
		/*CAPTCHA*/
	} else {
	  $error = true;
	
	  $messageStack->add('contact', Translate('Geef a.u.b. een bestaand e-mail adres!'));
	}
  }
  
if (tep_session_is_registered('customer_id')) {
	$account_query = tep_db_query("select customers_gender, customers_firstname, customers_dob, customers_email_address, customers_telephone, customers_fax from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
  	$account = tep_db_fetch_array($account_query);
} else {
	$account = '';
}
/*CAPTCHA*/
if ((USE_CAPTCHA=='true') && (USE_CAPTCHA_CONTACT=='true')) {
	$captcha_code = tep_captcha_image();
	if ( ! tep_session_is_registered('captcha_code') ) {
		tep_session_register('captcha_code');
	}
}
/*CAPTCHA*/
if (GA_EVENT_TRACKING=='true') {
	$contact_tracking = array(
			'contact_name' => ' onchange="_gaq.push([\'_trackEvent\', \'Contact\', \'fill-out\', \'contact_name\']);"',
			'contact_email_address' => ' onchange="_gaq.push([\'_trackEvent\', \'Contact\', \'fill-out\', \'contact_email_address\']);"',
			'contact_message' => ' onchange="_gaq.push([\'_trackEvent\', \'Contact\', \'fill-out\', \'contact_message\']);"',
			'contact_captcha' => ' onchange="_gaq.push([\'_trackEvent\', \'Contact\', \'fill-out\', \'contact_captcha\']);"',
			'contact_submitted' => ' onsubmit="_gaq.push([\'_trackEvent\', \'Contact\', \'submit\', \'contact_submitted\']);"'
	);
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
		<?php echo tep_draw_form('contact_us', tep_href_link(FILENAME_CONTACT_US, 'action=send'), 'POST', ' id="contact_us_form"'.$contact_tracking['contact_submitted']); ?>
		<script type="text/javascript" src="includes/js/form_validation.js"></script>
		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="2"><h1><?php echo Translate('Contact');?></h1></td>
			</tr>
			<tr>
				<td height="10"></td> 
			</tr>
<?php
  if ($messageStack->size('contact') > 0) {
?>
			<tr>
				<td colspan="2"><?php echo $messageStack->output('contact'); ?></td>
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
                <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '" class="button-a">'.Translate('Ga verder').'</a>'; ?></td>
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
			<td style="width: 250px; vertical-align: top;">
				<table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="vertical-align: middle">
                        	<table cellspacing="5" cellpadding="5" border="0">
								<tr>
									<td colspan="2">
										<?php echo tep_image(DIR_WS_IMAGES.'gegevens/'.STORE_IMAGE);?>
									</td>
								</tr>
                            	<tr>
                                	<td style="vertical-align: top"><b><?php echo Translate('Adres');?>:</b>
                                    <td>
                                    	<p>
			                            	<b><?php echo STORE_NAME; ?></b><br />
											<?php echo STORE_STREET_ADDRESS; ?><br />
											<?php echo STORE_POSTCODE; ?> <?php echo STORE_CITY; ?><br />
										</p>
                                    </td>
                                </tr>
                                <tr>
                                	<td colspan="2" style="height: 10px;"></td>
                                </tr>
                            	<tr>
                                	<td><b><?php echo Translate('E-mail');?>:</b></td>
                                    <td><a href="mailto:<?php echo STORE_OWNER_EMAIL_ADDRESS;?>"><?php echo STORE_OWNER_EMAIL_ADDRESS;?></a></td>
                                </tr>
                                <tr>
                                	<td colspan="2" style="height: 10px;"></td>
                                </tr>
								<?php
								if (STORE_TELEPHONE != '') {
								?>
                                <tr>
                                	<td><b><?php echo Translate('Telefoon');?>:</b></td>
                                    <td><?php echo STORE_TELEPHONE; ?></td>
                                </tr>
                                <tr>
                                	<td colspan="2" style="height: 10px;"></td>
                                </tr>
								<?php
								}
								if (STORE_BTW != '') {
								?>
                                <tr>
                                	<td style="vertical-align: top;"><b><?php echo Translate('Gegevens');?>:</b></td>
                                    <td>
                                    	<p>
                                        	<?php echo Translate('BTW');?> : <?=STORE_BTW?><br />
              		                      	<?php
											if (STORE_RPR != '') {
											?>
											<?php echo Translate('RPR');?> <?=STORE_RPR?><br />
											<?php
											}
											?>
                                        </p>
                                    </td>
                               	</tr>
								<?php
								}
								?>
                            </table>
                        </td>
					</tr>
				</table>
			</td>
        	<td style="vertical-align: top;" align="right">
				<table border="0" cellspacing="5" cellpadding="2">
					<tr>
						<td><?php echo Translate('Naam');?> </td>
					</tr>
					<tr>
						<td><?php echo tep_draw_input_field('name', $account['customers_firstname'], 'size="45" class="inputbox" style="width: 300px;"'.$contact_tracking['contact_name']); ?></td>
					</tr>	
					<tr>
						<td><?php echo Translate('E-mailadres');?> </td>
					</tr>
					<tr>
						<td><?php echo tep_draw_input_field('email', $account['customers_email_address'], 'size="45" class="inputbox" style="width: 300px;" condition="email_required"'.$contact_tracking['contact_email_address']); ?></td>
					</tr>	
					<tr>
						<td><?php echo Translate('Bericht');?>:</td>
					</tr>
					<tr>
						<td style="padding-right: 5px;"><?php echo tep_draw_textarea_field('enquiry', 'soft', 40, 5, '', ' class="inputbox" style="width: 300px;height:150px;" condition="1"'.$contact_tracking['contact_message']); ?></td>
					</tr>
                    <?php 
					/*CAPTCHA*/
					if ((USE_CAPTCHA=='true') && (USE_CAPTCHA_CONTACT=='true')) {
					?>
                        <tr>
                            <td style="padding-right: 5px;">
                                <table border="0" cellspacing="0" cellpadding="0" style="float:right;">
                                  <tr>
                                    <td valign="top" align="right"><label for="security_code"><?php echo Translate('Code overnemen'); ?></label><br /><input id="security_code" name="security_code" type="text" class="inputbox" condition="1" style="float:right;width:100px;"<?php echo $contact_tracking['contact_captcha']; ?> /></td>
                                    <td width="7"></td>
                                    <td valign="top"><img src="<?php echo DIR_WS_HTTP_CATALOG.CAPTCHA_IMAGE; ?>" /></td>
                                  </tr>
                                </table>
                            </td>
                        </tr>
					<?php
					}
					/*CAPTCHA*/
					?>
					<tr>
						<td height="5"></td> 
					</tr>
					<tr>
						<td align="right"><input type="submit" class="formbutton button-a" value="<?php echo Translate('Verzenden');?>" /></td>
					</tr>
				</table>
			</td>
      	</tr>
      	<tr>
        	<td height="10"></td> 
      	</tr>
		<tr>
			<td colspan="2" style="text-align: right;">
				<div style="position: relative; width: 570px; height: 450px; float: right;">
                    <iframe style="margin-left: auto; margin-right: auto; border: 1px solid #4e4e4e;" width="568" height="448" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=<? echo $languages_code;?>&amp;geocode=&amp;q=<? echo str_replace(' ', '+', STORE_STREET_ADDRESS);?>+<?=STORE_POSTCODE?>+<?=STORE_CITY?>&amp;z=15&amp;iwloc=A&amp;output=embed"></iframe>
				</div>
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
