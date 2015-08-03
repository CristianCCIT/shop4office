<?php
//parent class for all modules
class Modules {
	public static $disable_next_button = array();
	public static $available_countries = array();
	public $modules = array()
		 , $module_blocks = array()
		 , $modules_folder = 'includes/modules/checkout/modules/'
		 , $css = array()
		 , $js = array()
		 , $cronData = '';
	
	public function __construct($country = 0) {
		global $Analytics;
		//Get css
		$cssdirHandle = opendir(dirname(__FILE__).'/assets/css/');
		while(false !== ($css = readdir($cssdirHandle))) {
			if (is_file(dirname(__FILE__).'/assets/css/'.$css) && $css != '.' && $css != '..') {
				$this->css[] = dirname(__FILE__).'/assets/css/'.$css;
			}
		}	
		//Get js
		$jsdirHandle = opendir(dirname(__FILE__).'/assets/js/');
		while(false !== ($js = readdir($jsdirHandle))) {
			if (is_file(dirname(__FILE__).'/assets/js/'.$js) && $js != '.' && $js != '..') {
				$this->js[] = dirname(__FILE__).'/assets/js/'.$js;
			}
		}
		//get active modules and add to $modules
		if (is_dir(DIR_FS_CATALOG.$this->modules_folder)) {
			$dirHandle = opendir(DIR_FS_CATALOG.$this->modules_folder);
			while(false !== ($module = readdir($dirHandle))) {
				if (is_dir(DIR_FS_CATALOG.$this->modules_folder.$module) && $module != '.' && $module != '..') {
					//Get css files
					$css = glob(DIR_FS_CATALOG.$this->modules_folder.$module.'/*.css');
					if (isset($css[0])) {
						$this->css[] = $css[0];
					}
					//Get js files
					$js = glob(DIR_FS_CATALOG.$this->modules_folder.$module.'/*.js');
					if (isset($js[0])) {
						$this->js[] = $js[0];
					}

					//Find the class
					$object = glob(DIR_FS_CATALOG.$this->modules_folder.$module.'/*_module.php');
					if (!empty($object[0])) {
						//Include the class
						require_once($object[0]);
						//log every step
						$Analytics->abo_analytics_add_action('file', $object[0], 'include');
						//Prepare the variable
						$object = str_replace(DIR_FS_CATALOG.$this->modules_folder.$module.'/', "", $object[0]);
						$object = str_replace('.php', '', $object);
						//remove '_module'
						$class = str_replace('_module', '', $object);
						
						//initiate modules class if not registered yet
						if (!tep_session_is_registered($class) || !is_object($$class)) {
							global $$class;
							if ($temp_orders_id > 0) {
								echo $class;
								die($temp_orders_id);
							}
							$$class = new $class;
							//log every step
							$Analytics->abo_analytics_add_action('class', $class, 'initiate');
							if ($country > 0) {
								if ($$class->is_active($country)) {
									//sort by sort order in modules by type
									$this->modules[$$class->type][$$class->sort_order] = $class;
									//log every step
									$Analytics->abo_analytics_add_action('checkout', $class, 'add to modules', $$class);
								}
							} else {
								//sort by sort order in modules by type
								$this->modules[$$class->type][$$class->sort_order] = $class;
								//log every step
								$Analytics->abo_analytics_add_action('checkout', $class, 'add to modules', $$class);
							}
							tep_session_register($class);
						}
					}
				}
			}
			$this->getAvailableCountries();
		} else {
			//abo_error_message("<span style='color:#FF0000;'>[CHECKOUT MODULES]</span>&nbsp;The specified Module Folder, " . DIR_FS_CATALOG.$this->modules_folder . " does not exist or isn't accesible." );
		}
	}
	
	public function install_module($fields_array, $module) {
		tep_db_query('CREATE TABLE checkout_'.$module.'(
			name VARCHAR(255),
			type VARCHAR(255),
			value VARCHAR(255),
			options VARCHAR(255),
			sort_order INT(11))');
		tep_db_query('CREATE INDEX name ON checkout_'.$module.' (name)');
		tep_db_query('CREATE INDEX type ON checkout_'.$module.' (type)');
		foreach($fields_array as $key=>$value) {
			$db_array = array();
			$db_array['name'] = $key;
			foreach($value as $name=>$data) {
				if (is_array($data)) {
					$data = serialize($data);
				}
				$db_array[$name] = $data;
			}
			tep_db_perform('checkout_'.$module, $db_array, 'insert');
		}
	}
	
