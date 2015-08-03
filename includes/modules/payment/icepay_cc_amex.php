<?php
/*
#################################################################
#                                                             	#
#	The property of ICEPAY www.icepay.eu                      	#
#                                                             	#
#   The merchant is entitled to change de ICEPAY plug-in code,	#
#	any changes will be at merchant's own risk.					#
#	Requesting ICEPAY support for a modified plug-in will be	#
#	charged in accordance with the standard ICEPAY tariffs.		#
#                                                             	#
#################################################################

	osCommerce, Open Source E-Commerce Solutions
	http://www.oscommerce.com
	Copyright (c) 2008 osCommerce
	Released under the GNU General Public License
	
*/

require( "icepay.php" );

class icepay_cc_amex extends icepay
{

	var $icon = "creditcard.jpg";

	function icepay_cc_amex()
	{
		global $order;

		$this->code				= 'icepay_cc_amex';
		$this->title			= $this->getTitle();//MODULE_PAYMENT_ICEPAY_CC_TEXT_TITLE;
		$this->description		= $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>ICEPAY Creditcards</b><BR>The main ICEPAY module must be installed (does not have to be active) to use this payment method.<BR>";
		$this->sort_order		= MODULE_PAYMENT_ICEPAY_CC_AMEX_SORT_ORDER;
		$this->enabled			= ((MODULE_PAYMENT_ICEPAY_CC_AMEX_STATUS == 'True') ? true : false);

		if ((int)MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID > 0)
			$this->order_status = MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID;

		if (is_object($order))
			$this->update_status();
		
		$this->form_action_url = $this->base_url . '?type=creditcard';
	}
	
	function getLangStr($str){
		switch ($str){
			case "title":
				return Translate('Betalen met American Express');
				break;
		}
	}
	
	function process_button() {
		$hidden_fields = parent::process_button(); 
        return 	tep_draw_hidden_field('ic_paymentmethod',		'CREDITCARD').
				tep_draw_hidden_field('ic_issuer',		'AMEX').
				$hidden_fields;
	}
	
	function allowedCurrenciesCCard()
		{
			return array( 'EUR', 'GBP', 'USD', 'DETECT' );
		}
	
	function allowedLanguagesCCard()
		{
			return array( 'EN', 'NL', 'DE', 'DETECT' );
		}
	
	function check()
	{
		if (!isset($this->_check))
		{
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ICEPAY_CC_AMEX_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		}
		return $this->_check;
	}
	
	function update_status() {
      global $order;
	  
      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ICEPAY_CC_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ICEPAY_CC_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }

    }

	function install()
	{
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable ICEPAY Credit Card Module', 'MODULE_PAYMENT_ICEPAY_CC_STATUS', 'True', 'Do you want to accept Credit Card payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_ICEPAY_CC_SORT_AMEX_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

		// Regional settings
		$this->default_language = $this->getDefaultLanguage();
		$this->default_currency = DEFAULT_CURRENCY;
		$this->default_country 	= '00';
		
		$this->languages_dbstring = $this->db_implode($this->allowedLanguagesCCard());
		$this->currencies_dbstring = $this->db_implode($this->allowedCurrenciesCCard());

		
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Language display settings', 'MODULE_PAYMENT_ICEPAY_CC_LANGUAGE', '".$this->default_language."', 'Set the language. Default setting is current OSCommerce language.', '6', '1', 'tep_cfg_select_option(array(".$this->languages_dbstring."), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Currency', 'MODULE_PAYMENT_ICEPAY_CC_CURRENCY', '".$this->default_currency."', 'Set the currency. Default setting is current OSCommerce currency.', '6', '1', 'tep_cfg_select_option(array(".$this->currencies_dbstring."), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_ICEPAY_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '3', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");

	}

	function keys()
	{
		return array
		(
			'MODULE_PAYMENT_ICEPAY_CC_AMEX_STATUS',
			'MODULE_PAYMENT_ICEPAY_CC_AMEX_SORT_ORDER',
			'MODULE_PAYMENT_ICEPAY_CC_LANGUAGE',
			'MODULE_PAYMENT_ICEPAY_CC_CURRENCY',
			'MODULE_PAYMENT_ICEPAY_CC_COUNTRY',
			'MODULE_PAYMENT_ICEPAY_CC_ZONE'
		);
	}
}
?>