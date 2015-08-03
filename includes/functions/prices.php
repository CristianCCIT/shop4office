<?php
function tep_get_discountprice($products_price, $customers_id, $customers_group, $products_id, $categories_id, $manufacturers_id) {
	$prices = array();
	$categories = explode('_', $categories_id);
	if ((!empty($customers_id) && $customers_id != 0) || (!empty($customers_group))) { //check discount for customer and customer_group
		$where = '';
		if (!empty($customers_id) && $customers_id != 0) { //add customers id
			$where .= 'customers_id = "'.$customers_id.'" OR';
		}
		if (!empty($customers_group)) { //add customers group
			$where .= ' customers_group = "'.$customers_group.'"';
		} else {
			$where = substr($where, 0, -2); //remove 'OR'
		}
		$custdiscount_query = tep_db_query('SELECT * FROM customers_discount WHERE ('.$where.')');
		while ($custdiscount = tep_db_fetch_array($custdiscount_query)) { //loop through all discounts for this customer(group)
			$min_amount = 1;
			$discount = 0;
			if (!empty($custdiscount['discount_number'])) { //if there is a minimum purchase amount for this product
				$min_amount = $custdiscount['discount_number'];
			}
			if (!empty($custdiscount['products_id']) && $custdiscount['products_id'] == $products_id) { //if there is a discount for this customer(group) and this product
				if (empty($custdiscount['products_price'])) { //there is a discount value or percent
					if (strstr($custdiscount['customers_discount'], '%')) { //discount is percent
						$discount_percent = substr($custdiscount['customers_discount'], 0, -1);
						$discount = ($products_price/100) * $discount_percent;
					} else { //discount is value
						$discount = $custdiscount['customers_discount'];
					}
				} else { //fixed price
					$discount = $products_price - $custdiscount['products_price'];
				}
			} else if ((!empty($custdiscount['categories_id']) && in_array($custdiscount['categories_id'], $categories)) || (!empty($custdiscount['manufacturers_id']) && $custdiscount['manufacturers_id'] == $manufacturers_id)) {
				//if there is a discount for this customer(group) and this category or manufacturer
				if (strstr($custdiscount['customers_discount'], '%')) { //discount is percent
					$discount_percent = substr($custdiscount['customers_discount'], 0, -1);
					$discount = ($products_price/100) * $discount_percent;
				} else { //discount is value
					$discount = $custdiscount['customers_discount'];
				}
			} else if ($custdiscount['products_id'] == 0 && $custdiscount['categories_id'] == 0 && $custdiscount['manufacturers_id'] == 0) {
				if (strstr($custdiscount['customers_discount'], '%')) { //discount is percent
					$discount_percent = substr($custdiscount['customers_discount'], 0, -1);
					$discount = ($products_price/100) * $discount_percent;
				} else { //discount is value
					$discount = $custdiscount['customers_discount'];
				}
			}
			$price = $products_price - $discount;
			$prices[] = array('price' => $price, 'min_amount' => $min_amount, 'discount' => $custdiscount['customers_discount']);
		}
	}
	if (!empty($products_id) && $products_id != 0) {
		$pdiscount_query = tep_db_query('SELECT discount_number, customers_discount, products_price FROM customers_discount WHERE products_id = "'.$products_id.'" AND (customers_id = "0" AND customers_group = "")');
		while ($pdiscount = tep_db_fetch_array($pdiscount_query)) {
			$min_amount = 1;
			$discount = 0;
			if (!empty($pdiscount['discount_number'])) { //if there is a minimum purchase amount for this product
				$min_amount = $pdiscount['discount_number'];
			}
			if (empty($pdiscount['products_price'])) { //there is a discount value or percent
				if (strstr($pdiscount['customers_discount'], '%')) { //discount is percent
					$discount_percent = substr($pdiscount['customers_discount'], 0, -1);
					$discount = ($products_price/100) * $discount_percent;
				} else { //discount is value
					$discount = $pdiscount['customers_discount'];
				}
			} else { //fixed price
				$discount = $products_price - $pdiscount['products_price'];
			}
			$price = $products_price - $discount;
			$prices[] = array('price' => $price, 'min_amount' => $min_amount, 'discount' => $pdiscount['customers_discount']);
		}
	}
	if (!empty($categories) && $categories[0] != 0) {
		foreach ($categories as $categorie) {
			$cdiscount_query = tep_db_query('SELECT discount_number, customers_discount, products_price FROM customers_discount WHERE categories_id = "'.$categorie.'" AND (customers_id = "0" AND customers_group = "")');
			while ($cdiscount = tep_db_fetch_array($cdiscount_query)) {
				$min_amount = 1;
				$discount = 0;
				if (!empty($cdiscount['discount_number'])) { //if there is a minimum purchase amount for this product
					$min_amount = $cdiscount['discount_number'];
				}
				if (strstr($cdiscount['customers_discount'], '%')) { //discount is percent
					$discount_percent = substr($cdiscount['customers_discount'], 0, -1);
					$discount = ($products_price/100) * $discount_percent;
				} else { //discount is value
					$discount = $cdiscount['customers_discount'];
				}
				$price = $products_price - $discount;
				$prices[] = array('price' => $price, 'min_amount' => $min_amount, 'discount' => $cdiscount['customers_discount']);
			}
		}
	}
	if (!empty($manufacturers_id) && $manufacturers_id != 0) {
		$mdiscount_query = tep_db_query('SELECT discount_number, customers_discount, products_price FROM customers_discount WHERE manufacturers_id = "'.$manufacturers_id.'" AND (customers_id = "0" AND customers_group = "")');
		while ($mdiscount = tep_db_fetch_array($mdiscount_query)) {
			$min_amount = 1;
			$discount = 0;
			if (!empty($mdiscount['discount_number'])) { //if there is a minimum purchase amount for this product
				$min_amount = $mdiscount['discount_number'];
			}
			if (strstr($mdiscount['customers_discount'], '%')) { //discount is percent
				$discount_percent = substr($mdiscount['customers_discount'], 0, -1);
				$discount = ($products_price/100) * $discount_percent;
			} else { //discount is value
				$discount = $mdiscount['customers_discount'];
			}
			$price = $products_price - $discount;
			$prices[] = array('price' => $price, 'min_amount' => $min_amount, 'discount' => $mdiscount['customers_discount']);
		}
	}
	$lowest_price = array('price' => $products_price, 'min_amount' => 1, 'discount' => 0);
	$other_prices = array();
	foreach ($prices as $discounts) {
		if (($discounts['price'] < $lowest_price['price']) && $discounts['min_amount'] == 1) {
			$lowest_price = $discounts;
		} else if ($discounts['min_amount'] > 1) {
			$other_prices[] = $discounts;
		}
	}
	for ($i=0;$i<count($other_prices);$i++) {
		if ($other_prices[$i]['price'] > $lowest_price['price']) {
			unset($other_prices[$i]);
		}
	}
	return array('lowest' => $lowest_price, 'others' => $other_prices);
}
?>