	public function add_to_disable_next_button($var) {
		self::$disable_next_button[$var] = $var;
	}
	
	public function create_error($error) {
		$html = '';
		$html .= '<div class="form-error">';
		$html .= Translate($error);
		$html .= '<div class="form-error-pointer-right"><div class="form-error-pointer-right-inner"></div></div>';
		$html .= '</div>';
		return $html;
	}
	
	public function get_all_zones() {
		$zones = array();
		$query = tep_db_query('SELECT geo_zone_id, geo_zone_name FROM geo_zones');
		while ($zone = tep_db_fetch_array($query)) {
			$zones[$zone['geo_zone_id']] = $zone['geo_zone_name'];
		}
		return $zones;
	}
	
	public function get_zones($country_id) {
		$zones = array();
		$query = tep_db_query('SELECT geo_zone_id FROM zones_to_geo_zones WHERE zone_country_id = "'.$country_id.'"');
		while ($zone = tep_db_fetch_array($query)) {
			$zones[] = $zone['geo_zone_id'];
		}
		return $zones;
	}
	
	public function get_country_list($name, $selected = '', $parameters = '') {
		$countries_array = array(array('id' => '', 'text' => Translate('Maak uw keuze')));
		$query = tep_db_query('SELECT countries_name, countries_id FROM countries WHERE countries_id IN ("'.implode('", "', self::$available_countries).'")');
		while ($array = tep_db_fetch_array($query)) {
			$countries_array[] = array('id' => $array['countries_id'], 'text' => $array['countries_name']);
		}
		return tep_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
	}
	
	private function getAvailableCountries() {
		$all_available_countries = array();
		//go through all modules
		foreach($this->modules as $type=>$typeData) {
			$all_available_countries[$type] = array();
			foreach($typeData as $sort_order=>$module) {
				global $$module;
				//get the zones for each module
				$zones = $$module->getZones();
				if(!empty($zones)) {
					//go through all zones for this module
					foreach($zones as $zone) {
						foreach($this->getCountriesFromZone($zone) as $country) {
							//check if countrie already in array (prevent duplicates)
							if (!in_array($country, $all_available_countries[$type])) {
								$all_available_countries[$type][] = $country;
							}
						}
					}
				}
			}
		}
		//remove empty elements from array
		$all_available_countries = array_filter($all_available_countries);
		reset($all_available_countries);//set pointer to first element
		$intersects = $all_available_countries[key($all_available_countries)];
		//go through all module types
		foreach($all_available_countries as $type=>$countries) {
			$intersects = array_intersect($intersects, $countries);//get intersections from array (== equal values)
		}
		self::$available_countries = $intersects;
	}
	
	public function getCountriesFromZone($zone) {
		$countryList = array();
		if ($zone == '*') {
			$query = tep_db_query('SELECT countries_id FROM countries WHERE status = "true"');
		} else {
			$query = tep_db_query('SELECT zone_country_id as countries_id FROM zones_to_geo_zones WHERE geo_zone_id = "'.$zone.'"');
		}
		while($array = tep_db_fetch_array($query)) {
			$countryList[] = $array['countries_id'];
		}
		return $countryList;
	}
	
	//function to check if country is available in zone
	public function checkZone($zone, $country) {
		if (empty($zone) || $zone == '*') {
			//not active for zones or all zones are active, show for all countries
			return true;
		} else {
			//check if country is available, if not this module will not be shown.
			if (!empty($country)) {
				if (in_array($zone, $zones = $this->get_zones($country))){//check if country is in zone
					return true;
				}
			}
		}
		return false;
	}
	
