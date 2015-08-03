<?php
/*
#################################################################
#                                                             	#
#	The property of ICEPAY www.icepay.eu                      	  #
#                                                             	#
#   The merchant is entitled to change de ICEPAY plug-in code,	#
#	any changes will be at merchant's own risk.					          #
#	Requesting ICEPAY support for a modified plug-in will be			#
#	charged in accordance with the standard ICEPAY tariffs.				#
#                                                             	#
#################################################################

	osCommerce, Open Source E-Commerce Solutions
	http://www.oscommerce.com
	Copyright (c) 2008 osCommerce
	Released under the GNU General Public License
	
*/
require( "icepay.php" );

class icepay_ebanking extends icepay
{
	var $query_bankinfo;
	var $icon = "directebanking.jpg";
	
	function icepay_ebanking()
	{
		global $order;

		$this->code				= 'icepay_ebanking';
		$this->title			= $this->getTitle();//MODULE_PAYMENT_ICEPAY_EBANKING_TEXT_TITLE;
		$this->description		= $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>ICEPAY Direct E-Banking</b><BR>The main ICEPAY module must be installed (does not have to be active) to use this payment method.<BR>";
		$this->sort_order		= MODULE_PAYMENT_ICEPAY_EBANKING_SORT_ORDER;
		$this->enabled			= ($this->checkActivation(MODULE_PAYMENT_ICEPAY_EBANKING_CURRENCY) && ((MODULE_PAYMENT_ICEPAY_EBANKING_STATUS == 'True') && $this->doAllowedCountryCheck($this->allowedCountriesEBank()) ? true : false));
		$this->query_bankinfo	= ((MODULE_PAYMENT_ICEPAY_EBANKING_FETCH_BANKS == 'True') ? true : false);

		if ((int)MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID > 0)
			$this->order_status = MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID;

		if (is_object($order))
			$this->update_status();
		
                $this->form_action_url = $this->base_url . '?type=directebank';
	}
	
	function getLangStr($str){
		switch ($str){
			case "title":
				return Translate('Betaling via Direct E-Banking');
				break;
		}
	}
	
	function create_eBanking_box() {
		if (MODULE_PAYMENT_ICEPAY_EBANKING_COUNTRY == "USER"){
			$dropdown = '<select name="ic_country" id="setCOUNTRY">';
			foreach ( $this->allowedCountriesEBankStrict() as $val )
					$dropdown.='<option value="'.$val.'" >'.$this->getCountryName($val).'</option>';
			$dropdown.= '</select>';
			$langStr = Translate('Kies het land van Direct E-Banking account: ');
			$create_eBanking_box = "<div style=\"margin-right:20px; display:block; float:left;\">" . $this->generateIcon() . $langStr . $dropdown . "</div><br />";
		};
		
		return ($create_eBanking_box);
	}
	
	function process_button() {
		$hidden_fields = parent::process_button(); 
        return 	tep_draw_hidden_field('ic_paymentmethod',		'DIRECTEBANK').
				tep_draw_hidden_field('ic_issuer',          '').
				$hidden_fields;
	}

	function _process_button()
	{
	
		$icepay_country		= $this->getUserCountry(MODULE_PAYMENT_ICEPAY_EBANKING_COUNTRY);
		$icepay_currency	= $this->getUserCurrency(MODULE_PAYMENT_ICEPAY_EBANKING_CURRENCY);
		$icepay_language	= $this->getUserLanguage(MODULE_PAYMENT_ICEPAY_EBANKING_LANGUAGE);
		
		$amount				= $this->getOrderAmount(MODULE_PAYMENT_ICEPAY_EBANKING_CURRENCY);

		$process_button_string = tep_draw_hidden_field('ic_merchantid',		MODULE_PAYMENT_ICEPAY_MERCHANT_ID) .
														 tep_draw_hidden_field('ic_paymentmethod',	'DIRECTEBANK') .
														 tep_draw_hidden_field('ic_orderid',		$this->order_id) .
														 tep_draw_hidden_field('ic_amount',			$amount) .
														 tep_draw_hidden_field('ic_currency',		$icepay_currency) .
														 tep_draw_hidden_field('ic_language',		$icepay_language) .
														 $this->create_eBanking_box();
								 
		if (!MODULE_PAYMENT_ICEPAY_EBANKING_COUNTRY == "USER"){
			$process_button_string .= tep_draw_hidden_field('ic_country',		$icepay_country);
		};
		$process_button_string .=tep_draw_hidden_field('ic_description',	STORE_NAME) .
								 tep_draw_hidden_field('type',				"directebank");
		
		return $process_button_string;
	}

