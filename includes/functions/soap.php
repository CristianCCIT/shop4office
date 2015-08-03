<?php
function ShowAttributesTable ($product_id) {
	global $languages_id;
	$products_attributes_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$product_id . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "'");
	$products_attributes = tep_db_fetch_array($products_attributes_query);
	if ($products_attributes['total'] > 0) {
		$output = '<table border="0" cellspacing="1" cellpadding="7" width="180">';
		$products_options_name_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$product_id . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' order by popt.products_options_name");
		$count=0;
		while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
			$count++;
			$products_options_array = array();
			$products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$product_id . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "'");
			while ($products_options = tep_db_fetch_array($products_options_query)) {
				$stock = GetStockMaat($product_id, $products_options['products_options_values_name'], SOAP_STOCK_TYPE);
				if ($stock > 0) {
					$products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name'].' ('.GetStockMaat($product_id, $products_options['products_options_values_name'], SOAP_STOCK_TYPE).' op voorraad)');
				} else {
					$products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name'].' (niet op voorraad)');
				}
				if ($products_options['options_values_price'] != '0') {
					$products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . $products_options['price_prefix'] . $currencies->display_price($products_options['options_values_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) .') ';
				}
			}
			if (isset($cart->contents[$product_id]['attributes'][$products_options_name['products_options_id']])) {
				$selected_attribute = $cart->contents[$product_id]['attributes'][$products_options_name['products_options_id']];
			} else {
				$selected_attribute = false;
			}
			if (!empty($products_options_array)) {
				$output .= '<tr>';
				$output .= '<td class="main">'.$products_options_name['products_options_name'] . ':</td>';
				$output .= '<td class="main">'.tep_draw_pull_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute).'</td>';
				$output .= '</tr>';
			}
		}
		$output .= '</table>';
	}
	return $output;
}
function showPriceTable($product_id, $customer_id, $maat = '*ALL*', $aantal = 1, $table = true){
	global $currencies;
	$aboId_query = tep_db_query('SELECT abo_id FROM customers WHERE customers_id = "'.$customer_id.'"');
	$get_model_query = tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
	if ((tep_db_num_rows($aboId_query) > 0) && (tep_db_num_rows($get_model_query) > 0) && (SOAP_SERVER != '')) {
		$abo_id = tep_db_fetch_array($aboId_query);
		$get_model = tep_db_fetch_array($get_model_query);
		$Prices = SoapPriceRequest($get_model['products_model'], $abo_id['abo_id'], $maat, $aantal);
		if (is_array($Prices)) {
			$thisCustomersPriceClass = 'Prijs'.$Prices['Klant']['prijsCategorie'];
			if ($table == 'true') {
				$products_sizes_query = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_PLANT." WHERE products_model = '".tep_db_input($get_model['products_model'])."' ORDER BY plant_sort ASC");
				$products_titles_query = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_PLANT." WHERE products_model = '".tep_db_input($get_model['products_model'])."' ORDER BY plant_price ASC");
				$products_titles = tep_db_fetch_array($products_titles_query);
				$titles_array = getPricesToQty($products_titles['plant_price']);
				
				$result = '<table border="0" cellpadding="0" cellspacing="0" class="plantsizes-table" width="100%">';
				//Heading
				if (tep_db_num_rows($products_sizes_query) > 0) {
					$result .= '<tr class="heading"><td class="first">'.Translate('Aantal').'</td><td>'.Translate('Maat').'</td>';		
				} else {
					$result .= '<tr class="heading"><td class="first">'.Translate('Aantal').'</td>';	
				}
				foreach ($Prices['Data']['Basis'] as $class=>$data) {
					if ($class == $thisCustomersPriceClass) {
						$result .= '<td class="active">'.Translate('Uw prijs').'</td>';
					} else {
						$result .= '<td>'.$class.'</td>';
					}
				}
				$result .= '</tr>';
				//eof Heading
				$count = 0;
				if (tep_db_num_rows($products_sizes_query) > 0) {
					while ($products_size = tep_db_fetch_array($products_sizes_query)) {
						$count++;
						if ($count > 0)
						$result .= '<tr class="data">';
						$result .= '<td class="first">';
						$result .= tep_draw_hidden_field('products_id[]', $products_size['products_plant_id']).tep_draw_input_field('cart_quantity[]', '', 'size="3" class="'.$product_info['products_id'].'"');
						$result .= '</td>';
						$result .= '<td>';
						$result .= $products_size['plant_maat'];
						$result .= '</td>';
						$data = $Prices['Data']['Basis'];
						foreach ($Prices['Data'] as $Reeks=>$reeksData) { //Data uit de juiste reeks ophalen
							if (strstr(';'.$Reeks.';', ';'.$products_size['plant_maat'].';')) {
								$data = $reeksData;
							}
						}
						foreach ($data as $class=>$price) {
							if ($class == $thisCustomersPriceClass) {
								$result .= '<td class="active">';
							} else {
								$result .= '<td class="disabled">';
							}
							$result .= $currencies->format($price);
							$result .= '</td>';
						}
						$result .= '</tr>';
					}
				} else {
					$result .= '<tr class="data">';
					$result .= '<td class="first">';
					$result .= tep_draw_hidden_field('products_id[]', $product_id).tep_draw_input_field('cart_quantity[]', '', 'size="3"');
					$result .= '</td>';
					$data = $Prices['Data']['Basis'];
					foreach ($data as $class=>$price) {
						if ($class == $thisCustomersPriceClass) {
							$result .= '<td class="active">';
						} else {
							$result .= '<td class="disabled">';
						}
						$result .= $currencies->format($price);
						$result .= '</td>';
					}
					$result .= '</tr>';
				}
				
				$result .= '<tr class="data">';
				$result .= '<td colspan="6">';
				$result .= '<input type="submit" class="button-a" value="'.Translate('Voeg toe aan winkelwagen').'" />';
				$result .= '</td>';
				$result .= '</tr>';
				$result .= '</table>';
			} else {
				if ($Prices['Data']['Netto']) {
					$result = $Prices['Data']['Netto'];
				} else {
					$result = $Prices['Data']['Basis'][$thisCustomersPriceClass];
				}
			}
		} else {
			$result = Translate('Er zijn geen prijzen voor u gevonden. Neem a.u.b. contact met ons op.');
		}
	} else {
		$result = Translate('Er zijn geen prijzen voor u gevonden. Neem a.u.b. contact met ons op.');
	}
	return $result;
}
function showCustomerPrice($product_id, $customer_id, $aantal){
	global $currencies;
	$aboId_query = tep_db_query('SELECT abo_id FROM customers WHERE customers_id = "'.$customer_id.'"');
	$get_model_query = tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
	if ((tep_db_num_rows($aboId_query) > 0) && (tep_db_num_rows($get_model_query) > 0) && (SOAP_SERVER != '')) {
		$abo_id = tep_db_fetch_array($aboId_query);
		$get_model = tep_db_fetch_array($get_model_query);
		$Prices = SoapCustomerPriceRequest($get_model['products_model'], $abo_id['abo_id'], $aantal);
	}
	return $Prices;
}
?>