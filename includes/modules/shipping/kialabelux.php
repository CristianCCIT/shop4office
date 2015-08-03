<?php
class kialabelux {
	var $code, $title, $description, $icon, $enabled, $types;

    function kialabelux() {
	  global $order;
	  
      $this->code = 'kialabelux';
      $this->title = Translate('kialabelux titel');
      $this->description = Translate('kialabelux omschrijving');
      $this->sort_order = MODULE_SHIPPING_KIALABELUX_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_kiala.gif';
      $this->icontitle = Translate('Wat is Kiala?');
      $this->tax_class = MODULE_SHIPPING_KIALABELUX_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_KIALABELUX_STATUS == 'true') ? true : false);

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_KIALABELUX_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_KIALABELUX_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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

// class methods
    function quote($method = '') {
      global $_POST, $order, $shipping_weight, $shipping_num_boxes;

	    $shipping_weight = ceil($shipping_weight);
	  
  	  $result = $this->_getKialabeQuote($method, $shipping_weight, $shipping_num_boxes, $order->delivery['country']['iso_code_2']); //intn'l shipping

	    if (is_array($result)) {

        $this->quotes = $result;
        
      } elseif ($result != 'hide_module') {

        $this->quotes = array('module' => $this->title,
                              'error' => $result);
	    }
	    
	    if ((is_array($result)) || ($result != 'hide_module')) {

        if ($this->tax_class > 0) {
          $this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        }
	    
//      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);
	    
	    }

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_KIALABELUX_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable shipping via Kiala BeLux?', 'MODULE_SHIPPING_KIALABELUX_STATUS', 'True', 'Do you want to offer shipping via Kiala BeLux?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Maximum Weight', 'MODULE_SHIPPING_KIALABELUX_WEIGHT_MAX', '50', 'What is the maximum weight you will ship?', '6', '8', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Text for Maximum Weight', 'MODULE_SHIPPING_KIALABELUX_DISPLAY_WEIGHT', 'True', 'Do you want to display text if the maximum weight is exceeded?', '6', '7', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Shipping Cost', 'MODULE_SHIPPING_KIALABELUX_COST', '0.00', 'The shipping cost for all orders using this shipping method.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Minimum Order Amount', 'MODULE_SHIPPING_KIALABELUX_AMOUNT', '5.00', 'Minimum order amount purchased before enabling this shipping method?', '6', '8', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Text for Minimum Order Amount', 'MODULE_SHIPPING_KIALABELUX_DISPLAY_AMOUNT', 'True', 'Do you want to display text if the minimum amount is not reached?', '6', '7', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Preparation Duration', 'MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION', '2', 'Days of preparation at the DSP.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transport Duration', 'MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION', '3', 'Days of transportation to the KP.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Guaranteed Duration At KP', 'MODULE_SHIPPING_KIALABELUX_GUARANTEED_DURATION', '14', 'Guaranteed duration at KP in days.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_KIALABELUX_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_KIALABELUX_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_KIALABELUX_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Debug Mode', 'MODULE_SHIPPING_KIALABELUX_DEBUG_MODE', '0', 'Do you want to enble Debug Mode?', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
    //return array('MODULE_SHIPPING_KIALABELUX_STATUS', 'MODULE_SHIPPING_KIALABELUX_WEIGHT_MAX', 'MODULE_SHIPPING_KIALABELUX_DISPLAY_WEIGHT', 'MODULE_SHIPPING_KIALABELUX_COST', 'MODULE_SHIPPING_KIALABELUX_AMOUNT', 'MODULE_SHIPPING_KIALABELUX_DISPLAY_AMOUNT', 'MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION', 'MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION', 'MODULE_SHIPPING_KIALABELUX_GUARANTEED_DURATION', 'MODULE_SHIPPING_KIALABELUX_TAX_CLASS', 'MODULE_SHIPPING_KIALABELUX_ZONE', 'MODULE_SHIPPING_KIALABELUX_SORT_ORDER');
      return array('MODULE_SHIPPING_KIALABELUX_STATUS', 'MODULE_SHIPPING_KIALABELUX_WEIGHT_MAX', 'MODULE_SHIPPING_KIALABELUX_DISPLAY_WEIGHT', 'MODULE_SHIPPING_KIALABELUX_COST', 'MODULE_SHIPPING_KIALABELUX_AMOUNT', 'MODULE_SHIPPING_KIALABELUX_DISPLAY_AMOUNT', 'MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION', 'MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION', 'MODULE_SHIPPING_KIALABELUX_GUARANTEED_DURATION', 'MODULE_SHIPPING_KIALABELUX_TAX_CLASS', 'MODULE_SHIPPING_KIALABELUX_ZONE', 'MODULE_SHIPPING_KIALABELUX_SORT_ORDER', 'MODULE_SHIPPING_KIALABELUX_DEBUG_MODE');
    }

	function _getKialabeQuote($method, $weight, $boxes, $country) {
	  global $order, $language, $cart; // We need $language for the language on the Kiala website

    $check_weight = (($weight*$boxes) > MODULE_SHIPPING_KIALABELUX_WEIGHT_MAX);
    $check_amount = ($cart->show_total() < MODULE_SHIPPING_KIALABELUX_AMOUNT);
    $check_display_weight = (MODULE_SHIPPING_KIALABELUX_DISPLAY_WEIGHT == 'false');
    $check_display_amount = (MODULE_SHIPPING_KIALABELUX_DISPLAY_AMOUNT == 'false');

      // If shipping weight exceeded and order amount too low:
      if ($check_weight && $check_amount)
      {     if ($check_display_weight && $check_display_amount) { return 'hide_module'; }
            elseif ($check_display_weight) { return sprintf(Translate('Kiala kan enkel voor een bestelling van minimum %s euro.'), MODULE_SHIPPING_KIALABELUX_AMOUNT); }
            elseif ($check_display_amount) { return sprintf(Translate('Kiala kan enkel voor een bestelling van minder dan %s kilogram.'), MODULE_SHIPPING_KIALABELUX_WEIGHT_MAX); }
            else { return sprintf(Translate('Kiala kan enkel voor een bestelling van minder dan %s kilogram.'), MODULE_SHIPPING_KIALABELUX_WEIGHT_MAX) . '<br>' . sprintf(Translate('Kiala kan enkel voor een bestelling van minimum %s euro.'), MODULE_SHIPPING_KIALABELUX_AMOUNT); }

      // If shipping weight is exceeded:
      } elseif ($check_weight) {
            if ($check_display_weight) { return 'hide_module'; }
            else { return sprintf(Translate('Kiala kan enkel voor een bestelling van minder dan %s kilogram.'), MODULE_SHIPPING_KIALABELUX_WEIGHT_MAX); }

      // If minimum order amount is not purchased:
      } elseif ($check_amount) {
            if ($check_display_amount) { return 'hide_module'; }
            else { return sprintf(Translate('Kiala kan enkel voor een bestelling van minimum %s euro.'), MODULE_SHIPPING_KIALABELUX_AMOUNT); }

      // Else show Kiala module:
      } else {

      $kialabelux_quote = array();

      if ($method == '') {

        // ########################
        // Part 1: $method is empty
        // ########################
		if ($_POST['pc'] != '') { $postcode = $_POST['pc']; } else { $postcode = $order->delivery['postcode']; }
		

        $query_selectzip = "SELECT * FROM " . TABLE_KIALA_BELUX_ZIPKPLIST . " WHERE zip='" . $postcode . "' AND country='" . $order->delivery['country']['iso_code_2'] . "' ORDER BY suggestion_order";
        $result_selectzip = mysql_query($query_selectzip);

        // Check if $query_selectzip has been successful:
        
        if (!$result_selectzip = mysql_query($query_selectzip)) {

           // $query_selectzip was not successful.
           // NOTE: We use mysql_query() instead of tep_db_query() because we need a custom error instead of the osC error
           // Show text Kiala shipping module is not available:

           //return MODULE_SHIPPING_KIALABELUX_TEXT_NOT_AVAILABLE;
           if (MODULE_SHIPPING_KIALABELUX_DEBUG_MODE == 'true') { $debug_code = ' (1)'; }
           return Translate('Verzending via Kiala is momenteel niet mogelijk. Onze excuses hiervoor.') . $debug_code;

         } else {

           // $query_selectzip was successful.
           // Check if there are any results:
           
           if (mysql_num_rows($result_selectzip)>0)
           {  // We did find results!
              // Check the availability of all Kiala shops:

              $teller = 0;

              while (list($id, $line_type, $zip, $country, $kp_id, $suggestion_order, $distance) = mysql_fetch_row($result_selectzip))
              {  $query_countkpdetails = "SELECT * FROM " . TABLE_KIALA_BELUX_KPLIST . " 
			  WHERE kp_id='" . $kp_id . "' 
			  AND ( kp_activities_start_date<NOW() 
			  AND ( kp_activities_end_date='0000-00-00' OR kp_activities_end_date>NOW() ) ) 
			  AND ( last_delivery_date='0000-00-00' OR last_delivery_date>(DATE_ADD(NOW(), INTERVAL " . (MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION + MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION) . " DAY)) 
			  OR ( first_delivery_date!='0000-00-00' AND first_delivery_date<(DATE_ADD(NOW(), INTERVAL " . (MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION + MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION). " DAY)) ) ) 
			  AND ( temporary_closure_start_date='0000-00-00' OR temporary_closure_start_date>(DATE_ADD(NOW(), INTERVAL " . (MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION + MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION + MODULE_SHIPPING_KIALABELUX_GUARANTEED_DURATION) . " DAY))) ";
                 $result_countkpdetails = mysql_query($query_countkpdetails);

                    // Check if $query_countkpdetails has been successful:
                    
                    if (!$result_countkpdetails = mysql_query($query_countkpdetails)) {
                    
                      // $query_countkpdetails was not successful.
                      // NOTE: We use mysql_query() instead of tep_db_query() because we need a custom error instead of the osC error
                      // Show text Kiala shipping module is not available:

                      //return MODULE_SHIPPING_KIALABELUX_TEXT_NOT_AVAILABLE;
                      if (MODULE_SHIPPING_KIALABELUX_DEBUG_MODE == '1') { $debug_code = ' (2)'; }
                      return Translate('Verzending via Kiala is momenteel niet mogelijk. Onze excuses hiervoor.') . $debug_code;

                    } else {
                    
                       // $query_countkpdetails was successful!
                       // Check if there are any results:
                       // if ($result_countkpdetails['total']>0)
                       
                       if (mysql_num_rows($result_countkpdetails)>0)
                       {  // We found results!
                          // Now increase teller:
                          
                          $teller++;
                       }
                    }
              }

              if ($teller>0)
              {  // We found available Kiala shops.
                 // Execute the query again:
		if ($_POST['pc'] != '') { $postcode = $_POST['pc']; } else { $postcode = $order->delivery['postcode']; }
				 
                 $query_selectzip = "SELECT * FROM " . TABLE_KIALA_BELUX_ZIPKPLIST . " WHERE zip='" . $postcode . "' AND country='" . $order->delivery['country']['iso_code_2'] . "' ORDER BY suggestion_order";
                 $result_selectzip = mysql_query($query_selectzip);
                 
                 // Now get the data of the Kiala shops:
                 
                 while (list($id, $line_type, $zip, $country, $kp_id, $suggestion_order, $distance) = mysql_fetch_row($result_selectzip))
                 {  $query_getkpdetails = "SELECT * FROM " . TABLE_KIALA_BELUX_KPLIST . " WHERE kp_id='" . $kp_id . "' AND ( kp_activities_start_date<NOW() AND ( kp_activities_end_date='0000-00-00' OR kp_activities_end_date>NOW() ) ) AND ( last_delivery_date='0000-00-00' OR last_delivery_date>(DATE_ADD(NOW(), INTERVAL " . (MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION + MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION) . " DAY)) OR ( first_delivery_date!='0000-00-00' AND first_delivery_date<(DATE_ADD(NOW(), INTERVAL " . (MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION + MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION). " DAY)) ) )  ";
                    $result_getkpdetails = mysql_query($query_getkpdetails);
					//WEGGELATEN UIT EINDE VAN QUERY
					//AND ( temporary_closure_start_date='0000-00-00' OR temporary_closure_start_date>(DATE_ADD(NOW(), INTERVAL " . (MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION + MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION + MODULE_SHIPPING_KIALABELUX_GUARANTEED_DURATION) . " DAY)))
					
					
					
                    
                    // NOTE: We use mysql_query() instead of tep_db_query() because we need a custom error instead of the osC error
                    // Now get the details of the Kiala shops:
                    
                          while (list($id, $line_type, $kp_id, $address_language_1, $kp_name_1, $extra_address_line_1, $street_1, $street_nr_1, $locality_1, $city_1, $province_or_region_1, $location_hint_1, $address_language_2, $kp_name_2, $extra_address_line_2, $street_2, $street_nr_2, $locality_2, $city_2, $province_or_region_2, $location_hint_2, $zip) = mysql_fetch_row($result_getkpdetails))
                          {     // Select the correct language fields according to the user language
                                // If the user language is French, show fields with suffix _1
                                // If the user language is other than French, show fields with suffix_2
                                if ($language == "francais")
                                {  $kp_name = $kp_name_1;
                                   $street = $street_1;
                                   $street_nr = $street_nr_1;
                                   $city = $city_1;
                                   $langcode = "FR";
                                } else {
                                   $kp_name = $kp_name_2;
                                   $street = $street_2;
                                   $street_nr = $street_nr_2;
                                   $city = $city_2;
                                   $langcode = "NL"; }
                                   
                          // Make a list of all Kiala shops; Data is shown on checkout_shipping.php:
                          $kialabelux_quote[] = array( $kp_id => '<b>'.$kp_name . "</b> (KP" . $kp_id . ")<br>$street $street_nr<br>$zip " . strtoupper($city));

   	                     }
                 }
              } else {
              
                // No available Kiala shops found.
                // Show text No available Kiala shops found:
		if ($_POST['pc'] != '') { $postcode = $_POST['pc']; } else { $postcode = $order->delivery['postcode']; }
		         return sprintf(Translate('Er zijn in uw gemeente met postcode %s geen Kialapunten gevonden die momenteel een afhaling kunnen aanbieden. Wijzig de postcode in uw adres om te zoeken in een andere gemeente.'), $postcode);;

              }

           } else {
           
             // No results...
             // Show text zip is not found in the database:
		if ($_POST['pc'] != '') { $postcode = $_POST['pc']; } else { $postcode = $order->delivery['postcode']; }
		         return sprintf(Translate('De postcode %s in uw adres is niet gevonden. Gelieve uw postcode te controleren als u van Kiala gebruik wil maken.'), $postcode);;

           }
           
         }

      } else {

      // ############################
      // Part 2: $method is not empty
      // ############################

          $query_getkpdetails = "SELECT * FROM " . TABLE_KIALA_BELUX_KPLIST . " WHERE kp_id='" . $method . "' AND ( kp_activities_start_date<NOW() AND ( kp_activities_end_date='0000-00-00' OR kp_activities_end_date>NOW() ) ) AND ( last_delivery_date='0000-00-00' OR last_delivery_date>(DATE_ADD(NOW(), INTERVAL " . (MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION + MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION) . " DAY)) OR ( first_delivery_date!='0000-00-00' AND first_delivery_date<(DATE_ADD(NOW(), INTERVAL " . (MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION + MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION). " DAY)) ) ) AND ( temporary_closure_start_date='0000-00-00' OR temporary_closure_start_date>(DATE_ADD(NOW(), INTERVAL " . (MODULE_SHIPPING_KIALABELUX_PREPARATION_DURATION + MODULE_SHIPPING_KIALABELUX_TRANSPORT_DURATION + MODULE_SHIPPING_KIALABELUX_GUARANTEED_DURATION) . " DAY)) OR temporary_closure_start_date<(DATE_ADD(NOW(), INTERVAL -1 DAY))) ";
          $result_getkpdetails = mysql_query($query_getkpdetails);
          // NOTE: We use mysql_query() instead of tep_db_query() because we need a custom error instead of the osC error
          // Now get the details of the Kiala shops
                while (list($id, $line_type, $kp_id, $address_language_1, $kp_name_1, $extra_address_line_1, $street_1, $street_nr_1, $locality_1, $city_1, $province_or_region_1, $location_hint_1, $address_language_2, $kp_name_2, $extra_address_line_2, $street_2, $street_nr_2, $locality_2, $city_2, $province_or_region_2, $location_hint_2, $zip) = mysql_fetch_row($result_getkpdetails))
                {     // Select the correct language fields according to the user language
                      // If the user language is French, show fields with suffix _1
                      // If the user language is other than French, show fields with suffix_2
                      if ($language == "francais")
                      {  $kp_name = $kp_name_1;
                         $street = $street_1;
                         $street_nr = $street_nr_1;
                         $city = $city_1;
                         $langcode = "FR";
                      } else {
                         $kp_name = $kp_name_2;
                         $street = $street_2;
                         $street_nr = $street_nr_2;
                         $city = $city_2;
                         $langcode = "NL"; }
                         
                // Make a list of all Kiala shops; Data is shown on checkout_confirmation.php and account_history_info.php:
                $kialabelux_quote[] = array( $kp_id => "KP" . $kp_id . ": $kp_name, $street $street_nr, $zip " . strtoupper($city));

              }

      }
  
		if ( (is_array($kialabelux_quote)) && (sizeof($kialabelux_quote) > 0) ) {

      $methods = array();

      for ($i = 0, $j = sizeof($kialabelux_quote); $i < $j; $i++) {
        reset($kialabelux_quote[$i]);
        list($type, $kialabelux_shop) = each($kialabelux_quote[$i]);
		//GRATIS VERZENDING
		$kiala_kostprijs = MODULE_SHIPPING_KIALABELUX_COST;
		if ($order->info['subtotal'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
			$kiala_kostprijs = 0;
		}
        $methods[] = array('id' => $type,
                           'title' => $kialabelux_shop,
                           'cost' => $kiala_kostprijs);

		  }
		  
		  if (tep_not_null($methods)) {
        $quotes = array('id' => $this->code,
                         'module' => $this->title,
                         'methods' => $methods);
      } else {
		    $quotes = '';
      }
		} else {

      //$quotes = array('module' => $this->title,
      //                'error' => MODULE_SHIPPING_KIALABELUX_TEXT_NOT_AVAILABLE);
      if (MODULE_SHIPPING_KIALABELUX_DEBUG_MODE == '1') { $debug_code = ' (3)'; }
      $quotes = array('module' => $this->title,
                      'error' => Translate('Verzending via Kiala is momenteel niet mogelijk. Onze excuses hiervoor.') . $debug_code);
    }

    }

		return $quotes;
		
	}

  }
?>