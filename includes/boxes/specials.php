<?php
if ($random_product = tep_random_select("select p.products_id, pd.products_name, p.products_price, p.products_tax_class_id, p.products_image, s.specials_new_products_price, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_status = '1' and p.products_id = s.products_id and pd.products_id = s.products_id and pd.language_id = '" . (int)$languages_id . "' and s.status = '1' order by s.specials_date_added desc limit " . MAX_RANDOM_SELECT_SPECIALS)) {
	echo '<div class="box-title"><a href="'.tep_href_link(FILENAME_SPECIALS).'">'.Translate('Speciale aanbiedingen').'</a></div>';
	echo '<div class="box-content">';
	echo '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product["products_id"]) . '">' . tep_image(DIR_WS_IMAGES . $random_product['products_image'], $random_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product['products_id']) . '">' . $random_product['products_name'] . '</a><br />';
	if (USE_PRICES_TO_QTY == 'false' && PRICE_BOOK == 'true') { //added here so this can be used add the whole page
		$discount_price = tep_get_discountprice($random_product['products_price'], $customer_id, $customer_group, $random_product['products_id'], $cPath, $random_product['manufacturers_id']);
	}
	if ($discount_price['lowest']['discount'] > 0 && PRICE_BOOK == 'true') {
		if ($new_price = tep_get_products_special_price($random_product['products_id'])) {
			if ($new_price < $discount_price['lowest']['price']) {
				$products_price = '<span class="oldprice">';
				$products_price .= $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id']));
				$products_price .= '</span>&nbsp;';
				$products_price .= '<span class="specialprice">';
				$products_price .= $currencies->display_price($new_price, tep_get_tax_rate($random_product['products_tax_class_id']));
				$products_price .= '</span>';
			} else {
				$products_price = '<span class="oldprice">';
				$products_price .= $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id']));
				$products_price .= '</span>&nbsp;';
				$products_price .= '<span class="specialprice">';
				$products_price .= $currencies->display_price($discount_price['lowest']['price'], tep_get_tax_rate($random_product['products_tax_class_id']));
				$products_price .= '</span>';
			}
		} else {
			$products_price = '<span class="oldprice">';
			$products_price .= $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id']));
			$products_price .= '</span>&nbsp;';
			$products_price .= '<span class="specialprice">';
			$products_price .= $currencies->display_price($discount_price['lowest']['price'], tep_get_tax_rate($random_product['products_tax_class_id']));
			$products_price .= '</span>';
		}
	} else {
		if ($new_price = tep_get_products_special_price($random_product['products_id'])) {
			$products_price = '<span class="oldprice">' . $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id'])) . '</span> <span class="specialprice">' . $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';
		} else {
			$products_price = '<span class="yourprice">' . $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id'])).'</span>';
		}
	}
	if (CanShop() == 'true') {
		echo $products_price;
	}
	echo '</div>';
}
?>