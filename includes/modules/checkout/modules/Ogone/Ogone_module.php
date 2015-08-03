<?php
//payment class for cod => cash on delivery
class Ogone extends Modules {
	public $type = 'payment'
		 , $sort_order = 30
		 , $instances = array();
	public function __construct() {
		//load config => title, text, sort_order, status, zone, order_status_id, works_with_shipping_module
		if(tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'checkout_".get_class($this)."'"))< 1) {
			$this->install();
		}
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		while ($array = tep_db_fetch_array($query)) {
			$this->instances[get_class($this).'_'.$array['id']] = $array;
		}
	}
	public function update_instances() {
		$this->instances = array();
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		while ($array = tep_db_fetch_array($query)) {
			$this->instances[get_class($this).'_'.$array['id']] = $array;
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
					$html .= '<div class="'.$this->type.'_title">&nbsp; '.Translate($data['title']).'</div>';
					if (!empty($data['description'])) {
						$html .= '<div class="'.$this->type.'_description">&nbsp; '.Translate($data['description']).'</div>';
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
		foreach($this->instances as $instance=>$data) {
			$instance_images = explode(';', $data['method_images']);
			$images = array_merge($instance_images, $images);
		}
		foreach($images as $key=>$image) {
			$images[strtolower(substr($image, 0, strrpos($image, '.')))] = HTTP_SERVER.DIR_WS_HTTP_CATALOG.DIR_WS_MODULES.'checkout/modules/'.get_class($this).'/img/methods/'.$image;
			unset($images[$key]);
		}
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
		global $temp_orders_id, $customer_id, $currency;
		if (!isset($_GET['SHASIGN'])) {//only process if we don't come back from ogone
			$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
			foreach($temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
				if ($data['class'] == 'order_total') {
					$order_total = $data['value'];
				}
			}
			$ogone_amount = number_format($order_total * 100, 0, '', '');
			if (!empty($customer_id)) {
				$com_data = STORE_NAME.' '.Translate('bestelling. Klant #:').' '.$customer_id;
			} else {
				$com_data = STORE_NAME.' '.Translate('bestelling. Onbekende Klant');
			}
			$data = array(
				'ACCEPTURL' => tep_href_link(FILENAME_CHECKOUT, '', 'SSL'),
				'AMOUNT' => $ogone_amount,
				'BGCOLOR' => 'white',
				'BUTTONBGCOLOR' => 'white',
				'BUTTONTXTCOLOR' => 'black',
				'CANCELURL' => tep_href_link(FILENAME_CHECKOUT),
				'CATALOGURL' => tep_href_link(FILENAME_DEFAULT),
				'CN' => trim($temp_data[$temp_orders_id]['orders']['customers_name']),
				'COM' => $com_data,
				'CURRENCY' => $currency,
				'DECLINEURL' => tep_href_link(FILENAME_CHECKOUT),
				'EMAIL' => $temp_data[$temp_orders_id]['orders']['customers_email_address'],
				'EXCEPTIONURL' => tep_href_link(FILENAME_CHECKOUT),
				'FONTTYPE' => 'Verdana',
				'LANGUAGE' => 'en_US',
				'ORDERID' => $temp_orders_id.'||'.date("Y-m-d H:i:s"),
				'OWNERADDRESS' => $temp_data[$temp_orders_id]['orders']['delivery_street_address'],
				'OWNERZIP' => $temp_data[$temp_orders_id]['orders']['delivery_postcode'],
				'PARAMPLUS' => 'osCsid='.tep_session_id().'&customer_id='.$customer_id,
				'PMLISTTYPE' => '2',
				'PSPID' => $this->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['pspid'],
				'TBLBGCOLOR' => 'white',
				'TBLTXTCOLOR' => 'black',
				'TITLE' => STORE_NAME,
				'TP' => tep_href_link('includes/modules/checkout/modules/Ogone/ogone_template.php'),
				'LOGO' => tep_href_link('images/mail/logo.jpg'),
				'TXTCOLOR' => 'black'
			);
			$sha_data = '';
			ksort($data);
			foreach ($data as $key=>$value) {
				if (!empty($value)) {
					$process_button_string .= tep_draw_hidden_field($key, $value);
					$sha_data .= strtoupper($key).'='.$value.$this->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['sha_string'];
				}
			}
			$process_button_string .= tep_draw_hidden_field('SHASIGN', strtoupper(sha1($sha_data)));
			$html = '</div><div class="span12">';
			$html .= '<div class="step active"><div class="step_title">'.Translate('Betaling via beveiligde Ogone server').'</div></div>';
			$html .= '<form name="redirectForm" action="https://secure.ogone.com/ncol/'.$this->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['mode'].'/orderstandard.asp" method="POST">';
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
		$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);//get all orders data
		$data = '';
		foreach($_GET as $key=>$value) {
			$data .= $key.': '."\n";
			$data .= $value."\n\n";
		}
		tep_db_query('INSERT INTO payment_log (type, data, date) VALUES ("'.get_class($this).'", "'.$data.'", NOW())');
		tep_db_query('DELETE FROM payment_log WHERE date < DATE_SUB(NOW(), INTERVAL 30 DAY)');
		/*PAYMENT ACCEPTED*/
		if ($_GET['STATUS'] == '9') {
			tep_db_query('UPDATE temp_orders SET orders_status = 2 WHERE orders_id = "'.$temp_orders_id.'"');
		}
		/*PAYMENT AUTHORISED*/
		else if ($_GET['STATUS'] == '5') {
			tep_db_query('UPDATE temp_orders SET orders_status = 2 WHERE orders_id = "'.$temp_orders_id.'"');
		}
		/*WAIT FOR ACCEPTANCE */
		else if ($_GET['STATUS'] == '51' || $_GET['STATUS'] == '91' || $_GET['STATUS'] == '4' || $_GET['STATUS'] == '41' || $_GET['STATUS'] == '52' || $_GET['STATUS'] == '59' || $_GET['STATUS'] == '92') {
			//do nothing
		}
		/*PAYMENT DECLINED*/
		else if ($_GET['STATUS'] == '2' || $_GET['STATUS'] == '84' || $_GET['STATUS'] == '93') {
			tep_db_query('UPDATE temp_orders SET orders_status = 53 WHERE orders_id = "'.$temp_orders_id.'"');
			Checkout::send_order_error_mail(Translate('Ogone betaling geweigerd voor bestelling').': '.$temp_orders_id, sprintf(Translate('De betaling voor bestelling %s is geweigerd door ogone.'), $temp_orders_id));
			$_GET['force_checkout_step'] = Checkout::get_step_for_type($this->type);
			$this->errors[$temp_data[$temp_orders_id]['orders']['payment_method']] = Translate('Uw betaling werd geweigerd.');
		}
		/*PAYMENT CANCELED*/
		else if ($_GET['STATUS'] == '1' || $_GET['STATUS'] == '6' || $_GET['STATUS'] == '64' || $_GET['STATUS'] == '7' || $_GET['STATUS'] == '74') {
			tep_db_query('UPDATE temp_orders SET orders_status = 50 WHERE orders_id = "'.$temp_orders_id.'"');
			$_GET['force_checkout_step'] = Checkout::get_step_for_type($this->type);
			$this->errors[$temp_data[$temp_orders_id]['orders']['payment_method']] = Translate('Uw betaling werd geannuleerd.');
		}
		/*PAYMENT NOT VALID*/
		else if ($_GET['STATUS'] == '0') {
			tep_db_query('UPDATE temp_orders SET orders_status = 53 WHERE orders_id = "'.$temp_orders_id.'"');
			Checkout::send_order_error_mail(Translate('Ongeldige Ogone betaling voor bestelling').': '.$temp_orders_id, sprintf(Translate('De betaling voor bestelling %s is ongeldig verklaard door ogone.'), $temp_orders_id));
			$_GET['force_checkout_step'] = Checkout::get_step_for_type($this->type);
			$this->errors[$temp_data[$temp_orders_id]['orders']['payment_method']] = Translate('Uw betaling is ongeldig.');
		}
      return $this->errors;
    }
	public function after_extern_process() {
		global $temp_orders_id;
		if (isset($_GET['SHASIGN'])) {
			include(DIR_WS_CLASSES . 'sha.php');
			$get_params = array();
			foreach($_GET as $key=>$value) {
				if ($key != 'SHASIGN' && $key != 'force_checkout_step' && $key != 'customer_id' && $key != 'osCsid' && trim($value) != '') {
					$get_params[strtoupper($key)] = $value;
				}
			}
			ksort($get_params);
			$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
			$hash_string = '';
			foreach($get_params as $key=>$value) {
				$hash_string .= strtoupper($key).'='.$value.$this->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['sha_string'];
			}
			$sha = new SHA;
			$hasharray = $sha->hash_string($hash_string);
			$hash = strtoupper($sha->hash_to_string($hasharray));
			if ($_GET['SHASIGN'] != $hash) {	//wrong ogone parameters
				$this->errors[$temp_data[$temp_orders_id]['orders']['payment_method']] = Translate('Er is iets fout gelopen bij de afhandeling van uw betaling. Contacteer ons voor meer uitleg.');
			}
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
				if (!isset($_POST['method_images'])) {
					foreach($this->instances as $key=>$data) {
						$_POST['method_images'][$data['id']] = array();
					}
				}
				foreach($_POST as $key=>$data) {
					foreach ($data as $id=>$value) {
						if ($key == 'zone' || $key == 'shipping_module') {
							if (isset($value['*'])) {
								$instances[$id][$key] = '*';
							} else {
								$instances[$id][$key] = implode(';', $value);
							}
						} else if ($key == 'method_images') {
							$instances[$id][$key] = implode(';', $value);
						}  else {
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
				<label class="control-label" for="<?php echo $instance;?>_pspid"><?php echo Translate('PSP ID');?></label>
				<div class="controls">
					<input type="text" name="pspid[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['pspid'];?>" class="input-xlarge" id="<?php echo $instance;?>_pspid" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_sha_string"><?php echo Translate('SHA String');?></label>
				<div class="controls">
					<input type="text" name="sha_string[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['sha_string'];?>" class="input-xlarge" id="<?php echo $instance;?>_sha_string" />
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
			<div class="control-group">
				<label class="control-label"><?php echo Translate('Afbeeldingen actieve methodes');?></label>
				<div class="controls clearfix">
					<ul class="thumbnails">
						<?php
						$selected = explode(';', $instance_data['method_images']);
						$moddir = dirname(__FILE__).'/img/methods';
						$files = $this->listFolderFiles($moddir);
						foreach($files as $file) {
							$file = basename($file);
							echo '<li>';
							echo '<label for="'.$file.'" class="thumbnail" style="text-align:center;">';
							echo '<img src="'.HTTP_SERVER.DIR_WS_HTTP_CATALOG.DIR_WS_MODULES.'checkout/modules/'.get_class($this).'/img/methods/'.$file.'" />';
							echo '<input type="checkbox" name="method_images['.$instance_data['id'].'][]" value="'.$file.'"'.(in_array($file, $selected)?' checked="checked"':'').' id="'.$file.'" />';
							echo '</label>';
							echo '</li>';
						}
						?>
					</ul>
					<span class="help-block"><?php echo Translate('Selecteer de afbeeldingen van de methodes die actief zijn. Deze afbeeldingen worden getoond in de checkout.');?></span>
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
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_pspid"><?php echo Translate('PSP ID');?></label>
				<div class="controls">
					<input type="text" name="pspid[<?php echo $new_id;?>]" value="" class="input-xlarge" id="<?php echo get_class($this).'_'.$new_id;?>_pspid" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_sha_string"><?php echo Translate('SHA String');?></label>
				<div class="controls">
					<input type="text" name="sha_string[<?php echo $new_id;?>]" value="" class="input-xlarge" id="<?php echo get_class($this).'_'.$new_id;?>_sha_string" />
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
			<div class="control-group">
				<label class="control-label"><?php echo Translate('Afbeeldingen actieve methodes');?></label>
				<div class="controls clearfix">
					<ul class="thumbnails">
						<?php
						$moddir = dirname(__FILE__).'/img/methods';
						$files = $this->listFolderFiles($moddir);
						foreach($files as $file) {
							$file = basename($file);
							echo '<li>';
							echo '<label for="'.$file.'" class="thumbnail" style="text-align:center;">';
							echo '<img src="'.HTTP_SERVER.DIR_WS_HTTP_CATALOG.DIR_WS_MODULES.'checkout/modules/'.get_class($this).'/img/methods/'.$file.'" />';
							echo '<input type="checkbox" name="method_images['.$new_id.'][]" value="'.$file.'" id="'.$file.'" />';
							echo '</label>';
							echo '</li>';
						}
						?>
					</ul>
					<span class="help-block"><?php echo Translate('Selecteer de afbeeldingen van de methodes die actief zijn. Deze afbeeldingen worden getoond in de checkout.');?></span>
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
			'title' => 'Betaling via beveiligde Ogone server',
			'description' => '',
			'zone' => '*',
			'order_status_id' => '1',
			'shipping_module' => '*', //multiple are possible: Flat_1;Flat_2
			'pspid' => '',
			'sha_string' => '',
			'mode' => 'test',
			'method_images' => ''
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
			pspid VARCHAR(255),
			sha_string VARCHAR(255),
			mode VARCHAR(255),
			method_images TEXT)');
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
					,'Betaling via beveiligde Ogone server' => array(
							'1' => 'Betaling via beveiligde Ogone server', 
							'2' => 'Paiement sécurisé par Ogone serveur sécurisé', 
							'3' => 'Payment by secure server Ogone', 
							'4' => 'Zahlung per Secure Server Ogone')
					,'bestelling. Klant #:' => array(
							'1' => 'bestelling. Klant #:', 
							'2' => 'ordre. N ° de client:', 
							'3' => 'order. Customer #:', 
							'4' => 'ordnung. Kundennummer:')
					,'bestelling. Onbekende Klant' => array(
							'1' => 'bestelling. Onbekende Klant', 
							'2' => 'ordre. client inconnu', 
							'3' => 'order. unknown Customer', 
							'4' => 'Ordnung. unbekannten Kunden')
					,'Bestelgegevens worden doorgestuurd naar de beveiligde betaal server, even geduld...' => array(
							'1' => 'Bestelgegevens worden doorgestuurd naar de beveiligde betaal server, even geduld...', 
							'2' => 'Informations de commande est redirigé vers le serveur de paiement sécurisé, s\'il vous plaît patienter ...', 
							'3' => 'Ordering information is redirected to the secure payment server, please wait ...', 
							'4' => 'Bestell-Information wird an die sichere Zahlung Server umgeleitet, bitte warten ...')
					,'Ogone betaling geweigerd voor bestelling' => array(
							'1' => 'Ogone betaling geweigerd voor bestelling', 
							'2' => 'Ogone a refusé de payer de l\'ordre', 
							'3' => 'Ogone refused payment for order', 
							'4' => 'Ogone verweigert Zahlung für Ordnung')
					,'De betaling voor bestelling %s is geweigerd door ogone.' => array(
							'1' => 'De betaling voor bestelling %s is geweigerd door ogone.', 
							'2' => 'L\'ordre de paiement est rejeté par la société Ogone %s.', 
							'3' => 'The payment order is rejected by %s ogone.', 
							'4' => 'Der Zahlungsauftrag wird von Ogone %s abgelehnt.')
					,'Uw betaling werd geweigerd.' => array(
							'1' => 'Uw betaling werd geweigerd.', 
							'2' => 'Votre paiement a été refusé.', 
							'3' => 'Your payment was declined.', 
							'4' => 'Ihre Zahlung wurde abgelehnt.')
					,'Uw betaling werd geannuleerd.' => array(
							'1' => 'Uw betaling werd geannuleerd.', 
							'2' => 'Votre paiement a été annulé.', 
							'3' => 'Your payment was canceled.', 
							'4' => 'Ihre Zahlung wurde storniert.')
					,'Ongeldige Ogone betaling voor bestelling' => array(
							'1' => 'Ongeldige Ogone betaling voor bestelling', 
							'2' => 'Blancs de paiement Ogone pour l\'ordre', 
							'3' => 'Invalid Ogone payment for order', 
							'4' => 'Ungültige Ogone Zahlung für Bestellung')
					,'De betaling voor bestelling %s is ongeldig verklaard door ogone.' => array(
							'1' => 'De betaling voor bestelling %s is ongeldig verklaard door ogone.', 
							'2' => 'Paiement pour %s commande est déclarée invalide par Ogone.', 
							'3' => 'Payment for order %s is declared invalid by ogone.', 
							'4' => 'Die Bezahlung der Bestellung %s ist ungültig erklärt von Ogone.')
					,'Uw betaling is ongeldig.' => array(
							'1' => 'Uw betaling is ongeldig.', 
							'2' => 'Votre paiement est invalide.', 
							'3' => 'Your payment is invalid.', 
							'4' => 'Ihre Zahlung ist ungültig.')
					,'Er is iets fout gelopen bij de afhandeling van uw betaling. Contacteer ons voor meer uitleg.' => array(
							'1' => 'Er is iets fout gelopen bij de afhandeling van uw betaling. Contacteer ons voor meer uitleg.', 
							'2' => 'Quelque chose a mal tourné dans le traitement de votre paiement. Contactez-nous pour plus d\'informations.', 
							'3' => 'Something went wrong in the handling of your payment. Contact us for more information.', 
							'4' => 'Irgendetwas ging bei der Bearbeitung Ihrer Zahlung falsch. Kontaktieren Sie uns für weitere Informationen.')
					,'Verwijder module' => array(
							'1' => 'Verwijder module', 
							'2' => 'Retirez le module', 
							'3' => 'Remove module', 
							'4' => 'Modul entfernen')
					,'Voeg een instantie toe' => array(
							'1' => 'Voeg een instantie toe',
							'2' => 'Ajoutez une instance',
							'3' => 'Add an instance',
							'4' => 'Fügen Sie eine Instanz zu')
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
					,'PSP ID' => array(
							'1' => 'PSP ID',
							'2' => 'PSP ID',
							'3' => 'PSP ID',
							'4' => 'PSP ID')
					,'SHA String' => array(
							'1' => 'SHA String',
							'2' => 'SHA String',
							'3' => 'SHA String',
							'4' => 'SHA String')
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