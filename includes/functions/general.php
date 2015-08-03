<?php
/*
  $Id: general.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

////
// Stop from parsing any further PHP code
function tep_exit()
{
	tep_session_close();
	exit();
}

////
// Redirect to another page or site
function tep_redirect($url)
{
	if ((strstr($url, "\n") != false) || (strstr($url, "\r") != false)) {
		tep_redirect(tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));
	}

	if ((ENABLE_SSL == true) && (getenv('HTTPS') == 'on')) { // We are loading an SSL page
		if (substr($url, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) { // NONSSL url
			$url = HTTPS_SERVER . substr($url, strlen(HTTP_SERVER)); // Change it to SSL
		}
	}

	header('Location: ' . $url);

	tep_exit();
}

////
// Parse the data used in the html tags to ensure the tags will not break
function tep_parse_input_field_data($data, $parse)
{
	if (is_array($data)) {
		$param = $data[0];
	} else {
		$param = $data;
	}
	return strtr(trim($param), $parse);
}

function tep_output_string($string, $translate = false, $protected = false)
{
	if ($protected == true) {
		return htmlspecialchars($string);
	} else {
		if ($translate == false) {
			return tep_parse_input_field_data($string, array('"' => '&quot;'));
		} else {
			return tep_parse_input_field_data($string, $translate);
		}
	}
}

function tep_output_string_protected($string)
{
	return tep_output_string($string, false, true);
}

function tep_sanitize_string($string)
{
	$patterns = array('/ +/', '/[<>]/');
	$replace = array(' ', '_');
	return preg_replace($patterns, $replace, trim($string));
}

////
// Return a random row from a database query
function tep_random_select($query)
{
	$random_product = '';
	$random_query = tep_db_query($query);
	$num_rows = tep_db_num_rows($random_query);
	if ($num_rows > 0) {
		$random_row = tep_rand(0, ($num_rows - 1));
		tep_db_data_seek($random_query, $random_row);
		$random_product = tep_db_fetch_array($random_query);
	}

	return $random_product;
}

////
// Return a product's name
// TABLES: products
function tep_get_products_name($product_id, $language = '')
{
	global $languages_id;

	if (empty($language)) {
		$language = $languages_id;
	}

	$product_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_id . "' and language_id = '" . (int)$language . "'");
	$product = tep_db_fetch_array($product_query);
	/*language fallback*/
	if ((LANGUAGE_FALLBACK == 'true') && ($product['products_name'] == '')) {
		$language_fallback_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_id . "' and language_id = '1'");
		$language_fallback = tep_db_fetch_array($language_fallback_query);
		$product['products_name'] = $language_fallback['products_name'];
	}
	/*language fallback*/

	return $product['products_name'];
}

////
// Return a product's special price (returns nothing if there is no offer)
// TABLES: products
function tep_get_products_special_price($product_id)
{
	$product_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "' and status");
	$product = tep_db_fetch_array($product_query);

	return $product['specials_new_products_price'];
}

////
// Return a product's stock
// TABLES: products
function tep_get_products_stock($products_id)
{
	$products_id = tep_get_prid($products_id);
	$stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
	$stock_values = tep_db_fetch_array($stock_query);

	return $stock_values['products_quantity'];
}

////
// Check if the required stock is available
// If insufficent stock is available return an out of stock message
function tep_check_stock($products_id, $products_quantity)
{
	$stock_left = tep_get_products_stock($products_id) - $products_quantity;
	$out_of_stock = '';

	if ($stock_left < 0) {
		$out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
	}

	return $out_of_stock;
}

////
// Break a word in a string if it is longer than a specified length ($len)
function tep_break_string($string, $len, $break_char = '-')
{
	$l = 0;
	$output = '';
	for ($i = 0, $n = strlen($string); $i < $n; $i++) {
		$char = substr($string, $i, 1);
		if ($char != ' ') {
			$l++;
		} else {
			$l = 0;
		}
		if ($l > $len) {
			$l = 1;
			$output .= $break_char;
		}
		$output .= $char;
	}

	return $output;
}

////
// Return all HTTP GET variables, except those passed as a parameter
function tep_get_all_get_params($exclude_array = '')
{

	if (!is_array($exclude_array)) {
		$exclude_array = array();
	}

	$get_url = '';
	if (is_array($_GET) && (sizeof($_GET) > 0)) {
		reset($_GET);
		while (list($key, $value) = each($_GET)) {
			if ((strlen($value) > 0) && ($key != tep_session_name()) && ($key != 'error') && (!in_array($key,
					$exclude_array)) && ($key != 'x') && ($key != 'y')
			) {
				$get_url .= $key . '=' . rawurlencode(stripslashes($value)) . '&';
			}
		}
	}

	return $get_url;
}

////
// Returns an array with countries
// TABLES: countries
function tep_get_countries($countries_id = '', $with_iso_codes = false)
{
	$countries_array = array();
	if (tep_not_null($countries_id)) {
		if ($with_iso_codes == true) {
			$countries = tep_db_query("select countries_name, countries_iso_code_2, countries_iso_code_3 from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$countries_id . "' order by countries_name");
			$countries_values = tep_db_fetch_array($countries);
			$countries_array = array(
				'countries_name' => convert_to_entities($countries_values['countries_name']),
				'countries_iso_code_2' => $countries_values['countries_iso_code_2'],
				'countries_iso_code_3' => $countries_values['countries_iso_code_3']
			);
		} else {
			$countries = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$countries_id . "'");
			$countries_values = tep_db_fetch_array($countries);
			$countries_array = array('countries_name' => convert_to_entities($countries_values['countries_name']));
		}
	} else {
		if (COUNTRIES_SELECT != 'all') {
			$countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where status = 'true' order by countries_name");
		} else {
			$countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " order by countries_name");
		}
		while ($countries_values = tep_db_fetch_array($countries)) {
			$countries_array[] = array(
				'countries_id' => $countries_values['countries_id'],
				'countries_name' => convert_to_entities($countries_values['countries_name'])
			);
		}
	}

	return $countries_array;
}

////
// Alias function to tep_get_countries, which also returns the countries iso codes
function tep_get_countries_with_iso_codes($countries_id)
{
	return tep_get_countries($countries_id, true);
}

