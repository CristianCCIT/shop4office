<?php
//shipping class for Kiala
class Kiala extends Modules {
	private $Checkout;
	public $type = 'shipping'
		 , $sort_order = 40
		 , $config = array()
		 , $temp_data = array()
		 , $xml
		 , $order_subtotal = 0;
	public function __construct() {
		global $temp_orders_id;
		//load config => title, text, sort_order, status, zone, order_status_id, works_with_shipping_module
		if(tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'checkout_".get_class($this)."'"))< 1) {
			$this->install();
		}
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		$array = tep_db_fetch_array($query);
		$this->config = $array;
		if ($temp_orders_id > 0) {
			$this->temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
			foreach($this->temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
				if ($data['class'] == 'order_subtotal') {
					$this->order_subtotal = $data['value'];
				}
			}
		}
	}
	public function update_config() {
		$this->config = array();
		$query = tep_db_query('SELECT * FROM checkout_'.get_class($this));
		$array = tep_db_fetch_array($query);
		$this->config = $array;
	}
	public function is_active($country = 0) {
		global $temp_orders_id, $cart;
		if ($this->config['status'] == 'true' && !empty($this->config['dspid'])) {
			if ($country > 0) {
				if ($cart->show_total() >= $this->config['min_amount'] && ($this->config['max_weight'] >= $cart->show_weight() || $this->config['max_weight'] == 0)) {
					if ($this->checkCountry($country, 1000)) {
						return true;
					}
				}
			} else {
				if ($this->order_subtotal >= $this->config['min_amount'] && ($this->config['max_weight'] >= Checkout::calculate_weight($temp_orders_id, 'temp_') || $this->config['max_weight'] == 0)) {
					if ($this->checkCountry($this->temp_data[$temp_orders_id]['orders']['delivery_country'], $this->temp_data[$temp_orders_id]['orders']['delivery_postcode'])) {
						return true;
					}
				}
			}
		}
		return false;
	}
	public function output($step = 0) {
		global $currencies, $temp_orders_id;
		if ($temp_orders_id > 0) {
			$this->temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
			foreach($this->temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
				if ($data['class'] == 'order_subtotal') {
					$this->order_subtotal = $data['value'];
				}
			}
		}
		$html = '';
		//check for errors
		if (isset(Checkout::$errors[$this->type])) {
			$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate(Checkout::$errors[$this->type]).'</div>';
			unset(Checkout::$errors[$this->type]);
		}
		if ($this->config['status'] == 'true' && $this->order_subtotal >= $this->config['min_amount'] && ($this->config['max_weight'] >= Checkout::calculate_weight($temp_orders_id, 'temp_') || $this->config['max_weight'] == 0)) {
			if ($this->checkCountry($this->temp_data[$temp_orders_id]['orders']['delivery_country'], $this->temp_data[$temp_orders_id]['orders']['delivery_postcode'])) {
				//Search kialo points with postcode
				if  (isset($_POST[get_class($this).'_submit']) && $_POST[get_class($this).'_submit'] == 'search_postcode' && !empty($_POST[get_class($this).'_postcode'])) {
					$iso2_query = tep_db_query('SELECT countries_iso_code_2 FROM countries WHERE countries_id = "'.$this->temp_data[$temp_orders_id]['orders']['delivery_country'].'"');
					$iso2 = tep_db_fetch_array($iso2_query);
					$sort_extra = '';
					if ($this->temp_data[$temp_orders_id]['orders']['shipping_method'] == get_class($this) && !empty($this->temp_data[$temp_orders_id]['orders']['shipping_method_extra'])) {
						$sort_extra = '+'.$this->temp_data[$temp_orders_id]['orders']['shipping_method_extra'];
					}
					$this->xml = $this->convertXMLtoArray(simplexml_load_file('http://locateandselect.kiala.com/kplist?dspid='.$this->config['dspid'].'&country='.$iso2['countries_iso_code_2'].'&zip='.$_POST[get_class($this).'_postcode'].'&language='.$languages_code.'&max-result=5&sort-method=ACTIVE_FIRST'.$sort_extra));
				}
				if (isset(Checkout::$errors[$id])) {
					$html .= '<div class="alert alert-error"><strong>'.Translate('Opgelet!').'</strong> '.Translate(Checkout::$errors[$id]).'</div>';
				}
				$html .= '<label class="control-label" for="'.$this->type.'_'.get_class($this).'" style="display:block;">';
				$html .= '<div class="'.$this->type.'_item clearfix">';
				$html .= '<input type="radio" name="'.$this->type.'" value="'.get_class($this).'" id="'.$this->type.'_'.get_class($this).'"'.($this->temp_data[$temp_orders_id]['orders']['shipping_method']==get_class($this)?' checked=checked':'').' />';
				$html .= '<div class="'.$this->type.'_title">&nbsp; '.Translate($this->config['title']).'</div>';
				$html .= '<div class="'.$this->type.'_quote">'.($this->getQuote()>0?$currencies->display_price($this->getQuote(), 0):Translate('Gratis')).'</div>';
				if (!empty($data['description'])) {
					$html .= '<div class="'.$this->type.'_description">&nbsp; '.Translate($this->config['description']).'</div>';
				}
				$html .= '</div>';
				$html .= '</label>';
				$html .= '<div class="well" style="margin-left:25px;" id="Kiala_points_list">';
				//search box
				$html .= '<input type="text" name="'.get_class($this).'_postcode" value="'.$_POST[get_class($this).'_postcode'].'" placeholder="'.Translate('Vul hier uw postcode in').'" />';
				$html .= '&nbsp;<button type="submit" class="btn" name="'.get_class($this).'_submit" value="search_postcode">'.Translate('Zoeken').'</button>';
				//kiala punt lijst
				$html .= $this->getKpList($this->temp_data[$temp_orders_id]['orders']['delivery_country']);
				$html .= '</div>';
			}
		}
		return $html;
	}
	public function process_data() {
		global $temp_orders_id, $currencies;
		if (isset($_POST[$this->type])) {
			if ($_POST[$this->type] == get_class($this)) {
				if ($temp_orders_id == 0) {
					$temp_orders_id = parent::create_order();
				}
				$ot_query = tep_db_query('SELECT orders_total_id FROM temp_orders_total WHERE orders_id = "'.$temp_orders_id.'" AND class="'.$this->type.'"');
				if (tep_db_num_rows($ot_query)>0) {
					tep_db_query('UPDATE temp_orders_total SET title="'.$this->config['title'].'", text="'.$currencies->display_price($this->getQuote(), 0).'", value="'.$this->getQuote().'", sort_order="'.$this->config['sort_order'].'" WHERE orders_id = "'.$temp_orders_id.'" AND class="'.$this->type.'"');
				} else {
					tep_db_query('INSERT INTO temp_orders_total (orders_id, title, text, value, class, sort_order) VALUES ("'.$temp_orders_id.'", "'.$this->config['title'].'", "'.$currencies->display_price($this->getQuote(), 0).'", "'.$this->getQuote().'", "'.$this->type.'", "'.$this->config['sort_order'].'")');
				}
				if  (isset($_POST[get_class($this).'_submit']) && $_POST[get_class($this).'_submit'] == 'search_postcode') {//Search kialo points with postcode
					return false;
				}
				if (!isset($_POST[get_class($this).'_kp'])) {
					$this->errors[$this->type] = Translate('Kies a.u.b. een Kiala punt.');
					return false;
				}
				tep_db_query('UPDATE temp_orders SET shipping_method = "'.$_POST[$this->type].'", shipping_method_extra = "'.$_POST[get_class($this).'_kp'].'" WHERE orders_id = "'.$temp_orders_id.'"');
			}
			if ($temp_orders_id > 0) {
				$this->temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
				foreach($this->temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
					if ($data['class'] == 'order_subtotal') {
						$this->order_subtotal = $data['value'];
					}
				}
			}
			return true;
		} else {
			$this->errors[$this->type] = Translate('Kies a.u.b. een verzendmethode.');
			if ($temp_orders_id > 0) {
				$this->temp_data = Checkout::get_all_data_from_temp_db($temp_orders_id);
				foreach($this->temp_data[$temp_orders_id]['orders_total'] as $key=>$data) {
					if ($data['class'] == 'order_subtotal') {
						$this->order_subtotal = $data['value'];
					}
				}
			}
			return false;
		}
	}
	public function output_array($country = 0, $postcode = '1000') {
		global $currencies, $temp_orders_id, $cart;
		$array = array();
		if ($this->config['status'] == 'true' && $cart->show_total() >= $this->config['min_amount'] && $this->config['max_weight'] <= $cart->show_weight()) {
			if ($this->checkCountry($country, $postcode)) {
				$array[] = array('title' => Translate($this->config['title']), 'description' => Translate($this->config['description']), 'quote' => $this->getQuote());
			}
		}
		return $array;
	}
	private function getQuote() {
		$quote = $this->config['shipping_cost'];
		if ($this->order_subtotal > $this->config['free_shipping_over'] && $this->config['free_shipping_over'] > 0) {
			$quote = $this->config['free_shipping_over'];
		}
		return $quote;
	}
	public function getZones() {
		return array();
	}
	private function checkCountry($country, $postcode){
		global $languages_code, $temp_orders_id;
		if (!empty($country) && !empty($postcode)) {
			$iso2_query = tep_db_query('SELECT countries_iso_code_2 FROM countries WHERE countries_id = "'.$country.'"');
			$iso2 = tep_db_fetch_array($iso2_query);
			$sort_extra = '';
			if ($this->temp_data[$temp_orders_id]['orders']['shipping_method'] == get_class($this) && !empty($this->temp_data[$temp_orders_id]['orders']['shipping_method_extra'])) {
				$sort_extra = '+'.$this->temp_data[$temp_orders_id]['orders']['shipping_method_extra'];
			}
			$this->xml = $this->convertXMLtoArray(simplexml_load_file('http://locateandselect.kiala.com/kplist?dspid='.$this->config['dspid'].'&country='.$iso2['countries_iso_code_2'].'&zip='.$postcode.'&language='.$languages_code.'&max-result=5&sort-method=ACTIVE_FIRST'.$sort_extra));
		}
		if (count($this->xml['kp']) > 0) {
			return true;
		} else {
			return false;
		}
	}
	private function getKpList($country) {
		global $languages_code, $temp_orders_id;
		$iso2_query = tep_db_query('SELECT countries_iso_code_2 FROM countries WHERE countries_id = "'.$country.'"');
		$iso2 = tep_db_fetch_array($iso2_query);
		$html = '';
		foreach($this->xml['kp'] as $kpData) {
			$html .= '<label class="'.get_class($this).'_label" for="'.get_class($this).'_kp_'.$kpData['shortId'].'">';
			$html .= '<div class="'.get_class($this).'_kp clearfix">';
			$html .= '<input type="radio" name="'.get_class($this).'_kp" value="'.$kpData['shortId'].'" id="'.get_class($this).'_kp_'.$kpData['shortId'].'"'.($this->temp_data[$temp_orders_id]['orders']['shipping_method_extra']== $kpData['shortId']?' checked=checked':'').' />';
			$html .= '<div class="'.get_class($this).'_kp_image"><image src="'.$kpData['picture']['href'].'" width="160" /></div>';
			$html .= '<div class="'.get_class($this).'_kp_name">'.$kpData['name'].'</div>';
			$html .= '<div class="'.get_class($this).'_kp_id">(K0'.$kpData['shortId'].')</div>';
			$html .= '<div class="'.get_class($this).'_kp_address">'.$kpData['address']['street'].'<br />'.$kpData['address']['zip'].' '.$kpData['address']['city'].'</div>';
			$html .= '<div class="'.get_class($this).'_kp_locationhint">'.$kpData['address']['locationHint'].'</div>';
			$html .= '<div class="'.get_class($this).'_kp_remark">'.$kpData['remark'].'</div>';
			if (!is_array($kpData['status'])) {
				$html .= '<div class="'.get_class($this).'_kp_status">'.$kpData['status'].'</div>';
			}
			$html .= '<div class="'.get_class($this).'_kp_more_info"><a href="http://locateandselect.kiala.com/details?dspid='.$this->config['dspid'].'&country='.$iso2['countries_iso_code_2'].'&language='.$languages_code.'&shortID='.$kpData['shortId'].'&map=on&gui=sleek" title="'.Translate('Meer info over').' '.$kpData['name'].'" target="_blank" onclick="window.open(this.href, \''.Translate('Kiala Punt info').'\', \'width=750,height=500\'); return false;">'.Translate('Meer info').'</a></div>';
			$html .= '</div>';//end kp
			$html .= '</label>';
		}
		return $html;
	}
	private function convertXMLtoArray(SimpleXMLElement $xml,$attributesKey=null,$childrenKey=null,$valueKey=null){
		if($childrenKey && !is_string($childrenKey)){$childrenKey = '@children';}
		if($attributesKey && !is_string($attributesKey)){$attributesKey = '@attributes';}
		if($valueKey && !is_string($valueKey)){$valueKey = '@values';}
		$return = array();
		$name = $xml->getName();
		$_value = trim((string)$xml);
		if(!strlen($_value)){$_value = null;};
		if($_value!==null){
			if($valueKey){$return[$valueKey] = $_value;}
			else{$return = $_value;}
		}
		$children = array();
		$first = true;
		foreach($xml->children() as $elementName => $child){
			$value = $this->convertXMLtoArray($child,$attributesKey, $childrenKey,$valueKey);
			if(isset($children[$elementName])){
				if(is_array($children[$elementName])){
					if($first){
						$temp = $children[$elementName];
						unset($children[$elementName]);
						$children[$elementName][] = $temp;
						$first=false;
					}
					$children[$elementName][] = $value;
				}else{
					$children[$elementName] = array($children[$elementName],$value);
				}
			}
			else{
				$children[$elementName] = $value;
			}
		}
		if($children){
			if($childrenKey){$return[$childrenKey] = $children;}
			else{$return = array_merge($return,$children);}
		}
		$attributes = array();
		foreach($xml->attributes() as $name=>$value){
			$attributes[$name] = trim($value);
		}
		if($attributes){
			if($attributesKey) {
				$return[$attributesKey] = $attributes;
			} else {
				if (is_array($return)) {
					$return = array_merge($return, $attributes);
				}
			}
		}
		return $return;
	}
	public function getTitle() {
		global $temp_orders_id, $languages_code;
		$iso2_query = tep_db_query('SELECT countries_iso_code_2 FROM countries WHERE countries_id = "'.$this->temp_data[$temp_orders_id]['orders']['delivery_country'].'"');
		$iso2 = tep_db_fetch_array($iso2_query);
		$kiala_point = $this->convertXMLtoArray(simplexml_load_file('http://locateandselect.kiala.com/kplist?dspid='.$this->config['dspid'].'&country='.$iso2['countries_iso_code_2'].'&language='.$languages_code.'&shortID='.$this->temp_data[$temp_orders_id]['orders']['shipping_method_extra']));
		return $this->config['title'].' '.$kiala_point['kp']['name'].' (KP0'.$this->temp_data[$temp_orders_id]['orders']['shipping_method_extra'].'), '.str_replace(',', '', $kiala_point['kp']['address']['street']).' '.$kiala_point['kp']['address']['zip'].' '.$kiala_point['kp']['address']['city'];
	}
	public function after_process($orders_id) {
		global $temp_orders_id;
		if ($this->temp_data[$temp_orders_id]['orders']['shipping_method'] == get_class($this)) {
			tep_db_query('UPDATE orders_total SET title="'.$this->getTitle().'" WHERE orders_id = "'.$orders_id.'" AND class="'.$this->type.'"');
		}
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
			<div class="control-group">
				<label class="control-label" for="shipping_cost"><?php echo Translate('Verzendkost');?></label>
				<div class="controls">
					<input type="text" name="shipping_cost" value="<?php echo $this->config['shipping_cost'];?>" class="input-xlarge" id="shipping_cost" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="dspid"><?php echo Translate('DSPID');?></label>
				<div class="controls">
					<input type="text" name="dspid" value="<?php echo $this->config['dspid'];?>" class="input-xlarge" id="dspid" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="min_amount"><?php echo Translate('Minimum bedrag');?></label>
				<div class="controls">
					<input type="text" name="min_amount" value="<?php echo $this->config['min_amount'];?>" class="input-xlarge" id="min_amount" />
					<span class="help-block"><?php echo Translate('Minimum bedrag van de bestelling voor deze methode gebruikt kan worden');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="max_weight"><?php echo Translate('Maximum gewicht');?></label>
				<div class="controls">
					<input type="text" name="max_weight" value="<?php echo $this->config['max_weight'];?>" class="input-xlarge" id="max_weight" />
					<span class="help-block"><?php echo Translate('Het maximum gewicht die de bestelling mag hebben voor het gebruik van deze methode');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="free_shipping_over"><?php echo Translate('Gratis verzending vanaf');?></label>
				<div class="controls">
					<input type="text" name="free_shipping_over" value="<?php echo $this->config['free_shipping_over'];?>" class="input-xlarge" id="free_shipping_over" />&euro;
					<span class="help-block"><?php echo Translate('Verzending wordt gratis bij een totaal bestelbedrag boven dit bedrag');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="sort_order"><?php echo Translate('Volgorde');?></label>
				<div class="controls">
					<input type="text" name="sort_order" value="<?php echo $this->config['sort_order'];?>" class="input-xlarge" id="sort_order" />
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
			'title' => 'Kiala',
			'description' => '',
			'dspid' => '',
			'min_amount' => '0',
			'max_weight' => '0',
			'free_shipping_over' => '0',
			'shipping_cost' => '0',
			'sort_order' => 10
		);
		tep_db_query('CREATE TABLE checkout_'.get_class($this).'(
			id INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			status VARCHAR(255),
			title VARCHAR(255),
			description VARCHAR(255),
			dspid VARCHAR(255),
			min_amount FLOAT(10,2),
			max_weight FLOAT(10,2),
			free_shipping_over FLOAT(10,2),
			shipping_cost FLOAT(10,2),
			sort_order INT(11))');
		tep_db_query('CREATE INDEX title ON checkout_'.get_class($this).' (`title`)');
		tep_db_query('CREATE INDEX status ON checkout_'.get_class($this).' (`status`)');
		tep_db_query('CREATE INDEX sort_order ON checkout_'.get_class($this).' (`sort_order`)');
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
					,'Gratis' => array(
							'1' => 'Gratis', 
							'2' => 'Gratuit', 
							'3' => 'Free', 
							'4' => 'Gratis')
					,'Vul hier uw postcode in' => array(
							'1' => 'Vul hier uw postcode in', 
							'2' => 'Entrez votre code postal', 
							'3' => 'Enter your postcode', 
							'4' => 'Geben Sie Ihre Postleitzahl')
					,'Zoeken' => array(
							'1' => 'Zoeken', 
							'2' => 'Rechercher', 
							'3' => 'search', 
							'4' => 'Suche')
					,'Kies a.u.b. een Kiala punt.' => array(
							'1' => 'Kies a.u.b. een Kiala punt.', 
							'2' => 'Choisissez un point Kiala, s\'il vous plaît.', 
							'3' => 'Choose a Kiala point if you please.', 
							'4' => 'Wählen Sie eine Kiala Punkt, wenn ich bitten darf.')
					,'Kies a.u.b. een verzendmethode.' => array(
							'1' => 'Kies a.u.b. een verzendmethode.', 
							'2' => 'Choisissez une méthode d\'expédition, s\'il vous plaît.', 
							'3' => 'Choose a shipping method if you please.', 
							'4' => 'Wählen Sie eine Versandart, wenn ich bitten darf.')
					,'Meer info over' => array(
							'1' => 'Meer info over', 
							'2' => 'Plus d\'info', 
							'3' => 'More info', 
							'4' => 'Mehr Info')
					,'Kiala Punt info' => array(
							'1' => 'Kiala Punt info', 
							'2' => 'Info point Kiala', 
							'3' => 'Kiala point info', 
							'4' => 'Kiala Punkt info')
					,'Meer info' => array(
							'1' => 'Meer info', 
							'2' => 'Plus d\'info', 
							'3' => 'More info', 
							'4' => 'Mehr Info')
					,'Kiala' => array(
							'1' => 'Kiala', 
							'2' => 'Kiala', 
							'3' => 'Kiala', 
							'4' => 'Kiala')
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
					,'Verzendkost' => array(
							'1' => 'Verzendkost',
							'2' => 'Coût de la livraison',
							'3' => 'Shipping Cost',
							'4' => 'Versandkosten')
					,'Volgorde' => array(
							'1' => 'Volgorde',
							'2' => 'Ordre',
							'3' => 'Sort order',
							'4' => 'Sortierung')
					,'DSPID' => array(
							'1' => 'DSPID',
							'2' => 'DSPID',
							'3' => 'DSPID',
							'4' => 'DSPID')
					,'Minimum bedrag' => array(
							'1' => 'Minimum bedrag',
							'2' => 'Montant minimum',
							'3' => 'Minimum amount',
							'4' => 'Mindestbetrag')
					,'Minimum bedrag van de bestelling voor deze methode gebruikt kan worden' => array(
							'1' => 'Minimum bedrag van de bestelling voor deze methode gebruikt kan worden',
							'2' => 'Montant minimal de l\'ordre pour que cette méthode peut être utilisée',
							'3' => 'Minimum amount of the order for this method can be used',
							'4' => 'Minimale Höhe des Auftragswertes für diese Methode verwendet werden kann')
					,'Maximum gewicht' => array(
							'1' => 'Maximum gewicht',
							'2' => 'Poids maximum',
							'3' => 'Maximum weight',
							'4' => 'Maximales Gewicht')
					,'Het maximum gewicht die de bestelling mag hebben voor het gebruik van deze methode' => array(
							'1' => 'Het maximum gewicht die de bestelling mag hebben voor het gebruik van deze methode',
							'2' => 'Le poids maximum que l\'ordre peut avoir pour l\'utilisation de cette méthode',
							'3' => 'The maximum weight that the order can have for the use of this method',
							'4' => 'Das Gewicht, dass die Reihenfolge kann für die Verwendung dieses Verfahrens haben')
					,'Gratis verzending vanaf' => array(
							'1' => 'Gratis verzending vanaf',
							'2' => 'Livraison gratuite',
							'3' => 'Free shipping',
							'4' => 'Kostenloser Versand')
					,'Verzending wordt gratis bij een totaal bestelbedrag boven dit bedrag' => array(
							'1' => 'Verzending wordt gratis bij een totaal bestelbedrag boven dit bedrag',
							'2' => 'La livraison est gratuite avec un montant total de la commande supérieur à ce montant',
							'3' => 'Shipping is free with a total order amount above this amount',
							'4' => 'Der Versand ist kostenlos mit einem Gesamtauftragswert Menge über diesem Betrag')
					);
	}
}
?>