<?php
//payment class for cod => cash on delivery
class Table extends Modules {
	private $Checkout;
	public $type = 'shipping'
		 , $sort_order = 20
		 , $instances = array()
		 , $instance_max = array();
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
	public function is_active($country = 0) {
		global $temp_orders_id;
		$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
		foreach($this->instances as $data) {
			if ($data['status'] == 'true') {
				if ($temp_orders_id > 0) {
					if ($country > 0) {
						if (parent::checkZone($data['zone'], $country)) {
							return true;
						}
					} else {
						if (parent::checkZone($data['zone'], $temp_data[$temp_orders_id]['orders']['delivery_country'])) {
							return true;
						}
					}
				} else {
					if ($country > 0) {
						if (parent::checkZone($data['zone'], $country)) {
							return true;
						}
					} else {
						return true;
					}
				}
			}
		}
		return false;
	}
	public function output($step = 0) {
		global $currencies, $temp_orders_id, $cart;
		$html = '';
		//check for errors
		if (isset(Checkout::$errors[$this->type])) {
			$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate(Checkout::$errors[$this->type]).'</div>';
			unset(Checkout::$errors[$this->type]);
		}
		//select shipping method if orders_id is known
		//also get delivery country
		if (!empty($temp_orders_id)) {
			$selected_query = tep_db_query('SELECT shipping_method, delivery_country FROM temp_orders WHERE orders_id = "'.$temp_orders_id.'"');
			$selected = tep_db_fetch_array($selected_query);
		}
		foreach($this->instances as $id=>$data) {
			if ($data['status'] == 'true') {
				if (parent::checkZone($data['zone'], $selected['delivery_country'])) {
					$this->calculate_instance_max($id);
					//if ($this->instance_max[$id] > 0) { //@TODO check if this is needed???
						if (isset(Checkout::$errors[$id])) {
							$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate(Checkout::$errors[$id]).'</div>';
						}
						$html .= '<label class="control-label" for="'.$this->type.'_'.$id.'" style="display:block;">';
						$html .= '<div class="'.$this->type.'_item clearfix">';
						$html .= '<input type="radio" name="'.$this->type.'" value="'.$id.'" id="'.$this->type.'_'.$id.'"'.($selected['shipping_method']==$id?' checked=checked':'').' />';
						$html .= '<div class="'.$this->type.'_title">&nbsp; '.Translate($data['title']).'</div>';
						$html .= '<div class="'.$this->type.'_quote">'.($this->calculate_quote($id, $this->instance_max[$id])>0?$currencies->display_price($this->calculate_quote($id, $this->instance_max[$id]), 0):Translate('Gratis')).'</div>';
						if (!empty($data['description'])) {
							$html .= '<div class="'.$this->type.'_description">&nbsp; '.Translate($data['description']).'</div>';
						}
						$html .= '</div>';

						$html .= '</label>';
					//}
				}
			}
		}
		if (GIFT_WRAP=='true')
		$html .= '<div class="shipping_item clearfix"><div class="shipping_title"><input type="checkbox" name="gift_wrap"><span style="margin-left:8px;">'.Translate('Gift wrap').'</span></div></div>';
		return $html;
	}
	public function process_data() {
		global $temp_orders_id, $currencies;
		$strlen = strlen(get_class($this));

		if (isset($_POST[$this->type])) {
			if (substr($_POST[$this->type], 0, $strlen) == get_class($this)) {
				if ($temp_orders_id == 0) {
					$temp_orders_id = parent::create_order();
				}
				$this->calculate_instance_max($_POST[$this->type]);
				if (isset($_POST['gift_wrap']) && $_POST['gift_wrap']=="on" ) $gift_wrap="1";
				else $gift_wrap="0";
				$ot_query = tep_db_query('SELECT orders_total_id FROM temp_orders_total WHERE orders_id = "'.$temp_orders_id.'" AND class="'.$this->type.'"');
				if (tep_db_num_rows($ot_query)>0) {
					tep_db_query('UPDATE temp_orders_total SET title="'.Translate($this->instances[$_POST[$this->type]]['title']).'", text="'.$currencies->display_price($this->calculate_quote($_POST[$this->type], $this->instance_max[$_POST[$this->type]]), 0).'", value="'.$this->calculate_quote($_POST[$this->type], $this->instance_max[$_POST[$this->type]]).'", sort_order="'.$this->instances[$_POST[$this->type]]['sort_order'].'" WHERE orders_id = "'.$temp_orders_id.'" AND class="'.$this->type.'"');
				} else {
					tep_db_query('INSERT INTO temp_orders_total (orders_id, title, text, value, class, sort_order) VALUES ("'.$temp_orders_id.'", "'.Translate($this->instances[$_POST[$this->type]]['title']).'", "'.$currencies->display_price($this->calculate_quote($_POST[$this->type], $this->instance_max[$_POST[$this->type]]), 0).'", "'.$this->calculate_quote($_POST[$this->type], $this->instance_max[$_POST[$this->type]]).'", "'.$this->type.'", "'.$this->instances[$_POST[$this->type]]['sort_order'].'")');
				}
				tep_db_query('UPDATE temp_orders SET shipping_method = "'.$_POST[$this->type].'", gift_wrap="'.$gift_wrap.'" WHERE orders_id = "'.$temp_orders_id.'"');
				if(extension_loaded('apc') && ini_get('apc.enabled')) {
					apc_delete('temp_orders_total_'.$temp_orders_id);
				}
			}
			return true;
		} else {
			$this->errors[$this->type] = Translate('Kies a.u.b. een verzendmethode.');
			return false;
		}
	}
	public function output_array($country = 0) {
		global $currencies, $temp_orders_id, $cart;
		$array = array();
		foreach($this->instances as $id=>$data) {
			if ($data['status'] == 'true') {
				if (parent::checkZone($data['zone'], $country)) {
					$this->calculate_instance_max($id);
					$array[] = array('title' => Translate($data['title']), 'description' => Translate($data['description']), 'quote' => $this->calculate_quote($id, $this->instance_max[$id]));
				}
			}
		}
		return $array;
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
	private function calculate_instance_max($instance) {
		global $cart, $temp_orders_id;
		$temp_order_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
		//check mode
		if ($this->instances[$instance]['mode'] == 'weight') {
			$this->instance_max[$instance] = Checkout::calculate_weight($temp_orders_id, 'temp_');
		} else {
			//mode == price
			$subtotal = 0;
			if (is_array($temp_order_data[$temp_orders_id]['orders_total'])) {
				foreach($temp_order_data[$temp_orders_id]['orders_total'] as $total_data) {
					if ($total_data['class'] == 'order_subtotal') {
						$subtotal = $total_data['value'];
					}
				}
			}
			if ($subtotal > 0) {
				$this->instance_max[$instance] = $subtotal;
			} else {
				$this->instance_max[$instance] = $cart->show_total();
			}
		}
	}
	public function calculate_quote($instance, $max) {
		//max == weight or price of order
		$quote = 0;
		$table_cost = preg_split("/[:;]/" , $this->instances[$instance]['quote']);
		$size = sizeof($table_cost);
		for ($i=0, $n=$size; $i<$n; $i+=2) {
			if ($max <= $table_cost[$i]) {
				$quote = $table_cost[$i+1];
				break;
			}
		}
		return $quote;
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
						if ($key == 'zone') {
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
				<label class="control-label" for="<?php echo $instance;?>_mode"><?php echo Translate('Mode');?></label>
				<div class="controls">
					<div class="btn-group" data-toggle="buttons-radio">
						<button type="submit" name="mode[<?php echo $instance_data['id'];?>]" value="price" class="btn<?php echo ($instance_data['mode'] == 'price'?' active':'');?>"><?php echo Translate('Prijs');?></button>
						<button type="submit" name="mode[<?php echo $instance_data['id'];?>]" value="weight" class="btn<?php echo ($instance_data['mode'] == 'weight'?' active':'');?>"><?php echo Translate('Gewicht');?></button>
					</div>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_quote"><?php echo Translate('Verzendkost');?></label>
				<div class="controls">
					<input type="text" name="quote[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['quote'];?>" class="input-xlarge" id="<?php echo $instance;?>_quote" />
					<span class="help-block"><?php echo Translate('De verzendkost gebasseerd op de totale prijs of gewicht van de artikels. VB. 25:8.50;50:5.50;... Tot 25 is de kostprijs 8.50, vanaf 25 tot 50 is de kostprijs 5.50, ...');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="<?php echo $instance;?>_sort_order"><?php echo Translate('Volgorde');?></label>
				<div class="controls">
					<input type="text" name="sort_order[<?php echo $instance_data['id'];?>]" value="<?php echo $instance_data['sort_order'];?>" class="input-xlarge" id="<?php echo $instance;?>_sort_order" />
					<span class="help-block"><?php echo Translate('Volgorde waarin de instanties worden getoond in de checkout.');?></span>
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
				<label class="control-label" for="<?php echo get_class($this).'_'.$new_id;?>_sort_order"><?php echo Translate('Volgorde');?></label>
				<div class="controls">
					<input type="text" name="sort_order[<?php echo $new_id;?>]" value="" class="input-xlarge" id="<?php echo get_class($this).'_'.$new_id;?>_sort_order" />
					<span class="help-block"><?php echo Translate('Volgorde waarin de instanties worden getoond in de checkout.');?></span>
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
			'title' => 'Verzending België',
			'description' => '',
			'zone' => '*',
			'mode' => 'price',
			'quote' => '10:10.00;20:20.00;99999999:100.00',
			'order_status_id' => '3',
			'sort_order' => '20'
		);
		tep_db_query('CREATE TABLE checkout_'.get_class($this).'(
			id INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			status VARCHAR(255),
			title VARCHAR(255),
			description VARCHAR(255),
			zone VARCHAR(255),
			mode VARCHAR(255),
			quote VARCHAR(255),
			order_status_id INT(11),
			sort_order INT(11))');
		tep_db_query('CREATE INDEX title ON checkout_'.get_class($this).' (`title`)');
		tep_db_query('CREATE INDEX status ON checkout_'.get_class($this).' (`status`)');
		tep_db_query('CREATE INDEX zone ON checkout_'.get_class($this).' (`zone`)');
		tep_db_perform('checkout_'.get_class($this), $install_array, 'insert');
	}
	private function getTranslations() {
		return array('Opgelet!' => array('1' => 'Opgelet!', '2' => 'Attention!', '3' => 'Attention!', '4' => 'Achtung!')
					,'Kies a.u.b. een verzendmethode.' => array('1' => 'Kies a.u.b. een verzendmethode.', '2' => 'Choisissez une méthode d\'expédition, s\'il vous plaît.', '3' => 'Choose a shipping method if you please.', '4' => 'Wählen Sie eine Versandart, wenn ich bitten darf.')
					,'Verzending België' => array('1' => 'Verzending België', '2' => 'Livraison en Belgique', '3' => 'Shipping Belgium', '4' => 'Liefer-Belgien')
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
					,'All' => array(
							'1' => 'All',
							'2' => 'Tous',
							'3' => 'All',
							'4' => 'Alle')
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
					,'Mode' => array(
							'1' => 'Mode',
							'2' => 'Mode',
							'3' => 'Mode',
							'4' => 'Mode')
					,'Prijs' => array(
							'1' => 'Prijs',
							'2' => 'Prix',
							'3' => 'Price',
							'4' => 'Preis')
					,'Gewicht' => array(
							'1' => 'Gewicht',
							'2' => 'Poids',
							'3' => 'Weight',
							'4' => 'Gewicht')
					,'Verzendkost' => array(
							'1' => 'Verzendkost',
							'2' => 'Coût de la livraison',
							'3' => 'Shipping Cost',
							'4' => 'Versandkosten')
					,'De verzendkost gebasseerd op de totale prijs of gewicht van de artikels. VB. 25:8.50;50:5.50;... Tot 25 is de kostprijs 8.50, vanaf 25 tot 50 is de kostprijs 5.50, ...' => array(
							'1' => 'De verzendkost gebasseerd op de totale prijs of gewicht van de artikels. VB. 25:8.50;50:5.50;... Tot 25 is de kostprijs 8.50, vanaf 25 tot 50 is de kostprijs 5.50, ...',
							'2' => 'Le coût d\'expédition sur la base du coût total ou du poids des articles. VB. 25:8.50, 50:5.50; ... 8,50 à 25 est le prix de revient, de 25 à 50 est le prix de revient 5.50, ...',
							'3' => 'The shipping cost based on the total cost or weight of articles. VB. 25:8.50, 50:5.50; ... 8.50 to 25 is the cost price, from 25 to 50 is the cost price 5.50, ...',
							'4' => 'Die Versandkosten auf den Gesamtkosten oder das Gewicht der Artikel basiert. VB. 25:8.50, 50:5.50; ... 8,50 bis 25 ist der Einstandspreis, 25 bis 50 ist der Einstandspreis 5,50, ...')
					,'Volgorde' => array(
							'1' => 'Volgorde',
							'2' => 'Ordre',
							'3' => 'Sort order',
							'4' => 'Sortierung')
					,'Volgorde waarin de instanties worden getoond in de checkout.' => array(
							'1' => 'Volgorde waarin de instanties worden getoond in de checkout.',
							'2' => 'L\'ordre dans lequel les corps sont affichés dans la caisse.',
							'3' => 'Order in which the bodies are displayed in the checkout.',
							'4' => 'In welcher Reihenfolge die Körper sind in der Kasse angezeigt.')
					);
	}
}
?>