////
// Generate a path to categories
function tep_get_path($current_category_id = '')
{
	global $cPath_array;

	if (tep_not_null($current_category_id)) {
		$cp_size = sizeof($cPath_array);
		if ($cp_size == 0) {
			$cPath_new = $current_category_id;
		} else {
			$cPath_new = '';
			$last_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$cPath_array[($cp_size - 1)] . "'");
			$last_category = tep_db_fetch_array($last_category_query);

			$current_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
			$current_category = tep_db_fetch_array($current_category_query);

			if ($last_category['parent_id'] == $current_category['parent_id']) {
				for ($i = 0; $i < ($cp_size - 1); $i++) {
					$cPath_new .= '_' . $cPath_array[$i];
				}
			} else {
				for ($i = 0; $i < $cp_size; $i++) {
					$cPath_new .= '_' . $cPath_array[$i];
				}
			}
			$cPath_new .= '_' . $current_category_id;

			if (substr($cPath_new, 0, 1) == '_') {
				$cPath_new = substr($cPath_new, 1);
			}
		}
	} else {
		$cPath_new = implode('_', $cPath_array);
	}

	return 'cPath=' . $cPath_new;
}

////
// Returns the clients browser
function tep_browser_detect($component)
{
	global $HTTP_USER_AGENT;

	return stristr($HTTP_USER_AGENT, $component);
}

////
// Alias function to tep_get_countries()
function tep_get_country_name($country_id)
{
	$country_array = tep_get_countries($country_id);

	return $country_array['countries_name'];
}

////
// Returns the zone (State/Province) name
// TABLES: zones
function tep_get_zone_name($country_id, $zone_id, $default_zone)
{
	$zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_id = '" . (int)$zone_id . "'");
	if (tep_db_num_rows($zone_query)) {
		$zone = tep_db_fetch_array($zone_query);
		return $zone['zone_name'];
	} else {
		return $default_zone;
	}
}

////
// Returns the zone (State/Province) code
// TABLES: zones
function tep_get_zone_code($country_id, $zone_id, $default_zone)
{
	$zone_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_id = '" . (int)$zone_id . "'");
	if (tep_db_num_rows($zone_query)) {
		$zone = tep_db_fetch_array($zone_query);
		return $zone['zone_code'];
	} else {
		return $default_zone;
	}
}

////
// Wrapper function for round()
function tep_round($number, $precision)
{
	if (strpos($number, '.') && (strlen(substr($number, strpos($number, '.') + 1)) > $precision)) {
		$number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);

		if (substr($number, -1) >= 5) {
			if ($precision > 1) {
				$number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision - 1) . '1');
			} elseif ($precision == 1) {
				$number = substr($number, 0, -1) + 0.1;
			} else {
				$number = substr($number, 0, -1) + 1;
			}
		} else {
			$number = substr($number, 0, -1);
		}
	}

	return $number;
}

////
// Returns the tax rate for a zone / class
// TABLES: tax_rates, zones_to_geo_zones
function tep_get_tax_rate($class_id, $country_id = -1, $zone_id = -1)
{
	global $customer_zone_id, $customer_country_id;

	if (($country_id == -1) && ($zone_id == -1)) {
		if (!tep_session_is_registered('customer_id')) {
			$country_id = STORE_COUNTRY;
			$zone_id = STORE_ZONE;
		} else {
			$country_id = $customer_country_id;
			$zone_id = $customer_zone_id;
		}
	}

	$tax_query = tep_db_query("select sum(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$zone_id . "') and tr.tax_class_id = '" . (int)$class_id . "' group by tr.tax_priority");
	if (tep_db_num_rows($tax_query)) {
		$tax_multiplier = 1.0;
		while ($tax = tep_db_fetch_array($tax_query)) {
			$tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
		}
		return ($tax_multiplier - 1.0) * 100;
	} else {
		return 0;
	}
}

////
// Return the tax description for a zone / class
// TABLES: tax_rates;
function tep_get_tax_description($class_id, $country_id, $zone_id)
{
	$tax_query = tep_db_query("select tax_description from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_GEO_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) left join " . TABLE_GEO_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$zone_id . "') and tr.tax_class_id = '" . (int)$class_id . "' order by tr.tax_priority");
	if (tep_db_num_rows($tax_query)) {
		$tax_description = '';
		while ($tax = tep_db_fetch_array($tax_query)) {
			$tax_description .= $tax['tax_description'] . ' + ';
		}
		$tax_description = substr($tax_description, 0, -3);

		return $tax_description;
	} else {
		return TEXT_UNKNOWN_TAX_RATE;
	}
}

////
// Add tax to a products price
function tep_add_tax($price, $tax)
{
	if ((DISPLAY_PRICE_WITH_TAX == 'true') && ($tax > 0)) {
		return $price + tep_calculate_tax($price, $tax);
	} else {
		return $price;
	}
}

// Calculates Tax rounding the result
function tep_calculate_tax($price, $tax)
{
	return $price * $tax / 100;
}

////
// Return the number of products in a category
// TABLES: products, products_to_categories, categories
function tep_count_products_in_category($category_id, $include_inactive = false)
{
	$products_count = 0;
	if ($include_inactive == true) {
		$products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$category_id . "'");
	} else {
		$products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p.products_status = '1' and p2c.categories_id = '" . (int)$category_id . "'");
	}
	$products = tep_db_fetch_array($products_query);
	$products_count += $products['total'];

	$child_categories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$category_id . "'");
	if (tep_db_num_rows($child_categories_query)) {
		while ($child_categories = tep_db_fetch_array($child_categories_query)) {
			$products_count += tep_count_products_in_category($child_categories['categories_id'], $include_inactive);
		}
	}

	return $products_count;
}

