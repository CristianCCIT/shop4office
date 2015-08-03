<?php
/*
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License

  Autologin added by DJ Downey http://www.liquidgfx.com

*/

// WebMakers.com Added: Do not show if on login or create account
if (!tep_session_is_registered('customer_id')){
	echo '<a href="'.tep_href_link(FILENAME_LOGIN).'" title="'.Translate('Login').' - '.STORE_NAME.'">'.Translate('Login').'</a> (Nieuwe klant? <a href="'.tep_href_link(FILENAME_CREATE_ACCOUNT).'" title="'.Translate('Registreer').' - '.STORE_NAME.'">'.Translate('Registreer').'</a>)';
} else {
	echo '<a href="'.tep_href_link(FILENAME_ACCOUNT).'" title="'.Translate('Mijn account').' - '.STORE_NAME.'">'.Translate('Mijn account').'</a> <span class="split"><span>|</span></span> <a href="'.tep_href_link(FILENAME_LOGOFF).'" title="'.Translate('Log uit').' - '.STORE_NAME.'">'.Translate('Log uit').'</a>';
}
?>