	function check()
	{
		if (!isset($this->_check))
		{
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ICEPAY_EBANKING_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		}
		return $this->_check;
	}

		function allowedCurrenciesEBank()
		{
			return array( 'DETECT', 'EUR', 'GBP');
		}

		function allowedIssuersEBank()
		{
			return array();
		}
		
		function allowedCountriesEBank()
		{
			return array( 'AT', 'BE', 'DE', 'CH', 'USER', 'DETECT' );
		}
		
		function allowedCountriesEBankStrict()
		{
			return array( 'AT', 'BE', 'DE', 'CH' );
		}
		
		function allowedLanguagesEBank()
		{
			return array( 'EN', 'DE', 'NL', 'FR', 'DETECT' );
		}
	
	
	function getCountryName($iso){
		$query = tep_db_query(sprintf("SELECT countries_name FROM %s WHERE countries_iso_code_2 = '%s' LIMIT 1",
				TABLE_COUNTRIES,
				$iso));
		$result = tep_db_fetch_array($query);
		return $result['countries_name'];
	}
	
	function update_status() {
      global $order;
	  
      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ICEPAY_EBANKING_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ICEPAY_EBANKING_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable ICEPAY Direct E-Banking Module', 'MODULE_PAYMENT_ICEPAY_EBANKING_STATUS', 'True', 'Do you want to accept Direct E-Banking payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_ICEPAY_EBANKING_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '5', now())");

		// Regional settings
		$this->default_language = 'DETECT';
		$this->default_currency = 'EUR';
		$this->default_country 	= 'USER';
		
		$this->languages_dbstring = $this->db_implode($this->allowedLanguagesEBank());
		$this->currencies_dbstring = $this->db_implode($this->allowedCurrenciesEBank());
		$this->countries_dbstring = $this->db_implode($this->allowedCountriesEBank());
		
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Language display settings', 'MODULE_PAYMENT_ICEPAY_EBANKING_LANGUAGE', '".$this->default_language."', 'Set the language. Default setting is current OSCommerce language.', '6', '30', 'tep_cfg_select_option(array(".$this->languages_dbstring."), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Currency', 'MODULE_PAYMENT_ICEPAY_EBANKING_CURRENCY', '".$this->default_currency."', 'Set the currency. Default setting is current OSCommerce currency.', '6', '20', 'tep_cfg_select_option(array(".$this->currencies_dbstring."), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Country', 'MODULE_PAYMENT_ICEPAY_EBANKING_COUNTRY', '".$this->default_country."', 'Set the country.', '6', '10', 'tep_cfg_select_option(array(".$this->countries_dbstring."), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_ICEPAY_EBANKING_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '40', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");

		
	}

	function keys()
	{
		return array
		(
			'MODULE_PAYMENT_ICEPAY_EBANKING_STATUS',
			'MODULE_PAYMENT_ICEPAY_EBANKING_SORT_ORDER',
			'MODULE_PAYMENT_ICEPAY_EBANKING_LANGUAGE',
			'MODULE_PAYMENT_ICEPAY_EBANKING_CURRENCY',
			'MODULE_PAYMENT_ICEPAY_EBANKING_COUNTRY',
			'MODULE_PAYMENT_ICEPAY_EBANKING_ZONE'
		);
	}
}
?>