////
// Return true if the category has subcategories
// TABLES: categories
function tep_has_category_subcategories($category_id)
{
	$child_category_query = tep_db_query("select count(*) as count from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$category_id . "'");
	$child_category = tep_db_fetch_array($child_category_query);

	if ($child_category['count'] > 0) {
		return true;
	} else {
		return false;
	}
}

////
// Returns the address_format_id for the given country
// TABLES: countries;
function tep_get_address_format_id($country_id)
{
	$address_format_query = tep_db_query("select address_format_id as format_id from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$country_id . "'");
	if (tep_db_num_rows($address_format_query)) {
		$address_format = tep_db_fetch_array($address_format_query);
		return $address_format['format_id'];
	} else {
		return '1';
	}
}

////
// Return a formatted address
// TABLES: address_format
function tep_address_format($address_format_id, $address, $html, $boln, $eoln)
{
	$address_format_query = tep_db_query("select address_format as format from " . TABLE_ADDRESS_FORMAT . " where address_format_id = '" . (int)$address_format_id . "'");
	$address_format = tep_db_fetch_array($address_format_query);
	$company = tep_output_string_protected($address['company']);
    $tva_intracom = tep_output_string_protected($address['tva_intracom']);

	if (isset($address['firstname']) && tep_not_null($address['firstname'])) {
		$firstname = tep_output_string_protected($address['firstname']);
		$lastname = tep_output_string_protected($address['lastname']);
	} elseif (isset($address['name']) && tep_not_null($address['name'])) {
		$firstname = tep_output_string_protected($address['name']);
		$lastname = '';
	} else {
		$firstname = '';
		$lastname = '';
	}
	if (isset($address['email_address']) && tep_not_null($address['email_address'])) {
		$emailaddress = $address['email_address'];
	} else {
		$email_address = '';
	}
	if (isset($address['telephone']) && tep_not_null($address['telephone'])) {
		$telephone = $address['telephone'];
	} else {
		$telephone = '';
	}
	$street = tep_output_string_protected($address['street_address']);
	$suburb = tep_output_string_protected($address['suburb']);
	$city = tep_output_string_protected($address['city']);
	$state = tep_output_string_protected($address['state']);
	if (isset($address['country_id']) && tep_not_null($address['country_id'])) {
		$country = tep_get_country_name($address['country_id']);

		if (isset($address['zone_id']) && tep_not_null($address['zone_id'])) {
			$state = tep_get_zone_code($address['country_id'], $address['zone_id'], $state);
		}
	} elseif (isset($address['country']) && tep_not_null($address['country'])) {
		$country = tep_output_string_protected($address['country']['title']);
	} else {
		$country = '';
	}
	$postcode = tep_output_string_protected($address['postcode']);
	$zip = $postcode;

	if ($html) {
// HTML Mode
		$HR = '<hr>';
		$hr = '<hr>';
		if (($boln == '') && ($eoln == "\n")) { // Values not specified, use rational defaults
			$CR = '<br>';
			$cr = '<br>';
			$eoln = $cr;
		} else { // Use values supplied
			$CR = $eoln . $boln;
			$cr = $CR;
		}
	} else {
// Text Mode
		$CR = $eoln;
		$cr = $CR;
		$HR = '----------------------------------------';
		$hr = '----------------------------------------';
	}

	$statecomma = '';
	$streets = $street;
	if ($suburb != '') {
		$streets = $street . $cr . $suburb;
	}
	if ($state != '') {
		$statecomma = $state . ', ';
	}

	$fmt = $address_format['format'];
	eval("\$address = \"$fmt\";");

	if ((ACCOUNT_COMPANY == 'true') && (tep_not_null($company))) {
		$address = $company . $cr . $address;
	}

	return $address;
}

////
// Return a formatted address
// TABLES: customers, address_book
  function tep_address_label($customers_id, $address_id = 1, $html = false, $boln = '', $eoln = "\n") {
    if (is_array($address_id) && !empty($address_id)) {
      return tep_address_format($address_id['address_format_id'], $address_id, $html, $boln, $eoln);
    }

    $address_query = tep_db_query("select ab.entry_firstname as firstname, ab.entry_lastname as lastname, ab.entry_company as company, ab.entry_street_address as street_address, ab.entry_suburb as suburb, ab.entry_city as city, ab.entry_postcode as postcode, ab.entry_state as state, ab.entry_zone_id as zone_id, ab.entry_country_id as country_id, c.customers_email_address as email_address, c.customers_telephone as telephone from " . TABLE_ADDRESS_BOOK . " ab, customers c where c.customers_id = ab.customers_id AND c.customers_id = '".(int)$customers_id."' AND ab.address_book_id = '".(int)$address_id."'");
    $address = tep_db_fetch_array($address_query);

    $format_id = tep_get_address_format_id($address['country_id']);
    return tep_address_format($format_id, $address, $html, $boln, $eoln);
  }

  function tep_row_number_format($number) {
    if ( ($number < 10) && (substr($number, 0, 1) != '0') ) $number = '0' . $number;

    return $number;
  }

  function tep_get_categories($categories_array = '', $parent_id = '0', $indent = '') {
    global $languages_id;

    if (!is_array($categories_array)) $categories_array = array();

    $categories_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where parent_id = '" . (int)$parent_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by sort_order, cd.categories_name");
    while ($categories = tep_db_fetch_array($categories_query)) {
      $categories_array[] = array('id' => $categories['categories_id'],
                                  'text' => $indent . $categories['categories_name']);

      if ($categories['categories_id'] != $parent_id) {
        $categories_array = tep_get_categories($categories_array, $categories['categories_id'], $indent . '&nbsp;&nbsp;');
      }
    }

    return $categories_array;
  }

  function tep_get_manufacturers($manufacturers_array = '') {
    if (!is_array($manufacturers_array)) $manufacturers_array = array();

    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
      $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'], 'text' => $manufacturers['manufacturers_name']);
    }

    return $manufacturers_array;
  }

////
// Return all subcategory IDs
// TABLES: categories
  function tep_get_subcategories(&$subcategories_array, $parent_id = 0) {
    $subcategories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$parent_id . "'");
    while ($subcategories = tep_db_fetch_array($subcategories_query)) {
      $subcategories_array[sizeof($subcategories_array)] = $subcategories['categories_id'];
      if ($subcategories['categories_id'] != $parent_id) {
        tep_get_subcategories($subcategories_array, $subcategories['categories_id']);
      }
    }
  }

// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
  function tep_date_long($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') ) return false;

    $year = (int)substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    return strftime(DATE_FORMAT_LONG, mktime($hour,$minute,$second,$month,$day,$year));
  }

////
// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers
  function tep_date_short($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || empty($raw_date) ) return false;

    $year = substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      return preg_replace('/2037$/', $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, 2037)));
    }
  }

////
// Parse search string into indivual objects
  function tep_parse_search_string($search_str = '', &$objects) {
    $search_str = trim(strtolower($search_str));

// Break up $search_str on whitespace; quoted string will be reconstructed later
    $pieces = preg_split('/[[:space:]]+/', $search_str);
    $objects = array();
    $tmpstring = '';
    $flag = '';

    for ($k=0; $k<count($pieces); $k++) {
      while (substr($pieces[$k], 0, 1) == '(') {
        $objects[] = '(';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 1);
        } else {
          $pieces[$k] = '';
        }
      }

      $post_objects = array();

      while (substr($pieces[$k], -1) == ')')  {
        $post_objects[] = ')';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 0, -1);
        } else {
          $pieces[$k] = '';
        }
      }

