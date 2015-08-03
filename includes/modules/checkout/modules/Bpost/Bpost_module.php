<?php
//shipping class for Bpost
class Bpost extends Modules {
	private $Checkout;
	public $type = 'shipping'
		 , $sort_order = 50
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
	public function is_active() {
		global $temp_orders_id;
		if ($this->config['status'] == 'true' && !empty($this->config['account_id'])) {
			if ($this->order_subtotal >= $this->config['min_amount'] && ($this->config['max_weight'] >= Checkout::calculate_weight($temp_orders_id, 'temp_') || $this->config['max_weight'] == 0)) {
				if ($this->checkCountry($this->temp_data[$temp_orders_id]['orders']['delivery_street_address'], $this->temp_data[$temp_orders_id]['orders']['delivery_postcode'])) {
					return true;
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
			if ($this->checkCountry($this->temp_data[$temp_orders_id]['orders']['delivery_street_address'], $this->temp_data[$temp_orders_id]['orders']['delivery_postcode'])) {
				//Search bpost service points with customer address
				if  (isset($_POST[get_class($this).'_submit']) && $_POST[get_class($this).'_submit'] == 'search_postcode' && !empty($_POST[get_class($this).'_postcode'])) {
					$this->xml = $this->convertXMLtoArray(simplexml_load_file('http://taxipost.geo6.be/Locator?Partner='.$this->config['account_id'].'&AppId='.STORE_NAME.'&Function=search&Format=xml&Zone='.$_POST[get_class($this).'_postcode'].'&Language='.$languages_code.'&Type='.$this->config['bpost_types'].'&Limit='.$this->config['max_sp']));
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
				$html .= '<div class="well" style="margin-left:25px;" id="Bpost_points_list">';
				//search box
				$html .= '<input type="text" name="'.get_class($this).'_postcode" value="'.$_POST[get_class($this).'_postcode'].'" placeholder="'.Translate('Vul hier uw postcode in').'" />';
				$html .= '&nbsp;<button type="submit" class="btn" name="'.get_class($this).'_submit" value="search_postcode">'.Translate('Zoeken').'</button>';
				//Bpost punt lijst
				$html .= $this->getServicePointList($this->temp_data[$temp_orders_id]['orders']['delivery_street_address'], $this->temp_data[$temp_orders_id]['orders']['delivery_postcode']);
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
				if  (isset($_POST[get_class($this).'_submit']) && $_POST[get_class($this).'_submit'] == 'search_postcode') {//Search Bpost Service points with postcode
					return false;
				}
				if (!isset($_POST[get_class($this).'_sp'])) {
					$this->errors[$this->type] = Translate('Kies a.u.b. een Bpost Service punt.');
					return false;
				}
				
				if (isset($_POST[get_class($this).'_postcode']) && !empty($_POST[get_class($this).'_postcode'])) {
					$this->xml = $this->convertXMLtoArray(simplexml_load_file('http://taxipost.geo6.be/Locator?Partner='.$this->config['account_id'].'&AppId='.STORE_NAME.'&Function=search&Format=xml&Zone='.$_POST[get_class($this).'_postcode'].'&Language='.$languages_code.'&Type='.$this->config['bpost_types'].'&Limit='.$this->config['max_sp']));
				} else {
					$this->xml = $this->convertXMLtoArray(simplexml_load_file('http://taxipost.geo6.be/Locator?Partner='.$this->config['account_id'].'&AppId='.STORE_NAME.'&Function=search&Format=xml&Zone='.$this->temp_data[$temp_orders_id]['orders']['delivery_postcode'].'&Language='.$languages_code.'&Type='.$this->config['bpost_types'].'&Limit='.$this->config['max_sp']));
				}
				$type = 1;
				foreach($this->xml['PoiList']['Poi'] as $key=>$data) {
					if ($data['Record']['Id'] == $_POST[get_class($this).'_sp']) {
						$type = $data['Record']['Type'];
					}
				}
				tep_db_query('UPDATE temp_orders SET shipping_method = "'.$_POST[$this->type].'", shipping_method_extra = "&Id='.$_POST[get_class($this).'_sp'].'&Type='.$type.'" WHERE orders_id = "'.$temp_orders_id.'"');
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
		if ($country == 21) {
			if ($this->config['status'] == 'true' && $cart->show_total() >= $this->config['min_amount'] && $this->config['max_weight'] <= $cart->show_weight()) {
				if ($this->checkCountry('', $postcode)) {
					$array[] = array('title' => Translate($this->config['title']), 'description' => Translate($this->config['description']), 'quote' => $this->getQuote());
				}
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
	private function checkCountry($street_address, $postcode){
		global $languages_code, $temp_orders_id;
		if (!empty($street_address) && !empty($postcode)) {
			$sort_extra = '';
			$number = end(explode(' ', $street_address));
			$street = substr($street_address, 0, (strlen($street_address) - strlen($number)));
			$this->xml = $this->convertXMLtoArray(simplexml_load_file('http://taxipost.geo6.be/Locator?Partner='.$this->config['account_id'].'&AppId='.STORE_NAME.'&Function=search&Format=xml&Street='.$street.'&Number='.$number.'&Zone='.$postcode.'&Language='.$languages_code.'&Type='.$this->config['bpost_types'].'&Limit='.$this->config['max_sp']));
		}
		if (count($this->xml['PoiList']['Poi']) > 0) {
			return true;
		} else {
			return false;
		}
	}
	private function getServicePointList($street_address, $postcode) {
		global $languages_code, $temp_orders_id;
		$html = '';
		$types = array();
		foreach($this->xml['PoiList']['Poi'] as $data) {
			$types[$data['Record']['Type']][] = $data;
		}
		ksort($types);
		foreach($types as $type=>$typesData) {
			$html .= '<div class="'.get_class($this).'_type_block">';
			if ($type == '1') {
				$html .= '<h4>'.Translate('Pakketten ophalen in één van onze Postkantoren.').'</h4>';
			} else if ($type == '2') {
				$html .= '<h4>'.Translate('Pakketten ophalen in één van onze Postpunten.').'</h4>';
			} else if ($type == '4') {
				$html .= '<h4>'.Translate('Pakketten 24 uur per dag, 7 dagen op 7 ophalen in één van onze pakketautomaten.').'</h4>';
			}
			foreach($typesData as $spData) {
				$html .= '<label class="'.get_class($this).'_label" for="'.get_class($this).'_sp_'.$spData['Record']['Id'].'">';
				$html .= '<div class="'.get_class($this).'_sp clearfix">';
				$html .= '<input type="radio" name="'.get_class($this).'_sp" value="'.$spData['Record']['Id'].'" id="'.get_class($this).'_sp_'.$spData['Record']['Id'].'"'.($this->temp_data[$temp_orders_id]['orders']['shipping_method_extra']== $spData['Record']['Id']?' checked=checked':'').' />';
				if ($spData['Record']['Type'] == '4') {
					$image = 'Bpack24-7.png';
					$title = Translate('Bpack24/7: Pakketten 24 uur per dag, 7 dagen op 7 ophalen in 1 van onze pakketautomaten.');
				} else {
					$image = 'BpackATBpost.png';
					$title = Translate('Bpack@Bpost: Pakketten ophalen in 1 van onze PostPunten of Postkantoren.');
				}
				$html .= '<div class="'.get_class($this).'_sp_image"><image src="'.DIR_WS_MODULES.'checkout/modules/Bpost/'.$image.'" width="50" title="'.$title.'" /></div>';
				$html .= '<div class="'.get_class($this).'_sp_name">'.$spData['Record']['Name'].'</div>';
				//Calculate distance
				$distance = round($spData['Distance']);
				$distance_array = array();
				$unit = 1;
				while(strlen($distance) > 0) {
					$distance_array[$unit] = substr($distance, -1);
					$distance = substr($distance, 0, -1);
					$unit = $unit*10;
				}
				krsort($distance_array);
				$distancekm = '';
				$distancem = '';
				foreach($distance_array as $unit=>$value) {
					if ($unit > 1000) {
						$distancekm .= $value;
					} else if ($unit == 1000) {
						$distancekm .= $value.'km ';
					} else if ($unit == 1) {
						$distancem .= $value.'m';
					} else {
						$distancem .= $value;
					}
				}
				while (substr($distancem, 0, 1) == 0) {
					$distancem = substr($distancem, 1);
				}
				if ($distancem == 'm') {
					$distancem = '';
				}
				//EOF calculate distance
				$html .= '<div class="'.get_class($this).'_sp_locationhint">('.Translate('Afstand').': '.$distancekm.$distancem.')</div>';
				$html .= '<div class="'.get_class($this).'_sp_id">('.$spData['Record']['Id'].')</div>';
				$html .= '<div class="'.get_class($this).'_sp_address">'.$spData['Record']['Street'].' '.$spData['Record']['Number'].'<br />'.$spData['Record']['Zip'].' '.$spData['Record']['City'].'</div>';
				$html .= '<div class="'.get_class($this).'_sp_more_info"><a href="'.str_replace('?Function=info&', '?Function=page&', $spData['Info']['ServiceRef']).'" title="'.Translate('Meer info over').' '.$kpData['name'].'" target="_blank" onclick="window.open(this.href, \''.Translate('Bpost Punt info').'\', \'width=945,height=420\'); return false;">'.Translate('Meer info').'</a></div>';
				$html .= '</div>';//end kp
				$html .= '</label>';
			}
			$html .= '</div>';
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
		$Bpost_point = $this->convertXMLtoArray(simplexml_load_file('http://taxipost.geo6.be/Locator?Partner='.$this->config['account_id'].'&AppId='.STORE_NAME.'&Function=info&Format=xml&Language='.$languages_code.$this->temp_data[$temp_orders_id]['orders']['shipping_method_extra']));
		return $this->config['title'].' '.$Bpost_point['Poi']['Record']['OFFICE'].' ('.$Bpost_point['Poi']['Record']['ID'].'), '.$Bpost_point['Poi']['Record']['STREET'].' '.$Bpost_point['Poi']['Record']['NUMBER'].' '.$Bpost_point['Poi']['Record']['ZIP'].' '.$Bpost_point['Poi']['Record']['CITY'];
	}
	public function after_process($orders_id) {
		global $temp_orders_id;
		if ($this->temp_data[$temp_orders_id]['orders']['shipping_method'] == get_class($this)) {
			tep_db_query('UPDATE orders_total SET title="'.$this->getTitle().'" WHERE orders_id = "'.$orders_id.'" AND class="'.$this->type.'"');
		}
	}
	public function administrator() {
		global $Modules;
		echo '<h1>';
		echo get_class($this);
		echo '<button type="button" id="delete_module" href="'.tep_href_link('checkout.php', 'module='.$_GET['module']).'&action=delete_module" class="btn btn-danger pull-right">'.Translate('Verwijder module').'</button>';
		echo '</h1>';
		echo '<hr />';
		if (isset($_POST['action']) && $_POST['action'] == 'save') {
			/********************/
			/*	Save changes	*/
			/********************/
			unset($_POST['action']);
			foreach($_POST as $key=>$data) {
				if ($key == 'bpost_types') {
					$types_total = 0;
					foreach($data as $type=>$value) {
						$types_total += $type;
					}
					$_POST[$key] = $types_total;
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
				<label class="control-label" for="account_id"><?php echo Translate('Account Id');?></label>
				<div class="controls">
					<input type="text" name="account_id" value="<?php echo $this->config['account_id'];?>" class="input-xlarge" id="account_id" />
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="shipping_cost"><?php echo Translate('Verzendkost');?></label>
				<div class="controls">
					<input type="text" name="shipping_cost" value="<?php echo $this->config['shipping_cost'];?>" class="input-xlarge" id="shipping_cost" />
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
				<label class="control-label" for="max_sp"><?php echo Translate('Aantal afhaalpunten');?></label>
				<div class="controls">
					<input type="text" name="max_sp" value="<?php echo $this->config['max_sp'];?>" class="input-xlarge" id="max_sp" />
					<span class="help-block"><?php echo Translate('Het maximum aantal afhaalpunten die mag getoond worden in de checkout.');?></span>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label"><?php echo Translate('Post punt types');?></label>
				<div class="controls">
					<label class="checkbox" for="type1">
						<input name="bpost_types[1]" type="checkbox" value="" id="type1"<?php echo ($this->config['bpost_types']%2?'checked=checked':'');?> />
						<?php echo Translate('Post Office');?>
					</label>
					<label class="checkbox" for="type2">
						<input name="bpost_types[2]" type="checkbox" value="" id="type2"<?php echo (in_array($this->config['bpost_types'], array(2, 3, 6, 7))?'checked=checked':'');?> />
						<?php echo Translate('Post Point');?>
					</label>
					<label class="checkbox" for="type4">
						<input name="bpost_types[4]" type="checkbox" value="" id="type4"<?php echo (in_array($this->config['bpost_types'], array(4, 5, 6, 7))?'checked=checked':'');?> />
						<?php echo Translate('Bpack 24/7');?>
					</label>
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
			'status' => 'false',
			'title' => 'Bpost',
			'description' => '',
			'account_id' => '',
			'min_amount' => '0',
			'max_weight' => '0',
			'free_shipping_over' => '0',
			'shipping_cost' => '0',
			'max_sp' => '10',
			'bpost_types' => '7',
			'sort_order' => 20
		);
		tep_db_query('CREATE TABLE checkout_'.get_class($this).'(
			id INT(11) NOT NULL AUTO_INCREMENT,
			PRIMARY KEY(id),
			status VARCHAR(255),
			title VARCHAR(255),
			description VARCHAR(255),
			account_id VARCHAR(255),
			min_amount FLOAT(10,2),
			max_weight FLOAT(10,2),
			free_shipping_over FLOAT(10,2),
			shipping_cost FLOAT(10,2),
			max_sp INT(11),
			bpost_types INT(11),
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
					,'Kies a.u.b. een Bpost punt.' => array(
							'1' => 'Kies a.u.b. een Bpost punt.', 
							'2' => 'Choisissez un point Bpost, s\'il vous plaît.', 
							'3' => 'Choose a Bpost point if you please.', 
							'4' => 'Wählen Sie eine Bpost Punkt, wenn ich bitten darf.')
					,'Kies a.u.b. een verzendmethode.' => array(
							'1' => 'Kies a.u.b. een verzendmethode.', 
							'2' => 'Choisissez une méthode d\'expédition, s\'il vous plaît.', 
							'3' => 'Choose a shipping method if you please.', 
							'4' => 'Wählen Sie eine Versandart, wenn ich bitten darf.')
					,'Pakketten ophalen in één van onze Postkantoren.' => array(
							'1' => 'Pakketten ophalen in één van onze Postkantoren.', 
							'2' => 'Forfaits choisir l\'un de nos bureaux de poste.', 
							'3' => 'Packages pick one of our Post Offices.', 
							'4' => 'Pakete abholen einem unserer Postämter.')
					,'Pakketten ophalen in één van onze Postpunten.' => array(
							'1' => 'Pakketten ophalen in één van onze Postpunten.', 
							'2' => 'Forfaits en choisir un de nos Points Poste.', 
							'3' => 'Packages pick one of our Post Points.', 
							'4' => 'Pakete abholen einem unserer Post Points.')
					,'Pakketten 24 uur per dag, 7 dagen op 7 ophalen in één van onze pakketautomaten.' => array(
							'1' => 'Pakketten 24 uur per dag, 7 dagen op 7 ophalen in één van onze pakketautomaten.', 
							'2' => 'Forfaits 24 heures par jour, 7 jours sur 7 choix dans l\'une de nos machines emballage.', 
							'3' => 'Packages 24 hours a day, 7 days a week pick in one of our package machines.', 
							'4' => 'Pakete 24 Stunden am Tag, 7 Tage auf 7 Pick in einem unserer Paket Maschinen.')
					,'Bpack24/7: Pakketten 24 uur per dag, 7 dagen op 7 ophalen in 1 van onze pakketautomaten.' => array(
							'1' => 'Bpack24/7: Pakketten 24 uur per dag, 7 dagen op 7 ophalen in 1 van onze pakketautomaten.', 
							'2' => 'Bpack24/7: Packs 24 heures par jour, 7 jours sur 7 choix dans l\'une de nos machines de paquets.', 
							'3' => 'Bpack24/7: Packs 24 hours a day, 7 days on 7 pick in one of our package machines.', 
							'4' => 'Bpack24/7: Packs 24 Stunden am Tag, 7 Tage auf 7 Pick in einem unserer Paket Maschinen.')
					,'Bpack@Bpost: Pakketten ophalen in 1 van onze PostPunten of Postkantoren.' => array(
							'1' => 'Bpack@Bpost: Pakketten ophalen in 1 van onze PostPunten of Postkantoren.', 
							'2' => 'Bpack@Bpost: Forfaits récupérer dans un de nos points de poste ou les bureaux de poste.', 
							'3' => 'Bpack@Bpost: Packages retrieve in one of our Post Points or Post Offices.', 
							'4' => 'BPACK@Bpost: Pakete abzurufen in einem unserer Post Points oder der Post.')
					,'Afstand' => array(
							'1' => 'Afstand', 
							'2' => 'Distance', 
							'3' => 'Distance', 
							'4' => 'Abstand')
					,'Meer info over' => array(
							'1' => 'Meer info over', 
							'2' => 'Plus d\'info', 
							'3' => 'More info', 
							'4' => 'Mehr Info')
					,'Bpost Punt info' => array(
							'1' => 'Bpost Punt info', 
							'2' => 'Info point Bpost', 
							'3' => 'Bpost point info', 
							'4' => 'Bpost Punkt info')
					,'Meer info' => array(
							'1' => 'Meer info', 
							'2' => 'Plus d\'info', 
							'3' => 'More info', 
							'4' => 'Mehr Info')
					,'Verwijder module' => array(
							'1' => 'Verwijder module', 
							'2' => 'Retirez le module', 
							'3' => 'Remove module', 
							'4' => 'Modul entfernen')
					,'Bpost' => array(
							'1' => 'Bpost', 
							'2' => 'Bpost', 
							'3' => 'Bpost', 
							'4' => 'Bpost')
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
					,'Account Id' => array(
							'1' => 'Account Id',
							'2' => 'Account Id',
							'3' => 'Account Id',
							'4' => 'Account Id')
					,'Verzendkost' => array(
							'1' => 'Verzendkost',
							'2' => 'Coût de la livraison',
							'3' => 'Shipping Cost',
							'4' => 'Versandkosten')
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
					,'Aantal afhaalpunten' => array(
							'1' => 'Aantal afhaalpunten',
							'2' => 'Nombre points de ramassage',
							'3' => 'Number pick-up points',
							'4' => 'Anzahl Pick-up Points')
					,'Het maximum aantal afhaalpunten die mag getoond worden in de checkout.' => array(
							'1' => 'Het maximum aantal afhaalpunten die mag getoond worden in de checkout.',
							'2' => 'Le nombre maximum de points de collecte qui doivent être affichés dans la caisse.',
							'3' => 'The maximum number of collection points that should be displayed in the checkout.',
							'4' => 'Die maximale Anzahl der Sammelstellen, die in der Kasse angezeigt werden soll.')
					,'Post punt types' => array(
							'1' => 'Post punt types',
							'2' => 'Types Postpunt',
							'3' => 'Postpunt types',
							'4' => 'Postpunt typen')
					,'Post Office' => array(
							'1' => 'Post Office',
							'2' => 'Post Office',
							'3' => 'Post Office',
							'4' => 'Post Office')
					,'Post Point' => array(
							'1' => 'Post Point',
							'2' => 'Post Point',
							'3' => 'Post Point',
							'4' => 'Post Point')
					,'Bpack 24/7' => array(
							'1' => 'Bpack 24/7',
							'2' => 'Bpack 24/7',
							'3' => 'Bpack 24/7',
							'4' => 'Bpack 24/7')
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