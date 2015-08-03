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

//define(PAYMENT_INFORMATION_NL, "Kies uw bank in de lijst beneden.");

class icepay_ideal extends icepay
{
	var $query_bankinfo;
	var $icon = "ideal.jpg";

	function icepay_ideal()
	{
		global $order;

		$this->code				= 'icepay_ideal';
		$this->title			= $this->getTitle();
		$this->description		= $this->description = "<img src='images/icon_info.gif' border='0'>&nbsp;<b>ICEPAY iDeal</b><BR>The main ICEPAY module must be installed (does not have to be active) to use this payment method.<BR>";
		$this->sort_order		= MODULE_PAYMENT_ICEPAY_IDEAL_SORT_ORDER;
		$this->enabled			= ($this->doAllowedCurrencyCheck('EUR') && ((MODULE_PAYMENT_ICEPAY_IDEAL_STATUS == 'True') && $this->doAllowedCountryCheck($this->allowedCountriesIDeal()) ? true : false));
		$this->query_bankinfo	= ((MODULE_PAYMENT_ICEPAY_IDEAL_FETCH_BANKS == 'True') ? true : false);

		if ((int)MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID > 0)
			$this->order_status = MODULE_PAYMENT_ICEPAY_ORDER_STATUS_ID;

		if (is_object($order))
			$this->update_status();
		
		$this->form_action_url = $this->base_url . '?type=ideal';
	}
	
	function getLangStr($str){
		switch ($str){
			case "title":
				return Translate('Betalen via iDEAL');
				break;
		}
	}
	
	function create_iDeal_box() {
		if ( $this->query_bankinfo )
		{
			$client = new SoapClient( $this->wsdl );
			$params->merchantID = MODULE_PAYMENT_ICEPAY_MERCHANT_ID;
			$params->secretCode = MODULE_PAYMENT_ICEPAY_SECRET;
			$ideal_object			= $client->GetIDEALIssuers($params);
			$ideal_issuers		= $ideal_object->GetIDEALIssuersResult->string;
			$ideal_issuers_arr	= array();
			foreach ( $ideal_issuers as $ideal_issuer )
				$ideal_issuers_arr[] = array( 'id' => $ideal_issuer, 'text' => $ideal_issuer );
		}
		else
		{
			$ideal_issuers_arr[] = array( 'id' => 'ABNAMRO',		'text' => 'ABN AMRO' );
			$ideal_issuers_arr[] = array( 'id' => 'ASNBANK',		'text' => 'ASN Bank' );
			$ideal_issuers_arr[] = array( 'id' => 'FRIESLAND',		'text' => 'Friesland Bank' );
			$ideal_issuers_arr[] = array( 'id' => 'ING', 			'text' => 'ING' );
			$ideal_issuers_arr[] = array( 'id' => 'RABOBANK', 		'text' => 'Rabobank' );
			$ideal_issuers_arr[] = array( 'id' => 'SNSBANK',		'text' => 'SNS Bank' );
			$ideal_issuers_arr[] = array( 'id' => 'SNSREGIOBANK',	'text' => 'SNS Regio Bank' );
			$ideal_issuers_arr[] = array( 'id' => 'TRIODOSBANK',	'text' => 'Triodos Bank' );
			$ideal_issuers_arr[] = array( 'id' => 'VANLANSCHOT',	'text' => 'Van Lanschot' );
		}
			
		$dropdown = '<select name="ic_issuer" >';
		foreach ( $ideal_issuers_arr as $ideal_issuer )
				$dropdown .='<option value="'.$ideal_issuer['id'].'" >'.$ideal_issuer['text'].'</option>';
		$dropdown .= '</select>';
		$create_iDeal_box = "<div style=\"margin-right:20px; display:block; float:left;\">" . Translate('Kies uw bank voor het afrekenen met iDEAL: ') . $dropdown . "</div>";

		return ($create_iDeal_box);
		}
	
	
	
	
	function process_button() {
		$hidden_fields = parent::process_button(); 
        return 	tep_draw_hidden_field('ic_paymentmethod',		'IDEAL').
				$this->create_iDeal_box().
				$hidden_fields;
	}
    /*    function process_button()
	{
           return tep_draw_hidden_field('ic_paymentmethod',		'IDEAL').
                    tep_draw_hidden_field('ic_country',		$this->getUserCountry("NL")).
                    tep_draw_hidden_field('ic_currency',	$this->getUserCurrency("EUR")).
                    tep_draw_hidden_field('ic_language',	$this->getUserLanguage("NL")).
                    tep_draw_hidden_field('ic_amount',          $this->getOrderAmount("EUR")).
                    $this->create_iDeal_box();
	}*/
                
	function check()
	{
		if (!isset($this->_check))
		{
			$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ICEPAY_IDEAL_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		}
		return $this->_check;
	}
	
	function allowedCountriesIDeal()
		{
			return array( 'NL' );
		}

	function update_status() {
      global $order;
	  
      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ICEPAY_IDEAL_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ICEPAY_IDEAL_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable ICEPAY iDEAL Module', 'MODULE_PAYMENT_ICEPAY_IDEAL_STATUS', 'True', 'Do you want to accept iDEAL payments?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Query bank information', 'MODULE_PAYMENT_ICEPAY_IDEAL_QUERY_BANKINFO', 'True', 'Do you want to query available banks from ICEPAY server?', '6', '2', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_ICEPAY_IDEAL_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
		tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_ICEPAY_IDEAL_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '3', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");

	}

	function keys()
	{
		return array
		(
			'MODULE_PAYMENT_ICEPAY_IDEAL_STATUS',
			'MODULE_PAYMENT_ICEPAY_IDEAL_SORT_ORDER',
			'MODULE_PAYMENT_ICEPAY_IDEAL_QUERY_BANKINFO',
			'MODULE_PAYMENT_ICEPAY_IDEAL_ZONE'
		);
	}
}
?>