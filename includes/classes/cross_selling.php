<?php
class cross {
    var $products, $categories, $manufacturers;
	function cross($products_model='', $categories_id='') {
		$this->products = array();
		$this->categories = array();
		$this->manufacturers = array();
		if (!empty($products_model)) {
			$this->cross_products('SELECT products_model_cross, categories_id_cross FROM cross_selling WHERE products_model = "'.$products_model.'"');
		}
		if (!empty($categories_id)) {
			$this->cross_products('SELECT products_model_cross, categories_id_cross FROM cross_selling WHERE categories_id = "'.$categories_id.'"');
		}
	}
	function cross_products($query) {
		$cross_query = tep_db_query($query);
		while ($cross = tep_db_fetch_array($cross_query)) {
			if (!empty($cross['products_model_cross'])) {
				$this->product_info($cross['products_model_cross']);
			}
			if (!empty($cross['categories_id_cross'])) {
				$catprod_query = tep_db_query('SELECT p.products_model FROM products p, products_to_categories p2c WHERE p.products_id = p2c.products_id AND p.products_status = "1" AND p2c.categories_id = "'.$cross['categories_id_cross'].'"');
				while ($catprod = tep_db_fetch_array($catprod_query)) {
					$this->product_info($catprod['products_model']);
				}
			}
		}
	}
	function product_info($products_model) {
		global $languages_id;
		$info_query = tep_db_query('SELECT p.products_id, p.products_model, p.products_image, p.products_price, p.products_opt1, p.products_opt2, p.products_opt3, p.products_opt4, p.products_opt5, pd.products_name, pd.products_description, cd.categories_id, cd.categories_name, m.manufacturers_id, m.manufacturers_name FROM products p, products_description pd, products_to_categories p2c, categories_description cd, manufacturers m WHERE p.products_id = pd.products_id AND p.products_id = p2c.products_id AND p2c.categories_id = cd.categories_id AND p.manufacturers_id = m.manufacturers_id AND p.products_status = "1" AND pd.language_id = "'.$languages_id.'" AND p.products_model = "'.$products_model.'"');
		if (tep_db_num_rows($info_query) > 0) {
			$info = tep_db_fetch_array($info_query);
			$this->products[$info['products_id']] = $info;
			$this->product_special($info['products_id']);
			$this->product_attributes($info['products_id']);
			if (isset($this->categories[$info['categories_id']])) {
				$this->categories[$info['categories_id']]['products'][] = $info['products_id'];
			} else {
				$this->categories[$info['categories_id']] = array('id' => $info['categories_id'],
																'name' =>$info['categories_name'],
																'products' => array($info['products_id']));
			}
			if (isset($this->manufacturers[$info['manufacturers_id']])) {
				$this->manufacturers[$info['manufacturers_id']]['products'][] = $info['products_id'];
			} else {
				$this->manufacturers[$info['manufacturers_id']] = array('id' => $info['manufacturers_id'],
																		'name' => $info['manufacturers_name'],
																		'products' => array($info['products_id']));
			}
		}
	}
	function product_special($products_id) {
		$special_query = tep_db_query('SELECT specials_new_products_price, expires_date FROM specials WHERE products_id = "'.$products_id.'" AND status = "1"');
		if (tep_db_num_rows($special_query) > 0) {
			$special = tep_db_fetch_array($special_query);
			$this->products[$products_id]['specials_new_products_price'] = $special['specials_new_products_price'];
			$this->products[$products_id]['expires_date'] = $special['expires_date'];
		}
	}
	function product_attributes($products_id) {
		global $languages_id;
		$attr_query = tep_db_query('SELECT pa.options_id, pa.options_values_id, pa.options_values_price, pa.price_prefix, po.products_options_name, pov.products_options_values_name FROM products_attributes pa, products_options po, products_options_values pov WHERE pa.products_id = "'.$products_id.'" AND pa.options_id = po.products_options_id AND po.language_id = "'.(int)$languages_id.'" AND pa.options_values_id = pov.products_options_values_id AND pov.language_id = "'.(int)$languages_id.'"');
		if (tep_db_num_rows($attr_query) > 0) {
			while ($attr = tep_db_fetch_array($attr_query)) {
				$this->products[$products_id]['attributes'][$attr['options_id']]['values'][$attr['options_values_id']] = $attr;
				$this->products[$products_id]['attributes'][$attr['options_id']]['options_name'] = $attr['products_options_name'];
				$this->products[$products_id]['attribute_ids'][] = $products_id.'{'.$attr['options_id'].'}'.$attr['options_values_id'];
			}
		}		
	}
}
?>