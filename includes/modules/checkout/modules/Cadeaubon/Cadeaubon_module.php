<?php
//coupon class
class Cadeaubon extends Modules {
	public $type = 'cadeaubon'
		 , $sort_order = 80
		 , $config = array()
		 , $success = array();
	public function __construct() {
		//load config => title, text, sort_order, status, order_status_id, works_with_shipping_module
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
			return true;
		}
		return false;
	}
	public function output($step = 0) {
		global $temp_orders_id, $currencies;
		$html = '';
		if (tep_session_is_registered('customer_id')) {
			$amount = $this->calculate_amount();
			if ($amount > 0) {
				if (!empty($temp_orders_id)) {
					$selected_query = tep_db_query('SELECT value FROM temp_orders_total WHERE class="'.$this->type.'" AND orders_id = "'.$temp_orders_id.'"');
					$selected = tep_db_fetch_array($selected_query);
				}
				if ($this->config['status'] == 'true') {
					$html .= '<label class="control-label" for="'.$this->type.'" style="display:block;">';
					$html .= '<div class="'.$this->type.'_item clearfix">';
					$html .= '<input type="checkbox" name="'.$this->type.'" value="'.$id.'" id="'.$this->type.'"'.(isset($selected['value'])?' checked=checked':'').' />';
					$html .= '<div class="'.$this->type.'_title">&nbsp; '.$currencies->format($amount).' '.Translate($this->config['title']).'</div>';
					if (!empty($this->config['description'])) {
						$html .= '<div class="'.$this->type.'_description">&nbsp; '.Translate($this->config['description']).'</div>';
					}
					$html .= '</div>';
					$html .= '</label>';
				}
			}
		}
		return $html;
	}
	public function process_data() {
		global $temp_orders_id, $customer_id, $currency, $currencies;
		if (isset($_POST[$this->type])) {
			$cadeaubon_amount = $this->calculate_amount();
			$check_totc_query = tep_db_query('SELECT orders_total_id FROM temp_orders_total WHERE orders_id = "'.$temp_orders_id.'" AND class = "'.$this->type.'"');
			if (tep_db_num_rows($check_totc_query) > 0) {
				tep_db_query('UPDATE temp_orders_total SET title = "'.$this->config['title'].'", text = "'.$currencies->format(-$cadeaubon_amount).'", value="-'.$cadeaubon_amount.'", sort_order = "'.$this->sort_order.'" WHERE orders_id = "'.$temp_orders_id.'" AND class="'.$this->type.'"');
			} else{
				tep_db_query('INSERT INTO temp_orders_total (orders_id, title, text, value, class, sort_order) VALUES("'.$temp_orders_id.'", "'.$this->config['title'].'", "'.$currencies->format(-$cadeaubon_amount).'", "-'.$cadeaubon_amount.'", "'.$this->type.'", "'.$this->sort_order.'")');
			}
		}
		return true;
	}
	public function after_process($orders_id) {
		global $temp_orders_id, $customer_id;
		$cadeaubon_query = tep_db_query('SELECT value FROM temp_orders_total WHERE class="'.$this->type.'" AND orders_id = "'.$temp_orders_id.'"');
		if (tep_db_num_rows($cadeaubon_query) > 0) {
			$used_cadeaubon = tep_db_fetch_array($cadeaubon_query);
			$gv_amount_query = tep_db_query('SELECT amount FROM coupon_gv_customer WHERE customer_id ="'.$customer_id.'"');
			$gv_amount = tep_db_fetch_array($gv_amount_query);
			tep_db_query('UPDATE coupon_gv_customer SET amount = "'.($gv_amount['amount']-abs($used_cadeaubon['value'])).'" WHERE customer_id = "'.$customer_id.'"');
		}
	}
	private function calculate_amount() {
		global $customer_id, $temp_orders_id;
		$amount_query = tep_db_query('SELECT amount FROM coupon_gv_customer WHERE customer_id = "'.$customer_id.'"');
		$amount = tep_db_fetch_array($amount_query);
		$amount = $amount['amount'];
		$temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
		foreach($temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
			if ($data['class'] == 'order_total') {
				$total = $data['value'];
			}
		}
		if ($amount > $total) {
			$amount = $total;
		}
		return $amount;
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
		}
		echo '</h1>';
		echo '<hr />';
		if (isset($_POST['action']) && $_POST['action'] == 'save') {
			/********************/
			/*	Save changes	*/
			/********************/
			unset($_POST['action']);
			foreach($_POST as $key=>$data) {
				if ($key == 'zone') {
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
			'title' => 'Tegoed Cadeaubonnen',
			'description' => ''
		);
		tep_db_query('CREATE TABLE checkout_'.get_class($this).'(
			id INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			status VARCHAR(255),
			title VARCHAR(255),
			description VARCHAR(255))');
		tep_db_query('CREATE INDEX title ON checkout_'.get_class($this).' (`title`)');
		tep_db_query('CREATE INDEX status ON checkout_'.get_class($this).' (`status`)');
		tep_db_perform('checkout_'.get_class($this), $install_array, 'insert');
	}
	private function getTranslations() {
		return array('Tegoed Cadeaubonnen' => array(
							'1' => 'Tegoed Cadeaubonnen',
							'2' => 'cadeau de cr&eacute;dit',
							'3' => 'credit Gift',
							'4' => 'Kredit-Geschenk')
					,'Verwijder module' => array(
							'1' => 'Verwijder module', 
							'2' => 'Retirez le module', 
							'3' => 'Remove module', 
							'4' => 'Modul entfernen')
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