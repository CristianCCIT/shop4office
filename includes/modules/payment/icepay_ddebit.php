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

class icepay_ddebit extends icepay
{

	var $icon = "incasso.jpg";

	function icepay_ddebit()
	{
		global $order;

		$this->code				= 'icepay_ddebit';
		$this->title			= $this->getTitle();//MODULE_PAYMENT_ICEPAY_DDEBIT_TEXT_TITLE;
		$this->description		= $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>ICEPAY Direct-Debit (Incasso)</b><BR>The main ICEPAY module must be installed (does not have to be active) to use this payment method.<BR>";
		$this->sort_order		= MODULE_PAYMENT_ICEPAY_DDEBIT_SORT_ORDER;
		$this->enabled			= ($this->doAllowedCurrencyCheck('EUR') &&(MODULE_PAYMENT_ICEPAY_DDEBIT_STATUS == 'True') && $this->doAllowedCountryCheck(array(''.MODULE_PAYMENT_ICEPAY_DDEBIT_COUNTRY.'')) ? true : false);
		$this->query_bankinfo	= ((MODULE_PAYMENT_ICEPAY_DDEBIT_FETCH_BANKS == 'True') ? true : false);

		if ((int)MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID > 0)
			$this->order_status = MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID;

		if (is_object($order))
			$this->update_status();
		
		$this->form_action_url = $this->base_url . '?type=ddebit';
	}
	
	function getLangStr($str){
		switch ($str){
			case "title":
				return Translate('Betalen via automatische incasso');
				break;
		}
	}
	
    function process_button() {
		$ic_country = $this->getUserCountry(MODULE_PAYMENT_ICEPAY_DDEBIT_COUNTRY);
		$ic_currency = $this->getUserCurrency(MODULE_PAYMENT_ICEPAY_DDEBIT_CURRENCY);
		$ic_language = $this->getUserLanguage(MODULE_PAYMENT_ICEPAY_DDEBIT_LANGUAGE);
		$ic_amount = $this->getOrderAmount(MODULE_PAYMENT_ICEPAY_DDEBIT_CURRENCY);
		$hidden_fields = parent::process_button(array('ic_country' => $ic_country, 'ic_currency' => $ic_currency, 'ic_language' => $ic_language, 'ic_amount' => $ic_amount)); 
        return 	tep_draw_hidden_field('ic_paymentmethod',		'DDEBIT').
				tep_draw_hidden_field('ic_issuer',          'INCASSO').
				$hidden_fields;
	}
        
	
	function confirmation()
	{
		return false;
	}

	function check()
	{
		if (!isset($this->_check))
		{
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ICEPAY_DDEBIT_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function update_status() {
      global $order;
	  
      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ICEPAY_DDEBIT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ICEPAY_DDEBIT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
	
	function allowedCountriesDDebit()
		{
			return array( 'NL' );
		}

		function allowedLanguagesDDebit()
		{
			return array( 'EN', 'NL', 'DETECT' );
		}
		
	function allowedCurrenciesDDebit()
		{
			return array( 'EUR' );
		}
	
	function install()
	{
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable ICEPAY Direct Debit Module', 'MODULE_PAYMENT_ICEPAY_DDEBIT_STATUS', 'True', 'Do you want to accept Direct Debit payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_ICEPAY_DDEBIT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

			// Regional settings
			$this->default_language = "NL";
			$this->default_currency = "EUR";
			$this->default_country 	= 'NL';
			
			$this->languages_dbstring = $this->db_implode($this->allowedLanguagesDDebit());
			$this->currencies_dbstring = $this->db_implode($this->allowedCurrenciesDDebit());
			$this->countries_dbstring = $this->db_implode($this->allowedCountriesDDebit());
			
			tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Language display settings', 'MODULE_PAYMENT_ICEPAY_DDEBIT_LANGUAGE', '".$this->default_language."', 'Set the language. Default setting is current OSCommerce language.', '6', '1', 'tep_cfg_select_option(array(".$this->languages_dbstring."), ', now())");
			tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Currency', 'MODULE_PAYMENT_ICEPAY_DDEBIT_CURRENCY', '".$this->default_currency."', 'Set the currency. Default setting is current OSCommerce currency.', '6', '1', 'tep_cfg_select_option(array(".$this->currencies_dbstring."), ', now())");
			tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Country', 'MODULE_PAYMENT_ICEPAY_DDEBIT_COUNTRY', '".$this->default_country."', 'Set the country. Default setting is DETECT, using the country of the user.', '6', '1', 'tep_cfg_select_option(array(".$this->countries_dbstring."), ', now())");
			tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_ICEPAY_DDEBIT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '3', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");


	}

	function keys()
	{
		return array
		(
			'MODULE_PAYMENT_ICEPAY_DDEBIT_STATUS',
			'MODULE_PAYMENT_ICEPAY_DDEBIT_SORT_ORDER',
			'MODULE_PAYMENT_ICEPAY_DDEBIT_LANGUAGE',
			'MODULE_PAYMENT_ICEPAY_DDEBIT_CURRENCY',
			'MODULE_PAYMENT_ICEPAY_DDEBIT_COUNTRY',
			'MODULE_PAYMENT_ICEPAY_DDEBIT_ZONE'
		);
	}
}
?>