// Check individual words

      if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
        $objects[] = trim($pieces[$k]);

        for ($j=0; $j<count($post_objects); $j++) {
          $objects[] = $post_objects[$j];
        }
      } else {
/* This means that the $piece is either the beginning or the end of a string.
   So, we'll slurp up the $pieces and stick them together until we get to the
   end of the string or run out of pieces.
*/

// Add this word to the $tmpstring, starting the $tmpstring
        $tmpstring = trim(preg_replace('/"/', ' ', $pieces[$k]));

// Check for one possible exception to the rule. That there is a single quoted word.
        if (substr($pieces[$k], -1 ) == '"') {
// Turn the flag off for future iterations
          $flag = 'off';

          $objects[] = trim($pieces[$k]);

          for ($j=0; $j<count($post_objects); $j++) {
            $objects[] = $post_objects[$j];
          }

          unset($tmpstring);

// Stop looking for the end of the string and move onto the next word.
          continue;
        }

// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
        $flag = 'on';

// Move on to the next word
        $k++;

// Keep reading until the end of the string as long as the $flag is on

        while ( ($flag == 'on') && ($k < count($pieces)) ) {
          while (substr($pieces[$k], -1) == ')') {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
              $pieces[$k] = substr($pieces[$k], 0, -1);
            } else {
              $pieces[$k] = '';
            }
          }

// If the word doesn't end in double quotes, append it to the $tmpstring.
          if (substr($pieces[$k], -1) != '"') {
// Tack this word onto the current string entity
            $tmpstring .= ' ' . $pieces[$k];

// Move on to the next word
            $k++;
            continue;
          } else {
/* If the $piece ends in double quotes, strip the double quotes, tack the
   $piece onto the tail of the string, push the $tmpstring onto the $haves,
   kill the $tmpstring, turn the $flag "off", and return.
*/
            $tmpstring .= ' ' . trim(preg_replace('/"/', ' ', $pieces[$k]));

// Push the $tmpstring onto the array of stuff to search for
            $objects[] = trim($tmpstring);

            for ($j=0; $j<count($post_objects); $j++) {
              $objects[] = $post_objects[$j];
            }

            unset($tmpstring);

// Turn off the flag to exit the loop
            $flag = 'off';
          }
        }
      }
    }

// add default logical operators if needed
    $temp = array();
    for($i=0; $i<(count($objects)-1); $i++) {
      $temp[] = $objects[$i];
      if ( ($objects[$i] != 'and') &&
           ($objects[$i] != 'or') &&
           ($objects[$i] != '(') &&
           ($objects[$i+1] != 'and') &&
           ($objects[$i+1] != 'or') &&
           ($objects[$i+1] != ')') ) {
        $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
      }
    }
    $temp[] = $objects[$i];
    $objects = $temp;

    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;
    for($i=0; $i<count($objects); $i++) {
      if ($objects[$i] == '(') $balance --;
      if ($objects[$i] == ')') $balance ++;
      if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
        $operator_count ++;
      } elseif ( ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
        $keyword_count ++;
      }
    }

    if ( ($operator_count < $keyword_count) && ($balance == 0) ) {
      return true;
    } else {
      return false;
    }
  }

////
// Check date
  function tep_checkdate($date_to_check, $format_string, &$date_array) {
    $separator_idx = -1;

    $separators = array('-', ' ', '/', '.');
    $month_abbr = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
    $no_of_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    $format_string = strtolower($format_string);

    if (strlen($date_to_check) != strlen($format_string)) {
      return false;
    }

    $size = sizeof($separators);
    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($date_to_check, $separators[$i]);
      if ($pos_separator != false) {
        $date_separator_idx = $i;
        break;
      }
    }

    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($format_string, $separators[$i]);
      if ($pos_separator != false) {
        $format_separator_idx = $i;
        break;
      }
    }

    if ($date_separator_idx != $format_separator_idx) {
      return false;
    }

    if ($date_separator_idx != -1) {
      $format_string_array = explode( $separators[$date_separator_idx], $format_string );
      if (sizeof($format_string_array) != 3) {
        return false;
      }

      $date_to_check_array = explode( $separators[$date_separator_idx], $date_to_check );
      if (sizeof($date_to_check_array) != 3) {
        return false;
      }

      $size = sizeof($format_string_array);
      for ($i=0; $i<$size; $i++) {
        if ($format_string_array[$i] == 'mm' || $format_string_array[$i] == 'mmm') $month = $date_to_check_array[$i];
        if ($format_string_array[$i] == 'dd') $day = $date_to_check_array[$i];
        if ( ($format_string_array[$i] == 'yyyy') || ($format_string_array[$i] == 'aaaa') ) $year = $date_to_check_array[$i];
      }
    } else {
      if (strlen($format_string) == 8 || strlen($format_string) == 9) {
        $pos_month = strpos($format_string, 'mmm');
        if ($pos_month != false) {
          $month = substr( $date_to_check, $pos_month, 3 );
          $size = sizeof($month_abbr);
          for ($i=0; $i<$size; $i++) {
            if ($month == $month_abbr[$i]) {
              $month = $i;
              break;
            }
          }
        } else {
          $month = substr($date_to_check, strpos($format_string, 'mm'), 2);
        }
      } else {
        return false;
      }

      $day = substr($date_to_check, strpos($format_string, 'dd'), 2);
      $year = substr($date_to_check, strpos($format_string, 'yyyy'), 4);
    }

    if (strlen($year) != 4) {
      return false;
    }

    if (!settype($year, 'integer') || !settype($month, 'integer') || !settype($day, 'integer')) {
      return false;
    }

    if ($month > 12 || $month < 1) {
      return false;
    }

    if ($day < 1) {
      return false;
    }

    if (tep_is_leap_year($year)) {
      $no_of_days[1] = 29;
    }

    if ($day > $no_of_days[$month - 1]) {
      return false;
    }

    $date_array = array($year, $month, $day);

    return true;
  }

////
// Check if year is a leap year
  function tep_is_leap_year($year) {
    if ($year % 100 == 0) {
      if ($year % 400 == 0) return true;
    } else {
      if (($year % 4) == 0) return true;
    }

    return false;
  }

