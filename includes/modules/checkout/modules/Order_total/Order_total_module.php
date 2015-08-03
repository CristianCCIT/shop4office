<?php
class Order_total extends Modules {
	private $Checkout;
	public $type = 'order_total'
		 , $sort_order = 100
		 , $config = array()
		 , $quote = 0
		 , $temp_data = array()
		 , $errors = array();
	public function __construct() {
		global $temp_orders_id;
		if(tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'checkout_".get_class($this)."'"))< 1) {
			$this->install();
		}
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		$array = tep_db_fetch_array($query);
		$this->config = $array;
		if ($temp_orders_id > 0) {
			$this->temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
		}
	}
	public function update_config() {
		$this->config = array();
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		$array = tep_db_fetch_array($query);
		$this->config = $array;
	}
	public function is_active() {
		if ($this->config['status'] == 'true') {
			return true;
		}
		return false;
	}
	public function output($step = 0) {
		global $currencies, $cart, $temp_orders_id;
		if ($temp_orders_id > 0 && empty($this->temp_data[$temp_orders_id]['orders']['shipping_method'])) {
			$this->temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
		}
		$this->calculate_total();
		$html = '';
		$html .= '<table class="table table-condensed table-striped" style="width:100%;">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="product_name">'.Translate('Product').'</th>';
		$html .= '<th class="product_model">'.Translate('Referentie').'</th>';
		$html .= '<th class="product_quantity">'.Translate('Hoeveelheid').'</th>';
		$html .= '<th class="product_price">'.Translate('Prijs').'</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$products_query = tep_db_query('SELECT * FROM temp_orders_products WHERE orders_id = "'.$temp_orders_id.'" ORDER BY orders_products_id asc');
		while ($product = tep_db_fetch_array($products_query)) {
			$html .= '<tr>';
			$html .= '<td class="product_name">'.$product['products_name'].'</td>';
			$html .= '<td class="product_model">'.$product['products_model'].'</td>';
			$html .= '<td class="product_quantity">'.$product['products_quantity'].'</td>';
			$html .= '<td class="product_price">'.$currencies->display_price($product['final_price'], 0).'</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';
		$total_query = tep_db_query('SELECT * FROM temp_orders_total WHERE orders_id = "'.$temp_orders_id.'" AND class != "'.$this->type.'" ORDER BY sort_order asc');
		$html .= '<table style="width:100%; border:0;">';
		while($total = tep_db_fetch_array($total_query)) {
			$html .= '<tr class="'.$total['class'].'_item">';
			$class_title = $total['title'];
			if ($total['class'] == 'shipping') {
				global $Modules;
				$class = $this->temp_data[$temp_orders_id]['orders']['shipping_method'];
				foreach($Modules->modules['shipping'] as $module) {
					if (strstr($class, $module)) {
						global $$module;
						if (method_exists($$module, 'getTitle')) {
							$class_title = $$module->getTitle();
						}
					}
				}
			}
			$html .= '<td class="'.$total['class'].'_title">'.Translate($class_title).': &nbsp;</td>';
			$html .= '<td class="'.$total['class'].'_quote">'.$currencies->display_price($total['value'], 0).'</td>';
			$html .= '</tr>';
		}
		$html .= '<tr class="'.$this->type.'_item">';
		$html .= '<td class="'.$this->type.'_title">'.Translate($this->config['title']).': &nbsp;</td>';
		$html .= '<td class="'.$this->type.'_quote">'.$currencies->display_price($this->quote, 0).'</td>';
		$html .= '</tr>';
		$html .= '</table>';
		return $html;
	}
	public function process_data() {
		global $temp_orders_id, $currencies;
		$strlen = strlen(get_class($this));
		$this->calculate_total();
		if ($temp_orders_id == 0) {
			$temp_orders_id = Checkout::create_order();
		}
		$ot_query = tep_db_query('SELECT orders_total_id FROM temp_orders_total WHERE orders_id = "'.$temp_orders_id.'" AND class="'.$this->type.'"');
		if (tep_db_num_rows($ot_query)>0) {
			tep_db_query('UPDATE temp_orders_total SET title="'.Translate($this->config['title']).'", text="'.$currencies->display_price($this->quote, 0).'", value="'.$this->quote.'", sort_order="'.$this->config['sort_order'].'" WHERE orders_id = "'.$temp_orders_id.'" AND class="'.$this->type.'"');
		} else {
			tep_db_query('INSERT INTO temp_orders_total (orders_id, title, text, value, class, sort_order) VALUES ("'.$temp_orders_id.'", "'.Translate($this->config['title']).'", "'.$currencies->display_price($this->quote, 0).'", "'.$this->quote.'", "'.$this->type.'", "'.$this->config['sort_order'].'")');
		}
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			apc_delete('temp_orders_total_'.$temp_orders_id);
		}
		return true;
	}
	private function calculate_total() {
		global $cart, $temp_orders_id;
		$this->quote = 0;
		$total_query = tep_db_query('SELECT value FROM temp_orders_total WHERE orders_id = "'.$temp_orders_id.'" AND class != "'.$this->type.'"');
		while($total = tep_db_fetch_array($total_query)) {
			$this->quote += $total['value'];
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
		}
		echo '</h1>';
		echo '<hr />';
		if (isset($_POST['action']) && $_POST['action'] == 'save') {
			/********************/
			/*	Save changes	*/
			/********************/
			unset($_POST['action']);
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
				<label class="control-label" for="sort_order"><?php echo Translate('Volgorde');?></label>
				<div class="controls">
					<input type="number" name="sort_order" value="<?php echo $this->config['sort_order'];?>" class="input-xxlarge" id="sort_order" />
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
			'title' => 'Totaal',
			'description' => '',
			'sort_order' => '100'
		);
		tep_db_query('CREATE TABLE checkout_'.get_class($this).'(
			id INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			status VARCHAR(255),
			title VARCHAR(255),
			description VARCHAR(255),
			sort_order INT(11))');
		tep_db_query('CREATE INDEX title ON checkout_'.get_class($this).' (`title`)');
		tep_db_query('CREATE INDEX status ON checkout_'.get_class($this).' (`status`)');
		tep_db_perform('checkout_'.get_class($this), $install_array, 'insert');
	}
	private function getTranslations() {
		return array('Totaal' => array(
							'1' => 'Totaal',
							'2' => 'Total',
							'3' => 'Total',
							'4' => 'Gesamt')
					,'Verwijder module' => array(
							'1' => 'Verwijder module', 
							'2' => 'Retirez le module', 
							'3' => 'Remove module', 
							'4' => 'Modul entfernen')
					,'Product' => array(
							'1' => 'Product',
							'2' => 'Produit',
							'3' => 'Product',
							'4' => 'Produkt')
					,'Referentie' => array(
							'1' => 'Referentie',
							'2' => 'Référence',
							'3' => 'Reference',
							'4' => 'Referenz')
					,'Hoeveelheid' => array(
							'1' => 'Hoeveelheid',
							'2' => 'Quantité',
							'3' => 'Quantity',
							'4' => 'Menge')
					,'Prijs' => array(
							'1' => 'Prijs',
							'2' => 'Prix',
							'3' => 'Price',
							'4' => 'Preis')
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
					,'Volgorde' => array(
							'1' => 'Volgorde',
							'2' => 'Ordre',
							'3' => 'Sort order',
							'4' => 'Sortierung')
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