	//function to check if module can be used for the selected shipping module 
	public function checkShippingMethod($shipping) {
		global $temp_orders_id;
		//check if choosen shipping_module is active.
		if (empty($shipping) || $shipping == '*') {
			return true;
		} else {
			$selected_query = tep_db_query('SELECT shipping_method FROM temp_orders WHERE orders_id = "'.$temp_orders_id.'"');
			$selected = tep_db_fetch_array($selected_query);
			$shipping_modules = explode(';', $shipping);
			if (in_array($selected['shipping_method'], $shipping_modules)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	public function checkTranslations($directory, $translations) {
		//Go through all given translations
		foreach($translations as $string=>$translationData) {
			foreach($translationData as $id=>$translation) {
				if (!$this->translationExists($id, $string)) {//Check if this string exists in translation table for this language
					if (!empty($translation)) {
						//We have a translation
						tep_db_query('INSERT INTO translation (text, translation, language_id) VALUES("'.$string.'", "'.$translation.'", "'.$id.'")');
					} else {
						//We don't have a translation
						tep_db_query('INSERT INTO translation_request (request_text, language, location) VALUES("'.$string.'", "'.$id.'", "'.__FILE__.'")');
					}
				}
			}
		}
		//Search all translation functions in files
		$files = $this->getFilesFromDir($directory);
		foreach($files as $file) {
			$sourcestring = file_get_contents($file);
			preg_match_all('/Translate\x28\'([^\']*)/',$sourcestring,$matches);
			$languages = tep_get_languages(true);
			foreach($matches[1] as $string) {//loop through all translation strings
				foreach($languages as $lngData) {//loop through all active languages
					if (!$this->translationExists($lngData['id'], $string)) {//Check if this string exists in translation table
						//We don't have a translation
						tep_db_query('INSERT INTO translation_request (request_text, language, location) VALUES("'.$string.'", "'.$lngData['id'].'", "'.$file.'")');
					}
				}
			}
		}
	}
	
	private function translationExists($language_id, $string) {
		$t_query = tep_db_query('SELECT id FROM translation WHERE text = "'.$string.'" AND language_id = "'.$language_id.'"');
		if (tep_db_num_rows($t_query) > 0) {
			return true;
		}
		return false;
	}
	
	private function getFilesFromDir($dir) {
		$files = array();
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if(is_dir($dir.'/'.$file)) {
						$dir2 = $dir.'/'.$file;
						$files[] = $this->getFilesFromDir($dir2);
					}
					else {
					  $files[] = $dir.'/'.$file;
					}
				}
			}
			closedir($handle);
		}
		return $this->array_flat($files);
	}
	
	public function array_flat($array) {
		$tmp = array();
		foreach($array as $a) {
			if(is_array($a)) {
			$tmp = array_merge($tmp, $this->array_flat($a));
			} else {
				$tmp[] = $a;
			}
		}
		return $tmp;
	}
	
	private function generateCSS() {
		$css = '';
		sort($this->css);
		foreach($this->css as $cssFile) {
			$css .= file_get_contents($cssFile);
		}
		return $css;
	}
	
	public function generateCSSFile() {
		/*if (!is_file(dirname(__FILE__).'/cache/stylesheet.php')) {*/
			$css = $this->generateCSS();
			include_once(dirname(__FILE__).'/classes/minify.php');
			$minifyCCS = new MinifyCSS($css);
			$minifyCCS->minify(dirname(__FILE__).'/cache/stylesheet.php', $stripComments = true, $stripWhitespace = true, $shortenHex = true, $combineImports = true, $importFiles = true);
		/*}*/
		return DIR_WS_HTTP_CATALOG.DIR_WS_MODULES.'checkout/cache/stylesheet.php';
	}
	
	private function generateJS() {
		$js = '';
		sort($this->js);
		foreach($this->js as $jsFile) {
			$js .= file_get_contents($jsFile);
		}
		return $js;
	}
	
	public function generateJSFile() {
		if (!is_file(dirname(__FILE__).'/cache/javascript.php')) {
			$js = $this->generateJS();
			if (strlen($js) > 0) {
				include_once(dirname(__FILE__).'/classes/minify.php');
				$minifyJS = new MinifyJS($js);
				$minifyJS->minify(dirname(__FILE__).'/cache/javascript.php', $stripComments = true, $stripWhitespace = true);
			} else {
				$fhandle = fopen(dirname(__FILE__).'/cache/javascript.php', "w");
				fclose($fhandle);
			}
		}
		return DIR_WS_HTTP_CATALOG.DIR_WS_MODULES.'checkout/cache/javascript.php';
	}
	
