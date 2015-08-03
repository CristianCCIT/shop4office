<?php
ob_start();
require('includes/application_top.php');
$model = $_GET['model'];
$qty = $_GET['qty'];
$product_query = tep_db_query('SELECT p.products_id, p.products_price, p.products_status, p.products_tax_class_id, pd.products_name FROM products p, products_description pd WHERE p.products_id = pd.products_id AND pd.language_id = "'.(int)$languages_id.'" AND p.products_model = "'.$model.'"');
while ($product = tep_db_fetch_array($product_query)) {
	if ($new_price = tep_get_products_special_price($product['products_id'])) {
		$products_price = $new_price;
	} else {
		$products_price = $product['products_price'];
	}
	echo '"<a href="'.tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$product['products_id']).'" title="'.STORE_NAME.' - '.$product['products_name'].'" target="_blank">'.$product['products_name'].'</a>";';
	echo '"'.$currencies->display_price($products_price, tep_get_tax_rate($product['products_tax_class_id']), 1).'";';
	echo '"'.$currencies->display_price($products_price, tep_get_tax_rate($product['products_tax_class_id']), $qty).'";';
	echo '"'.$product['products_status'].'";';
	echo '"'.$product['products_id'].'";';
	/*ATTRIBUTES*/
	$products_attributes_query = tep_db_query("select count(*) as total from ".TABLE_PRODUCTS_OPTIONS." popt, ".TABLE_PRODUCTS_ATTRIBUTES." patrib where patrib.products_id='".(int)$product['products_id']."' and patrib.options_id = popt.products_options_id and popt.language_id = '".(int)$languages_id."'");
	$products_attributes = tep_db_fetch_array($products_attributes_query);
	if ($products_attributes['total'] > 0) {
		$products_options_name_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name from ".TABLE_PRODUCTS_OPTIONS." popt, ".TABLE_PRODUCTS_ATTRIBUTES." patrib where patrib.products_id='".(int)$product['products_id']."' and patrib.options_id = popt.products_options_id and popt.language_id = '".(int)$languages_id."' order by popt.products_options_name");
		while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
			$products_options_array = array();
			$products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from ".TABLE_PRODUCTS_ATTRIBUTES." pa, ".TABLE_PRODUCTS_OPTIONS_VALUES." pov where pa.products_id = '".(int)$product['products_id']."' and pa.options_id = '".(int)$products_options_name['products_options_id']."' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '".(int)$languages_id."'");
			while ($products_options = tep_db_fetch_array($products_options_query)) {
				$products_options_array[] = array('id' => $products_options_name['products_options_id'], 'value_id' => $products_options['products_options_values_id'], 'value' => $products_options['options_values_price'], 'name' => $products_options_name['products_options_name'].': '.$products_options['products_options_values_name']);
				if ($products_options['options_values_price'] != '0') {
					$products_options_array[sizeof($products_options_array)-1]['name'] .= '(' . $products_options['price_prefix'] . $currencies->display_price($products_options['options_values_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) .') ';
				}
			}
			if (count($products_options_array) > 1) {
				echo tep_draw_pull_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute);
			} else {
				echo '"'.$products_options_array[0]['id'].'";"'.$products_options_array[0]['value_id'].'";"'.$products_options_array[0]['name'].'";';
			}
		}
	}
	/*if ($prices['recyclage'] > 0) {
		echo '"0";"'.$prices['recyclage'].'";"'.Translate('Inclusief Recyclage').': ('.$currencies->display_price($prices['recyclage'], 0).')";';
	}*/
}

ob_end_flush();
?>