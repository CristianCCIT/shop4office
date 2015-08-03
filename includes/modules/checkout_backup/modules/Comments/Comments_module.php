<?php
//payment class for cod => cash on delivery
class Comments extends Modules {
	public $type = 'comment'
		 , $sort_order = 10
		 , $config = array();
	public function __construct() {
		//load config => title, text, sort_order, status, zone, order_status_id, works_with_shipping_module
		if(tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'checkout_".get_class($this)."'"))< 1) {
			$this->install();
		}
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		while ($array = tep_db_fetch_array($query)) {
			$this->config = $array;
		}
	}
	public function update_config() {
		$this->config = array();
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		while ($array = tep_db_fetch_array($query)) {
			$this->config = $array;
		}
	}
	public function is_active() {
		if ($this->config['status'] == 'true') {
			return true;
		}
		return false;
	}
	public function output($step = 0) {
		global $temp_orders_id;
		$html = '';
		if (!empty($temp_orders_id)) {
			$selected_query = tep_db_query('SELECT comments FROM temp_orders_status_history WHERE orders_id = "'.$temp_orders_id.'" ORDER BY orders_status_history_id desc LIMIT 1');
			$selected = tep_db_fetch_array($selected_query);
		}
		$html .= '<label class="control-label" for="'.$this->type.'" style="display:block;">';
		$html .= '<div class="'.$this->type.'_item clearfix">';
		$html .= '<div class="'.$this->type.'_title">&nbsp; '.Translate($this->config['title']).'</div>';
		$html .= '<textarea class="span8" rows="3" name="'.$this->type.'" id="'.$this->type.'"></textarea>';
		$html .= '</div>';
		$html .= '</label>';
		return $html;
	}
	public function process_data() {
		global $temp_orders_id;
		if ($temp_orders_id == 0) {
			$temp_orders_id = parent::create_order();
			tep_db_query('INSERT INTO temp_orders_status_history (orders_id, date_added, customer_notified, comments) VALUES("'.$temp_orders_id.'", NOW(), 0, "'.$_POST[$this->type].'")');
		} else{
			//check if orders_status_history instance for this order exists
			$osh_query = tep_db_query('SELECT orders_status_history_id FROM temp_orders_status_history WHERE orders_id = "'.$temp_orders_id.'" ORDER BY orders_status_history_id desc LIMIT 1');
			if (tep_db_num_rows($osh_query) > 0) {
				$osh = tep_db_fetch_array($osh_query);
				tep_db_query('UPDATE temp_orders_status_history SET comments = "'.$_POST[$this->type].'", date_added = NOW() WHERE orders_status_history_id = "'.$osh['orders_status_history_id'].'"');
			} else {
				tep_db_query('INSERT INTO temp_orders_status_history (orders_id, date_added, customer_notified, comments) VALUES("'.$temp_orders_id.'", NOW(), 0, "'.$_POST[$this->type].'")');
			}
		}
		return true;
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
				tep_db_perform('checkout_'.get_class($this), $_POST, 'update', 'id="1"');
			}
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
					<input type="text" name="title" value="<?php echo $this->config['title'];?>" class="input-xxlarge" id="title" />
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
			'title' => 'Vul een opmerking/bericht in bij uw bestelling'
		);
		tep_db_query('CREATE TABLE checkout_'.get_class($this).'(
			id INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			status VARCHAR(255),
			title VARCHAR(255))');
		tep_db_query('CREATE INDEX title ON checkout_'.get_class($this).' (`title`)');
		tep_db_query('CREATE INDEX status ON checkout_'.get_class($this).' (`status`)');
		tep_db_perform('checkout_'.get_class($this), $install_array, 'insert');
	}
	private function getTranslations() {
		return array('Vul een opmerking/bericht in bij uw bestelling' => array('1' => 'Vul een opmerking/bericht in bij uw bestelling', '2' => 'S\'il vous plaît entrer un commentaire / message avec votre commande', '3' => 'Please enter a comment / message with your order', '4' => 'Bitte geben Sie einen Kommentar / Nachricht mit Ihrer Bestellung')
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