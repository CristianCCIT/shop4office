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

class icepay_paysafecard extends icepay
{

	var $icon = "paysafecard.jpg";

	function icepay_paysafecard()
	{
		global $order;

		$this->code				= 'icepay_paysafecard';
		$this->title			= $this->getTitle();
		$this->description		= $this->getDescription();
		$this->sort_order		= MODULE_PAYMENT_ICEPAY_PAYSAFECARD_SORT_ORDER;
		$this->enabled			= ((MODULE_PAYMENT_ICEPAY_PAYSAFECARD_STATUS == 'True') && $this->doAllowedCountryCheck($this->allowedCountriesPaysafecard()) ? true : false);

		if ((int)MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID > 0)	$this->order_status = MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID;

		if (is_object($order)) $this->update_status();
		
		$this->form_action_url = $this->base_url . '?type=paysafecard';
	}
	

	
	
	
	function getDescription(){
		return "<b>".$this->getLangStr("title")."</b><BR>".$this->getLangStr("descr")."<BR>";
	}
	
	function getLangStr($str){
		switch ($str){
			case "title":
				return Translate('Betalen met paysafecard');
				break;
			case "descr":
				return Translate('The main ICEPAY module must be installed (does not have to be active) to use this payment method.');
				break;
		}
	}
	
	function process_button() {
		$hidden_fields = parent::process_button(); 
        return 	tep_draw_hidden_field('ic_paymentmethod',		'PAYSAFECARD').
				tep_draw_hidden_field('ic_issuer',          'DEFAULT').
				$hidden_fields;
	}
    
	/*function process_button()
	{
           return tep_draw_hidden_field('ic_paymentmethod',		'PAYSAFECARD').
                    tep_draw_hidden_field('ic_country',		"00").
                    tep_draw_hidden_field('ic_currency',	$this->getUserCurrency(MODULE_PAYMENT_ICEPAY_PAYSAFECARD_CURRENCY)).
                    tep_draw_hidden_field('ic_language',	$this->getUserLanguage("EN")).
                    tep_draw_hidden_field('ic_amount',          $this->getOrderAmount(MODULE_PAYMENT_ICEPAY_PAYSAFECARD_CURRENCY)).
                    tep_draw_hidden_field('ic_issuer',          'DEFAULT');
	}*/
        
	
	function confirmation()
	{
		return false;
	}

	function check()
	{
		if (!isset($this->_check))
		{
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ICEPAY_PAYSAFECARD_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function update_status() {
      global $order;
	  
      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ICEPAY_PAYSAFECARD_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ICEPAY_PAYSAFECARD_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
	
	function allowedCountriesPaysafecard()
		{
			return array( 'NL' );
		}

	function allowedLanguagesPaysafecard()
	{
		return array( 'EN', 'NL', 'DETECT' );
	}
		
	function allowedCurrenciesPaysafecard()
		{
			return array( 'DETECT', 'EUR', 'USD', 'GBP' );
		}
	
	function install()
	{
			tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable ICEPAY Direct Debit Module', 'MODULE_PAYMENT_ICEPAY_PAYSAFECARD_STATUS', 'True', 'Do you want to accept Direct Debit payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
			tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_ICEPAY_PAYSAFECARD_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

			// Regional settings
			$this->default_currency = "EUR";
			$this->currencies_dbstring = $this->db_implode($this->allowedCurrenciesPaysafecard());
			
			tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Currency', 'MODULE_PAYMENT_ICEPAY_PAYSAFECARD_CURRENCY', '".$this->default_currency."', 'Set the currency. Default setting is current OSCommerce currency.', '6', '1', 'tep_cfg_select_option(array(".$this->currencies_dbstring."), ', now())");
			tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_ICEPAY_PAYSAFECARD_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '3', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");


	}

	function keys()
	{
		return array
		(
			'MODULE_PAYMENT_ICEPAY_PAYSAFECARD_STATUS',
			'MODULE_PAYMENT_ICEPAY_PAYSAFECARD_SORT_ORDER',
			'MODULE_PAYMENT_ICEPAY_PAYSAFECARD_CURRENCY',
			'MODULE_PAYMENT_ICEPAY_PAYSAFECARD_ZONE'
		);
	}
}
?>