////
// Return table heading with sorting capabilities
  function tep_create_sort_heading($sortby, $colnum, $heading, $smaller = false) {
    global $_SERVER;

    $sort_prefix = '';
    $sort_suffix = '';

    /*if ($sortby) {*/
      $sort_prefix = '<a href="' . tep_href_link(basename($_SERVER['PHP_SELF']), tep_get_all_get_params(array('page', 'info', 'sort')) . 'page=1&sort=' . $colnum . ($sortby == $colnum . 'a' ? 'd' : 'a')) . '" title="' . tep_output_string(($sortby == $colnum . 'd' || substr($sortby, 0, 1) != $colnum ? Translate('Oplopend') : Translate('Afdalend')).' '.Translate('sorteren').' '. Translate('op').' ' . $heading) . '" class="productListing-heading">' ;
	  $sort_suffix = (substr($sortby, 0, 1) == $colnum ? (substr($sortby, 1, 1) == 'a' ? '<div class="sort_arrow down"></div>' : '<div class="sort_arrow up"></div>') : '<div class="sort_arrow default"></div>') . '</a>';
    /*}*/
	if ($smaller) {
		if (strlen($heading) > 10) {
			$heading = '<span style="font-size: 10px;">'.$heading.'</span>';
		}
	}
	

    return $sort_prefix . $heading . $sort_suffix;
  }

////
// Recursively go through the categories and retreive all parent categories IDs
// TABLES: categories
  function tep_get_parent_categories(&$categories, $categories_id) {
    $parent_categories_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id . "'");
    while ($parent_categories = tep_db_fetch_array($parent_categories_query)) {
      if ($parent_categories['parent_id'] == 0) return true;
      $categories[sizeof($categories)] = $parent_categories['parent_id'];
      if ($parent_categories['parent_id'] != $categories_id) {
        tep_get_parent_categories($categories, $parent_categories['parent_id']);
      }
    }
  }

////
// Construct a category path to the product
// TABLES: products_to_categories
  function tep_get_product_path($products_id) {
    $cPath = '';

    $category_query = tep_db_query("select p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = '" . (int)$products_id . "' and p.products_status = '1' and p.products_id = p2c.products_id limit 1");
    if (tep_db_num_rows($category_query)) {
      $category = tep_db_fetch_array($category_query);

      $categories = array();
      tep_get_parent_categories($categories, $category['categories_id']);

      $categories = array_reverse($categories);

      $cPath = implode('_', $categories);

      if (tep_not_null($cPath)) $cPath .= '_';
      $cPath .= $category['categories_id'];
    }

    return $cPath;
  }

////
// Return a product ID with attributes
  function tep_get_uprid($prid, $params) {
    if (is_numeric($prid)) {
      $uprid = $prid;

      if (is_array($params) && (sizeof($params) > 0)) {
        $attributes_check = true;
        $attributes_ids = '';

        reset($params);
        while (list($option, $value) = each($params)) {
          if (is_numeric($option) && is_numeric($value)) {
            $attributes_ids .= '{' . (int)$option . '}' . (int)$value;
          } else {
            $attributes_check = false;
            break;
          }
        }

        if ($attributes_check == true) {
          $uprid .= $attributes_ids;
        }
      }
    } else {
      $uprid = tep_get_prid($prid);

      if (is_numeric($uprid)) {
        if (strpos($prid, '{') !== false) {
          $attributes_check = true;
          $attributes_ids = '';

// strpos()+1 to remove up to and including the first { which would create an empty array element in explode()
          $attributes = explode('{', substr($prid, strpos($prid, '{')+1));

          for ($i=0, $n=sizeof($attributes); $i<$n; $i++) {
            $pair = explode('}', $attributes[$i]);

            if (is_numeric($pair[0]) && is_numeric($pair[1])) {
              $attributes_ids .= '{' . (int)$pair[0] . '}' . (int)$pair[1];
            } else {
              $attributes_check = false;
              break;
            }
          }

          if ($attributes_check == true) {
            $uprid .= $attributes_ids;
          }
        }
      } else {
        return false;
      }
    }

    return $uprid;
  }

////
// Return a product ID from a product ID with attributes
  function tep_get_prid($uprid) {
    $pieces = explode('{', $uprid);

    if (is_numeric($pieces[0])) {
      return $pieces[0];
    } else {
      return false;
    }
  }

////
// Return a customer greeting
  function tep_customer_greeting() {
    global $customer_id, $customer_first_name;

    if (tep_session_is_registered('customer_first_name') && tep_session_is_registered('customer_id')) {
      $greeting_string = sprintf(Translate('Welkom terug %s'), tep_output_string_protected($customer_first_name));
    } else {
      $greeting_string = Translate('Welkom Gast');
    }
    return $greeting_string;
  }

////
//! Send email (text/html) using MIME
// This is the central mail function. The SMTP Server should be configured
// correct in php.ini
// Parameters:
// $to_name           The name of the recipient, e.g. "Jan Wildeboer"
// $to_email_address  The eMail address of the recipient,
//                    e.g. jan.wildeboer@gmx.de
// $email_subject     The subject of the eMail
// $email_text        The text of the eMail, may contain HTML entities
// $from_email_name   The name of the sender, e.g. Shop Administration
// $from_email_adress The eMail address of the sender,
//                    e.g. info@mytepshop.com

  function tep_mail($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address) {
    if (SEND_EMAILS != 'true') return false;

    // Instantiate a new mail object
    $message = new email(array('X-Mailer: ABO CMS Mailer'));

    // Build the text version
    $text = strip_tags($email_text);
    if (EMAIL_USE_HTML == 'true') {
      $message->add_html($email_text, $text);
    } else {
      $message->add_text($text);
    }

    // Send message
    $message->build_message();
    return $message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);
  }

////
// Check if product has attributes
  function tep_has_product_attributes($products_id) {
    $attributes_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$products_id . "'");
    $attributes = tep_db_fetch_array($attributes_query);

    if ($attributes['count'] > 0) {
      return true;
    } else {
      return false;
    }
  }

