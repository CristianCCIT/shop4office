<?php
class Payment_method extends Modules {
	public $type = 'payment_method'
		 , $config = array();
	public function __construct() {
		//load config => title, text, sort_order, status, zone, order_status_id, works_with_shipping_module
		if(tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'checkout_".get_class($this)."'"))< 1) {
			$this->install();
		}
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		$this->config = tep_db_fetch_array($query);
	}
	public function update_config() {
		$this->config = array();
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		$this->config = tep_db_fetch_array($query);
	}
	public function is_active() {
		if ($this->config['status'] == 'true') {
			return true;
		} else {
			return false;
		}
	}
	public function output($step = 0) {
		global $temp_orders_id;
		$html = '';
		if ($temp_orders_id > 0) {
			$pm_query = tep_db_query('SELECT payment_method FROM temp_orders WHERE orders_id = "'.$temp_orders_id.'"');
			if (tep_db_num_rows($pm_query) > 0) {
				$pm = tep_db_fetch_array($pm_query);
				$module_instance = end(explode('_', $pm['payment_method']));
				$module = substr($pm['payment_method'], 0, -(strlen($module_instance)+1));
				global $$module;
				$html .= '<div class="payment_method">';
				$html .= '<h3 class="payment_method_title">'.Translate($this->config['title']).'</h3>';
				if (!empty($this->config['description'])) {
					$html .= '<p class="payment_method_description">'.Translate($this->config['description']).'</p>';
				}
				$html .= '<blockquote>';
				//method title
				$html .= '<p>'.Translate($$module->instances[$pm['payment_method']]['title']);
				//method description
				if (!empty($$module->instances[$pm['payment_method']]['description'])) {
					$constants = get_defined_constants();
					$description = '';
					$description_lines = explode("\n", stripslashes($$module->instances[$pm["payment_method"]]["description"]));
					foreach($description_lines as $line) {
						if (preg_match_all('/(\w*\([^)]*\))/',$line,$matches)) {
							foreach($matches[0] as $match) {
								print eval('$Nmatch = '.$match.';');
								$line = str_replace($match, $Nmatch, $line);
							}
						}
						$line = str_replace(array_keys($constants), $constants, $line);
						$description .= $line.'<br />';
					}
					$html .= '<small>'.$description.'</small>';
				}
				$html .= '</p>';
				$html .= '</blockquote>';
				$html .= '</div>';//end payment_method
			}
		}
		return $html;
	}
	public function process_data() {
		//if data has to be processed, add here
		return true;
	}
	public function getZones() {
		//if this moduel uses zones, collect them here
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
			'title' => 'Betaalmethode',
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
		return array('Betaalmethode' => array(
							'1' => 'Betaalmethode',
							'2' => 'Paiement',
							'3' => 'Payment',
							'4' => 'Zahlung')
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