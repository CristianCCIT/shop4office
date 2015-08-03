<?php
//payment class for cod => cash on delivery
class Icepay extends Modules {
	public $type = 'payment'
		 , $sort_order = 50
		 , $instances = array()
		 , $temp_data = array()
		 , $order_total = 0
		 , $paymentmethods = array();
	public function __construct() {
		global $temp_orders_id, $currency, $languages_code, $api;
		require_once 'api/icepay_api_basic.php';
		//load config => title, text, sort_order, status, zone, order_status_id, works_with_shipping_module
		if(tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'checkout_".get_class($this)."'"))< 1) {
			$this->install();
		}
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		while ($array = tep_db_fetch_array($query)) {
			$this->instances[get_class($this).'_'.$array['id']] = $array;
		}
		if ($temp_orders_id > 0) {
			$this->temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);//get all orders data
			foreach($this->temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
				if ($data['class'] == 'order_total') {
					$this->order_total = $data['value'];
				}
			}
			$country = tep_get_countries_with_iso_codes($this->temp_data[$temp_orders_id]['orders']['billing_country']);
			// Read paymentmethods from folder, load the classes and filter the data
			$api = Icepay_Api_Basic::getInstance()
			->readFolder(realpath('api/paymentmethods'))
			->prepareFiltering()
			//->filterByCurrency($currency)
			//->filterByCountry($country['countries_iso_code_2'])
			//->filterByLanguage($languages_code)
			->filterByStatus()
			->filterByAmount($this->order_total);
			// Store the filtered data in an array;
			$this->paymentmethods = $api->getArray();
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
		if (count($this->paymentmethods) > 0) {
			foreach($this->instances as $data) {
				if ($data['status'] == 'true') {
					if (parent::checkZone($data['zone'], $temp_data[$temp_orders_id]['orders']['billing_country']) && parent::checkShippingMethod($data['shipping_module'])) {
						return true;
					}
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
		foreach($this->instances as $id=>$data) {
			if ($data['status'] == 'true') {
				//check is active for zones and choosen shipping module
				if (parent::checkZone($data['zone'], $this->temp_data[$temp_orders_id]['orders']['billing_country']) && parent::checkShippingMethod($data['shipping_module'])) {
					if (isset(Checkout::$errors[$id])) {
						$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate(Checkout::$errors[$id]).'</div>';
					}
					$html .= '<label class="control-label" for="'.$this->type.'_'.$id.'" style="display:block;">';
					$html .= '<div class="'.$this->type.'_item clearfix">';
					$html .= '<input type="radio" name="'.$this->type.'" value="'.$id.'" id="'.$this->type.'_'.$id.'"'.($this->temp_data[$temp_orders_id]['orders']['payment_method']==$id?' checked=checked':'').' />';
					$paymentMethods = array();
					foreach($this->paymentmethods as $name => $value){
						$payment = new $value();
						//Store the issuers for this paymentmethod into an array
						$issuers = $payment->getSupportedIssuers();
						foreach($issuers as $issuer=>$issuerData){
							if (isset($issuerData['name'])) {
								$paymentMethods[] = array('value' => $value.'__'.$issuer, 'name' => $issuerData['name']);
							} else {
								$paymentMethods[] = array('value' => $value.'__'.$issuer, 'name' => $name);
							}
						}
					}
					if (count($paymentMethods) > 1) {
						$html .= '<select name="'.$this->type.'_paymentmethod">';
						foreach($paymentMethods as $key => $value){
							$html .= '<option value="'.$value['value'].'">'.$value['name'].'</option>';
						}
						$html .= '</select>';
					} else {
						$html .= '<input type="hidden" name="'.$this->type.'_paymentmethod" value="'.$paymentMethods[0]['value'].'" />';
					}
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
		$moddir = dirname(__FILE__).'/api/paymentmethods';
		$files = $this->listFolderFiles($moddir);
		foreach($this->instances as $instance=>$data) {
			foreach($files as $key=>$file) {
				$name = strtolower(substr(basename($file), 0, -4));
				$className = "Icepay_Paymentmethod_".ucfirst($name);
				$class = new $className();
				foreach($class->_issuer as $key=>$value) {
					if (isset($value['status']) && $value['status'] == 'on') {
						$images[] = $value['image'];
					}
				}
			}
		}
		foreach($images as $key=>$image) {
			$images[strtolower(substr($image, 0, strrpos($image, '.')))] = HTTP_SERVER.DIR_WS_HTTP_CATALOG.DIR_WS_MODULES.'checkout/modules/'.get_class($this).'/img/methods/'.$image;
			unset($images[$key]);
		}
		return $images;
	}
	public function process_data() {
		global $temp_orders_id, $languages_code, $currency;
		$strlen = strlen(get_class($this));
		if (isset($_POST[$this->type])) {
			if (substr($_POST[$this->type], 0, $strlen) == get_class($this)) {
				if ($temp_orders_id == 0) {
					$temp_orders_id = parent::create_order();
				}
				tep_db_query('UPDATE temp_orders SET payment_method = "'.$_POST[$this->type].'", payment_method_extra = "'.$_POST[$this->type.'_paymentmethod'].'", orders_status = "'.$this->instances[$_POST[$this->type]]['order_status_id'].'" WHERE orders_id = "'.$temp_orders_id.'"');
				if(extension_loaded('apc') && ini_get('apc.enabled')) {
					apc_delete('temp_order_'.$temp_orders_id);
				}
			}
			return true;
		} else {
			$this->errors[$this->type] = Translate('Kies a.u.b. een betaalmethode.');
			return false;
		}
	}
	public function before_confirm() {
		global $temp_orders_id, $customer_id, $currency, $languages_code;
		if(!isset($_GET['TransactionID'])){
			$post_payment = explode('__', $this->temp_data[$temp_orders_id]['orders']['payment_method_extra']);
			$paymentmethod = $post_payment[0];
			$issuer = $post_payment[1];
			$payment = new $paymentmethod();
			$country = tep_get_countries_with_iso_codes($this->temp_data[$temp_orders_id]['orders']['billing_country']);
			try {
				// Merchant Settings
				$payment->setMerchantID($this->instances[$this->temp_data[$temp_orders_id]['orders']['payment_method']]['merchant_id'])
						->setSecretCode($this->instances[$this->temp_data[$temp_orders_id]['orders']['payment_method']]['secret_code']);
				// Transaction Settings
				$payment->setAmount(($this->order_total*100))
						->setCountry($country['countries_iso_code_2'])
						->setLanguage(strtoupper($languages_code))
						->setCurrency($currency)
						->setIssuer($issuer)
						->setReference($temp_orders_id)
						->setDescription(STORE_NAME);
				// You should always set the order ID, however, this is ommitted here for testing purposes
				$url = $payment->setOrderID($temp_orders_id.' '.mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9).mt_rand(0, 9))->getURL();
				header("Location: ".$url);
				exit();
			} catch (Exception $e){
				$this->errors[$this->type] = Translate($e->getMessage());
			}
		}
	}
	function after_confirm() {
		global $temp_orders_id;
		$data = '';
		foreach($_GET as $key=>$value) {
			$data .= $key.': '."\n";
			$data .= $value."\n\n";
		}
		tep_db_query('INSERT INTO payment_log (type, data, date) VALUES ("'.get_class($this).'", "'.$data.'", NOW())');
		tep_db_query('DELETE FROM payment_log WHERE date < DATE_SUB(NOW(), INTERVAL 30 DAY)');
		$icepay = new Icepay_Result();
		$icepay->setMerchantID($this->instances[$this->temp_data[$temp_orders_id]['orders']['payment_method']]['merchant_id'])
				->setSecretCode($this->instances[$this->temp_data[$temp_orders_id]['orders']['payment_method']]['secret_code'])
				->enableLogging()
				->logToFile(true, realpath("../logs"));		
		try {
			if($icepay->validate()){
				switch ($icepay->getStatus()){
					case Icepay_StatusCode::OPEN:
						//do nothing
						break;
					case Icepay_StatusCode::SUCCESS:
						tep_db_query('UPDATE temp_orders SET orders_status = 2 WHERE orders_id = "'.$temp_orders_id.'"');
						break;
					case Icepay_StatusCode::ERROR:
						//Redirect to cart
						tep_db_query('UPDATE temp_orders SET orders_status = 53 WHERE orders_id = "'.$temp_orders_id.'"');
						Checkout::send_order_error_mail(Translate('Ongeldige Icepay betaling voor bestelling').': '.$temp_orders_id, sprintf(Translate('De betaling voor bestelling %s is ongeldig verklaard door Icepay.'), $temp_orders_id));
						$_GET['force_checkout_step'] = Checkout::get_step_for_type($this->type);
						$this->errors[$this->temp_data[$temp_orders_id]['orders']['payment_method']] = Translate($icepay->getStatus(true));
						break;
				}
			};
		} catch (Exception $e){
			echo($e->getMessage());
			$this->errors[$this->type] = Translate($e->getMessage());
		}
		return $this->errors;
    }
	public function after_extern_process() {
		global $temp_orders_id;
		if(isset($_GET['TransactionID'])){
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
						} else if (strstr($key, 'Icepay_Paymentmethod_')) {
							$file = strtolower(str_replace('Icepay_Paymentmethod_', '', $key));
							$this->writeToMethodFile($data, $key, $file);
							unset($_POST[$key]);
							break;
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
			header("Location: ".$_SERVER['HTTP_REFERER']);
			$this->update_instances();
		} else if (isset($_GET['action']) && $_GET['action'] == 'delete_module') {
			/********************/
			/*	Delete module	*/
			/********************/
			unset($_GET['action']);
			parent::delete_module(get_class($this));
		}
		$api = Icepay_Api_Basic::getInstance()->readFolder(realpath('api/paymentmethods'))->prepareFiltering();
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
				<label class="control-label" for="<?php echo $instance;?>_merchant_id"><?php echo Translate('Merchant ID');?></label>
				<div class="controls">
					<input type="text" name="merchant_id[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['merchant_id'];?>" class="input-xlarge" id="<?php echo $instance;?>_merchant_id" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_secret_code"><?php echo Translate('Geheime code');?></label>
				<div class="controls">
					<input type="text" name="secret_code[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['secret_code'];?>" class="input-xlarge" id="<?php echo $instance;?>_secret_code" />
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
				<label class="control-label"><?php echo Translate('Betaalmethodes');?></label>
				<div class="controls clearfix">
					<div class="tabbable">
						<?php
						$moddir = dirname(__FILE__).'/api/paymentmethods';
						$files = $this->listFolderFiles($moddir);
						?>
						<ul class="nav nav-tabs">
							<?php
							foreach($files as $key=>$file) {
							?>
							<li<?php echo ($key==0?' class="active"':'');?>><a href="#<?php echo substr(basename($file), 0, -4);?>" data-toggle="tab"><?php echo ucfirst(substr(basename($file), 0, -4));?></a></li>
							<?
							}
							?>
						</ul>
						<div class="tab-content">
							<?php
							foreach($files as $key=>$file) {
								$name = strtolower(substr(basename($file), 0, -4));
								$className = "Icepay_Paymentmethod_".ucfirst($name);
								$class = new $className();
							?>
							<div class="tab-pane<?php echo ($key==0?' active':'');?>" id="<?php echo substr(basename($file), 0, -4);?>">
								<input type="hidden" name="<?php echo $className;?>[_version]" value="<?php echo $class->_version;?>" />
								<input type="hidden" name="<?php echo $className;?>[_method]" value="<?php echo $class->_method;?>" />
								<div class="control-group">
									<label class="control-label" for="<?php echo $className;?>_status"><?php echo Translate('Status');?></label>
									<div class="controls clearfix">
										<div class="btn-group" data-toggle="buttons-radio">
											<button type="submit" name="<?php echo $className;?>[_status]" value="1" class="btn<?php echo ($class->_status == '1'?' active':'');?>"><?php echo Translate('Actief');?></button>
											<button type="submit" name="<?php echo $className;?>[_status]" value="0" class="btn<?php echo ($class->_status == '0'?' active':'');?>"><?php echo Translate('Niet actief');?></button>
										</div>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="<?php echo $className;?>_readable_name"><?php echo Translate('Naam');?></label>
									<div class="controls clearfix">
										<input type="text" name="<?php echo $className;?>[_readable_name]" class="input-medium" value="<?php echo $class->_readable_name;?>" id="<?php echo $className;?>_readable_name" />
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="<?php echo $className;?>_issuer"><?php echo Translate('Instantie');?></label>
									<div class="controls clearfix">
										<?php
										foreach($class->_issuer as $issuer=>$data) {
										?>
										<label class="checkbox">
											<input type="hidden" name="<?php echo $className;?>[_issuer][<?php echo $issuer;?>][name]" value="<?php echo $data['name'];?>" />
											<input type="hidden" name="<?php echo $className;?>[_issuer][<?php echo $issuer;?>][image]" value="<?php echo $data['image'];?>" />
											<input type="checkbox" name="<?php echo $className;?>[_issuer][<?php echo $issuer;?>][status]"<?php echo ($data['status'] == 'on'?' checked=checked':'');?>> <?php echo $data['name'];?>
										</label>
										<?php
										}
										?>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="<?php echo $className;?>_minimum_amount"><?php echo Translate('Minimum bestelbedrag');?></label>
									<div class="controls clearfix">
										<div class="input-append">
											<input type="number" name="<?php echo $className;?>[_amount][minimum]" class="input-medium" value="<?php echo $class->_amount['minimum'];?>" id="<?php echo $className;?>_minimum_amount" /><span class="add-on">€</span>
										</div>
									</div>
								</div>
								<div class="control-group">
									<label class="control-label" for="<?php echo $className;?>_maximum_amount"><?php echo Translate('Maximum bestelbedrag');?></label>
									<div class="controls clearfix">
										<div class="input-append">
											<input type="number" name="<?php echo $className;?>[_amount][maximum]" class="input-medium" value="<?php echo $class->_amount['maximum'];?>" id="<?php echo $className;?>_maximum_amount" /><span class="add-on">€</span>
										</div>
									</div>
								</div>
							</div>
							<?php
							}
							?>
						</div>
					</div>
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
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_merchant_id"><?php echo Translate('Merchant ID');?></label>
				<div class="controls">
					<input type="text" name="merchant_id[<?php echo $new_id;?>]" value="" class="input-xlarge" id="<?php echo get_class($this).'_'.$new_id;?>_merchant_id" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_secret_code"><?php echo Translate('Geheime code');?></label>
				<div class="controls">
					<input type="text" name="secret_code[<?php echo $new_id;?>]" value="" class="input-xlarge" id="<?php echo get_class($this).'_'.$new_id;?>_secret_code" />
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
	private function writeToMethodFile($postdata, $className, $file) {
		$api = Icepay_Api_Basic::getInstance()
			->readFolder(realpath('api/paymentmethods'))
			->prepareFiltering();
		$class = new $className();
		$string = '<?php'."\n";
		$string .= 'class '.$className.' extends Icepay_Basicmode {'."\n";
		if (!isset($postdata['_status'])) {
			$postdata['_status'] = $class->getSupportedStatus();
			if ($postdata['_status'] == '') {
				$postdata['_status'] = '0';
			}
		}
		foreach($postdata as $key=>$data) {
			$string .= "\t".'public'."\t\t".'$'.$key."\t\t".'= ';
			if (is_array($data)) {
				$string .= 'array('."\n";
				foreach($data as $name=>$value) {
					if (is_array($value)) {
						$string .= "\t\t\t\t\t\t\t\t\t\t".'"'.$name.'" => array('."\n";
						foreach($value as $kvalue=>$vvalue) {
							$string .= "\t\t\t\t\t\t\t\t\t\t\t\t".'"'.$kvalue.'" => "'.$vvalue.'",'."\n";
						}
						$string .= "\t\t\t\t\t\t\t\t\t\t".'),'."\n";
					} else {
						$string .= "\t\t\t\t\t\t\t\t\t\t".'"'.$name.'" => "'.$value.'",'."\n";
					}
				}
				$string .= "\t\t\t\t\t\t\t\t\t".');'."\n";
			} else {
				$string .= '"'.$data.'";'."\n";
			}
		}
		$string .= '}'."\n";
		$string .= '?>';
		$file = dirname(__FILE__).'/api/paymentmethods/'.$file.'.php';
		$handle = fopen($file, 'w');
		fwrite($handle, $string);
	}
	private function install() {
		//Check if translations are available
		parent::checkTranslations(dirname(__FILE__), $this->getTranslations());
		$install_array = array(
			'status' => 'true',
			'title' => 'Betaling via beveiligde Icepay server',
			'description' => '',
			'zone' => '*',
			'order_status_id' => '1',
			'shipping_module' => '*', //multiple are possible: Flat_1;Flat_2
			'merchant_id' => '',
			'secret_code' => ''
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
			merchant_id VARCHAR(255),
			secret_code VARCHAR(255))');
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
					,'Icepay betaling is goedgekeurd voor weborder' => array(
							'1' => 'Icepay betaling is goedgekeurd voor weborder', 
							'2' => 'Paiement Icepay est approuvé pour commande en ligne', 
							'3' => 'Icepay payment is approved for web order', 
							'4' => 'Icepay Zahlung ist für Web-Bestellung genehmigt')
					,'De betaling voor weborder %s is goedgekeurd door Icepay.' => array(
							'1' => 'De betaling voor weborder %s is goedgekeurd door Icepay.', 
							'2' => 'Le paiement pour %s web ordre a été approuvé par Icepay.', 
							'3' => 'Payment for web order %s has been approved by Icepay.', 
							'4' => 'Die Zahlung für Web-Bestellung in %s wurde von Icepay genehmigt worden.')
					,'Ongeldige Icepay betaling voor weborder' => array(
							'1' => 'Ongeldige Icepay betaling voor weborder', 
							'2' => 'Blancs de paiement de l\'ordre Icepay web', 
							'3' => 'Invalid Icepay payment for web order', 
							'4' => 'Ungültige Icepay Zahlung für Web-Bestellung')
					,'De betaling voor weborder %s is ongeldig verklaard door Icepay.' => array(
							'1' => 'De betaling voor weborder %s is ongeldig verklaard door Icepay.', 
							'2' => 'Le paiement pour %s web ordre est déclarée invalide par Icepay.', 
							'3' => 'Payment for web order %s is declared invalid by Icepay.', 
							'4' => 'Die Zahlung für Web-Bestellung in %s ist ungültig erklärt Icepay.')
					,'De betaling voor bestelling %s is ongeldig verklaard door Icepay.' => array(
							'1' => 'De betaling voor bestelling %s is ongeldig verklaard door Icepay.', 
							'2' => 'Paiement pour %s commande est déclarée invalide par Icepay.', 
							'3' => 'Payment for order %s is declared invalid by Icepay.', 
							'4' => 'Die Bezahlung der Bestellung %s ist ungültig erklärt Icepay.')
					,'Terugboeking Icepay betaling gestart voor weborder' => array(
							'1' => 'Terugboeking Icepay betaling gestart voor weborder', 
							'2' => 'Icepay inversion de l\'ordre de paiement initié web', 
							'3' => 'Reversal Icepay initiated payment for web order', 
							'4' => 'Reversal Icepay initiierte Zahlung für Web-Bestellung')
					,'De terugboeking voor weborder %s is gestart.' => array(
							'1' => 'De terugboeking voor weborder %s is gestart.', 
							'2' => 'Le renversement de %s web pour a commencé.', 
							'3' => 'The reversal for web order %s has started.', 
							'4' => 'Die Umkehrung für Web-Bestellung in %s hat begonnen.')
					,'Icepay betaling is terugbetaald voor weborder' => array(
							'1' => 'Icepay betaling is terugbetaald voor weborder', 
							'2' => 'Paiement Icepay est remboursé pour commande en ligne', 
							'3' => 'Icepay payment is refunded for web order', 
							'4' => 'Icepay Zahlung wird erstattet für Web-Bestellung')
					,'De betaling voor weborder %s is terugbetaald.' => array(
							'1' => 'De betaling voor weborder %s is terugbetaald.', 
							'2' => 'Le paiement pour %s web ordre a été remboursé.', 
							'3' => 'Payment for web order %s has been repaid.', 
							'4' => 'Die Zahlung für Web-Bestellung in %s zurückgezahlt wurde.')
					,'Ongeldige Icepay betaling voor bestelling' => array(
							'1' => 'Ongeldige Icepay betaling voor bestelling', 
							'2' => 'Blancs de paiement Icepay de l\'ordre', 
							'3' => 'Invalid Icepay payment for order', 
							'4' => 'Ungültige Icepay Zahlung für Auftrag')
					,'Merchant ID niet ingesteld, gebruik de setMerchantID() methode' => array(
							'1' => 'Merchant ID niet ingesteld, gebruik de setMerchantID() methode', 
							'2' => 'Merchant ID n\'est pas définie, utilisez la setMerchantID() la méthode', 
							'3' => 'Merchant ID not set, use the setMerchantID() method', 
							'4' => 'Merchant ID nicht gesetzt ist, verwenden Sie den setMerchantID()-Methode')
					,'Merchant ID niet ingesteld, gebruik de setSecretCode() methode' => array(
							'1' => 'Merchant ID niet ingesteld, gebruik de setSecretCode() methode', 
							'2' => 'Merchant ID n\'est pas définie, utilisez la setSecretCode() la méthode', 
							'3' => 'Merchant ID not set, use the setSecretCode() method', 
							'4' => 'Merchant ID nicht gesetzt ist, verwenden Sie den setSecretCode()-Methode')
					,'Issuer niet ingesteld, gebruik de setIssuer() methode' => array(
							'1' => 'Issuer niet ingesteld, gebruik de setIssuer() methode', 
							'2' => 'Issuer n\'est pas définie, utilisez la setIssuer() la méthode', 
							'3' => 'Issuer not set, use the setIssuer() method', 
							'4' => 'Issuer nicht gesetzt ist, verwenden Sie den setIssuer()-Methode')
					,'Taal niet ingesteld, gebruik de setLanguage() methode' => array(
							'1' => 'Taal niet ingesteld, gebruik de setLanguage() methode', 
							'2' => 'Le langage n\'est pas définie, utilisez la SetLanguage() la méthode', 
							'3' => 'Language not set, use the setLanguage() method', 
							'4' => 'Sprache nicht gesetzt ist, verwenden Sie die setLanguage()-Methode')
					,'Valuta is niet ingesteld, gebruik de setCurrency() methode' => array(
							'1' => 'Valuta is niet ingesteld, gebruik de setCurrency() methode', 
							'2' => 'Monnaie non définie, utilisez la setCurrency() la méthode', 
							'3' => 'Currency not set, use the setCurrency() method', 
							'4' => 'Währung nicht gesetzt ist, verwenden Sie den setCurrency()-Methode')
					,'Bedrag niet ingesteld, gebruik de setAmount() methode' => array(
							'1' => 'Bedrag niet ingesteld, gebruik de setAmount() methode', 
							'2' => 'Montant pas encore défini, utilisez la méthode setAmount()', 
							'3' => 'Amount not set, use the setAmount() method', 
							'4' => 'Betrag nicht festzulegen, verwenden Sie setAmount()-Methode')
					,'OrderID niet ingesteld, gebruik de setOrderID() methode' => array(
							'1' => 'OrderID niet ingesteld, gebruik de setOrderID() methode', 
							'2' => 'OrderID n\'est pas définie, utilisez la setOrderID() la méthode', 
							'3' => 'OrderID not set, use the setOrderID() method', 
							'4' => 'OrderID nicht gesetzt ist, verwenden Sie den setOrderID()-Methode')
					,'Fout bij het lezen van %s' => array(
							'1' => 'Fout bij het lezen van %s', 
							'2' => 'Erreur de lecture de %s', 
							'3' => 'Error reading %s', 
							'4' => 'Fehler beim Lesen von %s')
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
					,'Merchant ID' => array(
							'1' => 'Merchant ID',
							'2' => 'Merchant ID',
							'3' => 'Merchant ID',
							'4' => 'Merchant ID')
					,'Geheime code' => array(
							'1' => 'Secret code',
							'2' => 'Secret code',
							'3' => 'Secret code',
							'4' => 'Secret code')
					);
	}
}
?>