////
// Get the number of times a word/character is present in a string
  function tep_word_count($string, $needle) {
    $temp_array = preg_split('/' . $needle . '/', $string);

    return sizeof($temp_array);
  }

  function tep_count_modules($modules = '') {
    $count = 0;

    if (empty($modules)) return $count;

    $modules_array = explode(';', $modules);

    for ($i=0, $n=sizeof($modules_array); $i<$n; $i++) {
      $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));

      if (is_object($GLOBALS[$class])) {
        if ($GLOBALS[$class]->enabled) {
          $count++;
        }
      }
    }

    return $count;
  }

  function tep_count_payment_modules() {
    return tep_count_modules(MODULE_PAYMENT_INSTALLED);
  }

  function tep_count_shipping_modules() {
    return tep_count_modules(MODULE_SHIPPING_INSTALLED);
  }

  function tep_create_random_value($length, $type = 'mixed') {
    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;

    $rand_value = '';
    while (strlen($rand_value) < $length) {
      if ($type == 'digits') {
        $char = tep_rand(0,9);
      } else {
        $char = chr(tep_rand(0,255));
      }
      if ($type == 'mixed') {
        if (preg_match('/^[a-z0-9]$/i', $char)) $rand_value .= $char;
      } elseif ($type == 'chars') {
        if (preg_match('/^[a-z]$/i', $char)) $rand_value .= $char;
      } elseif ($type == 'digits') {
        if (preg_match('/^[0-9]$/', $char)) $rand_value .= $char;
      }
    }

    return $rand_value;
  }

  function tep_array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {
    if (!is_array($exclude)) $exclude = array();

    $get_string = '';
    if (sizeof($array) > 0) {
      while (list($key, $value) = each($array)) {
        if ( (!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y') ) {
          $get_string .= $key . $equals . $value . $separator;
        }
      }
      $remove_chars = strlen($separator);
      $get_string = substr($get_string, 0, -$remove_chars);
    }

    return $get_string;
  }

  function tep_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }

////
// Output the tax percentage with optional padded decimals
  function tep_display_tax_value($value, $padding = TAX_DECIMAL_PLACES) {
    if (strpos($value, '.')) {
      $loop = true;
      while ($loop) {
        if (substr($value, -1) == '0') {
          $value = substr($value, 0, -1);
        } else {
          $loop = false;
          if (substr($value, -1) == '.') {
            $value = substr($value, 0, -1);
          }
        }
      }
    }

    if ($padding > 0) {
      if ($decimal_pos = strpos($value, '.')) {
        $decimals = strlen(substr($value, ($decimal_pos+1)));
        for ($i=$decimals; $i<$padding; $i++) {
          $value .= '0';
        }
      } else {
        $value .= '.';
        for ($i=0; $i<$padding; $i++) {
          $value .= '0';
        }
      }
    }

    return $value;
  }

////
// Checks to see if the currency code exists as a currency
// TABLES: currencies
  function tep_currency_exists($code) {
    $code = tep_db_prepare_input($code);

    $currency_query = tep_db_query("select code from " . TABLE_CURRENCIES . " where code = '" . tep_db_input($code) . "' limit 1");
    if (tep_db_num_rows($currency_query)) {
      $currency = tep_db_fetch_array($currency_query);
      return $currency['code'];
    } else {
      return false;
    }
  }

  function tep_string_to_int($string) {
    return (int)$string;
  }

////
// Parse and secure the cPath parameter values
  function tep_parse_category_path($cPath) {
// make sure the category IDs are integers
    $cPath_array = array_map('tep_string_to_int', explode('_', $cPath));

// make sure no duplicate category IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($cPath_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($cPath_array[$i], $tmp_array)) {
        $tmp_array[] = $cPath_array[$i];
      }
    }

    return $tmp_array;
  }

////
// Return a random value
  function tep_rand($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

  function tep_setcookie($name, $value = '', $expire = 0, $path = '/', $domain = '', $secure = 0) {
    setcookie($name, $value, $expire, $path, (tep_not_null($domain) ? $domain : ''), $secure);
  }

  function tep_get_ip_address() {
    global $HTTP_SERVER_VARS;

    if (isset($HTTP_SERVER_VARS)) {
      if (isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) {
        $ip = $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'];
      } elseif (isset($HTTP_SERVER_VARS['HTTP_CLIENT_IP'])) {
        $ip = $HTTP_SERVER_VARS['HTTP_CLIENT_IP'];
      } else {
        $ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
      }
    } else {
      if (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
      } elseif (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
      } else {
        $ip = getenv('REMOTE_ADDR');
      }
    }

    return $ip;
  }

  function tep_count_customer_orders($id = '', $check_session = true) {
    global $customer_id, $languages_id;

    if (is_numeric($id) == false) {
      if (tep_session_is_registered('customer_id')) {
        $id = $customer_id;
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if ( (tep_session_is_registered('customer_id') == false) || ($id != $customer_id) ) {
        return 0;
      }
    }

    $orders_check_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$id . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "'");
    $orders_check = tep_db_fetch_array($orders_check_query);

    return $orders_check['total'];
  }

  function tep_count_customer_address_book_entries($id = '', $check_session = true) {
    global $customer_id;

    if (is_numeric($id) == false) {
      if (tep_session_is_registered('customer_id')) {
        $id = $customer_id;
      } else {
        return 0;
      }
    }

    if ($check_session == true) {
      if ( (tep_session_is_registered('customer_id') == false) || ($id != $customer_id) ) {
        return 0;
      }
    }

    $addresses_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$id . "'");
    $addresses = tep_db_fetch_array($addresses_query);

    return $addresses['total'];
  }

// nl2br() prior PHP 4.2.0 did not convert linefeeds on all OSs (it only converted \n)
  function tep_convert_linefeeds($from, $to, $string) {
    if ((PHP_VERSION < "4.0.5") && is_array($from)) {
      return preg_replace('/(/' . implode('|', $from) . ')/', $to, $string);
    } else {
      return str_replace($from, $to, $string);
    }
  }

function tep_get_categories_name($cat_id) {
	global $languages_id;
	$cat_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.(int)$cat_id.'" AND language_id = "'.(int)$languages_id.'"');
	$cat = tep_db_fetch_array($cat_query);
	return $cat['categories_name'];
}

function Translate($text) {
	global $languages_id, $_SERVER;
    $translation_query = tep_db_query('select `translation`, `pages`, `count` from `translation` where `text` = "' . $text . '" and language_id = "' . (int)$languages_id . '"');
	$translation = tep_db_fetch_array($translation_query);
	if (tep_db_num_rows($translation_query) > 0) {

		return $translation['translation'];

	} else {
        // The text is not found in the supplied language. This is the fallback clause.

        // Check whether the standard text has been entered at all in the DB.
        $sql = "SELECT code FROM languages WHERE languages_id = ".$languages_id;
        $query = tep_db_query($sql);
        $result = tep_db_fetch_array($query);
        $language = $result['code'];

        $inputtext = tep_db_prepare_input($text);
        $inputtext = tep_db_input($inputtext);

        $sql = "SELECT * FROM translation WHERE `text` = '" . $inputtext . "'";
        $query = tep_db_query($sql);

        if (tep_db_num_rows($query) > 0 ) {
            // It has already been entered into the database. Just not in supplied language.
            translateRequest($text, $language);
        } else {
            // The text has not yet been entered into the database. Place a general translation request.
            translateRequest($text);
        }

		return $text;
	}
}

