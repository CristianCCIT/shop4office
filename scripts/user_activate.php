<?php
require('includes/application_top.php');
$user = $_GET['user'];
$check_user_query = tep_db_query("SELECT customers_firstname, customers_email_address, customers_id, status FROM customers WHERE customers_email_address = '" . $user . "'");
if (tep_db_num_rows($check_user_query)>0)
{
	$check_user = tep_db_fetch_array($check_user_query);
	if ($check_user['status']=='1') {
		echo 'Gebruiker '.$user.' werd reeds geactiveerd';
	} else {
		tep_db_query("UPDATE customers SET status = '1' where customers_email_address = '" . $user . "'");
		echo 'Gebruiker '.$user.' is nu geactiveerd';
		$Varlogo = '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '"><img src="'. HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES.'mail/logo.jpg" border="0" /></a> ';
		$Vartable1 = '<table width="100%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">';
		$Vartable2 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#EFEFEF">';
		$Vartable3 = '<table width="100%" border="0" cellpadding="3" cellspacing="3" bgcolor="#ffffff">';
		$VarTitle = '<h1>'.Translate('Account geactiveerd').'</h1>';
		$Vartext2 = ' <b>'.Translate('Beste').' ' . $check_user['customers_firstname'] .' </b><br><br>';
		
		$Vartext2 .= Translate('Vanaf heden kunt u inloggen op onze website').': <strong><a href="'.HTTP_SERVER.DIR_WS_CATALOG.'login.php">'.STORE_NAME.'</a></strong><br /><br />'.Translate('Reageer a.u.b. op deze email als u nog vragen heeft.');
		
		$Varcopyright = Translate('Copyright © 2010');
		$Varmailfooter = Translate('Dit email adres is ingegeven op onze website door u of door &eacute;&eacute;n van onze bezoekers. Als u zich niet ingeschreven hebt op onze website contacteer ons dan via').' <a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>';
		//Check if HTML emails is set to true
		if (EMAIL_USE_HTML == 'true') {	
			//Prepare HTML email
			require(DIR_WS_MODULES . 'email/html_create_account.php');
			$email = $html_email_text;
		} else {		
			//Send text email
			$email = STORE_NAME."\n -==================================- \n".Translate('Vanaf heden kunt u inloggen op onze website').': <strong><a href="'.HTTP_SERVER.DIR_WS_CATALOG.'login.php">'.STORE_NAME.'</a></strong>'.Translate('Reageer a.u.b. op deze email als u nog vragen heeft.');
		}
		tep_mail($check_user['customers_firstname'], $check_user['customers_email_address'], "Account geactiveerd - ".STORE_NAME, $email, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
	}
}
?>

