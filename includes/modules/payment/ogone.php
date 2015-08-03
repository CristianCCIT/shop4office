<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
  
  Modified by Frank de Badts (frank@debadts.com) for new osCommerce checkout (>Nov 2002) procedure.
  Tested with Ogone eCommerce version Jan 2003 and later. For more
  infomation about OGONE: http://www.ogone.be or http://www.ogone.nl
  To see this payment module in action see http://www.knuffelpost.nl (Dutch)
*/

  class ogone {
    var $code, $title, $description, $enabled;

// class constructor
    function ogone() {
      $this->code = 'ogone';
      $this->title = Translate('Via beveiligde Ogone server');
      $this->description = Translate('Kredietkaart Test Info:<br><br>CC#: 4111111111111111<br>Vervalt: Any');
	  $this->sort_order = MODULE_PAYMENT_OGONE_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_OGONE_STATUS == 'True') ? true : false);
	  if((int)MODULE_PAYMENT_OGONE_ORDER_STATUS_ID > 0)
       $this->order_status = MODULE_PAYMENT_OGONE_ORDER_STATUS_ID;
	   
	  $this->form_action_url = 'https://secure.ogone.com/ncol/' . MODULE_PAYMENT_OGONE_MODE . '/orderstandard.asp';
    }

// class methods
    function javascript_validation() {
      return true;
    }

    function selection() {
    return array('id' => $this->code,'module' => $this->title);
    }

	function pre_confirmation_check() {		
		global $order_total_modules, $order_totals, $order;
		require_once(DIR_WS_CLASSES . 'order_total.php');
		//if ($_SERVER['REMOTE_ADDR'] == '91.183.44.122') {
			if (count($order_total_modules) < 1 || count($order_totals) < 1) {
				$order_total_modules = new order_total;
				$order_totals = $order_total_modules->process();
			}
			//echo '<pre>';
			//print_r($order_totals);
			//die(print_r($order_total_modules));
		//}
		//$order_totals = $order_total_modules->process();		
		order_process(false, $this->order_status);
		return false;
    }

    function confirmation() {
      return false;
    }
    /* For a detailled spec on these fields for ogone see https://secure.ogone.com/ncol/test/admin_ogone.asp */
    function process_button() {
      global $_POST, $customer_id, $order, $currencies, $insert_id;

      $ogone_orderID = $insert_id;
      $ogone_amount = number_format($order->info['total'] * 100 * $order->info['currency_value'], 0, '', '');
	  if (!empty($customer_id)) {
		  $com_data = STORE_NAME.Translate(' bestelling. Klant #: ').$customer_id;
	  } else {
		  $com_data = STORE_NAME.Translate(' bestelling. Onbekende Klant');
	  }
	  $data = array(
	  	'ACCEPTURL' => tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'),
		'AMOUNT' => $ogone_amount,
		'BGCOLOR' => 'white',
		'BUTTONBGCOLOR' => 'white',
		'BUTTONTXTCOLOR' => 'black',
		'CANCELURL' => tep_href_link(FILENAME_CHECKOUT_PROCESS),
		'CATALOGURL' => tep_href_link(FILENAME_DEFAULT),
		'CN' => trim($order->customer['firstname'] . ' ' . $order->customer['lastname']),
		'COM' => $com_data,
		'CURRENCY' => $order->info['currency'],
		'DECLINEURL' => tep_href_link(FILENAME_CHECKOUT_PROCESS),
		'EMAIL' => $order->customer['email_address'],
		'EXCEPTIONURL' => tep_href_link(FILENAME_CHECKOUT_PROCESS),
		'FONTTYPE' => 'Verdana',
		'LANGUAGE' => MODULE_PAYMENT_OGONE_LANGUAGE,
		'ORDERID' => $ogone_orderID,
		'OWNERADDRESS' => $order->delivery['street_address'],
		'OWNERZIP' => $order->delivery['postcode'],
		'PARAMPLUS' => 'osCsid='.tep_session_id().'&customer_id='.$customer_id,
		'PMLISTTYPE' => '2',
		'PSPID' => MODULE_PAYMENT_OGONE_PSPID,
		'TBLBGCOLOR' => 'white',
		'TBLTXTCOLOR' => 'black',
		'TITLE' => STORE_NAME,
		'TXTCOLOR' => 'black'
	  );
		$sha_data = '';
		ksort($data);
		foreach ($data as $key=>$value) {
			if (!empty($value)) {
				$process_button_string .= tep_draw_hidden_field($key, $value);
				$sha_data .= strtoupper($key).'='.$value.MODULE_PAYMENT_OGONE_SHA_STRING;
			}
		}
	if (MODULE_PAYMENT_OGONE_2011!='true') {
      include(DIR_WS_CLASSES . 'sha.php');
      $sha = new SHA;
      $hasharray = $sha->hash_string($ogone_orderID . $ogone_amount . $order->info['currency'] . MODULE_PAYMENT_OGONE_PSPID . MODULE_PAYMENT_OGONE_SHA_STRING);
      $process_button_string .= tep_draw_hidden_field('SHASign', $sha->hash_to_string($hasharray));
	} else {
	  $process_button_string .= tep_draw_hidden_field('SHASIGN', strtoupper(sha1($sha_data)));
	}
	  
		if(MODULE_PAYMENT_OGONE_DYNAMIC_TEMPLATE == 'Yes') {
			$process_button_string .= tep_draw_hidden_field('TP', MODULE_PAYMENT_OGONE_DYNAMIC_TEMPLATE_URL) . "\n";
		}
//die($process_button_string);
      return $process_button_string;
    }

    function before_process() {
      return false;
    }

    function after_process() {
		global $_GET;
		$data = '';
		foreach($_GET as $key=>$value) {
			$data .= $key.': '."\n";
			$data .= $value."\n\n";
		}
		tep_db_query('INSERT INTO payment_log (type, data, date) VALUES ("ogone", "'.$data.'", NOW())');
		tep_db_query('DELETE FROM payment_log WHERE date < DATE_SUB(NOW(), INTERVAL 30 DAY)');
		/*PAYMENT ACCEPTED*/
		if ($_GET['STATUS'] == '9') {
			send_order_mail($_GET['orderID']);
			tep_db_query('UPDATE orders SET orders_status = 1 WHERE orders_id = "'.$_GET['orderID'].'"');
		}
		/*PAYMENT AUTHORISED*/
		else if ($_GET['STATUS'] == '5') {
			send_order_mail($_GET['orderID']);
			tep_db_query('UPDATE orders SET orders_status = 1 WHERE orders_id = "'.$_GET['orderID'].'"');
		}
		/*WAIT FOR ACCEPTANCE */
		else if ($_GET['STATUS'] == '51' || $_GET['STATUS'] == '91' || $_GET['STATUS'] == '4' || $_GET['STATUS'] == '41' || $_GET['STATUS'] == '52' || $_GET['STATUS'] == '59' || $_GET['STATUS'] == '92') {
			send_order_mail($_GET['orderID']);
			//do nothing
		}
		/*PAYMENT DECLINED*/
		else if ($_GET['STATUS'] == '2' || $_GET['STATUS'] == '84' || $_GET['STATUS'] == '93') {
			tep_db_query('UPDATE orders SET orders_status = 11 WHERE orders_id = "'.$_GET['orderID'].'"');
			send_order_error_mail(Translate('Ogone betaling geweigerd voor bestelling').': '.$_GET['orderID'], sprintf(Translate('De betaling voor bestelling %s is geweigerd door ogone.'), $_GET['orderID']));
			tep_redirect(tep_href_link(FILENAME_CHECKOUT_ERROR, '', 'SSL'));
		}
		/*PAYMENT CANCELED*/
		else if ($_GET['STATUS'] == '1' || $_GET['STATUS'] == '6' || $_GET['STATUS'] == '64' || $_GET['STATUS'] == '7' || $_GET['STATUS'] == '74') {
			tep_db_query('UPDATE orders SET orders_status = 12 WHERE orders_id = "'.$_GET['orderID'].'"');
			send_order_error_mail(Translate('Ogone betaling geannuleerd voor bestelling').': '.$_GET['orderID'], sprintf(Translate('De betaling voor bestelling %s is geannuleerd.'), $_GET['orderID']));
			tep_redirect(tep_href_link(FILENAME_CHECKOUT, '', 'SSL'));
		}
		/*PAYMENT NOT VALID*/
		else if ($_GET['STATUS'] == '0') {
			tep_db_query('UPDATE orders SET orders_status = 13 WHERE orders_id = "'.$_GET['orderID'].'"');
			send_order_error_mail(Translate('Ongeldige Ogone betaling voor bestelling').': '.$_GET['orderID'], sprintf(Translate('De betaling voor bestelling %s is ongeldig verklaard door ogone.'), $_GET['orderID']));
			tep_redirect(tep_href_link(FILENAME_CHECKOUT_ERROR, '', 'SSL'));
		}
      return false;
    }

    function get_error() {
      return false;
    }


    function check() {
      if (!isset($this->check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_OGONE_STATUS'");
        $this->check = tep_db_num_rows($check_query);
      }
      return $this->check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Allow OGONE Payments', 'MODULE_PAYMENT_OGONE_STATUS', 'True', 'Do you want to accept OGONE payments?', '6', '20', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('OGONE Status Mode', 'MODULE_PAYMENT_OGONE_MODE', 'test', 'Status mode for OGONE payments? (test or prod)', '6', '21', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('OGONE PSPID', 'MODULE_PAYMENT_OGONE_PSPID', 'TESTSTD', 'Merchant NCOL ID', '6', '22', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('OGONE Client Language', 'MODULE_PAYMENT_OGONE_LANGUAGE', 'en_US', 'Client language', '6', '23', 'tep_cfg_pull_down_ogone_language(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('OGONE SHA String', 'MODULE_PAYMENT_OGONE_SHA_STRING', '', 'SHA string used for the signature (set at the merchant administration page)', '6', '24', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_OGONE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_OGONE_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
	tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('OGONE Dynamic Template', 'MODULE_PAYMENT_OGONE_DYNAMIC_TEMPLATE', 'No', 'Use dynamic template for payment form?', '6', '25', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ',now())");
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('OGONE Dynamic Template URL', 'MODULE_PAYMENT_OGONE_DYNAMIC_TEMPLATE_URL', ' http://www.ogone.com/ncol/template_standard.htm', 'Change the appearance of the payment form', '6', '25', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . join($this->keys(), "','") . "')");
    }

    function keys() {
		return array(
			'MODULE_PAYMENT_OGONE_STATUS',
			'MODULE_PAYMENT_OGONE_MODE',
			'MODULE_PAYMENT_OGONE_PSPID',
			'MODULE_PAYMENT_OGONE_LANGUAGE',
			'MODULE_PAYMENT_OGONE_SHA_STRING',
			'MODULE_PAYMENT_OGONE_SORT_ORDER',
			'MODULE_PAYMENT_OGONE_ORDER_STATUS_ID',
			'MODULE_PAYMENT_OGONE_DYNAMIC_TEMPLATE',
			'MODULE_PAYMENT_OGONE_DYNAMIC_TEMPLATE_URL'
		);
    }
  }
function tep_cfg_pull_down_ogone_language($language_id, $configuration_key = NULL) {
  $name = isset($configuration_key) ? 'configuration[' . $configuration_key . ']' : 'configuration_value';

  /* languages supported by Ogone */
  $languages = array(
    'en_US' => 'English',
    'fr_FR' => 'French',
    'nl_NL' => 'Dutch',
    'it_IT' => 'Italian',
    'de_DE' => 'German',
    'es_ES' => 'Spanish',
    'no_NO' => 'Norvegian'
  );

  $languages_array = array();

  foreach($languages as $id => $text) {
    $languages_array[] = array('id' => $id, 'text' => $text);
  }

  return tep_draw_pull_down_menu($name, $languages_array, $language_id);
}
?>