function TranslateDate($day) {

    $day = ucfirst(strtolower($day));

    switch ($day) {
        case 'Ma':
            return 'Mon';
            break;
        case 'Di':
            return 'Tue';
            break;
        case 'Wo':
            return 'Wed';
            break;
        case 'Do':
            return 'Thu';
            break;
        case 'Vr':
            return 'Fri';
            break;
        case 'Za':
            return 'Sat';
            break;
        case 'Zo':
            return 'Sun';
            break;
    }

    switch ($day) {
        case 'Maandag':
            return 'Monday';
            break;

        case 'Dinsdag':
            return 'Tuesday';
            break;

        case 'Woensdag':
            return 'Wednesday';
            break;

        case 'Donderdag':
            return 'Thursday';
            break;

        case 'Vrijdag':
            // Gotta get down on
            return 'Friday';
            break;

        case 'Zaterdag':
            return 'Saturday';
            break;

        case 'Zondag':
            return 'Sunday';
            break;
    }

    return $day;
}

/**
 * This is a function that places a translationRequest for a specified text
 * and language in the administrator panel. It is fired every time a text is
 * loaded up via Translate() but isn't translated due to a missing translation.
 *
 * Tables:
 * translation_todo
 *
 * @param $text
 *      The text that should be requested for translation
 * @param $lang
 *      The language that is missing
 */
function translateRequest($text, $lang = 'all') {

    $location = debug_backtrace();
    $loc = $location[1]['file'] . "::" . $location[1]['line'];

    // For this to work on WIN-machines, we need to replace the \ backslashes with forward slashes. Linux doesn't seem to care.

    $loc = str_replace('\\', '/', $loc);
    $loc = str_replace(DIR_FS_CATALOG, '', $loc);

    $text = tep_db_input ($text);

    // Check if the request hasn't been made yet:

    $sql = "SELECT * FROM translation_request WHERE request_text = '".$text. "' AND language = '".$lang."'";
    $query = tep_db_query($sql);
    if (tep_db_num_rows($query) == 0) {

        if ($lang == 'all') {
            // Translation has not been initialized.

            $sql = "INSERT INTO translation_request (request_text, language, location) VALUES ('".$text."', 'all', '".$loc."')";
            tep_db_query($sql) or die ('A mysql-error occured while entering the translation request into the database. Offical error description: ' . mysql_error());


        } else {
            // Only for a selected language.

            $sql = "INSERT INTO translation_request (request_text, language, location) VALUES ('".$text."', '".$lang."', '".$loc."')";
            tep_db_query($sql) or die ('A mysql-error occured while entering the translation request into the database. Offical error description: ' . mysql_error());
        }
    } else {

        // Ignore requests that have already been made.

    }
}

function ClassName($text) {
	$text = trim($text);
	$text = str_replace(" ", "", $text);
	$text = str_replace("'", "", $text);
	return $text;
}

function tep_get_full_cpath($path) {
	$parent_categories_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$path . "'");
	while ($parent_categories = tep_db_fetch_array($parent_categories_query))
	{
		if (($parent_categories['parent_id'] != $path) && ($parent_categories['parent_id'] != 0))
		{
			$path = tep_get_full_cpath($parent_categories['parent_id']).'_'.$path;
		}
	}
	return $path;
}

function tep_get_product_stars($product_id, $display_class, $discount = false) {
	global $currencies;
	if (SHOW_PRODUCT_STARS == 'true') {
		if (($display_class=='list') || ($display_class=='grid')) {
			$size = 'small';
		} else {
			$size = 'medium';
		}
		$product_query = tep_db_query('SELECT '.PRODUCT_STARS_DB_FIELD.' FROM products WHERE products_id = "'.(int)$product_id.'"');
		$product = tep_db_fetch_array($product_query);
		$product_stars = $product[PRODUCT_STARS_DB_FIELD];
		$count=0;
		$zindex = 0;
		$output = '';
		if ($discount) {
			$count++;
			$zindex = $zindex+1;
			if (!strstr($discount, '%')) {
				$discount = $currencies->format($discount);
			}
			$output .= '<div class="star star-'.$count.' '.$display_class.' '.$size.' discount" style="z-index:'.$zindex.';">';
			$output .= tep_image(DIR_WS_IMAGES.'sterren/'.$size.'/discount.png', Translate('Korting')).'<span>-'.$discount.'</span>';
			$output .= '</div>';
		}
		if (($display_class=='list' && $output == '') || $display_class != 'list') {
			if ($product_stars!='') {
				foreach (explode("\n", PRODUCT_STARS) as $star_config) {
					$star_param = explode(",", $star_config);
					if (strstr($product_stars, $star_param[0])) {
						$count++;
						$zindex = $zindex+1;
						$output .= '<div class="star star-'.$count.' '.$display_class.' '.$size.' '.strtolower(ClassName($star_param[1])).'" style="z-index:'.$zindex.';">'.tep_image(DIR_WS_IMAGES.'sterren/'.$size.'/'.$star_param[2], $star_param[1]).'</div>';
					}
				}
			}
		}
		return $output;
	} else {
		return '';	
	}
}
function tep_get_product_style($product_id) {
	if (SHOW_PRODUCT_STARS == 'true') {
		$product_query = tep_db_query('SELECT '.PRODUCT_STARS_DB_FIELD.' FROM products WHERE products_id = "'.(int)$product_id.'"');
		$product = tep_db_fetch_array($product_query);
		$product_stars = $product[PRODUCT_STARS_DB_FIELD];
		if ($product_stars!='') {
			foreach (explode("\n", PRODUCT_STARS) as $star_config) {
				$star_param = explode(",", $star_config);
				if ((strstr($product_stars, $star_param[0])) && ($star_param[0]!='')) {
					$output .= $star_param[3];
				}
			}
		} else {
			$output = '';
		}
		return $output;
	} else {
		return '';	
	}
}
function tep_show_manufacturers_table () {
	$output = '<div class="brands">';
	$alphabet = range('A', 'Z');
	foreach ($alphabet as $letter)
	{
		$manufacturers_query = tep_db_query("SELECT manufacturers_name, manufacturers_id FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_name LIKE '".$letter."%' ORDER BY manufacturers_name ASC");
		if (tep_db_num_rows($manufacturers_query) > 0)
		{
			$output .= '<p class="odd"><strong>'.$letter.'</strong></p>';
			$output .= '<p class="even">';
			$this_count = 0;
			while ($manufacturers = tep_db_fetch_array($manufacturers_query))
			{
				$this_count++;
				if ($this_count!=1)
				{
				$output .= ' - ';
				}
				$output .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $manufacturers['manufacturers_id'], 'NONSSL', false) . '">';
				$output .= $manufacturers['manufacturers_name'];
				$output .= "</a>";
			}
			$output .= '</p>';
		}                     
	}
	$output .= '</div>';
	return $output;
}
function CanShop() {
	global $customer_id;
	if (B2B_SITE == "true") {
		if (tep_session_is_registered('customer_id')) {
			return 'true';
		} else {
			return 'false';	
		}
	} else {
		return 'true';	
	}
}
function tep_get_cross_products($products_id, $limit = ''){
	$cross_products = array();
	if ($limit != '') {
		$products_query = tep_db_query('SELECT cross_id FROM products_cross WHERE products_id = "'.$products_id.'" LIMIT '.$limit.'');
	} else {
		$products_query = tep_db_query('SELECT cross_id FROM products_cross WHERE products_id = "'.$products_id.'"');		
	}
	while ($products = tep_db_fetch_array($products_query)) {
		$cross_products[] .= $products['cross_id'];
	}
	return $cross_products;
}
  function tep_get_languages($use_id_as_key = false) {
    $languages_query = tep_db_query("select languages_id, name, code, image, directory from " . TABLE_LANGUAGES . " WHERE status > 0 order by sort_order");
    $i = -1;
    while ($languages = tep_db_fetch_array($languages_query)) {
    	$key = ($use_id_as_key) ? $languages['languages_id'] : ++$i;
      $languages_array[$key] = array('id' => $languages['languages_id'],
                                 'name' => $languages['name'],
                                 'code' => $languages['code'],
                                 'image' => $languages['image'],
                                 'directory' => $languages['directory']);
    }

    return $languages_array;
  }
  
