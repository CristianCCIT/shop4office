<?php
/*Push Order*/
function AnalyticsTrackOrder () {
	global $customer_id;
	if (($customer_id!='') && (AnalyticsOrdersId()!='')) {
		$tracking_code .= "_gaq.push(['_addTrans',";
		$tracking_code .= "'".AnalyticsOrdersId()."', ";
		$tracking_code .= "'".STORE_NAME."', ";
		$tracking_code .= "'".AnalyticsTotal()."', ";
		$tracking_code .= "'".AnalyticsTax()."', ";
		$tracking_code .= "'".AnalyticsShipping()."', ";
		$tracking_code .= "'".AnalyticsCity()."', ";
		$tracking_code .= "'".AnalyticsCountry()."'";
		$tracking_code .= "]);";
	}
	return $tracking_code;
}
function AnalyticsOrdersId () {
	global $customer_id;
    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by date_purchased desc limit 1");
    $orders = tep_db_fetch_array($orders_query);
	return $orders['orders_id'];
}
function AnalyticsTotal () {
	global $customer_id;
	$orders_id = AnalyticsOrdersId();
    $total_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$orders_id . "' and class = 'ot_total'");
    $total = tep_db_fetch_array($total_query);
	return $total['value'];
}
function AnalyticsTax () {
	global $customer_id;
	$orders_id = AnalyticsOrdersId();
	$analytics_tax = 0;
    $tax_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$orders_id . "' and class = 'ot_tax'");
	if (tep_db_num_rows($tax_query)>0) {
		while($tax = tep_db_fetch_array($tax_query)) {
			$analytics_tax = $analytics_tax+$tax['value'];
		}
	}
	return $analytics_tax;
}
function AnalyticsShipping () {
	global $customer_id;
	$orders_id = AnalyticsOrdersId();
    $shipping_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$orders_id . "' and class = 'ot_shipping'");
	if (tep_db_num_rows($shipping_query)>0) {
	   $shipping = tep_db_fetch_array($shipping_query);
	   $shipping_cost = $shipping['value'];
	} else {
	   $shipping_cost = 0;
	}
	return $shipping_cost;
}
function AnalyticsCity () {
	global $customer_id;
	$orders_id = AnalyticsOrdersId();
    $city_query = tep_db_query("select customers_city from " . TABLE_ORDERS . " where orders_id = '" . (int)$orders_id . "'");
    $city = tep_db_fetch_array($city_query);
	return $city['customers_city'];
}
function AnalyticsCountry () {
	global $customer_id;
	$orders_id = AnalyticsOrdersId();
    $country_query = tep_db_query("select customers_country from " . TABLE_ORDERS . " where orders_id = '" . (int)$orders_id . "'");
    $country = tep_db_fetch_array($country_query);
	return $country['customers_country'];
}

/*Push Products*/
function AnalyticsTrackProducts () {
	global $customer_id;
	if (($customer_id!='') && (AnalyticsOrdersId()!='')) {
		
		$orders_products_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)AnalyticsOrdersId() . "'");
		while ($orders_products = tep_db_fetch_array($orders_products_query)) {
			$products_query = tep_db_query("select p.products_model, pd.products_name, cd.categories_name, p.products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_id = pd.products_id and p.products_id = p2c.products_id and p2c.categories_id = cd.categories_id and p.products_id = '" . (int)$orders_products['products_id'] . "'");
			$products = tep_db_fetch_array($products_query);
			
			$tracking_code .= "_gaq.push(['_addItem',";
			$tracking_code .= "'".AnalyticsOrdersId()."', ";
			$tracking_code .= "'".$products['products_model']."', ";
			$tracking_code .= "'".$products['products_name']."', ";
			$tracking_code .= "'".$products['categories_name']."', ";
			$tracking_code .= "'".$products['products_price']."', ";
			$tracking_code .= "'".$orders_products['products_quantity']."'";
			$tracking_code .= "]);";

		}
	}
	return $tracking_code;
}
?>