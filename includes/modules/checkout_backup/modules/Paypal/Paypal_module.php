<?php
//payment class for cod => cash on delivery
class Paypal extends Modules {
	public $type = 'payment'
		 , $sort_order = 40
		 , $identifier = 'ABO Service Paypal IPN v1.0'
		 , $instances = array();
	public function __construct() {
		//load config => title, text, sort_order, status, zone, order_status_id, works_with_shipping_module
		if(tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'checkout_".get_class($this)."'"))< 1) {
			$this->install();
		}
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		while ($array = tep_db_fetch_array($query)) {
			$this->instances[get_class($this).'_'.$array['id']] = $array;
			if ($array['mode'] == 'prod') {
				$this->instances[get_class($this).'_'.$array['id']]['url'] = 'https://www.paypal.com/cgi-bin/webscr';
			} else {
				$this->instances[get_class($this).'_'.$array['id']]['url'] = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			}
		}
	}
	public function update_instances() {
		$this->instances = array();
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		while ($array = tep_db_fetch_array($query)) {
			$this->instances[get_class($this).'_'.$array['id']] = $array;
			if ($array['mode'] == 'prod') {
				$this->instances[get_class($this).'_'.$array['id']]['url'] = 'https://www.paypal.com/cgi-bin/webscr';
			} else {
				$this->instances[get_class($this).'_'.$array['id']]['url'] = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			}
		}
	}
	public function is_active() {
		global $temp_orders_id;
		$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
		foreach($this->instances as $data) {
			if ($data['status'] == 'true') {
				if (parent::checkZone($data['zone'], $temp_data[$temp_orders_id]['orders']['billing_country']) && parent::checkShippingMethod($data['shipping_module'])) {
					return true;
				}
			}
		}
		return false;
	}
	public function output($step = 0) {
		global $temp_orders_id;
		$html = '';
		if (isset(Checkout::$errors[$this->type])) {
			$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate(Checkout::$errors[$this->type]).'</div>';
			unset(Checkout::$errors[$this->type]);
		}
		//select payment method if orders_id is known
		//also get billing country
		if (!empty($temp_orders_id)) {
			$selected_query = tep_db_query('SELECT payment_method, billing_country FROM temp_orders WHERE orders_id = "'.$temp_orders_id.'"');
			$selected = tep_db_fetch_array($selected_query);
		}
		foreach($this->instances as $id=>$data) {
			if ($data['status'] == 'true') {
				//check is active for zones and choosen shipping module
				if (parent::checkZone($data['zone'], $selected['billing_country']) && parent::checkShippingMethod($data['shipping_module'])) {
					if (isset(Checkout::$errors[$id])) {
						$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate(Checkout::$errors[$id]).'</div>';
					}
					$html .= '<label class="control-label" for="'.$this->type.'_'.$id.'" style="display:block;">';
					$html .= '<div class="'.$this->type.'_item clearfix">';
					$html .= '<input type="radio" name="'.$this->type.'" value="'.$id.'" id="'.$this->type.'_'.$id.'"'.($selected['payment_method']==$id?' checked=checked':'').' />';
					$html .= '<div class="'.$this->type.'_title">&nbsp; '.$data['title'].'</div>';
					if (!empty($data['description'])) {
						$html .= '<div class="'.$this->type.'_description">&nbsp; '.$data['description'].'</div>';
					}
					$html .= '</div>';
					$html .= '</label>';
				}
			}
		}
		return $html;
	}
	public function show_images() {
		$images = array();
		$images[strtolower(get_class($this))] = HTTP_SERVER.DIR_WS_HTTP_CATALOG.DIR_WS_MODULES.'checkout/modules/'.get_class($this).'/img/paypal.png';
		return $images;
	}
	public function process_data() {
		global $temp_orders_id;
		$strlen = strlen(get_class($this));
		if (isset($_POST[$this->type])) {
			if (substr($_POST[$this->type], 0, $strlen) == get_class($this)) {
				if ($temp_orders_id == 0) {
					$temp_orders_id = parent::create_order();
				} else{
					tep_db_query('UPDATE temp_orders SET payment_method = "'.$_POST[$this->type].'", orders_status = "'.$this->instances[$_POST[$this->type]]['order_status_id'].'", payment_method_extra = "" WHERE orders_id = "'.$temp_orders_id.'"');
				}
			}
			return true;
		} else {
			$this->errors[$this->type] = Translate('Kies a.u.b. een betaalmethode.');
			return false;
		}
	}
	public function before_confirm() {
		global $temp_orders_id, $customer_id, $currency, $languages_code, $currencies, $cart_PayPal_IPN_ID;
		if (!isset($_GET['tx'])) {//only process if we don't come back from paypal
			$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
			/*REGISTER PAYPAL IPN CART ID*/
			$cart_PayPal_IPN_ID =  $temp_orders_id.'||'.date("Y-m-d H:i:s");
			tep_session_register('cart_PayPal_IPN_ID');
			/*GET CURRENCY*/
			$my_currency = $currency;
			if (!in_array($my_currency, array('AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'SEK', 'SGD', 'USD'))) {
				$my_currency = 'USD';
			}
			$parameters = array();
			foreach($temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
				if ($data['class'] == 'order_total') {
					$total = $data['value'];
				}
			}
			/*AGGREGATED*/
			$parameters['cmd'] = '_ext-enter';
			$parameters['redirect_cmd'] = '_xclick';
			$parameters['item_name'] = STORE_NAME;
			$parameters['amount'] = number_format($total, $currencies->get_decimal_places($my_currency));
			$parameters['shipping'] = number_format(0 * $currencies->get_value($my_currency), $currencies->get_decimal_places($my_currency));
			$parameters['business'] = $this->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['ipn_id'];
			$parameters['address_override'] = '1';
			$parameters['no_shipping'] = '2';
			$parameters['night_phone_b'] = $temp_data[$temp_orders_id]['orders']['customers_telephone'];
			$parameters['first_name'] = $temp_data[$temp_orders_id]['orders']['customers_name'];
			$parameters['address1'] = $temp_data[$temp_orders_id]['orders']['billing_street_address'];
			$parameters['city'] = $temp_data[$temp_orders_id]['orders']['billing_city'];
			$parameters['zip'] = $temp_data[$temp_orders_id]['orders']['billing_postcode'];
			$parameters['country'] = tep_get_countries_with_iso_codes($temp_data[$temp_orders_id]['orders']['billing_country']);
			$parameters['country'] = $parameters['country']['countries_iso_code_2'];
			$parameters['email'] = $temp_data[$temp_orders_id]['orders']['customers_email_address'];
			$parameters['charset'] = "utf-8";
			$parameters['currency_code'] = $my_currency;
			$parameters['invoice'] = $temp_orders_id;
			$parameters['custom'] = $customer_id.'[-]'.substr($cart_PayPal_IPN_ID, strpos($cart_PayPal_IPN_ID, '-')+1);
			$parameters['no_note'] = '1';
			$parameters['notify_url'] = tep_href_link('ext/modules/payment/paypal_ipn/ipn.php', 'language='.$languages_code, 'SSL', false, false);
			$parameters['cbt'] = Translate('Vervolledig orderbevestiging');  
			$parameters['return'] = tep_href_link(FILENAME_CHECKOUT);
			$parameters['cancel_return'] = tep_href_link(FILENAME_CHECKOUT, 'tx=canceled', 'SSL');
			$parameters['bn'] = $this->identifier;
			$parameters['lc'] = $parameters['country'];
			reset($parameters);
			while (list($key, $value) = each($parameters)) {
				$process_button_string .= tep_draw_hidden_field($key, $value);
			}
			$html = '</div><div class="span12">';
			$html .= '<div class="step active"><div class="step_title">'.Translate('Betaling met Paypal').'</div></div>';
			$html .= '<form name="redirectForm" action="'.$this->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['url'].'" method="POST">';
			$html .= $process_button_string;
			$html .= '</form>';
			$html .= '<div style="text-align:center;"><img src="'.DIR_WS_IMAGES.'ajax-loader.gif"><br>'.Translate('Bestelgegevens worden doorgestuurd naar de beveiligde betaal server, even geduld...').'</div>';
			$html .= '</div><div>';
			$html .= '<script>document.forms["redirectForm"].submit();</script>';
			echo $html;
			die();
		}
	}
	function after_confirm() {
		global $temp_orders_id;
		if (isset($_GET['tx'])) {
			$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);//get all orders data
			if ($_GET['tx'] == 'canceled') {
				$_GET['force_checkout_step'] = Checkout::get_step_for_type($this->type);
				$this->errors[$temp_data[$temp_orders_id]['orders']['payment_method']] = Translate('Uw betaling werd geannuleerd.');
			} else {
				tep_db_query('DELETE FROM payment_log WHERE date < DATE_SUB(NOW(), INTERVAL 30 DAY)');
				$req = 'cmd=_notify-synch';
				$tx_token = $_GET['tx'];
				$auth_token = $this->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['pdt_identy_token'];
				$req .= "&tx=$tx_token&at=$auth_token";
				$header = '';
				$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
				$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
				$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
				if ($this->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['mode'] == 'prod') {
					$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
				} else {
					$fp = fsockopen ('www.sandbox.paypal.com', 80, $errno, $errstr, 30);
				}
				if (!$fp) {
					$data = '';
					foreach ($_GET as $key=>$value) {
						$data .= urldecode($key).': '."\n";
						$data .= urldecode($value)."\n\n";
					}
					$order_id = substr($_GET['cm'], strpos($_GET['cm'], '[-]')+3);
					tep_db_query('INSERT INTO payment_log (type, data, date) VALUES ("'.get_class($this).'", "'.$data.'", NOW())');
					send_order_error_mail(Translate('Er is iets fout gelopen met paypal bestelling').' '.$order_id, sprintf(Translate('Voor bestelling %s is er een fout gegenereerd! Controleer dit a.u.b. voordat u deze bestelling verder verwerkt.<br />Als u vragen hebt i.v.m. de fout contacteer dan ABO Service!'), $order_id));
				} else {
					fputs ($fp, $header . $req);
					$res = '';
					$headerdone = false;
					while (!feof($fp)) {
						$line = fgets ($fp, 1024);
						if (strcmp($line, "\r\n") == 0) {
							$headerdone = true;
						} else if ($headerdone) {
							$res .= $line;
						}
					}
					$lines = explode("\n", $res);
					$keyarray = array();
					$data = '';
					if (strcmp ($lines[0], "SUCCESS") == 0) {
						for ($i=1; $i<count($lines);$i++){
							list($key,$val) = explode("=", $lines[$i]);
							$keyarray[urldecode($key)] = urldecode($val);
							$data .= urldecode($key).': '."\n";
							$data .= urldecode($val)."\n\n";
						}
						tep_db_query('INSERT INTO payment_log (type, data, date) VALUES ("'.get_class($this).'", "'.$data.'", NOW())');
						if (empty($keyarray['invoice'])) {
							$order_id = substr($keyarray['custom'], strpos($keyarray['custom'], '[-]')+3);
						} else {
							$order_id = $keyarray['invoice'];
						}
						/*COMPLETED OR PROCESSED*/
						if ($keyarray['payment_status'] == 'Completed' || $keyarray['payment_status'] == 'Processed') {
							tep_db_query('UPDATE temp_orders SET orders_status = 2 WHERE orders_id = "'.$order_id.'"');
						/*EXPIRED*/
						} else if ($keyarray['payment_status'] == 'Expired') {
							send_order_error_mail(Translate('Status onzeker paypal bestelling').': '.$order_id, sprintf(Translate('De status voor bestelling %s is onzeker doordat de autorisatie verlopen was op het moment dat de klant terug op de shop kwam.'), $order_id));
							tep_db_query('UPDATE orders SET orders_status = 53 WHERE orders_id = "'.$order_id.'"');
							$_GET['force_checkout_step'] = Checkout::get_step_for_type($this->type);
							$this->errors[$temp_data[$temp_orders_id]['orders']['payment_method']] = Translate('Er is een fout opgetreden bij de betaling. Contacteer ons voor meer info.');
						/*FAILED*/
						} else if ($keyarray['payment_status'] == 'Failed') {
							tep_db_query('UPDATE orders SET orders_status = 53 WHERE orders_id = "'.$order_id.'"');
							$_GET['force_checkout_step'] = Checkout::get_step_for_type($this->type);
							$this->errors[$temp_data[$temp_orders_id]['orders']['payment_method']] = Translate('Er is een fout opgetreden bij de betaling. Contacteer ons voor meer info.');
						/*PENDING*/
						} else if ($keyarray['payment_status'] == 'Pending') {
							//
						}
					} else if (strcmp ($lines[0], "FAIL") == 0) {
						for ($i=1; $i<count($lines);$i++){
							list($key,$val) = explode("=", $lines[$i]);
							$data .= urldecode($key).': '."\n";
							$data .= urldecode($val)."\n\n";
						}
						tep_db_query('INSERT INTO payment_log (type, data, date) VALUES ("'.get_class($this).'", "'.$data.'", NOW())');
						if (empty($keyarray['invoice'])) {
							$order_id = substr($keyarray['custom'], strpos($keyarray['custom'], '[-]')+3);
						} else {
							$order_id = $keyarray['invoice'];
						}
						send_order_error_mail(Translate('Er is iets fout gelopen met paypal bestelling').' '.$order_id, sprintf(Translate('Voor bestelling %s is er een fout gegenereerd! Controleer dit a.u.b. voordat u deze bestelling verder verwerkt.<br />Als u vragen hebt i.v.m. de fout contacteer dan ABO Service!'), $order_id));
						$_GET['force_checkout_step'] = Checkout::get_step_for_type($this->type);
						$this->errors[$temp_data[$temp_orders_id]['orders']['payment_method']] = Translate('Er is een fout opgetreden bij de betaling. Contacteer ons voor meer info.');
					}
				}
				fclose ($fp);
			}
		}
		return $this->errors;
    }
	public function after_extern_process() {
		global $temp_orders_id;
		if (isset($_GET['tx'])) {
			$_GET['force_checkout_step'] = Checkout::get_step_for_type($this->type);
			$_POST['checkout_step'] = Checkout::last_active_step();
		}
		return;
	}
	public function getZones() {
		$zones = array();
		foreach($this->instances as $data) {
			if (!empty($data['zone'])) {
				$zones[] = $data['zone'];
			}
		}
		return $zones;
	}
	public function administrator() {
		global $Modules, $login;
		echo '<h1>';
		echo get_class($this);
		if ($login == 'aboservice') {
			echo '<button type="button" id="delete_module" href="'.tep_href_link('checkout.php', 'module='.$_GET['module']).'&action=delete_module" class="btn btn-danger pull-right">'.Translate('Verwijder module').'</button>';
			echo '<button type="button" id="add_new" class="btn btn-primary pull-right" style="margin-right:5px;">'.Translate('Voeg een instantie toe').'</button>';
		}
		echo '</h1>';
		echo '<hr />';
		if (isset($_POST['action']) && $_POST['action'] == 'save') {
			if (isset($_POST['delete'])) {
				/********************/
				/*	Delete instance	*/
				/********************/
				tep_db_query('DELETE FROM checkout_'.get_class($this).' WHERE id = "'.$_POST['delete'].'"');
			} else {
				/********************/
				/*	Save changes	*/
				/********************/
				$instances = array();
				unset($_POST['action']);
				foreach($_POST as $key=>$data) {
					foreach ($data as $id=>$value) {
						if ($key == 'zone' || $key == 'shipping_module') {
							if (isset($value['*'])) {
								$instances[$id][$key] = '*';
							} else {
								$instances[$id][$key] = implode(';', $value);
							}
						} else {
							$instances[$id][$key] = $value;
						}
					}
				}
				foreach($instances as $id=>$data) {
					if (isset($this->instances[get_class($this).'_'.$id])) {
						tep_db_perform('checkout_'.get_class($this), $data, 'update', 'id="'.$id.'"');
					} else {
						$data['id'] = $id;
						tep_db_perform('checkout_'.get_class($this), $data, 'insert');
					}
				}
			}
			$this->update_instances();
		} else if (isset($_GET['action']) && $_GET['action'] == 'delete_module') {
			/********************/
			/*	Delete module	*/
			/********************/
			unset($_GET['action']);
			parent::delete_module(get_class($this));
		}
		foreach($this->instances as $instance=>$instance_data) {
		?>
		<form name="<?php echo $instance;?>" class="form-horizontal well" action="<?php echo tep_href_link('checkout.php', 'module='.$_GET['module']);?>" method="post">
			<input type="hidden" name="action" value="save" />
			<fieldset>
			<legend>
				<?php echo $instance;?>
				<button class="btn btn-danger pull-right" type="submit" name="delete" value="<?php echo $instance_data['id'];?>"><?php echo Translate('Verwijderen');?></button>
			</legend>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_title"><?php echo Translate('Titel');?></label>
				<div class="controls">
					<input type="text" name="title[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['title'];?>" class="input-xlarge" id="<?php echo $instance;?>_title" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_status"><?php echo Translate('Status');?></label>
				<div class="controls">
					<div class="btn-group" data-toggle="buttons-radio">
						<button type="submit" name="status[<?php echo $instance_data['id'];?>]" value="true" class="btn<?php echo ($instance_data['status'] == 'true'?' active':'');?>"><?php echo Translate('Actief');?></button>
						<button type="submit" name="status[<?php echo $instance_data['id'];?>]" value="false" class="btn<?php echo ($instance_data['status'] == 'false'?' active':'');?>"><?php echo Translate('Niet actief');?></button>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_ipn_id"><?php echo Translate('IPN ID');?></label>
				<div class="controls">
					<input type="text" name="ipn_id[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['ipn_id'];?>" class="input-xlarge" id="<?php echo $instance;?>_ipn_id" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_pdt_identy_token"><?php echo Translate('PDT Identity token');?></label>
				<div class="controls">
					<input type="text" name="pdt_identy_token[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['pdt_identy_token'];?>" class="input-xlarge" id="<?php echo $instance;?>_pdt_identy_token" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_mode"><?php echo Translate('Mode');?></label>
				<div class="controls">
					<div class="btn-group" data-toggle="buttons-radio">
						<button type="submit" name="mode[<?php echo $instance_data['id'];?>]" value="test" class="btn<?php echo ($instance_data['mode'] == 'test'?' active':'');?>"><?php echo Translate('Test');?></button>
						<button type="submit" name="mode[<?php echo $instance_data['id'];?>]" value="prod" class="btn<?php echo ($instance_data['mode'] == 'prod'?' active':'');?>"><?php echo Translate('Productie');?></button>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_description"><?php echo Translate('Omschrijving');?></label>
				<div class="controls">
					<input type="text" name="description[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['description'];?>" class="input-xxlarge" id="<?php echo $instance;?>_description" />
					<span class="help-block"><?php echo Translate('Omschrijving gebruikt in de checkout, samen met de titel');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_order_status_id"><?php echo Translate('Bestel status');?></label>
				<div class="controls">
					<select name="order_status_id[<?php echo $instance_data['id'];?>]" class="input-xlarge" id="<?php echo $instance;?>_order_status_id">
						<?php
						$statusses = parent::get_order_statusses();
						foreach($statusses as $id=>$name) {
							echo '<option value="'.$id.'"'.($instance_data['order_status_id'] == $id?' selected="selected"':'').'>'.$name.'</option>';
						}
						?>
					</select>
					<span class="help-block"><?php echo Translate('De status na het plaatsen van de bestelling met deze methode.');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo Translate('Verzendmethode');?></label>
				<div class="controls">
					<label class="checkbox inline"><?php echo Translate('All');?>
					<input type="checkbox" name="shipping_module[<?php echo $instance_data['id'];?>][*]" <?php echo ($instance_data['shipping_module'] == '*'?' checked="checked"':'');?> />
					</label>
					<?php
					$shipping_modules = explode(';', $instance_data['shipping_module']);
					foreach($Modules->modules['shipping'] as $module) {
						global $$module;
						if (isset($$module->instances)) {
							foreach($$module->instances as $shipping_instance=>$shipping_instance_data) {
								echo '<label class="checkbox inline">'.$shipping_instance_data['title'];
								echo '<input type="checkbox" name="shipping_module['.$instance_data['id'].']['.$shipping_instance.']" value="'.$shipping_instance.'"'.(in_array($shipping_instance, $shipping_modules)?' checked="checked"':'').' />';
								echo '</label>';
							}
						} else {
							echo '<label class="checkbox inline">'.$$module->config['title'];
							echo '<input type="checkbox" name="shipping_module['.$instance_data['id'].']['.$module.']" value="'.$module.'"'.(in_array($module, $shipping_modules)?' checked="checked"':'').' />';
							echo '</label>';
						}
					}
					?>
					<span class="help-block"><?php echo Translate('Voor welke verzendmethodes is deze methode actief');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo Translate('Zone');?></label>
				<div class="controls">
					<label class="checkbox inline"><?php echo Translate('All');?>
					<input type="checkbox" name="zone[<?php echo $instance_data['id'];?>][*]" value="*" <?php echo ($instance_data['zone'] == '*'?' checked="checked"':'');?> />
					</label>
					<?php
					$selected_zones = explode(';', $instance_data['zone']);
					$zones = parent::get_all_zones();
					foreach ($zones as $zone_id=>$zone_name) {
						echo '<label class="checkbox inline">'.$zone_name;
						echo '<input type="checkbox" name="zone['.$instance_data['id'].']['.$zone_id.']" value="'.$zone_id.'"'.(in_array($zone_id, $selected_zones)?' checked="checked"':'').' />';
						echo '</label>';
					}
					?>
					<span class="help-block"><?php echo Translate('Voor welke zone is deze methode actief');?></span>
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary" type="submit"><?php echo Translate('Opslaan');?></button>
				<button class="btn" type="reset"><?php echo Translate('Annuleren');?></button>
			</div>
			</fieldset>
		</form>
		<?php
		}
		end($this->instances);
		$new_id = end(explode('_', key($this->instances)))+1;
		?>
		<div id="new_instance" style="display:none;">
		<form name="<?php echo get_class($this).'_'.$new_id;?>" class="form-horizontal well" action="<?php echo tep_href_link('checkout.php', 'module='.$_GET['module']);?>" method="post">
			<input type="hidden" name="action" value="save" />
			<fieldset>
			<legend>
				<?php echo get_class($this).'_'.$new_id;?>
			</legend>
			<div class="control-group">
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_title"><?php echo Translate('Titel');?></label>
				<div class="controls">
					<input type="text" name="title[<?php echo $new_id;?>]" value="" class="input-xlarge" id="<?php echo get_class($this).'_'.$new_id;?>_title" />
				</div>
			</div>
			<input type="hidden" name="status[<?php echo $new_id;?>]" value="false" />
			<div class="control-group">
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_ipn_id"><?php echo Translate('IPN ID');?></label>
				<div class="controls">
					<input type="text" name="ipn_id[<?php echo $new_id;?>]" value="" class="input-xlarge" id="<?php echo get_class($this).'_'.$new_id;?>_ipn_id" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_pdt_identy_token"><?php echo Translate('PDT Identity token');?></label>
				<div class="controls">
					<input type="text" name="pdt_identy_token[<?php echo $new_id;?>]" value="" class="input-xlarge" id="<?php echo get_class($this).'_'.$new_id;?>_pdt_identy_token" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_description"><?php echo Translate('Omschrijving');?></label>
				<div class="controls">
					<input type="text" name="description[<?php echo $new_id;?>]" value="" class="input-xxlarge" id="<?php echo get_class($this).'_'.$new_id;?>_description" />
					<span class="help-block"><?php echo Translate('Omschrijving gebruikt in de checkout, samen met de titel');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_order_status_id"><?php echo Translate('Bestel status');?></label>
				<div class="controls">
					<select name="order_status_id[<?php echo $new_id;?>]" class="input-xlarge" id="<?php echo get_class($this).'_'.$new_id;?>_order_status_id">
						<?php
						$statusses = parent::get_order_statusses();
						foreach($statusses as $id=>$name) {
							echo '<option value="'.$id.'">'.$name.'</option>';
						}
						?>
					</select>
					<span class="help-block"><?php echo Translate('De status na het plaatsen van de bestelling met deze methode.');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo Translate('Verzendmethode');?></label>
				<div class="controls">
					<label class="checkbox inline"><?php echo Translate('All');?>
					<input type="checkbox" name="shipping_module[<?php echo $new_id;?>][*]" />
					</label>
					<?php
					$shipping_modules = explode(';', $instance_data['shipping_module']);
					foreach($Modules->modules['shipping'] as $module) {
						global $$module;
						if (isset($$module->instances)) {
							foreach($$module->instances as $shipping_instance=>$shipping_instance_data) {
								echo '<label class="checkbox inline">'.$shipping_instance_data['title'];
								echo '<input type="checkbox" name="shipping_module['.$new_id.']['.$shipping_instance.']" value="'.$shipping_instance.'" />';
								echo '</label>';
							}
						} else {
							echo '<label class="checkbox inline">'.$$module->config['title'];
							echo '<input type="checkbox" name="shipping_module['.$new_id.']['.$module.']" value="'.$module.'"'.(in_array($module, $shipping_modules)?' checked="checked"':'').' />';
							echo '</label>';
						}
					}
					?>
					<span class="help-block"><?php echo Translate('Voor welke verzendmethodes is deze methode actief');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo Translate('Zone');?></label>
				<div class="controls">
					<label class="checkbox inline"><?php echo Translate('All');?>
					<input type="checkbox" name="zone[<?php echo $new_id;?>][*]" value="*" />
					</label>
					<?php
					$zones = parent::get_all_zones();
					foreach ($zones as $zone_id=>$zone_name) {
						echo '<label class="checkbox inline">'.$zone_name;
						echo '<input type="checkbox" name="zone['.$new_id.']['.$zone_id.']" value="'.$zone_id.'" />';
						echo '</label>';
					}
					?>
					<span class="help-block"><?php echo Translate('Voor welke zone is deze methode actief');?></span>
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary" type="submit"><?php echo Translate('Opslaan');?></button>
				<button class="btn" type="reset"><?php echo Translate('Annuleren');?></button>
			</div>
			</fieldset>
		</form>
		</div>
		<?php
	}
	private function install() {
		//Check if translations are available
		parent::checkTranslations(dirname(__FILE__), $this->getTranslations());
		$install_array = array(
			'status' => 'true',
			'title' => 'Betaling met Paypal',
			'description' => '',
			'zone' => '*',
			'order_status_id' => '1',
			'shipping_module' => '*', //multiple are possible: Flat_1;Flat_2
			'ipn_id' => '',
			'pdt_identy_token' => '',
			'mode' => 'test'
		);
		tep_db_query('CREATE TABLE checkout_'.get_class($this).'(
			id INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			status VARCHAR(255),
			title VARCHAR(255),
			description VARCHAR(255),
			zone VARCHAR(255),
			order_status_id INT(11),
			shipping_module VARCHAR(255),
			ipn_id VARCHAR(255),
			pdt_identy_token VARCHAR(255),
			mode VARCHAR(255))');
		tep_db_query('CREATE INDEX title ON checkout_'.get_class($this).' (`title`)');
		tep_db_query('CREATE INDEX status ON checkout_'.get_class($this).' (`status`)');
		tep_db_query('CREATE INDEX zone ON checkout_'.get_class($this).' (`zone`)');
		tep_db_query('CREATE INDEX shipping_module ON checkout_'.get_class($this).' (`shipping_module`)');
		tep_db_perform('checkout_'.get_class($this), $install_array, 'insert');
	}
	private function getTranslations() {
		return array('Opgelet!' => array(
							'1' => 'Opgelet!', 
							'2' => 'Attention!', 
							'3' => 'Attention!', 
							'4' => 'Achtung!')
					,'Kies a.u.b. een betaalmethode.' => array(
							'1' => 'Kies a.u.b. een betaalmethode.', 
							'2' => 'Choisissez une méthode de paiement, s\'il vous plaît.', 
							'3' => 'Choose a payment method if you please.', 
							'4' => 'Wählen Sie eine Zahlungsmethode, wenn Sie wollen.')
					,'Betaling met Paypal' => array(
							'1' => 'Betaling met Paypal', 
							'2' => 'Paiement par Paypal', 
							'3' => 'Payment by Paypal', 
							'4' => 'Bezahlung per Paypal')
					,'Vervolledig orderbevestiging' => array(
							'1' => 'Vervolledig orderbevestiging', 
							'2' => 'La confirmation complète', 
							'3' => 'Complete confirmation', 
							'4' => 'Komplette Bestätigung')
					,'Bestelgegevens worden doorgestuurd naar de beveiligde betaal server, even geduld...' => array(
							'1' => 'Bestelgegevens worden doorgestuurd naar de beveiligde betaal server, even geduld...', 
							'2' => 'Informations de commande est redirigé vers le serveur de paiement sécurisé, s\'il vous plaît patienter ...', 
							'3' => 'Ordering information is redirected to the secure payment server, please wait ...', 
							'4' => 'Bestell-Information wird an die sichere Zahlung Server umgeleitet, bitte warten ...')
					,'Uw betaling werd geannuleerd.' => array(
							'1' => 'Uw betaling werd geannuleerd.', 
							'2' => 'Votre paiement a été annulé.', 
							'3' => 'Your payment was canceled.', 
							'4' => 'Ihre Zahlung wurde storniert.')
					,'Er is iets fout gelopen met paypal bestelling' => array(
							'1' => 'Er is iets fout gelopen met paypal bestelling', 
							'2' => 'Quelque chose se passait mal avec l\'ordre paypal', 
							'3' => 'Something went wrong with paypal order', 
							'4' => 'Irgendetwas ging schief, um mit PayPal')
					,'Voor bestelling %s is er een fout gegenereerd! Controleer dit a.u.b. voordat u deze bestelling verder verwerkt.<br />Als u vragen hebt i.v.m. de fout contacteer dan ABO Service!' => array(
							'1' => 'Voor bestelling %s is er een fout gegenereerd! Controleer dit a.u.b. voordat u deze bestelling verder verwerkt.<br />Als u vragen hebt i.v.m. de fout contacteer dan ABO Service!', 
							'2' => 'Pour commander %s est une erreur générée! S\'il vous plaît vérifier avant de commander un traitement ultérieur. <br /> Si vous avez des questions concernant la faute avec ABO Service!', 
							'3' => 'To order %s is an error generated! Please check before ordering further processed. <br /> If you have questions regarding the fault contact ABO Service!', 
							'4' => 'Um %s bestellen, wird ein Fehler generiert! Bitte überprüfen Sie vor der Bestellung weiter verarbeitet werden. <br /> Wenn Sie Fragen zu die Schuld Kontakt ABO Service!')
					,'Status onzeker paypal bestelling' => array(
							'1' => 'Status onzeker paypal bestelling', 
							'2' => 'Statut afin incertain paypal', 
							'3' => 'Status uncertain paypal order', 
							'4' => 'Status unsicher paypal bestellen')
					,'De status voor bestelling %s is onzeker doordat de autorisatie verlopen was op het moment dat de klant terug op de shop kwam.' => array(
							'1' => 'De status voor bestelling %s is onzeker doordat de autorisatie verlopen was op het moment dat de klant terug op de shop kwam.', 
							'2' => 'Le statut de l ordre %s est incertain en ce que l\'autorisation a été menée au moment où le client vers le magasin est venu.', 
							'3' => 'The status of order %s is uncertain in that the authorization was conducted at the time that the customer back to the shop came.', 
							'4' => 'Der Status der Bestellung %s ist nicht sicher, dass die Genehmigung zu der Zeit, dass der Kunde zurück in den Laden kam, wurde durchgeführt.')
					,'Er is een fout opgetreden bij de betaling. Contacteer ons voor meer info.' => array(
							'1' => 'Er is een fout opgetreden bij de betaling. Contacteer ons voor meer info.', 
							'2' => 'Il ya une erreur avec le transfert. Contactez-nous pour plus d\'informations.', 
							'3' => 'There is an error with the transfer. Contact us for more info.', 
							'4' => 'Es ist ein Fehler mit der Übertragung. Kontaktieren Sie uns für weitere Informationen.')
					,'Er is iets fout gelopen met paypal bestelling' => array(
							'1' => 'Er is iets fout gelopen met paypal bestelling', 
							'2' => 'Quelque chose se passait mal avec l\'ordre paypal', 
							'3' => 'Something went wrong with paypal order', 
							'4' => 'Irgendetwas ging mit PayPal um falsch')
					,'Er is een fout opgetreden bij de betaling. Contacteer ons voor meer info.' => array(
							'1' => 'Er is een fout opgetreden bij de betaling. Contacteer ons voor meer info.', 
							'2' => 'Il ya une erreur avec le transfert. Contactez-nous pour plus d\'informations.', 
							'3' => 'There is an error with the transfer. Contact us for more info.', 
							'4' => 'Es ist ein Fehler mit der Übertragung. Kontaktieren Sie uns für weitere Informationen.')
					,'Voeg een instantie toe' => array(
							'1' => 'Voeg een instantie toe',
							'2' => 'Ajoutez une instance',
							'3' => 'Add an instance',
							'4' => 'Fügen Sie eine Instanz zu')
					,'Verwijder module' => array(
							'1' => 'Verwijder module', 
							'2' => 'Retirez le module', 
							'3' => 'Remove module', 
							'4' => 'Modul entfernen')
					,'Verwijderen' => array(
							'1' => 'Verwijderen',
							'2' => 'Supprimer',
							'3' => 'Remove',
							'4' => 'Entfernen')
					,'Titel' => array(
							'1' => 'Titel',
							'2' => 'Titre',
							'3' => 'Title',
							'4' => 'Titel')
					,'Status' => array(
							'1' => 'Status',
							'2' => 'Statut',
							'3' => 'Status',
							'4' => 'Status')
					,'Actief' => array(
							'1' => 'Actief',
							'2' => 'Actif',
							'3' => 'Active',
							'4' => 'Aktiv')
					,'Niet actief' => array(
							'1' => 'Niet actief',
							'2' => 'Inactif',
							'3' => 'Inactive',
							'4' => 'Inaktiv')
					,'Omschrijving gebruikt in de checkout, samen met de titel' => array(
							'1' => 'Omschrijving gebruikt in de checkout, samen met de titel',
							'2' => 'Description de l\'utiliser dans la caisse, ainsi que le titre',
							'3' => 'Description used in the checkout, along with the title',
							'4' => 'Beschreibung an der Kasse verwendet, zusammen mit dem Titel')
					,'Omschrijving' => array(
							'1' => 'Omschrijving',
							'2' => 'Description',
							'3' => 'Description',
							'4' => 'Beschreibung')
					,'Bestel status' => array(
							'1' => 'Bestelstatus',
							'2' => 'Suivi de commande',
							'3' => 'Order Status',
							'4' => 'Status der Bestellung')
					,'De status na het plaatsen van de bestelling met deze methode.' => array(
							'1' => 'De status na het plaatsen van de bestelling met deze methode.',
							'2' => 'Le statut après avoir passé commande avec cette méthode.',
							'3' => 'The status after placing the order with this method.',
							'4' => 'Der Zustand nach der Bestellung mit dieser Methode.')
					,'Verzendmethode' => array(
							'1' => 'Verzendmethode',
							'2' => 'Méthode d\'expédition',
							'3' => 'Shipping Method',
							'4' => 'Liefer-Methode')
					,'All' => array(
							'1' => 'All',
							'2' => 'Tous',
							'3' => 'All',
							'4' => 'Alle')
					,'Voor welke verzendmethodes is deze methode actief' => array(
							'1' => 'Voor welke verzendmethodes is deze methode actief',
							'2' => 'Modes de livraison où cette méthode fonctionne',
							'3' => 'Shipping methods which this method operates',
							'4' => 'Liefer-Methoden, die diese Methode operiert')
					,'Zone' => array(
							'1' => 'Zone',
							'2' => 'Zone',
							'3' => 'Zone',
							'4' => 'Zone')
					,'Voor welke zone is deze methode actief' => array(
							'1' => 'Voor welke zone is deze methode actief',
							'2' => 'Pour la zone qui, cette méthode est active',
							'3' => 'To which zone, this method is active',
							'4' => 'Um welche Zone, ist diese Methode aktiv')
					,'Opslaan' => array(
							'1' => 'Opslaan',
							'2' => 'Rappeler',
							'3' => 'Save',
							'4' => 'Merken')
					,'Annuleren' => array(
							'1' => 'Annuleren',
							'2' => 'Annuler',
							'3' => 'Cancel',
							'4' => 'Stornieren')
					,'IPN ID' => array(
							'1' => 'IPN ID',
							'2' => 'IPN ID',
							'3' => 'IPN ID',
							'4' => 'IPN ID')
					,'PDT Identity token' => array(
							'1' => 'PDT Identity token',
							'2' => 'PDT Identity token',
							'3' => 'PDT Identity token',
							'4' => 'PDT Identity token')
					,'Test' => array(
							'1' => 'Test',
							'2' => 'Test',
							'3' => 'Test',
							'4' => 'Test')
					,'Productie' => array(
							'1' => 'Productie',
							'2' => 'Production',
							'3' => 'Production',
							'4' => 'Produktion')
					,'Mode' => array(
							'1' => 'Mode',
							'2' => 'Mode',
							'3' => 'Mode',
							'4' => 'Mode')
					);
	}
}
?>