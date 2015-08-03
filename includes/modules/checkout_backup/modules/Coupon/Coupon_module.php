<?php
//coupon class
class Coupon extends Modules {
	public $type = 'coupon'
		 , $sort_order = 30
		 , $config = array()
		 , $success = array();
	public function __construct() {
		//load config => title, text, sort_order, status, zone, order_status_id, works_with_shipping_module
		if(tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'checkout_".get_class($this)."'"))< 1) {
			$this->install();
		}
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		$array = tep_db_fetch_array($query);
		$this->config = $array;
	}
	public function update_config() {
		$this->config = array();
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		$array = tep_db_fetch_array($query);
		$this->config = $array;
	}
	public function is_active() {
		global $temp_orders_id;
		$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
		if ($this->config['status'] == 'true') {
			if (parent::checkZone($this->config['zone'], $temp_data[$temp_orders_id]['orders']['billing_country']) && parent::checkShippingMethod($this->config['shipping_module'])) {
				return true;
			}
		}
		return false;
	}
	public function output($step = 0) {
		global $temp_orders_id;
		$html = '';
		if (isset(Checkout::$errors[$this->type])) {
			$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate(Checkout::$errors[$this->type]).'</div>';
			tep_db_query('DELETE FROM temp_orders_total WHERE orders_id = "'.$temp_orders_id.'" AND class= "'.$this->type.'"');
			unset(Checkout::$errors[$this->type]);
		} else {//Only show success alert if there are NO error alerts (Normaly it isn't possible to have both...)
			if (isset($this->success[$this->type])) {
				$html .= '<div class="alert alert-success">'.Translate($this->success[$this->type]).'</div>';
				unset($this->success[$this->type]);
			} else {
				$this->recalculate();
			}
		}
		//select payment method if orders_id is known
		//also get billing country
		if (!empty($temp_orders_id)) {
			$selected_query = tep_db_query('SELECT payment_method, billing_country FROM temp_orders WHERE orders_id = "'.$temp_orders_id.'"');
			$selected = tep_db_fetch_array($selected_query);
		}
		if ($this->config['status'] == 'true') {
			//check is active for zones and choosen shipping module
			if (parent::checkZone($this->config['zone'], $selected['billing_country']) && parent::checkShippingMethod($this->config['shipping_module'])) {
				$html .= '<div class="'.$this->type.'">';
				$html .= '<label class="control-label '.$this->type.'_title" for="'.$this->type.'_input">'.Translate($this->config['title']).'</label>';
				$html .= '<div class="control-group">';
				if (!empty($this->config['description'])) {
					$html .= '<div class="'.$this->type.'_description">'.Translate($this->config['description']).'</div>';
				}
				$html .= '<div class="controls">';
				$html .= '<input type="text" name="'.$this->type.'" value="'.$_POST[$this->type].'" placeholder="'.Translate('Vul hier uw kortingscode in.').'" id="'.$this->type.'_input" />&nbsp;';
				$html .= '<button type="submit" name="submit_'.$this->type.'" value="process" class="btn">'.Translate('Inwisselen').'</button>';
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</div>';
			}
		}
		return $html;
	}
	public function process_data($recalculate = false) {
		global $temp_orders_id, $customer_id, $currency, $currencies;
		if ((isset($_POST[$this->type]) && $_POST['submit_'.$this->type] == 'process') || $recalculate) {
			if ($recalculate) {
				$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
				$coupon_id = $temp_data[$temp_orders_id]['orders']['coupon_id'];
				$coupon_query = tep_db_query('SELECT coupon_id, coupon_code, coupon_type, coupon_amount, coupon_minimum_order, coupon_start_date, coupon_expire_date, uses_per_coupon, uses_per_user FROM coupons WHERE coupon_active = "Y" AND coupon_id = "'.$coupon_id.'"');
			} else {
				$coupon_query = tep_db_query('SELECT coupon_id, coupon_code, coupon_type, coupon_amount, coupon_minimum_order, coupon_start_date, coupon_expire_date, uses_per_coupon, uses_per_user FROM coupons WHERE coupon_active = "Y" AND coupon_code = "'.$_POST[$this->type].'"');
			}
			if (tep_db_num_rows($coupon_query) > 0) {
				$coupon_array = tep_db_fetch_array($coupon_query);
				$coupon_code = $coupon_array['coupon_code'];
				//check if coupon is already active
				if ($coupon_array['coupon_start_date'] > date("Y-m-d H:i:s")) {
					$this->errors[$this->type] = Translate('Deze kortingscode is nog niet actief.');
					return false;
				}
				//check if coupon is still active
				if ($coupon_array['coupon_expire_date'] < date("Y-m-d H:i:s")) {
					$this->errors[$this->type] = Translate('Deze kortingscode is niet meer actief.');
					return false;
				}
				//check uses per coupon
				$coupon_count = tep_db_query('select coupon_id from coupon_redeem_track where coupon_id = "'.$coupon_array['coupon_id'].'"');
				if (tep_db_num_rows($coupon_count)>=$coupon_array['uses_per_coupon'] && $coupon_array['uses_per_coupon'] > 0) {
					$this->errors[$this->type] = Translate('Deze kortingscode is al het maximum aantal toegelaten keren gebruikt.');
					return false;
				}
				//check uses per customer
				if ($coupon_array['uses_per_user'] > 0) {
					//user must be loged in to check this. Otherwise coupon can not be used
					if (tep_session_is_registered('customer_id')) {
						$coupon_count_customer = tep_db_query('select coupon_id from coupon_redeem_track where coupon_id = "'.$coupon_array['coupon_id'].'" and customer_id = "'.$customer_id.'"');
						if (tep_db_num_rows($coupon_count_customer)>=$coupon_array['uses_per_user'] && $coupon_array['uses_per_user'] > 0) {
							$this->errors[$this->type] = Translate('U hebt deze kortingscode al het maximum aantal toegelaten keren gebruikt.');
							return false;
						}
					} else {
						$this->errors[$this->type] = Translate('U moet ingelogd zijn om deze kortingscode te gebruiken.');
						return false;
					}
				}
				tep_db_query('UPDATE temp_orders SET coupon_id = "'.$coupon_array['coupon_id'].'" WHERE orders_id = "'.$temp_orders_id.'"');//put coupon id in db for use in other steps
				$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
				//check minimum order
				foreach($temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
					if ($data['class'] == 'order_total') {
						$total = $data['value'];
					}
					if ($data['class'] == 'order_subtotal') {
						$subtotal = $data['value'];
					}
					if ($data['class'] == 'shipping') {
						$shipping = $data['value'];
					}
				}
				$coupon_amount_array= $this->calculate_credit($subtotal);
				if (count($coupon_amount_array['errors']) > 0) {
					$this->errors[$this->type] = $coupon_amount_array['errors'];
					return false;
				}
				$coupon_amount = $coupon_amount_array['coupon_amount'];
				$coupon_amount_out = $currencies->format($coupon_amount).' ';
				if ($coupon_array['coupon_minimum_order'] > 0) {
					$coupon_amount_out .= 'on orders greater than ' . $currencies->format($coupon_result['coupon_minimum_order']);
				}
				if ($coupon_amount == 0 && $shipping != 0) {
					$this->errors[$this->type] = Translate('Dit is een geldige kortingscode. Er kan echter geen korting worden toegepast, bekijk de beperkingen in de mail die u ontvangen hebt.');
					return false;
				} else {
					$check_totc_query = tep_db_query('SELECT orders_total_id FROM temp_orders_total WHERE orders_id = "'.$temp_orders_id.'" AND class = "'.$this->type.'"');
					if (tep_db_num_rows($check_totc_query) > 0) {
						tep_db_query('UPDATE temp_orders_total SET title = "'.$this->config['title'].' '.$coupon_code.', '.$coupon_amount_array['coupon_text'].'", text = "'.$currencies->format(-$coupon_amount).'", value="-'.$coupon_amount.'", sort_order = "'.$this->sort_order.'" WHERE orders_id = "'.$temp_orders_id.'" AND class="'.$this->type.'"');
					} else{
						tep_db_query('INSERT INTO temp_orders_total (orders_id, title, text, value, class, sort_order) VALUES("'.$temp_orders_id.'", "'.$this->config['title'].' '.$coupon_code.', '.$coupon_amount_array['coupon_text'].'", "'.$currencies->format(-$coupon_amount).'", "-'.$coupon_amount.'", "'.$this->type.'", "'.$this->sort_order.'")');
					}
					$this->success[$this->type] = sprintf(Translate('Een korting van %s is toegekend aan uw bestelling.'), $currencies->format($coupon_amount));
				}
			} else {
				$this->errors[$this->type] = Translate('U hebt een ongeldige kortingscode ingegeven, probeer opnieuw.');
			}
			return false;
		}
		return true;
	}
	public function recalculate() {
		global $temp_orders_id;
		$this->process_data(true);
	}
	public function calculate_credit($amount) {
		global $temp_orders_id, $currencies, $currency;
		$return = array();
		$return['coupon_amount'] = 0;
		$return['coupon_text'] = '';
		$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
		foreach($temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
			if ($data['class'] == 'shipping') {
				$shipping_cost = $data['value'];
			}
			if ($data['class'] == 'order_total') {
				$order_total = $data['value'];
			}
		}
		if (isset($temp_data[$temp_orders_id]['orders']['coupon_id']) && $temp_data[$temp_orders_id]['orders']['coupon_id'] > 0) {
			$coupon_query = tep_db_query("select coupon_code, coupon_amount, coupon_minimum_order, restrict_to_products, restrict_to_categories, restrict_to_zones, coupon_type from coupons where coupon_id = '".$temp_data[$temp_orders_id]['orders']['coupon_id']."'");
			$coupon_result = tep_db_fetch_array($coupon_query);
			//percent
			if ($coupon_result['coupon_type'] == 'P' && ($coupon_result['restrict_to_products'] != '' || $coupon_result['restrict_to_categories'] != '')) {
				$coupon_result['coupon_amount'] = substr($coupon_result['coupon_amount'], 0, -1);
				$return['coupon_text'] = number_format($coupon_result['coupon_amount']).'% '.Translate('op').' ';
			} else if ($coupon_result['coupon_type'] == 'P') {
				$coupon_result['coupon_amount'] = substr($coupon_result['coupon_amount'], 0, -1);
				$return['coupon_text'] = number_format($coupon_result['coupon_amount']).'%';
			} else  if ($coupon_result['restrict_to_products'] != ''  || $coupon_result['restrict_to_categories'] != '') {
				$return['coupon_text'] = ' '.Translate('op').' ';
			}
			if ($coupon_result['coupon_minimum_order'] <= $order_total) {
				if ($coupon_result['restrict_to_products'] != '' || $coupon_result['restrict_to_categories'] != '' || $coupon_result['restrict_to_zones'] != '') {
					$used_restricted_categories = array();
					foreach($temp_data[$temp_orders_id]['orders_products'] as $order_products_id=>$products_data) {
						if ($coupon_result['restrict_to_categories'] != '') {
							/****************************/
							/*	RESTRICT TO CATEGORIES	*/
							/****************************/
							$cat_ids = preg_split("/[,]/", $coupon_result['restrict_to_categories']);
							$my_path = tep_get_product_path($products_data['products_id']);
							$sub_cat_ids = preg_split("/[_]/", $my_path);
							for ($iii = 0; $iii < count($sub_cat_ids); $iii++) {//loop through all category IDs from the product
								for ($ii = 0; $ii < count($cat_ids); $ii++) {//Loop through all restricted category IDs
									if ($sub_cat_ids[$iii] == $cat_ids[$ii]) { //Check if there are similar category IDs
										if ($coupon_result['coupon_type'] == 'P') {//Percent
											$pr_c = ($products_data['final_price'] * $products_data['products_quantity']);
											$pod_amount = round($pr_c*10)/10*$coupon_result['coupon_amount']/100;
											$return['coupon_amount'] = $return['coupon_amount'] + $pod_amount;
											$used_restricted_categories[] = $cat_ids[$ii];
											continue 2;
										} else {
											//Fixed amount
											$return['coupon_amount'] = $coupon_result['coupon_amount'];
											//No shipping costs
											if ($coupon_result['coupon_type']=='S') {
												$return['coupon_text'] = Translate('Gratis verzending');
												$return['coupon_amount'] = $shipping_cost;
											}
											if ($coupon_result['coupon_type']=='S' && $coupon_result['coupon_amount'] > 0 ) {
												$return['coupon_amount'] = $shipping_cost + $coupon_result['coupon_amount'];
											}
											continue 2;
										}
									}
								}
							}
						}
						if ($coupon_result['restrict_to_products'] != '') {
							/****************************/
							/*	RESTRICT TO PRODUCTS	*/
							/****************************/
							$pr_ids = preg_split("/[,]/", $coupon_result['restrict_to_products']);
							for ($ii = 0; $ii < count($pr_ids); $ii++) { //Loop through restricted products_ids
								if ($pr_ids[$ii] == $products_data['products_id']) {
									if ($coupon_result['coupon_type'] == 'P') {//Percent
										$pr_c = ($products_data['final_price'] * $products_data['products_quantity']);
										$pod_amount = round($pr_c*10)/10*$coupon_result['coupon_amount']/100;
										$return['coupon_amount'] = $return['coupon_amount'] + $pod_amount;
										$return['coupon_text'] .= $products_data['products_name'].', ';
									} else {//fixed amount
										$return['coupon_text'] .= $products_data['products_name'].', ';
										$return['coupon_amount'] = $coupon_result['coupon_amount'];
										//No shipping costs
										if ($coupon_result['coupon_type']=='S') {
											$return['coupon_text'] = Translate('Gratis verzending');
											$return['coupon_amount'] = $shipping_cost;
										}
										if ($coupon_result['coupon_type']=='S' && $coupon_result['coupon_amount'] > 0 ) {
											$return['coupon_amount'] = $shipping_cost + $coupon_result['coupon_amount'];
										}
									}
								}
							}
						}
						if ($coupon_result['restrict_to_zones'] != '') {
							/************************/
							/*	RESTRICT TO ZONES	*/
							/************************/
							$zones_ids = preg_split("/[,]/", $coupon_result['restrict_to_zones']);
							for ($ii = 0; $ii < count($zones_ids); $ii++) { //Loop through restricted zone_ids
								if (parent::checkZone($zones_ids[$ii], $temp_data[$temp_orders_id]['orders']['delivery_country'])) {
									if ($coupon_result['coupon_type'] == 'P') {//Percent
										$pr_c = ($products_data['final_price'] * $products_data['products_quantity']);
										$pod_amount = round($pr_c*10)/10*$coupon_result['coupon_amount']/100;
										$return['coupon_amount'] = $return['coupon_amount'] + $pod_amount;
									} else {//fixed amount
										$return['coupon_amount'] = $coupon_result['coupon_amount'];
										//No shipping costs
										if ($coupon_result['coupon_type']=='S') {
											$return['coupon_text'] = Translate('Gratis verzending');
											$return['coupon_amount'] = $shipping_cost;
										}
										if ($coupon_result['coupon_type']=='S' && $coupon_result['coupon_amount'] > 0 ) {
											$return['coupon_amount'] = $shipping_cost + $coupon_result['coupon_amount'];
										}
									}
								}
							}
						}
					}
					$used_restricted_categories = array_unique($used_restricted_categories);
					foreach($used_restricted_categories as $cat_id) {
						$return['coupon_text'] .= tep_get_categories_name($cat_id).', ';
					}
				} else {//not restricted to products, categories or zones
					//percent
					if ($coupon_result['coupon_type'] =='P') {
						$return['coupon_amount'] = $amount * ($coupon_result['coupon_amount'] / 100);
					}					
					//No shipping costs
					if ($coupon_result['coupon_type']=='S') {
						$return['coupon_text'] = Translate('Gratis verzending');
						$return['coupon_amount'] = $shipping_cost;
					}
					if ($coupon_result['coupon_type']=='S' && $coupon_result['coupon_amount'] > 0 ) {
						$return['coupon_amount'] = $shipping_cost + $coupon_result['coupon_amount'];
					}
					//fixed amount
					if ($coupon_result['coupon_type']=='G') {
						$return['coupon_amount'] = $amount;
					}
				}
				if ($coupon_result['coupon_type'] == 'P' && ($coupon_result['restrict_to_products'] || $coupon_result['restrict_to_categories'])) {
					$return['coupon_text'] = substr($return['coupon_text'],0 ,-2);
				} else if (strlen($return['coupon_text']) > 2 && ($coupon_result['restrict_to_products'] || $coupon_result['restrict_to_categories'])) {
					$return['coupon_text'] = substr($return['coupon_text'],0 ,-2);
				}
			} else { //coupon minimum order is more then order total
				$return['errors'] = Translate('Om de kortingscode te gebruiken moet uw bestelling een minimum bedrag hebben van').' '.$currencies->format($coupon_result['coupon_minimum_order']);
			}
			if ($coupon_result['coupon_type'] != 'S') {
				if ($return['coupon_amount']>$amount) $return['coupon_amount'] = $amount;
			}
		}
		$return['coupon_amount'] = tep_round($return['coupon_amount'], $currencies->currencies[$currency]['decimal_places']);
		return $return;
	}
	public function after_process($orders_id) {
		global $temp_orders_id, $customer_id;
		$coupon_id_query = tep_db_query('SELECT coupon_id FROM temp_orders WHERE orders_id = "'.$temp_orders_id.'"');
		if (tep_db_num_rows($coupon_id_query) > 0) {
			$coupon_id = tep_db_fetch_array($coupon_id_query);
			if ($coupon_id['coupon_id'] > 0) {
				tep_db_query("insert into coupon_redeem_track (coupon_id, redeem_date, redeem_ip, customer_id, order_id) values ('".$coupon_id['coupon_id']."', now(), '".$_SERVER['REMOTE_ADDR']."', '".$customer_id."', '".$orders_id."')");
			}
		}
	}
	public function getZones() {
		$zones = array();
		if (!empty($this->config['zone'])) {
			$zones[] = $this->config['zone'];
		}
		return $zones;
	}
	public function administrator() {
		global $Modules, $login;
		echo '<h1>';
		echo get_class($this);
		if ($login == 'aboservice') {
			echo '<button type="button" id="delete_module" href="'.tep_href_link('checkout.php', 'module='.$_GET['module']).'&action=delete_module" class="btn btn-danger pull-right">'.Translate('Verwijder module').'</button>';
		}
		echo '</h1>';
		echo '<hr />';
		if (isset($_POST['action']) && $_POST['action'] == 'save') {
			/********************/
			/*	Save changes	*/
			/********************/
			unset($_POST['action']);
			foreach($_POST as $key=>$data) {
				if ($key == 'zone' || $key == 'shipping_module') {
					if (isset($data['*'])) {
						$_POST[$key] = '*';
					} else {
						$_POST[$key] = implode(';', $data);
					}
				}
			}
			tep_db_perform('checkout_'.get_class($this), $_POST, 'update', 'id="1"');
			$this->update_config();
		} else if (isset($_GET['action']) && $_GET['action'] == 'delete_module') {
			/********************/
			/*	Delete module	*/
			/********************/
			unset($_GET['action']);
			parent::delete_module(get_class($this));
		}
		?>
		<form name="<?php echo get_class($this);?>" class="form-horizontal well" action="<?php echo tep_href_link('checkout.php', 'module='.$_GET['module']);?>" method="post">
			<input type="hidden" name="action" value="save" />
			<fieldset>
			<div class="control-group">
				<label class="control-label" for="title"><?php echo Translate('Titel');?></label>
				<div class="controls">
					<input type="text" name="title" value="<?php echo $this->config['title'];?>" class="input-xlarge" id="title" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="status"><?php echo Translate('Status');?></label>
				<div class="controls">
					<div class="btn-group" data-toggle="buttons-radio">
						<button type="submit" name="status" value="true" class="btn<?php echo ($this->config['status'] == 'true'?' active':'');?>"><?php echo Translate('Actief');?></button>
						<button type="submit" name="status" value="false" class="btn<?php echo ($this->config['status'] == 'false'?' active':'');?>"><?php echo Translate('Niet actief');?></button>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="description"><?php echo Translate('Omschrijving');?></label>
				<div class="controls">
					<input type="text" name="description" value="<?php echo $this->config['description'];?>" class="input-xxlarge" id="description" />
					<span class="help-block"><?php echo Translate('Omschrijving gebruikt in de checkout, samen met de titel');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="order_status_id"><?php echo Translate('Bestel status');?></label>
				<div class="controls">
					<select name="order_status_id" class="input-xlarge" id="order_status_id">
						<?php
						$statusses = parent::get_order_statusses();
						foreach($statusses as $id=>$name) {
							echo '<option value="'.$id.'"'.($this->config['order_status_id'] == $id?' selected="selected"':'').'>'.$name.'</option>';
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
					<input type="checkbox" name="shipping_module[*]" <?php echo ($this->config['shipping_module'] == '*'?' checked="checked"':'');?> />
					</label>
					<?php
					$shipping_modules = explode(';', $this->config['shipping_module']);
					foreach($Modules->modules['shipping'] as $module) {
						global $$module;
						if (isset($$module->instances)) {
							foreach($$module->instances as $shipping_instance=>$shipping_instance_data) {
								echo '<label class="checkbox inline">'.$shipping_instance_data['title'];
								echo '<input type="checkbox" name="shipping_module['.$shipping_instance.']" value="'.$shipping_instance.'"'.(in_array($shipping_instance, $shipping_modules)?' checked="checked"':'').' />';
								echo '</label>';
							}
						} else {
							echo '<label class="checkbox inline">'.$$module->config['title'];
							echo '<input type="checkbox" name="shipping_module['.$module.']" value="'.$module.'"'.(in_array($module, $shipping_modules)?' checked="checked"':'').' />';
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
					<input type="checkbox" name="zone[*]" value="*" <?php echo ($this->config['zone'] == '*'?' checked="checked"':'');?> />
					</label>
					<?php
					$selected_zones = explode(';', $this->config['zone']);
					$zones = parent::get_all_zones();
					foreach ($zones as $zone_id=>$zone_name) {
						echo '<label class="checkbox inline">'.$zone_name;
						echo '<input type="checkbox" name="zone['.$zone_id.']" value="'.$zone_id.'"'.(in_array($zone_id, $selected_zones)?' checked="checked"':'').' />';
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
	private function install() {
		//Check if translations are available
		parent::checkTranslations(dirname(__FILE__), $this->getTranslations());
		$install_array = array(
			'status' => 'true',
			'title' => 'Kortingscode',
			'description' => '',
			'zone' => '*',
			'order_status_id' => '3',
			'shipping_module' => '*' //multiple are possible: Flat_1;Flat_2
		);
		tep_db_query('CREATE TABLE checkout_'.get_class($this).'(
			id INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			status VARCHAR(255),
			title VARCHAR(255),
			description VARCHAR(255),
			zone VARCHAR(255),
			order_status_id INT(11),
			shipping_module VARCHAR(255))');
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
					,'Verwijder module' => array(
							'1' => 'Verwijder module', 
							'2' => 'Retirez le module', 
							'3' => 'Remove module', 
							'4' => 'Modul entfernen')
					,'Kortingscode' => array(
							'1' => 'Kortingscode', 
							'2' => 'Code de Réduction', 
							'3' => 'Discount Code', 
							'4' => 'Rabatt-Code')
					,'Vul hier uw kortingscode in.' => array(
							'1' => 'Vul hier uw kortingscode in.', 
							'2' => 'S\'il vous plaît entrer votre code de réduction.', 
							'3' => 'Please enter your discount code.', 
							'4' => 'Bitte geben Sie Ihren Rabatt-Code.')
					,'Inwisselen' => array(
							'1' => 'Inwisselen', 
							'2' => 'Échange', 
							'3' => 'Exchange', 
							'4' => 'Austausch')
					,'Deze kortingscode is nog niet actief.' => array(
							'1' => 'Deze kortingscode is nog niet actief.', 
							'2' => 'Ce code de réduction n\'est pas encore actif.', 
							'3' => 'This discount code is not active yet.', 
							'4' => 'Diese Rabatt-Code ist noch nicht aktiv.')
					,'Deze kortingscode is niet meer actief.' => array(
							'1' => 'Deze kortingscode is niet meer actief.', 
							'2' => 'Ce code de réduction n\'est plus actif.', 
							'3' => 'This discount code is no longer active.', 
							'4' => 'Diese Rabatt-Code ist nicht mehr aktiv.')
					,'Deze kortingscode is al het maximum aantal toegelaten keren gebruikt.' => array(
							'1' => 'Deze kortingscode is al het maximum aantal toegelaten keren gebruikt.', 
							'2' => 'Ce code de réduction est déjà le nombre maximum autorisé de fois utilisées.', 
							'3' => 'This discount code is already the maximum allowed number of times used.', 
							'4' => 'Diese Rabatt-Code ist bereits die höchste zulässige Anzahl von Malen verwendet.')
					,'U hebt deze kortingscode al het maximum aantal toegelaten keren gebruikt.' => array(
							'1' => 'U hebt deze kortingscode al het maximum aantal toegelaten keren gebruikt.', 
							'2' => 'Vous avez le code de réduction a déjà le nombre maximum autorisé de fois utilisées.', 
							'3' => 'You have the discount code already has the maximum allowed number of times used.', 
							'4' => 'Sie haben den Rabatt-Code verfügt bereits über die maximal zulässige Anzahl von Malen verwendet.')
					,'U moet ingelogd zijn om deze kortingscode te gebruiken.' => array(
							'1' => 'U moet ingelogd zijn om deze kortingscode te gebruiken.', 
							'2' => 'Vous devez être connecté pour ce code de réduction.', 
							'3' => 'You must be logged in for this discount code.', 
							'4' => 'Sie müssen sich für diesen Rabatt-Code protokolliert werden.')
					,'Dit is een geldige kortingscode. Er kan echter geen korting worden toegepast, bekijk de beperkingen in de mail die u ontvangen hebt.' => array(
							'1' => 'Dit is een geldige kortingscode. Er kan echter geen korting worden toegepast, bekijk de beperkingen in de mail die u ontvangen hebt.', 
							'2' => 'Il s\'agit d\'un code de réduction valable. Toutefois, aucun escompte ne sera appliqué, vérifier les restrictions dans l\'e-mail que vous avez reçu.', 
							'3' => 'This is a valid discount code. However, no discount will be applied, check the restrictions in the mail you received.', 
							'4' => 'Dies ist eine gültige Rabatt-Code. Jedoch kann keine Ermäßigung angewendet werden, überprüfen Sie die Einschränkungen in der Mail, die Sie erhalten haben.')
					,'Een korting van %s is toegekend aan uw bestelling.' => array(
							'1' => 'Een korting van %s is toegekend aan uw bestelling.', 
							'2' => 'Une remise de %s est attribué à votre commande.', 
							'3' => 'A discount of %s is assigned to your order.', 
							'4' => 'Ein Rabatt von %s ist zu Ihrer Bestellung zugeordnet.')
					,'U hebt een ongeldige kortingscode ingegeven, probeer opnieuw.' => array(
							'1' => 'U hebt een ongeldige kortingscode ingegeven, probeer opnieuw.', 
							'2' => 'Vous avez entré un code promo valide, essayez à nouveau.', 
							'3' => 'You have entered an invalid coupon code, try again.', 
							'4' => 'Sie haben einen ungültigen Gutschein-Code eingegeben haben, versuchen Sie es erneut.')
					,'op' => array(
							'1' => 'op', 
							'2' => 'sur', 
							'3' => 'on', 
							'4' => 'auf')
					,'Gratis verzending' => array(
							'1' => 'Gratis verzending', 
							'2' => 'Livraison gratuite', 
							'3' => 'Free shipping', 
							'4' => 'Kostenloser Versand')
					,'Om de kortingscode te gebruiken moet uw bestelling een minimum bedrag hebben van' => array(
							'1' => 'Om de kortingscode te gebruiken moet uw bestelling een minimum bedrag hebben van', 
							'2' => 'Pour utiliser le code de réduction doit avoir votre commande d\'un montant minimum de', 
							'3' => 'To use the discount code must have your order a minimum amount of', 
							'4' => 'Um den Rabatt-Code verwenden, müssen Sie Ihre Bestellung einen Mindestbetrag von')
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
					);
	}
}
?>