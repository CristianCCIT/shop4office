<?php
class Customers_info extends Modules {
	private $Checkout;
	public $type = 'customers_info'
		 , $sort_order = 10
		 , $config = array()
		 , $fields = array()
		 , $address_book_fields = array();
	public function __construct() {
		//check if module is installed
		if(tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'checkout_".get_class($this)."'"))< 1) {
			//install module
			$this->install();
		}
		$this->fields = array(
			'billing' => array(
				'title' => Translate('Facturatieadres'),
				'fields' => array()
			),
			'delivery' => array(
				'title' => Translate('Leveringsadres'),
				'fields' => array()
			)
		);
		//get all configuration items for this module
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this).' ORDER BY sort_order asc');
		while ($array = tep_db_fetch_array($query)) {
			if ($array['type'] == 'config') {
				$this->config[$array['name']] = array('value' => $array['value'], 'options' => unserialize($array['options']));
			} else if (($array['type'] == 'field' || $array['type'] == 'dropdown') && $array['value'] == 'true') {
				$this->fields[$array['block']]['fields'][$array['name']] = $array;
			}
		}
		if (isset($_GET['delete_address'])) {//delete address from address_book
			tep_db_query('DELETE FROM address_book WHERE address_book_id = "'.$_GET['delete_address'].'"');
		}
		parent::add_to_disable_next_button('add_new_address');
		$this->address_book_fields['entry_firstname'] = Translate('Vul a.u.b. een volledige naam in.');
		$this->address_book_fields['entry_street_address'] = Translate('Vul a.u.b. een straat + nummer in.');
		$this->address_book_fields['entry_postcode'] = Translate('Vul a.u.b. een postcode in.');
		$this->address_book_fields['entry_city'] = Translate('Vul a.u.b. een gemeente in.');
		$this->address_book_fields['entry_country_id'] = Translate('Kies a.u.b. een land.');
	}
	public function update_config() {
		$this->config = array();
		$this->fields = array();
		//get all configuration items for this module
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this).' ORDER BY sort_order asc');
		while ($array = tep_db_fetch_array($query)) {
			if ($array['type'] == 'config') {
				$this->config[$array['name']] = array('value' => $array['value'], 'options' => unserialize($array['options']));
			} else if (($array['type'] == 'field' || $array['type'] == 'dropdown') && $array['value'] == 'true') {
				$this->fields[$array['block']]['fields'][$array['name']] = $array;
			}
		}
	}
	public function is_active() {
		if ($this->config['status']['value'] == 'true') {
			return true;
		} else {
			return false;
		}
	}
	public function output($step = 0) {
		global $customer_id, $temp_orders_id;
		$html = '';
		if (tep_session_is_registered('customer_id')) {
			//show address book item form
			if (isset($_GET['add_new_address']) && $_GET['add_new_address'] == 'true' && $this->config['change_billing_address']['value'] == 'true') {
				$html .= '<h3>'.Translate('Voeg nieuw adres toe').'</h3>';
				$html .= '<div class="well">';
				if (count($this->errors[get_class($this)]['address_book']) > 0) {
					$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate('Vul a.u.b. alle velden correct in.').'</div>';
				}
				$html .= '<div class="address_book_item">';
				$html .= '<input type="hidden" name="save_new_address" value="true" />';
				//get fields from db
				$ab_query = tep_db_query('SHOW columns FROM address_book');
				while($ab_fields = tep_db_fetch_array($ab_query)) {
					foreach($this->fields[key($this->fields)]['fields'] as $field=>$field_data) {
						if ($field_data['dbvalue'] == $ab_fields['Field']) {
							if ($field_data['condition'] != '') {
								$show_field = eval($field_data['condition'].';');
							} else {
								$show_field = true;
							}
							if ($show_field) {
								//check if there where errors with the filled in data for this field
								$class = '';
								if ($this->errors[get_class($this)]['address_book'][$field_data['dbvalue']]) {
									$class .= ' error';
									//$html .= parent::create_error($this->errors[get_class($this)]['address_book'][$field]);
									unset($this->errors[get_class($this)]['address_book'][$field_data['dbvalue']]);
								}
								$html .= '<div class="control-group'.$class.'">';
								$html .= '<label class="control-label" for="address_book_item_'.$field_data['dbvalue'].'">'.$field_data['label'].'</label>';
								$html .= '<div class="controls">';
								if ($field_data['type'] == 'dropdown') {
									if(strstr($field_data['dbvalue'], 'country')) {
										$html .= parent::get_country_list($field_data['dbvalue'], $_POST[$field_data['dbvalue']], 'id="address_book_item_'.$field_data['dbvalue'].'"');
									}
								} else {
									$html .= '<input type="'.$field_data['input'].'" id="address_book_item_'.$field_data['dbvalue'].'" name="'.$field_data['dbvalue'].'" value="'.htmlspecialchars(stripslashes($_POST[$field_data['dbvalue']])).'" />';
								}
								$html .= '</div>';//end controle
								$html .= '</div>';//end control-group
							}
						}
					}
				}
				$html .= '<div class="form-actions">';
				//submit
				$html .= '<button type="submit" class="btn btn-success">'.Translate('Nieuw adres opslaan').'</button> ';
				//cancel
				$html .= '<button type="submit" name="action" value="cancel" class="btn">'.Translate('Annuleren').'</button>';
				$html .= '</div>';//end form actions
				$html .= '</div>';//end address_book_item
				$html .= '</div>';//end well
				//eof address book item form
			} else {//address list
				//list address book items
				//get customers_info if customer is logged in
				$c_query = tep_db_query('SELECT customers_email_address, customers_telephone, customers_default_address_id FROM customers WHERE customers_id = "'.$customer_id.'"');
				$c = tep_db_fetch_array($c_query);
				//check if address_id's are known
				if ($temp_orders_id > 0) {
					$a_id_query = tep_db_query('SELECT delivery_address_id, billing_address_id FROM temp_orders WHERE orders_id = "'.$temp_orders_id.'"');
					$a_id = tep_db_fetch_array($a_id_query);
				}
				$count = 0;
				$max_count = count($this->fields);
				if (is_array($this->errors[get_class($this)]['address_book_list'])) {
					foreach($this->errors[get_class($this)]['address_book_list'] as $error) {
						$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.$error.'</div>';
					}
				}
				$html .= '<div class="row-fluid">';
				//foreach block (billing, delivery, ...)
				foreach($this->fields as $block=>$block_data) {
					$is_checked = false;
					$count++;
					$html .= '<div class="span6">';
					//title eg. 'Facturatie adres', 'leveringsadres', ...
					$html .= '<h3>'.$block_data['title'].'</h3>';
					$html .= '<div class="well">';
					$html .= '<ul class="address_list">';
					if ($block == 'billing' && $this->config['change_billing_address']['value'] == 'false') {
						$ab_query = tep_db_query('SELECT * FROM address_book WHERE customers_id = "'.$customer_id.'" AND entry_country_id IN ("'.implode('", "', parent::$available_countries).'") AND address_book_id = "'.$c['customers_default_address_id'].'"');
					} else {
						$ab_query = tep_db_query('SELECT * FROM address_book WHERE customers_id = "'.$customer_id.'" AND entry_country_id IN ("'.implode('", "', parent::$available_countries).'")');
					}
					while ($ab = tep_db_fetch_array($ab_query)) {
						$html .= '<li class="address_block clearfix">';
						//radio button
						if (!$is_checked) {
							if ($_POST['address_book_id'][$block] == $ab['address_book_id']) {
								$checked = ' checked=checked';
								$is_checked = true;
							} else if (isset($a_id[$block.'_address_id']) && $a_id[$block.'_address_id'] == $ab['address_book_id']) {
								$checked = ' checked=checked';
								$is_checked = true;
							} else if ($c['customers_default_address_id'] == $ab['address_book_id']) {
								$checked = ' checked=checked';
								$is_checked = true;
							}
						} else {
							$checked = '';
						}
						$html .= '<input type="radio" id="address_book_id_'.$block.'_'.$ab['address_book_id'].'" name="address_book_id['.$block.']" value="'.$ab['address_book_id'].'"'.$checked.' />';
						$html .= '<label for="address_book_id_'.$block.'_'.$ab['address_book_id'].'">';
						$html .= '<div class="address">';
						//name
						$html .= '<div class="address_name">'.$ab['entry_firstname'].'</div>';
						//street
						$html .= '<div class="address_street">'.$ab['entry_street_address'].'</div>';
						//city + postcode + country
						$html .= '<div class="address_city">'.$ab['entry_postcode'].' '.$ab['entry_city'].' '.tep_get_country_name($ab['entry_country_id']).'</div>';
						$html .= '</div>';//end address
						$html .= '</label>';
						//link to delete address book item
						if ($c['customers_default_address_id'] == $ab['address_book_id']) {
							$html .= '<div class="delete_address">['.Translate('Hoofdadres').']</div>';
						} else {
							$html .= '<div class="delete_address"><a href="'.tep_href_link(basename($_SERVER['PHP_SELF']), 'delete_address='.$ab['address_book_id']).'" title="'.Translate('Verwijder adres').'" class="btn btn-mini">'.Translate('Verwijder adres').'</a></div>';
						}
						$html .= '</li>';//end address_block
					}
					$html .= '</ul>';//end address_list
					//link to add new address book item
					if (($block == 'billing' && $this->config['change_billing_address']['value'] == 'true') || $block != 'billing') {
						$html .= '<div class="add_new_address"><a href="'.tep_href_link(basename($_SERVER['PHP_SELF']), 'checkout_step='.$step.'&add_new_address=true').'" title="'.Translate('Nieuw adres toevoegen').'" class="btn btn-mini"><i class="icon-plus-sign"></i> '.Translate('Nieuw adres toevoegen').'</a></div>';
					}
					$html .= '</div>';//end well
					$html .= '</div>';//end span6
				}
				$html .= '</div>';//end row-fluid
				//eof address list
			}
		} else {//Not logged in
			//get last filled in data if that is available
			if ($temp_orders_id > 0) {
				$to_query = tep_db_query('SELECT * FROM temp_orders WHERE orders_id = "'.$temp_orders_id.'"');
				if (tep_db_num_rows($to_query) > 0) {
					$to = tep_db_fetch_array($to_query);
					if (!isset($_POST['customers_info_data'])) {
						//check if there where errors in that step
						$tos_query = tep_db_query('SELECT errors FROM temp_orders_steps WHERE orders_id ="'.$temp_orders_id.'" AND step = "'.$step.'" ORDER BY date desc LIMIT 1');
						if (tep_db_num_rows($tos_query) > 0) {
							$tos = tep_db_fetch_array($tos_query);
							$tos['errors'] = unserialize($tos['errors']);
							$this->errors = $tos['errors'][get_class($this)];
						}
					}
				}
			}
			//create form
			$html .= '<input type="hidden" name="customers_info_data" value="true" />';
			/************************/
			/*	show login block?	*/
			/************************/
			if ($this->config['login']['value'] == 'true' && !tep_session_is_registered('customer_id')) {
				//login box
				if (!empty($this->errors[get_class($this)]['login'])) {
					$html .= '<div class="alert alert-error">'.$this->errors[get_class($this)]['login'].'</div>';
				}
				$html .= Translate('Terugkerende klant').'? ';
				$html .= '<a href="#" id="login_modal_button" style="display:none;">'.Translate('Inloggen').'</a>';
				$html .= '<div class="well form-inline login_modal" id="login_block">';
				$html .= '<h2>'.Translate('Inloggen').'</h2>';
				$html .= '<div class="control-group">';
				//email
				$html .= '<input type="text" name="login_email" placeholder="'.Translate('E-mailadres').'" value="'.$_POST['login_email'].'" /> ';
				//password
				$html .= '<input type="password" placeholder="'.Translate('Paswoord').'" name="login_pass" value="" />';
				$html .= '</div>';
				//submit
				$html .= ' <button type="submit" name="action" value="login" class="btn">'.Translate('Log in').'</button>';
				
				$html .= '</div>';//end well
				$html .= '</form>';
				$html .= '<form name="process_step2" method="POST" action="'.tep_href_link(basename($_SERVER['PHP_SELF'])).'" class="form-inline">';
				$html .= '<input type="hidden" name="checkout_step" value="'.$step.'" />';
				$html .= '<input type="hidden" name="checkout_modules[]" value="'.get_class($this).'" />';
				$html .= '<input type="hidden" name="customers_info_data" value="true" />';
				//eof login box
			}
			$html .= '<div class="row-fluid">';
			/************************************************************************/
			/*	show all blocks eg 'billing', 'delivery' with there active fields	*/
			/************************************************************************/
			foreach($this->fields as $block=>$block_data) {
				//if this is the delivery block, show checkbox for different delivery address then billing address
				$html .= '<div class="span6">';
				$html .= '<h3>'.$block_data['title'].'</h3>';
				if ($this->errors[get_class($this)][$block]) {
					$html .= '<div class="alert alert-error">'.Translate('Vul a.u.b. alle velden correct in.').'</div>';
				}
				$html .= '<div class="well">';
				if ($block == 'delivery') {
					//Check if delivery and billing address are different
					$different_address = false;
					foreach($this->fields['delivery']['fields'] as $field=>$field_data) {
						foreach($this->fields['billing']['fields'] as $bfield=>$bfield_data) {
							if ($field_data['dbvalue'] == $bfield_data['dbvalue']) {
								if ($to[$field] != $to[$bfield]) {
									$different_address = true;
									continue 2;
								}
								continue 1;
							}
						}
					}
					//checkbox
					$html .= '<div class="control-group">';
					$html .= '<div class="controls" id="showDelivery">';
					$html .= '<input class="form-checkbox" type="checkbox" id="check_different_delivery_address" name="different_delivery_address" value="true"'.($_POST['different_delivery_address'] == 'true'?' checked=checked':($different_address?' checked=checked':'')).' />';
					$html .= '<label class="checkbox inline" for="check_different_delivery_address" id="label_different_delivery_address">';
					$html .= Translate('Leveringsadres is verschillend van facturatieadres.');
					$html .= '</label>';
					$html .= '</div>';//end controls
					$html .= '</div>';//end control-group
				}
				//block title eg. 'Facturatie adres', 'leveringsadres', ...
				foreach($block_data['fields'] as $field=>$field_data) {
					if ($field_data['condition'] != '') {
						eval($field_data['condition'].';');
					} else {
						$show_field = true;
					}
					if ($show_field) {
						//check if there where errors with the filled in data for this field
						$class = '';
						if ($block == 'delivery') {
							$class .= ' hideDelivery';
						}
						if ($this->errors[get_class($this)][$block][$field]) {
							$class .= ' error';
							$html .= '<div class="form-error">'.Translate($this->errors[get_class($this)][$block][$field]).'</div>';
						}
						$html .= '<div class="control-group'.$class.'">';
						$html .= '<label class="control-label" for="'.get_class($this).'_input_'.$field.'">'.$field_data['label'].'</label>';
						$html .= '<div class="controls">';
						if ($field_data['type'] == 'dropdown') {
							if(strstr($field, 'country')) {
								$html .= parent::get_country_list(get_class($this).'_'.$field, (isset($_POST[get_class($this).'_'.$field])?$_POST[get_class($this).'_'.$field]:$to[$field]), 'id="'.get_class($this).'_input_'.$field.'"');
							}
						} else {
							$html .= '<input type="'.$field_data['input'].'" id="'.get_class($this).'_input_'.$field.'" name="'.get_class($this).'_'.$field.'" value="'.(isset($_POST[get_class($this).'_'.$field])?$_POST[get_class($this).'_'.$field]:$to[$field]).'" />';
						}
						$html .= '</div>';//end controle
						$html .= '</div>';//end control-group
					}
				}
				$html .= '</div>';//end well
				$html .= '</div>';//end span6
			}
			$html .= '</div>';//end row-fluid
			if ($this->config['create_account']['value'] == 'true' && !tep_session_is_registered('customer_id')) {
				$html .= '<a href="#" id="show_create_account_button" style="display:none;margin-bottom:5px;">'.Translate('Uw gegevens onthouden voor de volgende keer?').'</a>';
				$html .= '<div id="create_account_block">';
				$html .= '<h3>'.Translate('Maak een account aan').'</h3>';
				$html .= '<div class="well form-inline">';
				if (!empty($this->errors[get_class($this)]['create_account'])) {
					$html .= '<div class="alert alert-error">'.$this->errors[get_class($this)]['create_account'].'</div>';
				}
				//Terms
				$html .= '<div id="CAparagraph">';
				$html .= tep_draw_checkbox_field('TermsAgree','true', false, 'id="TermsAgree"'); 
				$html .= '<label for="TermsAgree">';
				$termsAgree = sprintf(Translate("Ik heb de %s gelezen en ga hiermee akkoord"), '<a href="'.tep_href_link('conditions_modal.php').'" target="_blank">'.Translate('Algemene voorwaarden').'</a>');
				if (CONDITIONS_CREATE_ACCOUNT == 'Link') {
					$html .= $termsAgree;
				}else{
					$html .= strip_tags($termsAgree);
				}
				$html .= '</label>';
				$html .= '</div>';
				//Password field
				$html .= '<input type="password" id="'.get_class($this).'_input_password" name="'.get_class($this).'_password" value="'.(isset($_POST[get_class($this).'_password'])?$_POST[get_class($this).'_password']:'').'" placeholder="'.Translate('Wachtwoord').'" />&nbsp;';
				//Password2 field
				$html .= '<input type="password" id="'.get_class($this).'_input_password2" name="'.get_class($this).'_password2" value="'.(isset($_POST[get_class($this).'_password2'])?$_POST[get_class($this).'_password2']:'').'" placeholder="'.Translate('Wachtwoord bevestigen').'" />&nbsp;';
				//Create account button
				$html .= '<button type="submit" name="action" value="create_account" class="btn">'.Translate('Registreer').'</button>';
				$html .= '</div>';//end well
				$html .= '</div>';//End create_account_block
			}
		}
		return $html;
	}
	public function process_data() {
		global $temp_orders_id, $step, $customer_id, $cart, $customer_country_id, $Customer;
		$dbfields = array();
		$billing_is_delivery = true;
		$this->errors[get_class($this)] = array();
		//check if customer wants to login
		if ($this->config['login']['value'] == 'true' && $_POST['action'] == 'login') {
			$loggedIn = log_customer_in($_POST['login_email'], $_POST['login_pass']);
			if ($loggedIn !== true) {
				$this->errors[get_class($this)]['login'] = $loggedIn;
			} else {
				$this->update_products_to_db($customer_country_id);
			}
			return false;
		} else {
			if (tep_session_is_registered('customer_id')) {//logged in
				if (isset($_POST['save_new_address']) && $_POST['save_new_address'] == 'true') {//add new address
					if ($_POST['action'] != 'cancel') {
						if (!$this->create_address_book_item()) {
							$_GET['add_new_address'] = 'true';
						}
					}
					return false;//do not proceed to next step
					//end add new address
				} else {//process choosen address
					//check if address is choosen for each block
					if (count($_POST['address_book_id']) != count($this->fields)) {
						//there isn't an address choosen for each block
						if (count($_POST['address_book_id']) == 0) {
							$this->errors[get_class($this)]['address_book_list'][] = Translate('Kies a.u.b. uw adressen.');
						} else {
							foreach($this->fields as $block=>$blockdata) {
								if (!isset($_POST['address_book_id'][$block])) {
									$this->errors[get_class($this)]['address_book_list'][] = Translate('Kies a.u.b. een adres voor').' "'.$blockdata['title'].'"';
								}
							}
						}
						return false;//de not proceed to next step
					} else{
						//address is choosen for each block, save choosen data
						//save address_book_id
						tep_db_query('UPDATE temp_orders SET delivery_address_id = "'.$_POST['address_book_id']['delivery'].'", billing_address_id = "'.$_POST['address_book_id']['billing'].'" WHERE orders_id = "'.$temp_orders_id.'"');
						$c_query = tep_db_query('SELECT customers_firstname, customers_email_address, customers_telephone, customers_default_address_id FROM customers WHERE customers_id = "'.$customer_id.'"');
						$c = tep_db_fetch_array($c_query);
						$dbfields['customers_name'] = $c['customers_firstname'];
						$firstblock = key($_POST['address_book_id']);
						foreach($_POST['address_book_id'] as $block=>$address_book_id) {
							$ab_query = tep_db_query('SELECT * FROM address_book WHERE address_book_id = "'.$address_book_id.'"');
							$ab = tep_db_fetch_array($ab_query);
							$ab = array_merge($ab, $c);
							if ($block == $firstblock) {
								$dbfields['customers_id'] = $customer_id;
								$dbfields['customers_company'] = $ab['entry_company'];
								$dbfields['customers_street_address'] = $ab['entry_street_address'];
								$dbfields['customers_suburb'] = $ab['entry_suburb'];
								$dbfields['customers_city'] = $ab['entry_city'];
								$dbfields['customers_postcode'] = $ab['entry_postcode'];
								$dbfields['customers_state'] = $ab['entry_state'];
								$dbfields['customers_country'] = $ab['entry_country_id'];
							}
							foreach($this->fields[$block]['fields'] as $field=>$fieldData) {
								//check if data is in database for this field
								if (isset($ab[$fieldData['dbvalue']])) {
									if ($field == 'billing_country') {
										$this->update_products_to_db($ab[$fieldData['dbvalue']]);
									}
									$dbfields[$field] = $ab[$fieldData['dbvalue']];
									//check if this field has a regular expression check
									if ($fieldData['expression'] != '') {
										if (!preg_match($fieldData['expression'], $ab[$fieldData['dbvalue']])) {
											$this->errors[get_class($this)][$field] = $fieldData['error'];
										}
									//check if this field has a function check
									} else if ($fieldData['function'] != '') {
										if (!$fieldData['function']($ab[$fieldData['dbvalue']])) {
											$this->errors[get_class($this)][$field] = $fieldData['error'];
										}
									}
								}
							}
						}
					}//end check if address is choosen for each block
				}//end process choosen address
			} else {//end logged in
				if(isset($_POST['different_delivery_address']) && $_POST['different_delivery_address'] == 'true') {
					$billing_is_delivery = false;
				}
				//go through all active fields
				foreach($this->fields as $block=>$blockData) {
					foreach($blockData['fields'] as $field=>$fieldData) {
						//check if data is posted for this field
						if ($fieldData['block'] == 'delivery' && $billing_is_delivery) {
							//delivery data is equal to billing data, don't process it
						} else {
							if (isset($_POST[get_class($this).'_'.$field])) {
								$dbfields[$field] = $_POST[get_class($this).'_'.$field];
								if ($billing_is_delivery && strstr($field, 'billing') && $field != 'billing_tva_intracom') {
									$delivery_field = str_replace('billing', 'delivery', $field);
									$dbfields[$delivery_field] = $_POST[get_class($this).'_'.$field];
								}
								if ($field == 'billing_name') {
									$dbfields['customers_name'] = $_POST[get_class($this).'_'.$field];
								}
								if ($field == 'billing_company') {
									$dbfields['customers_company'] = $_POST[get_class($this).'_'.$field];
								}
								if ($field == 'billing_street_address') {
									$dbfields['customers_street_address'] = $_POST[get_class($this).'_'.$field];
								}
								if ($field == 'billing_city') {
									$dbfields['customers_city'] = $_POST[get_class($this).'_'.$field];
								}
								if ($field == 'billing_postcode') {
									$dbfields['customers_postcode'] = $_POST[get_class($this).'_'.$field];
								}
								if ($field == 'billing_country') {
									$dbfields['customers_country'] = $_POST[get_class($this).'_'.$field];
								}
								//check if this field has a regular expression check
								if ($fieldData['expression'] != '') {
									if (!preg_match($fieldData['expression'], $_POST[get_class($this).'_'.$field])) {
										$this->errors[get_class($this)][$block][$field] = $fieldData['error'];
									}
								//check if this field has a function check
								} else if ($fieldData['function'] != '') {
									if (!$fieldData['function']($_POST[get_class($this).'_'.$field])) {
										$this->errors[get_class($this)][$block][$field] = $fieldData['error'];
									}
								}
							}
						}
					}
				}
				$this->update_products_to_db($_POST[get_class($this).'_billing_country']);
			}//end not logged in
			//save data to db
			if (count($dbfields) > 0) {
				if ($temp_orders_id == 0) {
					if (is_object($Checkout)) {
						$temp_orders_id = $Checkout->create_order();
						tep_db_perform('temp_orders', $dbfields, 'update', 'orders_id = "'.$temp_orders_id.'"');
					}
				} else{
					tep_db_perform('temp_orders', $dbfields, 'update', 'orders_id = "'.$temp_orders_id.'"');
				}
			}
			//check if there where errors found
			if (count($this->errors[get_class($this)]) > 0) {
				return false;
			} else {
				//Create account
				if ($this->config['create_account']['value'] == 'true' && !tep_session_is_registered('customer_id') && $_POST['action'] == 'create_account') {
					if ($_POST[get_class($this).'_password'] == $_POST[get_class($this).'_password2']) {
						//Transform data for use with Customer class
						$_POST['name'] = $_POST[get_class($this).'_billing_name'];
						$_POST['email_address'] = $_POST[get_class($this).'_customers_email_address'];
						$_POST['street_address'] = $_POST[get_class($this).'_billing_street_address'];
						$_POST['postcode'] = $_POST[get_class($this).'_billing_postcode'];
						$_POST['city'] = $_POST[get_class($this).'_billing_city'];
						$_POST['country'] = $_POST[get_class($this).'_billing_country'];
						$_POST['telephone'] = $_POST[get_class($this).'_customers_telephone'];
						$_POST['password'] = $_POST[get_class($this).'_password'];
						$_POST['confirmation'] = $_POST[get_class($this).'_password2'];
						//Create account
						$create_customer = $Customer->create_customer($_POST);
						//Check if there were errors
						//If everything is right the only error here can be 'terms'.
						//If there are other errors then 'terms' then the conditions are different in $Customer and here
						if (isset($create_customer['errors'])) {
							if (isset($create_customer['errors']['name'])) {
								$this->errors[get_class($this)]['billing']['billing_name'] = $create_customer['errors']['name'];
							}
							if (isset($create_customer['errors']['email_address'])) {
								$this->errors[get_class($this)]['billing']['customers_email_address'] = $create_customer['errors']['email_address'];
							}
							if (isset($create_customer['errors']['street_address'])) {
								$this->errors[get_class($this)]['billing']['billing_street_address'] = $create_customer['errors']['street_address'];
							}
							if (isset($create_customer['errors']['postcode'])) {
								$this->errors[get_class($this)]['billing']['billing_postcode'] = $create_customer['errors']['postcode'];
							}
							if (isset($create_customer['errors']['city'])) {
								$this->errors[get_class($this)]['billing']['billing_city'] = $create_customer['errors']['city'];
							}
							if (isset($create_customer['errors']['country'])) {
								$this->errors[get_class($this)]['billing']['billing_country'] = $create_customer['errors']['country'];
							}
							if (isset($create_customer['errors']['telephone'])) {
								$this->errors[get_class($this)]['billing']['customers_telephone'] = $create_customer['errors']['telephone'];
							}
							if (isset($create_customer['errors']['terms'])) {
								$this->errors[get_class($this)]['create_account'] = $create_customer['errors']['terms'];
							}
							if (isset($create_customer['errors']['confirmation'])) {
								$this->errors[get_class($this)]['create_account'] = $create_customer['errors']['confirmation'];
							}
							if (isset($create_customer['errors']['password'])) {
								$this->errors[get_class($this)]['create_account'] = $create_customer['errors']['password'];
							}
						} else {
							$customer_id = $create_customer['customer_id'];
							//Check if billing address and delivery address are different
							if (isset($_POST['different_delivery_address']) && $_POST['different_delivery_address'] == 'true') {
								$address_book_data = array();
								$address_book_data['name'] = $_POST[get_class($this).'_delivery_name'];
								$address_book_data['street_address'] = $_POST[get_class($this).'_delivery_street_address'];
								$address_book_data['city'] = $_POST[get_class($this).'_delivery_city'];
								$address_book_data['postcode'] = $_POST[get_class($this).'_delivery_postcode'];
								$address_book_data['country'] = $_POST[get_class($this).'_delivery_country'];
								$address_book_item = $Customer->create_address_book_item($customer_id, $address_book_data);
								if (isset($address_book_item['errors'])) {
									if (isset($address_book_item['errors']['name'])) {
										$this->errors[get_class($this)]['delivery']['delivery_name'] = $address_book_item['errors']['name'];
									}
									if (isset($address_book_item['errors']['street_address'])) {
										$this->errors[get_class($this)]['delivery']['delivery_street_address'] = $address_book_item['errors']['street_address'];
									}
									if (isset($address_book_item['errors']['postcode'])) {
										$this->errors[get_class($this)]['delivery']['delivery_postcode'] = $address_book_item['errors']['postcode'];
									}
									if (isset($address_book_item['errors']['city'])) {
										$this->errors[get_class($this)]['delivery']['delivery_city'] = $address_book_item['errors']['city'];
									}
									if (isset($address_book_item['errors']['country'])) {
										$this->errors[get_class($this)]['delivery']['delivery_country'] = $address_book_item['errors']['country'];
									}
									return false;
								} else {
									tep_db_query('UPDATE temp_orders SET delivery_address_id = "'.$address_book_item['address_book_id'].'" WHERE orders_id = "'.$temp_orders_id.'"');
								}
							}
							//Set billing address id
							tep_db_query('UPDATE temp_orders SET customers_id = "'.$create_customer['customer_id'].'", billing_address_id = "'.$create_customer['address_book_id'].'" WHERE orders_id = "'.$temp_orders_id.'"');
							return true;
						}
						return false;
					} else {
						$this->errors[get_class($this)]['create_account'] = Translate('De ingevoerde wachtwoorden moeten hetzelfde zijn. Voer ze opnieuw in.');
						return false;
					}
				}
				return true;
			}
		}
	}
	public function create_address_book_item() {
		global $customer_id;
		$sql_fields = array();
		$sql_fields['customers_id'] = $customer_id;
		$query = tep_db_query('SHOW COLUMNS FROM address_book');
		while($field = tep_db_fetch_array($query)) {
			if (isset($_POST[$field['Field']])) {
				if (!empty($_POST[$field['Field']])) {
					$sql_fields[$field['Field']] = stripslashes($_POST[$field['Field']]);
				} else {
					$this->errors[get_class($this)]['address_book'][$field['Field']] = $this->address_book_fields[$field['Field']];
				}
			}
		}
		foreach($this->fields['billing']['fields'] as $field=>$fieldData) {
			//check if data is in database for this field
			if (isset($sql_fields[$fieldData['dbvalue']])) {
				//check if this field has a regular expression check
				if ($fieldData['expression'] != '') {
					if (!preg_match($fieldData['expression'], $sql_fields[$fieldData['dbvalue']])) {
						$this->errors[get_class($this)]['address_book'][$fieldData['dbvalue']] = $fieldData['error'];
					}
				//check if this field has a function check
				} else if ($fieldData['function'] != '') {
					if (!$fieldData['function']($sql_fields[$fieldData['dbvalue']])) {
						$this->errors[get_class($this)]['address_book'][$fieldData['dbvalue']] = $fieldData['error'];
					}
				}
			}
		}
		if (count($sql_fields) > 1 && count($this->errors[get_class($this)]['address_book']) == 0) {
			tep_db_perform('address_book', $sql_fields, 'insert');
			return true;
		} else if (count($sql_fields) == 1) {
			return true;
		} else {
			return false;
		}
	}
	public function create_account() {
		// @TODO create class for account creation
		// @TODO add the option 'create account'
		//external class/function for creation of account with received form data
	}
	private function update_products_to_db($country_id) {
		global $cart;
		//update products to set tax by selected country
		if ($cart->count_contents() > 0) { //check if there are products in cart
			Checkout::add_products_to_db(false, $country_id);
		}
	}
	public function getZones() {
		return array();
	}
	public function administrator() {
		global $Modules, $login;
		echo '<h1>';
		echo get_class($this);
		if ($login == 'aboservice') {
			echo '<button type="button" id="delete_module" href="'.tep_href_link('checkout.php', 'module='.$_GET['module']).'&action=delete_module" class="btn btn-danger pull-right">'.Translate('Verwijder module').'</button>';
			echo '<button type="button" id="add_new" class="btn btn-primary pull-right" style="margin-right:5px;">'.Translate('Voeg een veld toe').'</button>';
		}
		echo '</h1>';
		echo '<hr />';
		if (isset($_POST['action']) && $_POST['action'] == 'save') {
			/********************/
			/*	Save changes	*/
			/********************/
			unset($_POST['action']);
			$db_array = array();
			foreach($_POST as $key=>$data) {
				foreach($data as $id=>$value) {
					$db_array[$id][$key] = $value;
				}
			}
			foreach($db_array as $id=>$data) {
				tep_db_perform('checkout_'.get_class($this), $data, 'update', 'id="'.$id.'"');
			}
			$this->update_config();
		} else if (isset($_POST['action']) && $_POST['action'] == 'create') {
			/********************/
			/*	Create field	*/
			/********************/
			unset($_POST['action']);
			tep_db_perform('checkout_'.get_class($this), $_POST, 'insert');
			$this->update_config();
		} else if (isset($_GET['action']) && $_GET['action'] == 'delete') {
			/********************/
			/*	Delete field	*/
			/********************/
			unset($_GET['action']);
			tep_db_query('DELETE FROM checkout_'.get_class($this).' WHERE id = "'.$_GET['id'].'"');
			unset($_GET['id']);
			$this->update_config();
		} else if (isset($_GET['action']) && $_GET['action'] == 'delete_module') {
			/********************/
			/*	Delete module	*/
			/********************/
			unset($_GET['action']);
			parent::delete_module(get_class($this));
		}
		?>
		<form name="<?php echo get_class($this);?>" class="form-horizontal" action="<?php echo tep_href_link('checkout.php', 'module='.$_GET['module']);?>" method="post">
			<input type="hidden" name="action" value="save" />
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th><?php echo Translate('Naam');?></th>
						<th><?php echo Translate('Type');?></th>
						<th><?php echo Translate('Input type');?></th>
						<th><?php echo Translate('Status');?></th>
						<th><?php echo Translate('Block');?></th>
						<th><?php echo Translate('Label');?></th>
						<th><?php echo Translate('Database value');?></th>
						<th><?php echo Translate('Regular expression');?></th>
						<th><?php echo Translate('Functie');?></th>
						<th><?php echo Translate('Voorwaarde');?></th>
						<th><?php echo Translate('Error');?></th>
						<th><?php echo Translate('Volgorde');?></th>
						<th><?php echo Translate('Verwijderen');?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$db_query = tep_db_query('SELECT * FROM checkout_'.get_class($this).' ORDER BY block asc, sort_order asc');
				while($db = tep_db_fetch_array($db_query)) {
					?>
					<tr<?php echo ($db['value']=='false'?' class="inactive"':'');?>>
						<td><input type="text" name="name[<?php echo $db['id'];?>]" value="<?php echo $db['name'];?>" class="input-large" /></td>
						<td>
							<select name="type[<?php echo $db['id'];?>]" class="input-medium">
								<option value="config"<?php echo ($db['type']=='config'?' selected="selected"':'');?>><?php echo Translate('configuratie');?></option>
								<option value="field"<?php echo ($db['type']=='field'?' selected="selected"':'');?>><?php echo Translate('Veld');?></option>
								<option value="dropdown"<?php echo ($db['type']=='dropdown'?' selected="selected"':'');?>><?php echo Translate('Dropdown');?></option>
							</select>
						</td>
						<td>
							<select name="input[<?php echo $db['id'];?>]" class="input-medium">
								<option value=""><?php echo Translate('Niet van toepassing');?></option>
								<option value="text"<?php echo ($db['input']=='text'?' selected="selected"':'');?>><?php echo Translate('Tekst');?></option>
								<option value="email"<?php echo ($db['input']=='email'?' selected="selected"':'');?>><?php echo Translate('E-mail');?></option>
								<option value="tel"<?php echo ($db['input']=='tel'?' selected="selected"':'');?>><?php echo Translate('Telefoon');?></option>
							</select>
						</td>
						<td>
							<select name="value[<?php echo $db['id'];?>]" class="input-small">
								<option value="true"<?php echo ($db['value']=='true'?' selected="selected"':'');?>><?php echo Translate('Actief');?></option>
								<option value="false"<?php echo ($db['value']=='false'?' selected="selected"':'');?>><?php echo Translate('Niet Actief');?></option>
							</select>
						</td>
						<td>
							<select name="block[<?php echo $db['id'];?>]" class="input-medium">
								<option value=""><?php echo Translate('Niet van toepassing');?></option>
								<option value="delivery"<?php echo ($db['block']=='delivery'?' selected="selected"':'');?>><?php echo Translate('Leveringsadres');?></option>
								<option value="billing"<?php echo ($db['block']=='billing'?' selected="selected"':'');?>><?php echo Translate('Facturatieadres');?></option>
							</select>
						</td>
						<td><input type="text" name="label[<?php echo $db['id'];?>]" value="<?php echo $db['label'];?>" class="input-medium" /></td>
						<td>
							<select name="dbvalue[<?php echo $db['id'];?>]" class="input-large">
								<option value=""><?php echo Translate('Niet van toepassing');?></option>
								<?php
								$dbvalues_array = array();
								$dbvalues_query = tep_db_query('SHOW COLUMNS FROM address_book');
								while($dbvalues = tep_db_fetch_array($dbvalues_query)) {
									$dbvalues_array[] = $dbvalues['Field'];
								}
								$dbvalues_query = tep_db_query('SHOW COLUMNS FROM temp_orders');
								while($dbvalues = tep_db_fetch_array($dbvalues_query)) {
									$dbvalues_array[] = $dbvalues['Field'];
								}
								$dbvalues_array = array_unique($dbvalues_array);
								foreach($dbvalues_array as $dbvalues) {
								?>
								<option value="<?php echo $dbvalues;?>"<?php echo ($db['dbvalue']==$dbvalues?' selected="selected"':'');?>><?php echo $dbvalues;?></option>
								<?php	
								}
								?>
							</select>
						</td>
						<td><input type="text" name="expression[<?php echo $db['id'];?>]" value="<?php echo stripslashes($db['expression']);?>" class="input-large" /></td>
						<td><input type="text" name="function[<?php echo $db['id'];?>]" value="<?php echo stripslashes($db['function']);?>" class="input-medium" /></td>
						<td><input type="text" name="condition[<?php echo $db['id'];?>]" value="<?php echo stripslashes($db['condition']);?>" class="input-xlarge" /></td>
						<td><input type="text" name="error[<?php echo $db['id'];?>]" value="<?php echo stripslashes($db['error']);?>" class="input-xlarge" /></td>
						<td><input type="number" name="sort_order[<?php echo $db['id'];?>]" value="<?php echo stripslashes($db['sort_order']);?>" class="input-mini" /></td>
						<td><a href="<?php echo tep_href_link('checkout.php', 'module='.$_GET['module'].'&action=delete&id='.$db['id']);?>" class="btn btn-danger"><?php echo Translate('Verwijderen');?></a></td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
			<div class="form-actions">
				<button class="btn btn-primary" type="submit"><?php echo Translate('Opslaan');?></button>
				<button class="btn" type="reset"><?php echo Translate('Annuleren');?></button>
			</div>
		</form>
		<div id="new_instance" style="display:none;">
		<form name="<?php echo get_class($this);?>" class="form-horizontal well" action="<?php echo tep_href_link('checkout.php', 'module='.$_GET['module']);?>" method="post">
			<input type="hidden" name="action" value="create" />
			<div class="control-group">
				<label class="control-label" for="name"><?php echo Translate('Naam');?></label>
				<div class="controls">
					<input type="text" name="name" value="" class="input-xlarge" id="name" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="type"><?php echo Translate('Type');?></label>
				<div class="controls">
					<select name="type" class="input-xlarge" id="type">
						<option value="config"><?php echo Translate('configuratie');?></option>
						<option value="field"><?php echo Translate('Veld');?></option>
						<option value="dropdown"><?php echo Translate('Dropdown');?></option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="input"><?php echo Translate('Input type');?></label>
				<div class="controls">
					<select name="input" class="input-xlarge" id="input">
						<option value=""><?php echo Translate('Niet van toepassing');?></option>
						<option value="text"><?php echo Translate('Tekst');?></option>
						<option value="email"><?php echo Translate('E-mail');?></option>
						<option value="tel"><?php echo Translate('Telefoon');?></option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="value"><?php echo Translate('Status');?></label>
				<div class="controls">
					<select name="value" class="input-xlarge" id="value">
						<option value="true"><?php echo Translate('Actief');?></option>
						<option value="false"><?php echo Translate('Niet Actief');?></option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="block"><?php echo Translate('Block');?></label>
				<div class="controls">
					<select name="block" class="input-xlarge" id="block">
						<option value=""><?php echo Translate('Niet van toepassing');?></option>
						<option value="delivery"><?php echo Translate('Leveringsadres');?></option>
						<option value="billing"><?php echo Translate('Facturatieadres');?></option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="label"><?php echo Translate('Label');?></label>
				<div class="controls">
					<input type="text" name="label" value="" class="input-xlarge" id="label" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="dbvalue"><?php echo Translate('Database value');?></label>
				<div class="controls">
					<select name="dbvalue" class="input-xlarge" id="dbvalue">
						<option value=""><?php echo Translate('Niet van toepassing');?></option>
						<?php
						$dbvalues_array = array();
						$dbvalues_query = tep_db_query('SHOW COLUMNS FROM address_book');
						while($dbvalues = tep_db_fetch_array($dbvalues_query)) {
							$dbvalues_array[] = $dbvalues['Field'];
						}
						$dbvalues_query = tep_db_query('SHOW COLUMNS FROM temp_orders');
						while($dbvalues = tep_db_fetch_array($dbvalues_query)) {
							$dbvalues_array[] = $dbvalues['Field'];
						}
						$dbvalues_array = array_unique($dbvalues_array);
						foreach($dbvalues_array as $dbvalues) {
						?>
						<option value="<?php echo $dbvalues;?>"><?php echo $dbvalues;?></option>
						<?php	
						}
						?>
					</select>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="expression"><?php echo Translate('Regular expression');?></label>
				<div class="controls">
					<input type="text" name="expression" value="" class="input-xlarge" id="expression" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="function"><?php echo Translate('Functie');?></label>
				<div class="controls">
					<input type="text" name="function" value="" class="input-xlarge" id="function" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="condition"><?php echo Translate('Voorwaarde');?></label>
				<div class="controls">
					<input type="text" name="condition" value="" class="input-xlarge" id="condition" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="error"><?php echo Translate('Error');?></label>
				<div class="controls">
					<input type="text" name="error" value="" class="input-xlarge" id="error" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="sort_order"><?php echo Translate('Volgorde');?></label>
				<div class="controls">
					<input type="text" name="sort_order" value="" class="input-xlarge" id="sort_order" />
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary" type="submit"><?php echo Translate('Opslaan');?></button>
				<button class="btn" type="reset"><?php echo Translate('Annuleren');?></button>
			</div>
		</form>
		</div>
		<?php
	}
	private function install() {
		//Check if translations are available
		parent::checkTranslations(dirname(__FILE__), $this->getTranslations());
		$install_array = array(
			'status' => array('type' => 'config', 'value' => 'true', 'options' => array('true', 'false')),
			'login' => array('type' => 'config', 'value' => 'true', 'options' => array('true', 'false')),
			'create_account' => array('type' => 'config', 'value' => 'true', 'options' => array('true', 'false')),
			'change_billing_address' => array('type' => 'config', 'value' => 'true', 'options' => array('true', 'false')),
			'billing_name' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'billing',
					'label' => 'Naam + Familienaam',
					'dbvalue' => 'entry_firstname',
					'expression' => "/^[\D]+[\s][\D]+$/",
					'error' => 'Vul a.u.b. een volledige naam in.',
					'sort_order' => 1),
			'billing_company' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'false',
					'options' => array('true', 'false'),
					'block' => 'billing',
					'label' => 'Bedrijf',
					'dbvalue' => 'entry_company',
					'condition' =>"!tep_session_is_registered('customer_id')",
					'sort_order' => 2),
			'billing_tva_intracom' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'false',
					'options' => array('true', 'false'),
					'block' => 'billing',
					'label' => 'BTW nr.',
					'dbvalue' => 'billing_tva_intracom',
					'condition' => "!tep_session_is_registered('customer_id')",
					'sort_order' => 3),
			'billing_street_address' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'billing',
					'label' => 'Straat + nr.',
					'dbvalue' => 'entry_street_address',
					'expression' => "/^[\D]+[\s][\d]+[\D]{0,3}+$/",
					'error' => 'Vul a.u.b. een straat + nummer in.',
					'sort_order' => 4),
			'billing_city' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'billing',
					'label' => 'Gemeente',
					'dbvalue' => 'entry_city',
					'expression' => "/^[\D]+[\s]?[\D]*$/",
					'error' => 'Vul a.u.b. een gemeente in.',
					'sort_order' => 5),
			'billing_postcode' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'billing',
					'label' => 'Postcode',
					'dbvalue' => 'entry_postcode',
					'expression' => "/^[a-zA-Z0-9]+$/",
					'error' => 'Vul a.u.b. een postcode in.',
					'sort_order' => 6),
			'billing_country' => array(
					'type' => 'dropdown',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'billing',
					'label' => 'Land',
					'dbvalue' => 'entry_country_id',
					'expression' => "/^[1-9][0-9]*$/",
					'error' => 'Kies a.u.b. een land.',
					'sort_order' => 7),
			'customers_telephone' => array(
					'type' => 'field',
					'input' => 'tel',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'billing',
					'label' => 'Tel.',
					'dbvalue' => 'customers_telephone',
					'function' => "validate_phone",
					'error' => 'Vul a.u.b. een telefoon nummer in.',
					'sort_order' => 8),
			'customers_email_address' => array(
					'type' => 'field',
					'input' => 'email',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'billing',
					'label' => 'E-mailadres',
					'dbvalue' => 'customers_email_address',
					'function' => "validate_email",
					'error' => 'Vul a.u.b. een geldig e-mail adres in.',
					'sort_order' => 9),
			'delivery_name' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'delivery',
					'label' => 'Naam + Familienaam',
					'dbvalue' => 'entry_firstname',
					'expression' => "/^[\D]+[\s][\D]+$/",
					'error' => 'Vul a.u.b. een volledige naam in.',
					'sort_order' => 1),
			'delivery_company' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'false',
					'options' => array('true', 'false'),
					'block' => 'delivery',
					'label' => 'Bedrijf',
					'dbvalue' => 'entry_company',
					'condition' => "!tep_session_is_registered('customer_id')",
					'sort_order' => 2),
			'delivery_tva_intracom' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'false',
					'options' => array('true', 'false'),
					'block' => 'delivery',
					'label' => 'BTW nr.',
					'dbvalue' => '',
					'condition' => "!tep_session_is_registered('customer_id')",
					'sort_order' => 3),
			'delivery_street_address' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'delivery',
					'label' => 'Straat + nr.',
					'dbvalue' => 'entry_street_address',
					'expression' => "/^[\D]+[\s][\d]+[\D]{0,3}+$/",
					'error' => 'Vul a.u.b. een straat + nummer in.',
					'sort_order' => 4),
			'delivery_city' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'delivery',
					'label' => 'Gemeente',
					'dbvalue' => 'entry_city',
					'expression' => "/^[\D]+[\s]?[\D]*$/",
					'error' => 'Vul a.u.b. een gemeente in.',
					'sort_order' => 5),
			'delivery_postcode' => array(
					'type' => 'field',
					'input' => 'text',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'delivery',
					'label' => 'Postcode',
					'dbvalue' => 'entry_postcode',
					'expression' => "/^[a-zA-Z0-9]+$/",
					'error' => 'Vul a.u.b. een postcode in.',
					'sort_order' => 6),
			'delivery_country' => array(
					'type' => 'dropdown',
					'value' => 'true',
					'options' => array('true', 'false'),
					'block' => 'delivery',
					'label' => 'Land',
					'dbvalue' => 'entry_country_id',
					'expression' => "/^[1-9][0-9]*$/",
					'error' => 'Kies a.u.b. een land.',
					'sort_order' => 7),
		);
		tep_db_query('CREATE TABLE checkout_'.get_class($this).'(
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			`name` VARCHAR(255),
			`type` VARCHAR(255),
			`input` VARCHAR(255),
			`value` VARCHAR(255),
			`options` VARCHAR(255),
			`block` VARCHAR(255),
			`label` VARCHAR(255),
			`dbvalue` VARCHAR(255),
			`expression` VARCHAR(255),
			`function` VARCHAR(255),
			`condition` VARCHAR(255),
			`error` VARCHAR(255),
			`sort_order` INT(11))');
		tep_db_query('CREATE INDEX name ON checkout_'.get_class($this).' (name)');
		tep_db_query('CREATE INDEX type ON checkout_'.get_class($this).' (type)');
		foreach($install_array as $key=>$value) {
			$db_array = array();
			$db_array['name'] = $key;
			foreach($value as $name=>$data) {
				if (is_array($data)) {
					$data = serialize($data);
				}
				$db_array[$name] = $data;
			}
			tep_db_perform('checkout_'.get_class($this), $db_array, 'insert');
		}
	}
	private function getTranslations() {
		return array('Facturatieadres' => array(
							'1' => 'Facturatieadres', 
							'2' => 'Adresse de facturation', 
							'3' => 'Billing Address', 
							'4' => 'Rechnungsadresse')
					,'Leveringsadres' => array(
							'1' => 'Leveringsadres', 
							'2' => 'Adresse de livraison', 
							'3' => 'Delivery Address', 
							'4' => 'Lieferanschrift')
					,'Vul a.u.b. een volledige naam in.' => array(
							'1' => 'Vul a.u.b. een volledige naam in.', 
							'2' => 'Entrez un nom complet s\'il vous plaît.', 
							'3' => 'Enter a full name if you please.', 
							'4' => 'Geben Sie einen vollständigen Namen, wenn ich bitten darf.')
					,'Vul a.u.b. een straat + nummer in.' => array(
							'1' => 'Vul a.u.b. een straat + nummer in.', 
							'2' => 'Entrez dans une rue + numéro, s\'il vous plaît.', 
							'3' => 'Enter a street + number, if you please.', 
							'4' => 'Geben Sie eine Straße + Nummer, wenn ich bitten darf.')
					,'Vul a.u.b. een postcode in.' => array(
							'1' => 'Vul a.u.b. een postcode in.', 
							'2' => 'Entrez un code postal, s\'il vous plaît.', 
							'3' => 'Enter a zip code if you please.', 
							'4' => 'Geben Sie eine Postleitzahl wenn ich bitten darf.')
					,'Vul a.u.b. een gemeente in.' => array(
							'1' => 'Vul a.u.b. een gemeente in.', 
							'2' => 'Remplissez une municipalité s\'il vous plaît.', 
							'3' => 'Fill in a municipality if you please.', 
							'4' => 'Füllen Sie in einer Gemeinde, wenn ich bitten darf.')
					,'Kies a.u.b. een land.' => array(
							'1' => 'Kies a.u.b. een land.', 
							'2' => 'Choisissez un pays s\'il vous plaît.', 
							'3' => 'Choose a country if you please.', 
							'4' => 'Wählen Sie ein Land, wenn ich bitten darf.')
					,'Voeg nieuw adres toe' => array(
							'1' => 'Voeg nieuw adres toe', 
							'2' => 'Ajouter une nouvelle adresse', 
							'3' => 'Add new address', 
							'4' => 'Fügen Sie neue Adresse')
					,'Opgelet!' => array(
							'1' => 'Opgelet!', 
							'2' => 'Attention!', 
							'3' => 'Attention!', 
							'4' => 'Achtung!')
					,'Vul a.u.b. alle velden correct in.' => array(
							'1' => 'Vul a.u.b. alle velden correct in.', 
							'2' => 'Si vous s\'il vous plaît remplir tous les champs correctement.', 
							'3' => 'If you please fill in all fields correctly.', 
							'4' => 'Wenn Sie füllen Sie bitte alle Felder korrekt aus.')
					,'Nieuw adres opslaan' => array(
							'1' => 'Nieuw adres opslaan', 
							'2' => 'Magasin Nouvelle adresse', 
							'3' => 'Save new address', 
							'4' => 'Neue Adresse speichern')
					,'Annuleren' => array(
							'1' => 'Annuleren', 
							'2' => 'Annuler', 
							'3' => 'Cancel', 
							'4' => 'Stornieren')
					,'Hoofdadres' => array(
							'1' => 'Hoofdadres', 
							'2' => 'Adresse principale', 
							'3' => 'Main Address', 
							'4' => 'Main Anschrift')
					,'Verwijder adres' => array(
							'1' => 'Verwijder adres', 
							'2' => 'Supprimez l\'adresse', 
							'3' => 'Remove address', 
							'4' => 'Adresse entfernen')
					,'Nieuw adres toevoegen' => array(
							'1' => 'Nieuw adres toevoegen', 
							'2' => 'Ajouter une nouvelle adresse', 
							'3' => 'Add a new address', 
							'4' => 'Eine neue Adresse hinzufügen')
					,'E-mailadres' => array(
							'1' => 'E-mailadres', 
							'2' => 'Email', 
							'3' => 'Email', 
							'4' => 'E-Mail')
					,'Paswoord' => array(
							'1' => 'Paswoord', 
							'2' => 'Mot de passe', 
							'3' => 'Password', 
							'4' => 'Kennwort')
					,'Log in' => array(
							'1' => 'Log in', 
							'2' => 'Connectez-vous', 
							'3' => 'Log in', 
							'4' => 'Einloggen')
					,'Leveringsadres is verschillend van facturatieadres.' => array(
							'1' => 'Leveringsadres is verschillend van facturatieadres.', 
							'2' => 'Adresse de livraison est différente de l\'adresse de facturation.', 
							'3' => 'Delivery address is different from billing address.', 
							'4' => 'Lieferanschrift ist abweichend von der Rechnungsadresse.')
					,'Maak een account aan' => array(
							'1' => 'Maak een account aan',
							'2' => 'Créer un compte',
							'3' => 'Create an account',
							'4' => 'Erstellen Sie ein Konto')
					,'Algemene voorwaarden' => array(
							'1' => 'Algemene voorwaarden',
							'2' => 'Conditions générales',
							'3' => 'General conditions',
							'4' => 'Allgemeinen Geschäftsbedingungen')
					,'Wachtwoord' => array(
							'1' => 'Wachtwoord',
							'2' => 'Mot de passe',
							'3' => 'Password',
							'4' => 'Kennwort')
					,'Wachtwoord bevestigen' => array(
							'1' => 'Wachtwoord bevestigen',
							'2' => 'Confirmer Mot de passe',
							'3' => 'Confirm Password',
							'4' => 'Kennwort bestätigen')
					,'Registreer' => array(
							'1' => 'Registreer',
							'2' => 'S\'inscrire',
							'3' => 'Register',
							'4' => 'Registrieren')
					,'Kies a.u.b. uw adressen.' => array(
							'1' => 'Kies a.u.b. uw adressen.', 
							'2' => 'Choisissez vos adresses s\'il vous plaît.', 
							'3' => 'Choose your addresses if you please.', 
							'4' => 'Wählen Sie Ihre Adressen, wenn es Ihnen beliebt.')
					,'Kies a.u.b. een adres voor' => array(
							'1' => 'Kies a.u.b. een adres voor', 
							'2' => 'Choisissez une adresse, s\'il vous plaît', 
							'3' => 'Choose an address if you please', 
							'4' => 'Wählen Sie eine Adresse, wenn Sie bitte')
					,'De ingevoerde wachtwoorden moeten hetzelfde zijn. Voer ze a.u.b. opnieuw in.' => array(
							'1' => 'De ingevoerde wachtwoorden moeten hetzelfde zijn. Voer ze a.u.b. opnieuw in.',
							'2' => 'Les mots de passe entrés doivent être identiques. Les saisir à nouveau, s\'il vous plaît.',
							'3' => 'The entered passwords must be identical. Enter them again if you please.',
							'4' => 'Die eingegebenen Passwörter müssen identisch sein. Geben Sie sie wieder, wenn ich bitten darf.')
					,'Verwijder module' => array(
							'1' => 'Verwijder module', 
							'2' => 'Retirez le module', 
							'3' => 'Remove module', 
							'4' => 'Modul entfernen')
					,'Voeg een veld toe' => array(
							'1' => 'Voeg een veld toe',
							'2' => 'Ajouter un champ',
							'3' => 'Add a field',
							'4' => 'Fügen Sie ein Feld')
					,'Naam' => array(
							'1' => 'Naam',
							'2' => 'Nom',
							'3' => 'Name',
							'4' => 'Name')
					,'Type' => array(
							'1' => 'Type',
							'2' => 'Type',
							'3' => 'Type',
							'4' => 'Typ')
					,'Input type' => array(
							'1' => 'Input type',
							'2' => 'Type d\'entrée',
							'3' => 'Input type',
							'4' => 'Art des Eingangs')
					,'Status' => array(
							'1' => 'Status',
							'2' => 'Statut',
							'3' => 'Status',
							'4' => 'Status')
					,'Block' => array(
							'1' => 'Block',
							'2' => 'Block',
							'3' => 'Block',
							'4' => 'Block')
					,'Label' => array(
							'1' => 'Label',
							'2' => 'Étiquette',
							'3' => 'Label',
							'4' => 'Etikett')
					,'Database value' => array(
							'1' => 'Database value',
							'2' => 'Database value',
							'3' => 'Database value',
							'4' => 'Database value')
					,'Regular expression' => array(
							'1' => 'Regular expression',
							'2' => 'Regular expression',
							'3' => 'Regular expression',
							'4' => 'Regular expression')
					,'Functie' => array(
							'1' => 'Functie',
							'2' => 'Fonction',
							'3' => 'Function',
							'4' => 'Funktion')
					,'Voorwaarde' => array(
							'1' => 'Voorwaarde',
							'2' => 'Condition',
							'3' => 'Condition',
							'4' => 'Zustand')
					,'Error' => array(
							'1' => 'Error',
							'2' => 'Error',
							'3' => 'Error',
							'4' => 'Error')
					,'Volgorde' => array(
							'1' => 'Volgorde',
							'2' => 'Ordre',
							'3' => 'Sort order',
							'4' => 'Sortierung')
					,'Verwijderen' => array(
							'1' => 'Verwijderen',
							'2' => 'Supprimer',
							'3' => 'Remove',
							'4' => 'Entfernen')
					,'configuratie' => array(
							'1' => 'configuratie',
							'2' => 'configuration',
							'3' => 'configuration',
							'4' => 'Konfiguration')
					,'Veld' => array(
							'1' => 'Veld',
							'2' => 'Domaine',
							'3' => 'Field',
							'4' => 'Feld')
					,'Dropdown' => array(
							'1' => 'Dropdown',
							'2' => 'Dropdown',
							'3' => 'Dropdown',
							'4' => 'Dropdown')
					,'Niet van toepassing' => array(
							'1' => 'Niet van toepassing',
							'2' => 'Non applicable',
							'3' => 'Not applicable',
							'4' => 'Nicht anwendbar')
					,'Tekst' => array(
							'1' => 'Tekst',
							'2' => 'Texte',
							'3' => 'Text',
							'4' => 'Text')
					,'E-mail' => array(
							'1' => 'E-mail', 
							'2' => 'Email', 
							'3' => 'Email', 
							'4' => 'E-Mail')
					,'Telefoon' => array(
							'1' => 'Telefoon',
							'2' => 'Téléphone',
							'3' => 'Phone',
							'4' => 'Telefon')
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
					,'Opslaan' => array(
							'1' => 'Opslaan',
							'2' => 'Rappeler',
							'3' => 'Save',
							'4' => 'Merken')
					,'Naam + Familienaam' => array(
							'1' => 'Naam + Familienaam', 
							'2' => 'Nom + Prénom', 
							'3' => 'Name + Surname', 
							'4' => 'Name + Vorname')
					,'Bedrijf' => array(
							'1' => 'Bedrijf', 
							'2' => 'Entreprise', 
							'3' => 'Company', 
							'4' => 'Geschäft')
					,'BTW nr.' => array(
							'1' => 'BTW nr.', 
							'2' => 'Nr TVA', 
							'3' => 'VAT no', 
							'4' => 'Umsatzsteuernummer')
					,'Straat + nr.' => array(
							'1' => 'Straat + nr.', 
							'2' => 'Rue + pas', 
							'3' => 'Street + no', 
							'4' => 'Straße + Nr.')
					,'Gemeente' => array(
							'1' => 'Gemeente', 
							'2' => 'Ville', 
							'3' => 'City', 
							'4' => 'Gemeinde')
					,'Postcode' => array(
							'1' => 'Postcode', 
							'2' => 'Code postal', 
							'3' => 'Zip code', 
							'4' => 'Postleitzahl')
					,'Land' => array(
							'1' => 'Land', 
							'2' => 'Pays', 
							'3' => 'Country', 
							'4' => 'Land')
					,'Tel.' => array(
							'1' => 'Tel.', 
							'2' => 'Tél.', 
							'3' => 'Tel.', 
							'4' => 'Tel.')
					,'Vul a.u.b. een telefoon nummer in.' => array(
							'1' => 'Vul a.u.b. een telefoon nummer in.', 
							'2' => 'Entrez un numéro de téléphone s\'il vous plaît.', 
							'3' => 'Enter a phone number if you please.', 
							'4' => 'Geben Sie eine Telefonnummer, wenn ich bitten darf.')
					,'Vul a.u.b. een geldig e-mail adres in.' => array(
							'1' => 'Vul a.u.b. een geldig e-mail adres in.', 
							'2' => 'Si vous s\'il vous plaît entrer une adresse email valide.', 
							'3' => 'If you please enter a valid email address.', 
							'4' => 'Wenn Sie bitte geben Sie eine gültige E-Mail-Adresse.')
					,'Ik heb de %s gelezen en ga hiermee akkoord:' => array(
							'1' => 'Ik heb de %s gelezen en ga hiermee akkoord:',
							'2' => 'J\'ai lu les %s et acceptez:',
							'3' => 'I have read the %s and agree:',
							'4' => 'Ich habe die %s gelesen und akzeptiere:')
					);
	}
}
?>