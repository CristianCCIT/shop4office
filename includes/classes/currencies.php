<?php
/*
  $Id: currencies.php 1803 2008-01-11 18:16:37Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

////
// Class to handle currencies
// TABLES: currencies
  class currencies {
    var $currencies;

// class constructor
    function currencies() {
      $this->currencies = array();
      $currencies_query = tep_db_query("select code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from " . TABLE_CURRENCIES);
      while ($currencies = tep_db_fetch_array($currencies_query)) {
        $this->currencies[$currencies['code']] = array('title' => $currencies['title'],
                                                       'symbol_left' => $currencies['symbol_left'],
                                                       'symbol_right' => $currencies['symbol_right'],
                                                       'decimal_point' => $currencies['decimal_point'],
                                                       'thousands_point' => $currencies['thousands_point'],
                                                       'decimal_places' => $currencies['decimal_places'],
                                                       'value' => $currencies['value']);
      }
    }

// class methods
    function format($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {
      global $currency;

      if (empty($currency_type)) $currency_type = $currency;

      if ($calculate_currency_value == true) {
        $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format(tep_round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      } else {
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format(tep_round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      }

      return $format_string;
    }

    function calculate_price($products_price, $products_tax, $quantity = 1) {
      global $currency;

      return tep_round(tep_add_tax($products_price, $products_tax), $this->currencies[$currency]['decimal_places']) * $quantity;
    }

    function is_set($code) {
      if (isset($this->currencies[$code]) && tep_not_null($this->currencies[$code])) {
        return true;
      } else {
        return false;
      }
    }

    function get_value($code) {
      return $this->currencies[$code]['value'];
    }

    function get_decimal_places($code) {
      return $this->currencies[$code]['decimal_places'];
    }
    // KORTINGSPERCENTAGE BEREKENEN
	function calculate_discount_value($discount, $products_price, $quantity,$discount_query)
	{
		$id = (int)$id;
		$products_id = (int)$products_id;
		// KORTING IS LEEG
		$new_price = '';
			// Find Out the Quantity Record
			$flag = 0;
			$discount_value = '';
			$fix_price = 0;
			
			while ( $discount )
			{
				//ALS DE BESTELDE HOEVEELHEID GROTER OF GELIJK IS AAN MINIMUM HOEVEELHEID OM KORTING TE GEBRUIKEN (DISCOUNT_NUMBER)
				if ( ( $quantity >= $discount['discount_number'] ) )
				{
					if ( flag == 1 )
					{
						// a greater quantity values exists
						break;
					}
					//$discount_value is gelijk aan het kortingspercentage uit database
					$discount_value = $discount['customers_discount'];
					//$fix_price is gelijk aan de ingevuld productprijs in database
					$fix_price = $discount['products_price'];
				}
				// ALS MINIMUM HOEVEELHEID OM KORTING TE KRIJGEN INGEVULD IS
				if ( $discount['discount_number'] == 0 )
				{
					//$discount_value is gelijk aan het kortingspercentage uit database
					$discount_value = $discount['customers_discount'];
					//$fix_price is gelijk aan de ingevuld productprijs in database
					$fix_price = $discount['products_price'];
				}
				$discount = tep_db_fetch_array($discount_query);
			}
			// ALS DE PRODUCTS_PRICE IN DATABASE IS INGEVULD
			if ( $fix_price != "" && $fix_price != 0 )
			{
				//GEEF DIE PRIJS WEER
				return $fix_price;
			}
			//PERCENTAGETEKEN WEGHALEN
			$percentindex = strpos($discount_value,'%');
			//ALS PERCENTAGE NIET IS INGEVULD
			if ( $percentindex === false )
			{	
				// KORTING = NIETS
				$new_price = $discount_value;
			}
			else
			{
				// KORTING = ORIGINELE PRODUCTPRIJS * PERCENTAGE
				$new_price = ($products_price * ($discount_value/100));
			}
			// GEEF WEER: ORIGINELE PRIJS - KORTING
			return ($products_price - $new_price);
	}
	
	// KORTINGSPRIJS BEREKENEN
    function price_discount($products_id, $products_price, $quantity = '1')
	{
		$id = (int)$id;
		$products_id = (int)$products_id;
    	global $customer_id;
		// KORTINGSPRIJS IS LEEG
		$new_price = '';
		// ALS ER GEEN KLANT-ID IS INGEVULD
		if ( $customer_id == '' || $customer_id == 0 )
		{
			//GEEF WEER : ORIGINELE PRIJS - KORTINGSPRIJS
			return ($products_price - $new_price);
		}
		// CATEGORY-ID OPHALEN
		$categories_query = tep_db_query("select categories_id from ".TABLE_PRODUCTS_TO_CATEGORIES." where products_id='".$products_id."'");
    	$category = tep_db_fetch_array($categories_query);
		$category_id = $category['categories_id'];
		//PARENT CATEGORY
		$sub_categories_query = tep_db_query("select parent_id from ".TABLE_CATEGORIES." where categories_id='".$category_id."'");
    	$sub_category = tep_db_fetch_array($sub_categories_query);
		$parent_category_id = $sub_category['parent_id'];
		//PARENT_PARENT CATEGORY
		$sub_sub_categories_query = tep_db_query("select parent_id from ".TABLE_CATEGORIES." where categories_id='".$parent_category_id."'");
    	$sub_sub_category = tep_db_fetch_array($sub_sub_categories_query);
		$parent_parent_category_id = $sub_sub_category['parent_id'];
		
		if ($parent_category_id == 0) {
			$catid = "categories_id = '" . $category_id . "'";
		} else {
			if ($parent_parent_category_id != 0) {
				$catid = "categories_id = '" . $category_id . "' OR  categories_id = '" . $parent_category_id . "' OR  categories_id = '" . $parent_parent_category_id . "'";
			} else {
				$catid = "categories_id = '" . $category_id . "' OR  categories_id = '" . $parent_category_id . "'";
			}
		}
		
		/*if (isset($parent_category_id))
		{
			$catid = $category_id;
			if ($parent_parent_category_id != 0)
			{
			$catid = $parent_category_id;
			}
		}
		else
		{
		$catid = $category_id;
		}*/
		
		
		// MANUFACTURERS-ID OPHALEN
    	$manufacturers_query = tep_db_query("SELECT manufacturers_id FROM ".TABLE_PRODUCTS." WHERE products_id = '".$products_id."'");
    	$manufacturer = tep_db_fetch_array($manufacturers_query);
		
		$manid = $manufacturer['manufacturers_id'];
		if ($manid == '')
		{
		$manid = 0;
		}
    	
		$caract = strpos($products_id,"{");
		echo $caract;

		// HAAL ALLES OP VOOR DEZE KLANT_ID EN PRODUCT_ID
		$discount_query = tep_db_query("SELECT * FROM customers_discount where products_id = '".$products_id."' AND customers_id = '".$customer_id."' ORDER BY discount_number asc");
		$discount = tep_db_fetch_array($discount_query);

		if ( $discount )
		{
			return $this->calculate_discount_value($discount, $products_price, $quantity, $discount_query);
		}

		// HAAL ALLES OP VOOR DEZE KLANT EN CATEGORIE_ID
		$discount_query = tep_db_query("SELECT * FROM customers_discount where (" . $catid . ") AND customers_id = '".$customer_id."' AND manufacturers_id = ".$manid." ORDER BY discount_number asc");
		$discount = tep_db_fetch_array($discount_query);
		
		//KORTING IS LEEG
		$new_price = '';
		
		if ( $discount )
		{
			// BEREKEN KORTING
			$new_price = $this->calculate_discount_value($discount, $products_price, $quantity, $discount_query);
			
			// ALS KORTING GELIJK IS AAN PRODUCTPRIJS
			if ( $new_price === $products_price )
			{
				//DOE NIETS
			}
			else
			{
				// GEEF KORTING WEER
				return $new_price;
			}
		}

		$discount_query = tep_db_query("SELECT * FROM customers_discount where (" . $catid . ") AND customers_id = '".$customer_id."' AND manufacturers_id=0 ORDER BY discount_number asc");

		$discount = tep_db_fetch_array($discount_query);
		//echo "1.1<br>";
		$new_price = '';
		if ( $discount )
		{
			// Case 2
			//return $this->calculate_discount_value($discount, $products_price, $quantity,$discount_query);
			$new_price = $this->calculate_discount_value($discount, $products_price, $quantity,$discount_query);
			//echo "Products Price: " . $products_price . ", New Price: " . $new_price . "<br>";
			if ( $new_price == $products_price ){}
			else
			{
				return $new_price;
			}

		}



		$discount_query = tep_db_query("SELECT * FROM customers_discount where manufacturers_id = ".$manid . " AND customers_id = '".$customer_id."' AND categories_id = 0 ORDER BY discount_number asc");

		$discount = tep_db_fetch_array($discount_query);
		//echo $discount['categories_id'] . "-abc<br>";
		//echo "2<br>";
		$new_price = '';
		if ( $discount )
		{
			// Case 3
			// Get Manufacturer Discount	
			//return $this->calculate_discount_value($discount, $products_price, $quantity,$discount_query);
			$new_price = $this->calculate_discount_value($discount, $products_price, $quantity,$discount_query);
			if ( $new_price == $products_price ){}
			else
			{
				return $new_price;
			}
		}

		$discount_query = tep_db_query("SELECT customers_group FROM ".TABLE_CUSTOMERS." where customers_id = '" . $customer_id . "'");
		$discount = tep_db_fetch_array($discount_query);
		//echo "3<br>";
		$new_price = '';
		$customer_group = '';
		if ( $discount )
		{
			// Case 4
			if ( $discount['customers_group'] == '' )
			{
				// There is no discount
				return $products_price;
			}
			else
			{
				$customer_group = $discount['customers_group'];
				$discount_query1 = tep_db_query("SELECT * FROM customers_discount where customers_group = '".$customer_group . "' AND products_id = '". $products_id . "' ORDER BY discount_number asc");
				$discount1 = tep_db_fetch_array($discount_query1);
				if ( $discount1 )
				{
					// Calculate group discount
					return $this->calculate_discount_value($discount1, $products_price, $quantity,$discount_query1);				
				}
			}
		}
		$discount_query = tep_db_query("SELECT * FROM customers_discount where customers_group = '".$customer_group . "' AND (".$catid.") AND manufacturers_id = '" . $manid . "' ORDER BY discount_number asc");
		//echo "-abc<br>";		
		$discount = tep_db_fetch_array($discount_query);
		if ( $discount )
		{
			return $this->calculate_discount_value($discount, $products_price, $quantity,$discount_query);
		}

		$discount_query = tep_db_query("SELECT * FROM customers_discount where customers_group = '".$customer_group . "' AND (".$catid.") AND manufacturers_id = 0 ORDER BY discount_number asc");
		$discount = tep_db_fetch_array($discount_query);
		//echo "4<br>";		
		if ( $discount )
		{
			return $this->calculate_discount_value($discount, $products_price, $quantity,$discount_query);
		}
		$discount_query = tep_db_query("SELECT * FROM customers_discount where customers_group = '".$customer_group . "' AND manufacturers_id=".$manid." AND categories_id = 0 ORDER BY discount_number asc");
		$discount = tep_db_fetch_array($discount_query);
		//echo "5<br>";		
		if ( $discount )
		{
			if ( $discount['categories_id'] == '' || $discount['categories_id'] == 0 )
			{
				// Get Group and Manufacturer discount
				return $this->calculate_discount_value($discount, $products_price, $quantity,$discount_query);
			}
		}

		return $products_price;
    }
    function display_price($products_price, $products_tax, $quantity = 1) {
      return $this->format($this->calculate_price($products_price, $products_tax, $quantity));
    }
  }
?>