function log_customer_in($email_address = '', $password = '') {
	global $cart;
	$error = false;
	$check_customer_query = tep_db_query("select customers_id, abo_id, customers_firstname, customers_password, customers_email_address, customers_username, customers_default_address_id, status, customers_group from customers where customers_email_address = '".tep_db_input($email_address)."' OR customers_username = '".tep_db_input($email_address)."'");
	if (!tep_db_num_rows($check_customer_query)) {
		$error = true;
	} else {
		$check_customer = tep_db_fetch_array($check_customer_query);
		if (!tep_validate_password($password, $check_customer['customers_password'])) {
			$error = true;
		} else {
			if ($check_customer['status']=='0') {
				$active_error = true;
			} else {
				if (SESSION_RECREATE == 'True') {
					tep_session_recreate();
				}
				$check_country_query = tep_db_query("select entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$check_customer['customers_id'] . "' and address_book_id = '" . (int)$check_customer['customers_default_address_id'] . "'");
				$check_country = tep_db_fetch_array($check_country_query);
				global $customer_id, $abo_id, $customer_default_address_id, $customer_first_name, $customer_country_id, $customer_zone_id, $customer_group, $customers_email_address, $customers_username;
				$customer_id = $check_customer['customers_id'];
				$abo_id = $check_customer['abo_id'];
				$customer_default_address_id = $check_customer['customers_default_address_id'];
				$customer_first_name = $check_customer['customers_firstname'];
				$customer_country_id = $check_country['entry_country_id'];
				$customer_zone_id = $check_country['entry_zone_id'];
				$customer_group = $check_customer['customers_group'];
				$customers_email_address = $check_customer['customers_email_address'];
				$customers_username = $check_customer['customers_username'];
				tep_session_register('customer_id');
				tep_session_register('abo_id');
				tep_session_register('customer_default_address_id');
				tep_session_register('customer_first_name');
				tep_session_register('customer_country_id');
				tep_session_register('customer_zone_id');
				tep_session_register('customer_group');
				tep_session_register('customers_email_address');
				tep_session_register('customers_username');
				/*autologin*/
				$cookie_url_array = parse_url((ENABLE_SSL == true ? HTTPS_SERVER : HTTP_SERVER) . substr(DIR_WS_CATALOG, 0, -1));
				$cookie_path = $cookie_url_array['path'];	
				if ((ALLOW_AUTOLOGON == 'false') || ($_POST['remember_me'] == '')) {
					setcookie("email_address", "", time() - 3600, $cookie_path);   // Delete email_address cookie
					setcookie("password", "", time() - 3600, $cookie_path);	       // Delete password cookie
				} else {
					setcookie('email_address', $email_address, time()+ (365 * 24 * 3600), $cookie_path, '', ((getenv('HTTPS') == 'on') ? 1 : 0));
					setcookie('password', $check_customer['customers_password'], time()+ (365 * 24 * 3600), $cookie_path, '', ((getenv('HTTPS') == 'on') ? 1 : 0));
				}
				/*autologin*/
				tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$customer_id . "'");
				$cart->restore_contents();
				/*FORUM*/
				if ((FORUM_ACTIVE=='true') && (FORUM_CROSS_LOGIN=='true')) {
					$user->session_begin();
					$auth->acl($user->data);
					$get_forum_username_query = tep_db_query("SELECT username_clean FROM ".FORUM_DB_DATABASE.".users WHERE user_email = '".$_POST['email_address']."'");
					$get_forum_username = tep_db_fetch_array($get_forum_username_query);
					if ($_POST['remember_me']=='on') {
						$remember = 'true';
					} else {
						$remember = 'false';	
					}
					$auth->login($get_forum_username['username_clean'], $_POST['password'], $remember, 1, 0);
				}
				/*FORUM*/
			}
		}
	}
	if ($error == true) {
		return Translate('Fout: er kon niet ingelogd worden met het ingegeven e-mailadres en wachtwoord. Gelieve opnieuw te proberen');
	}
	if ($active_error == true) {
		return Translate('Uw account werd nog niet geactiveerd.');
	}
	return true;
}


///Function to get Gift Coupon module status - #1179
//////////////////////////////////////////////////////////////// - #1179 - 30-05-2013
function get_coupon_status(){
	$qrycouponactivequery = tep_db_query("SELECT * FROM extensions WHERE name='Cadeaubon/coupon'");
	$arrcouponactivequery = tep_db_fetch_array($qrycouponactivequery);
	if(strtolower($arrcouponactivequery['value']) == 'on'){
		$qrycouponstatus  = tep_db_query("SELECT value FROM ".trim($arrcouponactivequery['table']) ." WHERE name='coupon'");
		$arrcouponstatus  = tep_db_fetch_array($qrycouponstatus);
		return $arrcouponstatus['value'];
	}else{ 
		return false;
	}
}

?>