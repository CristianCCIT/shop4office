<?php

require_once('includes/application_top.php');

$filename = $_GET['localfile'];

$tempDir = 'temp/';

$seperator = "\t";

$languages = tep_get_languages();

if (preg_match("/^artikel\d{4}([\.])txt$/", $filename)) { //verwerking artikel.txt

	$lines = file(DIR_FS_CATALOG.$tempDir.$filename);

	$headertags = array();

	foreach (explode($seperator,$lines[0]) as $title) { //create list of table names

		$headertags[] = substr($title, 2); //delete 'v_' from every titel

	}

	unset($lines[0]); //delete first row == titles

	$products_fields = tep_get_table_fields('products');

	foreach ($lines as $key=>$value) { //go through all lines in file

		$data = array();

		foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line

			$data[$headertags[$count]] = trim($waarde);

		}

		if ($data['status'] == 'Active') { //product to add

			$products_data = array();

			if (($products_id = tep_get_products_id($data['products_model'])) == '') { //check if products_model exists

				tep_db_query('INSERT INTO products (products_model, products_date_added) VALUES ("'.$data['products_model'].'", NOW())');

				$products_id = tep_db_insert_id();

				echo '<span style="color:#499900;font-weight:bold;">New product: '.$data['products_model'].'</span><br />';

			}

			

			//MANUFACTURERS

			if (isset($data['manufacturers_name'])) { //check if manufacturers_name is in file

				if (($manufacturers_id = tep_get_manufacturers_id($data['manufacturers_name'])) == '') { //check if manufacturers_name exist

					tep_db_query('INSERT INTO manufacturers (manufacturers_name, date_added, last_modified) VALUES ("'.$data['manufacturers_name'].'", NOW(), NOW())');

					$manufacturers_id = tep_db_insert_id();

					echo '<span style="color:#0289FF;font-weight:bold;">New manufacturer: '.$data['manufacturers_name'].'</span><br />';

				}

				$products_data['manufacturers_id'] = $manufacturers_id; //add manufacturers_id to products list

			}

			

			//CATEGORIES

			$categories_data = array();

			tep_db_query('DELETE FROM products_to_categories WHERE products_id = "'.$products_id.'"'); //delete this product from any categorie

			foreach ($languages as $key=>$value) { //go through all installed languages

				foreach ($data as $titel=>$waarde) { 

					$pattern = "@^(?:categories_name_){1}([^.]+)@";

					if (preg_match($pattern, $titel, $matches)) { //check for first level

						$categories_data[$matches[1]][1] = $waarde; //[level] [language_id] = naam categorie

					}

					$pattern = "@^(?:categories_name){1}([^_]+)[_]{1}([^.]+)@";

					if (preg_match($pattern, $titel, $matches)) { //check for other levels then first

						if ($matches[1] == $value['id']) {

							$categories_data[$matches[2]][$value['id']] = $waarde; //[level] [language_id] = naam categorie

						}

					}

				}

			}		

			foreach ($categories_data as $level=>$categories) {

				foreach ($categories as $language_id=>$name) {

					if ($name != '') {

						if ($language_id == '1') {

							$parent_id = $categories_id; //Only change parent_id when All other languages are passed. Language_id 1 is always first.

						}

						if ($level == '1') {

							$parent_id = 0; //first level is parent_id 1

						}

						if (($categories_id = tep_get_categorie_id($name, $language_id, $parent_id)) == '') { //if categorie does not exist, make it

							if ($language_id == '1') {

								tep_db_query('INSERT INTO categories (parent_id, date_added, last_modified) VALUES ("'.$parent_id.'", NOW(), NOW())');

								$categories_id = tep_db_insert_id();

								tep_db_query('INSERT INTO categories_description (categories_id, language_id, categories_name) VALUES ("'.$categories_id.'", "'.$language_id.'", "'.$name.'")');

								echo '<span style="color:#07B298;font-weight:bold;">New categorie: '.$name.'</span><br />';

							} else {

								$categories_id = tep_get_categorie_id($categories_data[$level]['1'], 1, $parent_id); //Get categories_id FROM dutch translation

								if (tep_check_categorie_id($categories_id, $language_id)) {

									tep_db_query('UPDATE categories_description SET categories_name = "'.$name.'" WHERE categories_id = "'.$categories_id.'" AND language_id = "'.$language_id.'"');

								} else {

									tep_db_query('INSERT INTO categories_description (categories_id, language_id, categories_name) VALUES ("'.$categories_id.'", "'.$language_id.'", "'.$name.'")');

								}

							}

						}

					}

				}

			}

			tep_db_query('INSERT INTO products_to_categories (products_id, categories_id) VALUES("'.$products_id.'", "'.$categories_id.'")');

			

			//PRODUCTS

			if (isset($data['status']) && $data['status'] == 'Active') { //add products_status

				$products_data['products_status'] = '1';

			}

			if (isset($data['date_avail'])) { //add products_date_available

                $matches = preg_split("/[\-]+/", $data['date_avail']);

                if (strlen($matches[0]) == '4') {

                    $products_data['products_date_available'] = $matches[0].'-'.$matches[1].'-'.$matches[2];

                } else {

                    $products_data['products_date_available'] = $matches[2].'-'.$matches[1].'-'.$matches[0];

                }

			}

            if (isset($data['date_added'])) { //add products_date_added

                $matches = preg_split("/[\-]+/", $data['date_added']);

                if (strlen($matches[0]) == '4') {

                    $products_data['products_date_added'] = $matches[0].'-'.$matches[1].'-'.$matches[2];

                } else {

                    $products_data['products_date_added'] = $matches[2].'-'.$matches[1].'-'.$matches[0];

                }

            }

			if (isset($data['tax_class_title'])) { //add products_tax_class_id

				$products_data['products_tax_class_id'] = tep_get_tax_title_class_id($data['tax_class_title']);

			}

			foreach ($products_fields as $field) { //go through all products fields

				if (isset($data[$field])) { //if field exists in data, add it

					$products_data[$field] = $data[$field];

				}

			}

			$products_data['products_last_modified'] = 'now()';

			tep_db_perform('products', $products_data, 'update', 'products_id = "'.$products_id.'"'); //update products table for this product

			echo '<span style="color:#6DB207;font-weight:bold;">Product updated: '.$products_data['products_model'].'</span><br />';

			

			//SPECIALS

			if (isset($data['specials_new_products_price']) && $data['specials_new_products_price'] != '' && isset($data['expires_date']) && $data['expires_date'] != '') { //check if special data is complete

				$split_expires_date = explode('/', $data['expires_date']);

				if (strlen($split_expires_date[2]) == 2) {

					$split_expires_date[2] = '20'.$split_expires_date[2];

				}

				$specials_data = array('specials_new_products_price' => $data['specials_new_products_price'],

									   'specials_last_modified' => 'now()',

									   'expires_date' => $split_expires_date[2].'-'.$split_expires_date[1].'-'.$split_expires_date[0]

									   );

				if (isset($data['status']) && $data['status'] == 'Active') { //check if special is active

					$specials_data['status'] = '1';

				} else {

					$specials_data['status'] = '0';

				}

				if (tep_check_special($products_id)) { //if special exist, update

					tep_db_perform('specials', $specials_data, 'update', 'products_id="'.$products_id.'"');

					echo '<span style="color:#FF9000;font-weight:bold;">Special updated: '.$products_data['products_model'].'</span><br />';

				} else { //special does not exist, make new

					$specials_data['products_id'] = $products_id;

					$specials_data['specials_date_added'] = 'now()';

					tep_db_perform('specials', $specials_data, 'insert');

					echo '<span style="color:#FF9000;font-weight:bold;">New special: '.$products_data['products_model'].'</span><br />';

				}

			} else {

				tep_db_query('DELETE FROM specials WHERE products_id = "'.$products_id.'"');

			}

			

			//PRODUCTS_DESCRITPTION

			$products_description_data = array();

			$products_description_fields = tep_get_table_fields('products_description');

			foreach ($products_description_fields as $field) { //go through all products_description fields

				foreach($languages as $id=>$values) {

					if (isset($data[$field.'_'.$values['id']])) {

						$products_description_data[$values['id']][$field] = $data[$field.'_'.$values['id']]; //add products_description data width language_id

					}

				}

			}

			foreach ($languages as $id=>$values) {

				if (isset($products_description_data[$values['id']])) { //check if there is data for the language

					if (tep_check_products_description($products_id, $values['id'])) { //update

						tep_db_perform('products_description', $products_description_data[$values['id']], 'update', 'products_id="'.$products_id.'" AND language_id ="'.$values['id'].'"');

					} else { //insert

						$products_description_data[$values['id']]['products_id'] = $products_id;

						$products_description_data[$values['id']]['language_id'] = $values['id'];

						tep_db_perform('products_description', $products_description_data[$values['id']], 'insert');

					}

				}

			}

			

			//ATTRIBUTES

			$products_attributes_data = array();

			foreach ($data as $titel=>$waarde) {

				$pattern = "@^(?:attribute_options_id_){1}([^.]+)@";

				if (preg_match($pattern, $titel, $matches) && !empty($waarde)) {

					$products_attributes_data['options']['id'] = $waarde;

				}

				$pattern = "@^(?:attribute_options_name_){1}([^.]+)[_]{1}([^.]+)@";

				if (preg_match($pattern, $titel, $matches) && !empty($waarde)) {

					$products_attributes_data['options'][$matches[2]] = $waarde; //[options] [language_id]

				}

				$pattern = "@^(?:attribute_values_id_){1}([^.]+)[_]{1}([^.]+)@";

				if (preg_match($pattern, $titel, $matches) && !empty($waarde)) {

					$products_attributes_data['values']['id'] = $waarde;

				}

				$pattern = "@^(?:attribute_values_price_){1}([^.]+)[_]{1}([^.]+)@";

				if (preg_match($pattern, $titel, $matches) && !empty($waarde)) {

					$products_attributes_data['values']['price'] = $waarde;

				}

				$pattern = "@^(?:attribute_values_name_){1}([^.]+)[_]{1}([^.]+)[_]{1}([^.]+)@";

				if (preg_match($pattern, $titel, $matches) && !empty($waarde)) {

					$products_attributes_data['values'][$matches[3]] = $waarde; //[options] [language_id]

				}

			}

			

			if (!empty($products_attributes_data)) {

				//PRODUCTS_ATTRIBUTES

				if (($products_attributes_id = tep_get_products_attributes_id($products_id, $products_attributes_data['options']['id'], $products_attributes_data['values']['id'])) == '') {

					tep_db_query('INSERT INTO products_attributes (products_id, options_id, options_values_id) VALUES ("'.$products_id.'", "'.$products_attributes_data['options']['id'].'", "'.$products_attributes_data['values']['id'].'")');

					$products_attributes_id = tep_db_insert_id();

					echo '<span style="color:#6DB207;font-weight:bold;">New attribute: '.$products_attributes_data['options'][1].'</span><br />';

				}

				if ($products_attributes_data['values']['price'] >= 0) {

					$products_attributes_data['values']['prefix'] = '+';

				} else {

					$products_attributes_data['values']['prefix'] = '-';

				}

				tep_db_query('UPDATE products_attributes SET options_values_price = "'.$products_attributes_data['values']['price'].'", price_prefix = "'.$products_attributes_data['values']['prefix'].'" WHERE products_attributes_id = "'.$products_attributes_id.'"');

				echo '<span style="color:#6DB207;font-weight:bold;">Attribute price updated: '.$products_attributes_data['values']['prefix'].$products_attributes_data['values']['price'].'</span><br />';

				

				//PRODUCTS_OPTIONS

				foreach ($languages as $id=>$values) {

					if (!tep_check_products_options_id($products_attributes_data['options']['id'], $values['id'])) {

						tep_db_query('INSERT INTO products_options (products_options_id, language_id, products_options_name) VALUES ("'.$products_attributes_data['options']['id'].'", "'.$values['id'].'", "'.$products_attributes_data['options'][$values['id']].'")');

					}

					tep_db_query('UPDATE products_options SET products_options_name = "'.$products_attributes_data['options'][$values['id']].'" WHERE products_options_id = "'.$products_attributes_data['options']['id'].'" AND language_id = "'.$values['id'].'"');

					echo '<span style="color:#6DB207;font-weight:bold;">Attribute name updated: '.$products_attributes_data['options'][$values['id']].'</span><br />';

				}

				

				//PRODUCTS_OPTIONS_VALUES

				foreach ($languages as $id=>$values) {

					if (isset($products_attributes_data['values'][$values['id']])) {

						if (!tep_check_products_options_values_id($products_attributes_data['values']['id'], $values['id'])) {

							tep_db_query('INSERT INTO products_options_values (products_options_values_id, language_id, products_options_values_name) VALUES ("'.$products_attributes_data['values']['id'].'", "'.$values['id'].'", "'.$products_attributes_data['values'][$values['id']].'")');

						}

						tep_db_query('UPDATE products_options_values SET products_options_values_name = "'.$products_attributes_data['values'][$values['id']].'" WHERE products_options_values_id = "'.$products_attributes_data['values']['id'].'" AND language_id = "'.$values_id.'"');

						echo '<span style="color:#6DB207;font-weight:bold;">Attribute value updated: '.$products_attributes_data['values'][$values['id']].'</span><br />';

					}

				}

				

				//PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS

				if (!tep_check_products_options_values_to_products_options_id($products_attributes_data['values']['id'], $products_attributes_data['options']['id'])) {

					tep_db_query('INSERT INTO products_options_values_to_products_options (products_options_id, products_options_values_id) VALUES ("'. $products_attributes_data['options']['id'].'", "'.$products_attributes_data['values']['id'].'")');

				}

			}

		} else if ($data['status'] == 'Delete') {

			delete_product($data['products_model']);

			echo '<span style="color:#FF0000;font-weight:bold;">Product deleted: '.$data['products_model'].'</span><br />';

		} else {

			echo 'ongeldige status voor product '.$data['products_model'].': '.$data['status'].'<br />';

		}

		echo '<hr />';

	}

} else if (preg_match("/^vertaal\d{4}([\.])txt$/", $filename)) { //verwerking vertaal.txt
	$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
	$headertags = array();
	foreach (explode($seperator,$lines[0]) as $title) { //create list of table names
		$headertags[] = substr($title, 2); //delete 'v_' from every titel
	}
	unset($lines[0]); //delete first row == titles
	foreach ($lines as $key=>$value) { //go through all lines in file
		$data = array();
		foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line
			$data[$headertags[$count]] = trim($waarde);
		}
		if ($data['status'] == 'Active') { //product to add
			$products_data = array();
			if (($products_id = tep_get_products_id($data['products_model'])) > 0) { //check if products_model exists

				//CATEGORIES
				$categories_data = array();
				tep_db_query('DELETE FROM products_to_categories WHERE products_id = "'.$products_id.'"'); //delete this product from any categorie
				foreach ($languages as $key=>$value) { //go through all installed languages
					foreach ($data as $titel=>$waarde) { 
						$pattern = "@^(?:categories_name_){1}([^.]+)@";
						if (preg_match($pattern, $titel, $matches)) { //check for first level
							$categories_data[$matches[1]][1] = $waarde; //[level] [language_id] = naam categorie
						}
						$pattern = "@^(?:categories_name){1}([^_]+)[_]{1}([^.]+)@";
						if (preg_match($pattern, $titel, $matches)) { //check for other levels then first
							if ($matches[1] == $value['id']) {
								$categories_data[$matches[2]][$value['id']] = $waarde; //[level] [language_id] = naam categorie
							}
						}
					}
				}		
				foreach ($categories_data as $level=>$categories) {
					if ($level == '1') {
						$parent_id = 0;
					} else {
						$parent_id = $categories_id;
					}
					if ($categories[1] != '') {
						if (($categories_id = tep_get_categorie_id($categories[1], '1', $parent_id)) == '') { //if categorie does not exist, make it
							tep_db_query('INSERT INTO categories (parent_id, date_added, last_modified) VALUES ("'.$parent_id.'", NOW(), NOW())');
							$categories_id = tep_db_insert_id();
							tep_db_query('INSERT INTO categories_description (categories_id, language_id, categories_name) VALUES ("'.$categories_id.'", "'.$language_id.'", "'.$name.'")');
							foreach($categories as $language_id=>$name) {
								if ($language_id != '1' && $name != '') {
									tep_db_query('INSERT INTO categories_description (categories_id, language_id, categories_name) VALUES ("'.$categories_id.'", "'.$language_id.'", "'.$name.'")');
								}
							}
						} else { //categorie exists in language 1
							foreach($categories as $language_id=>$name) {
								if ($name != '') {
									if (tep_check_categorie_id($categories_id, $language_id)) {
										tep_db_query('UPDATE categories_description SET categories_name = "'.$name.'" WHERE language_id = "'.$language_id.'" AND categories_id = "'.$categories_id.'"');
									} else {
										tep_db_query('INSERT INTO categories_description (categories_id, language_id, categories_name) VALUES ("'.$categories_id.'", "'.$language_id.'", "'.$name.'")');
									}
								}
							}
						}
					}
				}
				tep_db_query('INSERT INTO products_to_categories (products_id, categories_id) VALUES("'.$products_id.'", "'.$categories_id.'")');
			
				//PRODUCTS_DESCRITPTION
				$products_description_data = array();
				$products_description_fields = tep_get_table_fields('products_description');
				foreach ($products_description_fields as $field) { //go through all products_description fields
					foreach($languages as $id=>$values) {
						if (isset($data[$field.'_'.$values['id']])) {
							$products_description_data[$values['id']][$field] = $data[$field.'_'.$values['id']]; //add products_description data width language_id
						}
					}
				}
				if (!empty($products_description_data)) {
					foreach ($languages as $id=>$values) {
						if (isset($products_description_data[$values['id']])) { //check if there is data for the language
							if (tep_check_products_description($products_id, $values['id'])) { //update
								tep_db_perform('products_description', $products_description_data[$values['id']], 'update', 'products_id="'.$products_id.'" AND language_id ="'.$values['id'].'"');
							} else { //insert
								$products_description_data[$values['id']]['products_id'] = $products_id;
								$products_description_data[$values['id']]['language_id'] = $values['id'];
								tep_db_perform('products_description', $products_description_data[$values['id']], 'insert');
							}
						}
					}
				}
			}
			echo '<span style="color:#6DB207;font-weight:bold;">Product updated: '.$data['products_model'].'</span><br />';
		} else {
			echo 'ongeldige status voor product '.$data['products_model'].': '.$data['status'].'<br />';
		}
		echo '<hr />';
	}	
} else if (preg_match("/^extra\d{4}([\.])txt$/", $filename)) { //verwerking extra.txt
	$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
	$headertags = array();
	foreach (explode($seperator,$lines[0]) as $title) { //create list of table names
		$headertags[] = str_replace('attribute_', '', substr($title, 2)); //delete 'v_' and replace 'attribute_' from every title
	}
	$previous_model = '';
	unset($lines[0]); //delete first row == titles
	foreach ($lines as $key=>$value) { //go through all lines in file
		$data = array();
		foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line
			$data[$headertags[$count]] = trim($waarde);
		}
		if ($previous_model != $data['products_model']) {
			$products_id = tep_get_products_id($data['products_model']);
			tep_db_query('DELETE FROM products_attributes WHERE products_id = "'.$products_id.'"');
		}
		$previous_model = $data['products_model'];
		if ($data['status'] == 'Active') { //product to add
			if (($products_id = tep_get_products_id($data['products_model'])) > 0) { //check if products_model exists
				$products_attributes_data = array();
				foreach ($data as $titel=>$waarde) {
					$pattern = "@^(?:options_id_){1}([^.]+)@";
					if (preg_match($pattern, $titel, $matches)) {
						$products_attributes_data['options']['id'] = $waarde;
					}
					$pattern = "@^(?:options_name_){1}([^.]+)[_]{1}([^.]+)@";
					if (preg_match($pattern, $titel, $matches)) {
						$products_attributes_data['options'][$matches[2]] = $waarde; //[options] [language_id]
					}
					$pattern = "@^(?:values_id_){1}([^.]+)[_]{1}([^.]+)@";
					if (preg_match($pattern, $titel, $matches)) {
						$products_attributes_data['values']['id'] = $waarde;
					}
					$pattern = "@^(?:values_price_){1}([^.]+)[_]{1}([^.]+)@";
					if (preg_match($pattern, $titel, $matches)) {
						$products_attributes_data['values']['price'] = $waarde;
					}
					$pattern = "@^(?:values_name_){1}([^.]+)[_]{1}([^.]+)[_]{1}([^.]+)@";
					if (preg_match($pattern, $titel, $matches)) {
						$products_attributes_data['values'][$matches[3]] = $waarde; //[options] [language_id]
					}
				}
				if (!empty($products_attributes_data)) {
					//PRODUCTS_ATTRIBUTES
					if (($products_attributes_id = tep_get_products_attributes_id($products_id, $products_attributes_data['options']['id'], $products_attributes_data['values']['id'])) == '') {
						tep_db_query('INSERT INTO products_attributes (products_id, options_id, options_values_id) VALUES ("'.$products_id.'", "'.$products_attributes_data['options']['id'].'", "'.$products_attributes_data['values']['id'].'")');
						$products_attributes_id = tep_db_insert_id();
						echo '<span style="color:#6DB207;font-weight:bold;">New attribute: '.$products_attributes_data['options'][1].'</span><br />';
					}
					if ($products_attributes_data['values']['price'] >= 0) {
						$products_attributes_data['values']['prefix'] = '+';
					} else {
						$products_attributes_data['values']['prefix'] = '-';
					}
					tep_db_query('UPDATE products_attributes SET options_values_price = "'.$products_attributes_data['values']['price'].'", price_prefix = "'.$products_attributes_data['values']['prefix'].'"');
					echo '<span style="color:#6DB207;font-weight:bold;">Attribute price updated: '.$products_attributes_data['values']['prefix'].$products_attributes_data['values']['price'].'</span><br />';
					
					//PRODUCTS_OPTIONS
					foreach ($languages as $id=>$values) {
						if (!tep_check_products_options_id($products_attributes_data['options']['id'], $values['id'])) {
							tep_db_query('INSERT INTO products_options (products_options_id, language_id, products_options_name) VALUES ("'.$products_attributes_data['options']['id'].'", "'.$values['id'].'", "'.$products_attributes_data['options'][$values['id']].'")');
						}
						tep_db_query('UPDATE products_options SET products_options_name = "'.$products_attributes_data['options'][$values['id']].'" WHERE products_options_id = "'.$products_attributes_data['options']['id'].'" AND language_id = "'.$values['id'].'"');
						echo '<span style="color:#6DB207;font-weight:bold;">Attribute name updated: '.$products_attributes_data['options'][$values['id']].'</span><br />';
					}
					//PRODUCTS_OPTIONS_VALUES
					foreach ($languages as $id=>$values) {
						if (isset($products_attributes_data['values'][$values['id']])) {
							if (!tep_check_products_options_values_id($products_attributes_data['values']['id'], $values['id'])) {
								tep_db_query('INSERT INTO products_options_values (products_options_values_id, language_id, products_options_values_name) VALUES ("'.$products_attributes_data['values']['id'].'", "'.$values['id'].'", "'.$products_attributes_data['values'][$values['id']].'")');
							}
							tep_db_query('UPDATE products_options_values SET products_options_values_name = "'.$products_attributes_data['values'][$values['id']].'" WHERE products_options_values_id = "'.$products_attributes_data['values']['id'].'" AND language_id = "'.$values_id.'"');
							echo '<span style="color:#6DB207;font-weight:bold;">Attribute value updated: '.$products_attributes_data['values'][$values['id']].'</span><br />';
						}
					}
					
					//PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS
					if (!tep_check_products_options_values_to_products_options_id($products_attributes_data['values']['id'], $products_attributes_data['options']['id'])) {
						tep_db_query('INSERT INTO products_options_values_to_products_options (products_options_id, products_options_values_id) VALUES ("'. $products_attributes_data['options']['id'].'", "'.$products_attributes_data['values']['id'].'")');
					}
				}
			}
		} else {
			echo 'ongeldige status voor product '.$data['products_model'].': '.$data['status'].'<br />';
		}
		echo '<hr />';
	}	
} else if (preg_match("/^wis\d{4}([\.])txt$/", $filename)) { //verwerking wis.txt
	$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
	unset($lines[0]); //delete first row == titles
	foreach ($lines as $key=>$value) { //go through all lines in file
		$data = explode($seperator, $value);
		delete_product($data[0]);
		echo 'product deleted: '.$data[0].'<br />';
	}
} else if (preg_match("/^stock\d{4}([\.])txt$/", $filename)) { //verwerking stock.txt
	$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
	$headertags = array();
	foreach (explode($seperator,$lines[0]) as $title) { //create list of table names
		$headertags[] = substr($title, 2); //delete 'v_' and replace 'attribute_' from every title
	}
	unset($lines[0]); //delete first row == titles
	$products_fields = tep_get_table_fields('products');
	foreach ($lines as $key=>$value) { //go through all lines in file
		$products_data = array();
		$data = array();
		foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line
			$data[$headertags[$count]] = trim($waarde);
		}
		if ($data['status'] == 'Active') { //product to change stock
			if (($products_id = tep_get_products_id($data['products_model'])) > 0) { //check if products_model exists
				foreach ($products_fields as $field) { //go through all products fields
					if (isset($data[$field])) { //if field exists in data, add it
						$products_data[$field] = $data[$field];
					}
				}
				$products_data['products_last_modified'] = 'now()';
				tep_db_perform('products', $products_data, 'update', 'products_id = "'.$products_id.'"'); //update products table for this product
				echo '<span style="color:#6DB207;font-weight:bold;">Product updated: '.$products_data['products_model'].'</span><br />';
				echo '<hr />';
			}
		} else {
			echo 'ongeldige status voor product '.$data['products_model'].': '.$data['status'].'<br />';
			echo '<hr />';
		}
	}
} else if (preg_match("/^familie([\.])txt$/", $filename)) { //verwerking familie.txt
	$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
	$headertags = array();
	foreach (explode($seperator,$lines[0]) as $title) { //create list of table names
		$headertags[] = substr($title, 2); //delete 'v_' from every titel
	}
	unset($lines[0]); //delete first row == titles
	$p2c_fields = tep_get_table_fields('products_to_categories');
	foreach ($lines as $key=>$value) { //go through all lines in file
		$data = array();
		foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line
			$data[$headertags[$count]] = trim($waarde);
		}
		$products_data = array();
		$categories_data = array();
		if (($products_id = tep_get_products_id($data['products_model'])) != '') { //check if products_model exists
			//CATEGORIES
			foreach ($languages as $key=>$value) {
				foreach ($data as $titel=>$waarde) {
					$pattern = "@^(?:categories_name_){1}([^.]+)@";
					if (preg_match($pattern, $titel, $matches)) { //check for first level
						$categories_data[$matches[1]][1] = $waarde; //[level] [language_id] = naam categorie
					}
					$pattern = "@^(?:categories_name){1}([^_]+)[_]{1}([^.]+)@";
					if (preg_match($pattern, $titel, $matches)) { //check for other levels then first
						if ($matches[1] == $value['id']) {
							$categories_data[$matches[2]][$value['id']] = $waarde; //[level] [language_id] = naam categorie
						}
					}
				}
			}
			foreach ($categories_data as $level=>$categories) {
				foreach ($categories as $language_id=>$name) {
					if ($name != '') {
						if ($language_id == '1') {
							$parent_id = $categories_id; //Only change parent_id when All other languages are passed. Language_id 1 is always first.
							if ($level == '1') {
								$parent_id = 0; //first level is parent_id 0
							}
							$categories_name = $name;
							$categories_id = tep_get_categorie_id($name, $language_id, $parent_id);
						}
					}
				}
			}
			if ($categories_id != '') {
				if ($data['status'] == 'ACTIVE') { //product to add
					$check_query = tep_db_query('SELECT products_id FROM products_to_categories WHERE products_id = "'.$products_id.'" AND categories_id = "'.$categories_id.'"');
					if (tep_db_num_rows($check_query) < 1) {
						tep_db_query('INSERT INTO products_to_categories (products_id, categories_id) VALUES("'.$products_id.'", "'.$categories_id.'")');
						echo '<span style="color:#499900;font-weight:bold;">New product: '.$data['products_model'].'('.$products_id.') for categorie: '.$categories_name.'('.$categories_id.')</span><br />';
					} else {
						echo 'Product: '.$data['products_model'].'('.$products_id.') for categorie: '.$categories_name.'('.$categories_id.') already exists<br />';
					}
				} else if ($data['status'] == 'Delete') {
					tep_db_query('DELETE FROM products_to_categories WHERE products_id = "'.$products_id.'" AND categories_id = "'.$categories_id.'"');
					echo '<span style="color:#FF0000;font-weight:bold;">Product deleted: '.$data['products_model'].'</span><br />';
				} else {
					echo 'ongeldige status voor product '.$data['products_model'].': '.$data['status'].'<br />';
				}
			} else {
				echo '<span style="color:#FF0000;font-weight:bold;">Categorie bestaat niet: '.$categories_name.'</span><br />';
			}
		} else {
			echo 'Dit product bestaat niet: '.$data['products_model'].'<br />';
		}
		echo '<hr />';
	}
} else if (preg_match("/^cross\d{4}([\.])txt$/", $filename)) { //verwerking stock.txt
	if ($filename == 'cross0001.txt') {
		mysql_query("TRUNCATE TABLE `cross_selling`");
	}
	$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
	$headertags = array();
	foreach (explode($seperator,$lines[0]) as $title) { //create list of table names
		$headertags[] = trim($title); //delete 'v_' and replace 'attribute_' from every title
	}
	unset($lines[0]); //delete first row == titles
	$cross_fields = tep_get_table_fields('cross_selling');
	foreach ($lines as $key=>$value) { //go through all lines in file
		$data = array();
		$products_data = array();
		foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line
			$data[$headertags[$count]] = trim($waarde);
		}
		foreach ($cross_fields as $field) { //go through all products fields
			if (isset($data[$field]) && !empty($data[$field])) { //if field exists in data, add it
				$products_data[$field] = $data[$field];
			}
		}
		echo 'Cross selling added<br />';
		foreach($products_data as $field=>$value) {
			echo $field.': '.$value.'<br />';
		}
		echo '<hr />';
		tep_db_perform('cross_selling', $products_data);
	}
} else if (preg_match("/^devs\d{4}([\.])txt$/", $filename)) { //verwerking stock.txt
	if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'devices'"))) {
		if ($filename == 'devs0001.txt') {
			mysql_query("TRUNCATE TABLE `devices`");
		}
		$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
		$headertags = array();
		foreach (explode($seperator,$lines[0]) as $title) {
			$headertags[] = trim($title);
		}
		unset($lines[0]);
		$devices_fields = tep_get_table_fields('devices');
		foreach ($lines as $key=>$value) {
			$data = array();
			$products_data = array();
			foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line
				$data[$headertags[$count]] = trim($waarde);
			}
			foreach ($devices_fields as $field) { //go through all products fields
				if (isset($data[$field]) && !empty($data[$field])) { //if field exists in data, add it
					$products_data[$field] = $data[$field];
				}
			}
			echo 'Devices added<br />';
			foreach($products_data as $field=>$value) {
				echo $field.': '.$value.'<br />';
			}
			echo '<hr />';
			tep_db_perform('devices', $products_data, 'insert ignore');
		}
	}
} else if (preg_match("/^item\d{4}([\.])txt$/", $filename)) { //verwerking stock.txt
	if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'devices_to_products'"))) {
		if ($filename == 'item0001.txt') {
			mysql_query("TRUNCATE TABLE `devices_to_products`");
		}
		$lines = file(DIR_FS_CATALOG.$tempDir.$filename);
		$headertags = array();
		foreach (explode($seperator,$lines[0]) as $title) {
			$headertags[] = trim($title);
		}
		unset($lines[0]);
		$devices_to_products_fields = tep_get_table_fields('devices_to_products');
		foreach ($lines as $key=>$value) {
			$data = array();
			$products_data = array();
			foreach (explode($seperator, $value) as $count=>$waarde) { //get through all values of 1 line
				$data[$headertags[$count]] = trim($waarde);
			}
			foreach ($devices_to_products_fields as $field) { //go through all products fields
				if (isset($data[$field]) && !empty($data[$field])) { //if field exists in data, add it
					$products_data[$field] = $data[$field];
				}
			}
			echo 'Devices added<br />';
			foreach($products_data as $field=>$value) {
				echo $field.': '.$value.'<br />';
			}
			echo '<hr />';
			tep_db_perform('devices_to_products', $products_data, 'insert ignore');
		}
	}
} else {
	echo $_SERVER['PHP_SELF'].' geen geldige filename: '.$filename;
}
//FUNCTIONS
function tep_get_products_id($products_model) {
	$pid_query = tep_db_query('SELECT products_id FROM products WHERE products_model = "'.$products_model.'"');
	$pid = tep_db_fetch_array($pid_query);
	return $pid['products_id'];
}
function tep_get_manufacturers_id($manufacturers_name) {
	$mid_query = tep_db_query('SELECT manufacturers_id FROM manufacturers WHERE manufacturers_name = "'.$manufacturers_name.'"');
	$mid = tep_db_fetch_array($mid_query);
	return $mid['manufacturers_id'];
}
function tep_get_table_fields($table) {
	$field_list = array();
	$table_fields_query = tep_db_query('SHOW COLUMNS FROM '.$table);
	while ($table_fields = tep_db_fetch_array($table_fields_query)) {
		$field_list[] = $table_fields['Field'];
	}
	return $field_list;
}
function tep_check_special($products_id) {
	$special_query = tep_db_query('SELECT products_id FROM specials WHERE products_id = "'.$products_id.'"');
	if (tep_db_num_rows($special_query) > 0) {
		return true;
	} else {
		return false;
	}
}
function tep_check_products_description($products_id, $languages_id) {
	$pd_query = tep_db_query('SELECT products_id FROM products_description WHERE products_id = "'.$products_id.'" AND language_id = "'.(int)$languages_id.'"');
	if (tep_db_num_rows($pd_query) > 0) {
		return true;
	} else {
		return false;
	}
}
function tep_get_categorie_id($categories_name, $languages_id, $parent_id) {
	$cid_query = tep_db_query('SELECT c.categories_id FROM categories c, categories_description cd WHERE c.categories_id = cd.categories_id AND cd.categories_name = "'.$categories_name.'" AND cd.language_id = "'.(int)$languages_id.'" AND c.parent_id = "'.(int)$parent_id.'"');
	$cid = tep_db_fetch_array($cid_query);
	return $cid['categories_id'];
}
function delete_product($products_model) {
	$pid_query = tep_db_query('SELECT products_id FROM products WHERE products_model = "'.$products_model.'"');
	while($pid = tep_db_fetch_array($pid_query)) {
		tep_remove_product($pid['products_id'], $products_model);
	}
}
function tep_get_products_attributes_id($products_id, $options_id, $values_id) {
	$paid_query = tep_db_query('SELECT products_attributes_id FROM products_attributes WHERE products_id = "'.$products_id.'" AND options_id = "'.$options_id.'" AND options_values_id = "'.$values_id.'"');
	$paid = tep_db_fetch_array($paid_query);
	return $paid['products_attributes_id'];
}
function tep_check_products_options_id($options_id, $languages_id) {
	$poid_query = tep_db_query('SELECT products_options_id FROM products_options WHERE products_options_id = "'.$options_id.'" AND language_id = "'.$languages_id.'"');
	if (tep_db_num_rows($poid_query) > 0) {
		return true;
	} else {
		return false;
	}
}
function tep_check_products_options_values_id($values_id, $languages_id) {
	$poid_query = tep_db_query('SELECT products_options_values_id FROM products_options_values WHERE products_options_values_id = "'.$values_id.'" AND language_id = "'.$languages_id.'"');
	if (tep_db_num_rows($poid_query) > 0) {
		return true;
	} else {
		return false;
	}
}
function tep_check_products_options_values_to_products_options_id($values_id, $options_id) {
	$povtpoid_query = tep_db_query('SELECT products_options_values_to_products_options_id FROM products_options_values_to_products_options WHERE products_options_id = "'.$options_id.'" AND products_options_values_id = "'.$values_id.'"');
	if (tep_db_num_rows($povtpoid_query) > 0) {
		return true;
	} else {
		return false;
	}
}
function tep_get_tax_title_class_id($tax_class_title) {
	$classes_query = tep_db_query("select tax_class_id from " . TABLE_TAX_CLASS . " WHERE tax_class_title = '" . $tax_class_title . "'" );
	$tax_class_array = tep_db_fetch_array($classes_query);
	$tax_class_id = $tax_class_array['tax_class_id'];
	return $tax_class_id ;
}

function tep_check_categorie_id($categories_id, $language_id) {
	$check_cat_query = tep_db_query('SELECT categories_id FROM categories_description WHERE categories_id = "'.$categories_id.'" AND language_id = "'.$language_id.'"');
	if (tep_db_num_rows($check_cat_query) > 0) {
		return true;
	} else {
		return false;
	}
}
?>