	public function addCron($min = '*', $hour = '*', $dayOfMonth = '*', $month = '*', $dayOfWeek = '*', $cmd = '') {
		if (!in_array('exec', explode(', ', ini_get('disable_functions')))) {
			include_once(dirname(__FILE__).'/classes/Crontab.php');
			$cron = new Crontab(get_current_user());
			$cron->addCron($c_min, $c_hour, $dayOfMonth, $month, $dayOfWeek, $cmd);
			$cron->writeCrontab();
		} else {
			echo '<div class="alert alert-error"><strong>'.Translate('EXEC function is disabled').'</strong>&nbsp;'.Translate('Creëer handmatig de conjob').'<br />
			Min: '.$min.'<br />
			Hour: '.$hour.'<br />
			Day of the month: '.$dayOfMonth.'<br />
			Month: '.$month.'<br />
			Day of the week: '.$dayOfWeek.'<br />
			Command: '.$cmd.'<br />
			</div>';
		}
	}
	
	public function get_order_statusses() {
		global $languages_id;
		$statusses = array();
		$s_query = tep_db_query('SELECT orders_status_id, orders_status_name FROM orders_status WHERE language_id = "'.(int)$languages_id.'" ORDER BY orders_status_id asc');
		while ($s = tep_db_fetch_array($s_query)) {
			$statusses[$s['orders_status_id']] = $s['orders_status_name'];
		}
		return $statusses;
	}
	
	public function add_new_module() {
		if($_FILES["zip_file"]["name"]) {
			$filename = $_FILES["zip_file"]["name"];
			$source = $_FILES["zip_file"]["tmp_name"];
			$type = $_FILES["zip_file"]["type"];
		 
			$name = explode(".", $filename);
			$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
			foreach($accepted_types as $mime_type) {
				if($mime_type == $type) {
					$okay = true;
					break;
				} 
			}
		 
			$continue = strtolower($name[1]) == 'zip' ? true : false;
			if(!$continue) {
				$message = "The file you are trying to upload is not a .zip file. Please try again.";
			}
		 
			$target_path = DIR_FS_CATALOG.'temp/'.$filename;  // change this to the correct site path
			if(move_uploaded_file($source, $target_path)) {
				$zip = new ZipArchive();
				chmod($target_path, 0777);
				$x = $zip->open($target_path);
				if ($x === true) {
					$mod_dir = dirname($zip->getNameIndex(0));
					$zip->extractTo(DIR_FS_CATALOG.$this->modules_folder); // change this to the correct site path
					$zip->close();
		 
					unlink($target_path);
					$files = $this->listFolderFiles(DIR_FS_CATALOG.$this->modules_folder.$mod_dir);
					foreach($files as $file) {
						chmod($file, 0777);
						if (end(explode('.', $file)) == 'php') {
							$content = file_get_contents($file);
							$newcontent = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $content);
							file_put_contents($file, "$newcontent");
						}
					}
					header('location: '.tep_href_link('checkout.php', 'module='.$mod_dir));
				}
				$message = "Your .zip file was uploaded and unpacked.";
			} else {	
				$message = "There was a problem with the upload. Please try again.";
			}
		}
		if($message) echo "<p>$message</p>";
		?>
		<form enctype="multipart/form-data" method="post" action="">
		<label>Choose a zip file to upload: <input type="file" name="zip_file" /></label>
		<br />
		<button class="btn" type="submit" name="submit" value="Upload"><?php echo Translate('Upload');?></button>
		</form>
		<?php
	}
	
	public function delete_module($module) {
		$moddir = DIR_FS_CATALOG.$this->modules_folder.$module;
		tep_db_query('DROP TABLE checkout_'.$module);
		$files = $this->listFolderFiles($moddir);
		foreach($files as $file) {
			if (is_dir($file)) {
				rmdir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($moddir);
		header('location: '.tep_href_link('checkout.php'));
	}
	
	public function listFolderFiles($dir) {
		$files = array();
		$ffs = scandir($dir);
		foreach($ffs as $ff){
			if($ff != '.' && $ff != '..'){
				if(!is_dir($dir.'/'.$ff)){
					$files[] = $dir.'/'.$ff;
				} else {
					$files = array_merge($files, $this->listFolderFiles($dir.'/'.$ff));
					$files[] = $dir.'/'.$ff;
				}
			}
		}
		return $files;
	}
}
?>