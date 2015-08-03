<?php
/*
  $Id: shopping_cart.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class shoppingCart {
    var $contents, $total, $weight, $cartID, $content_type;
	
    function shoppingCart() {
      $this->reset();
    }

    function restore_contents() {
// Start - CREDIT CLASS Gift Voucher Contribution
//    global $customer_id;
      global $customer_id, $gv_id, $_SERVER;
// End - CREDIT CLASS Gift Voucher Contribution

      if (!tep_session_is_registered('customer_id')) return false;

// insert current cart contents in database
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $qty = $this->contents[$products_id]['qty'];
          $product_query = tep_db_query("select products_id from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "' ORDER BY customers_basket_id asc");
          if (!tep_db_num_rows($product_query)) {
            tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET . " (customers_id, products_id, customers_basket_quantity, customers_basket_date_added) values ('" . (int)$customer_id . "', '" . tep_db_input($products_id) . "', '" . tep_db_input($qty) . "', '" . date('Ymd') . "')");
            if (isset($this->contents[$products_id]['attributes'])) {
              reset($this->contents[$products_id]['attributes']);
              while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
                tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id) values ('" . (int)$customer_id . "', '" . tep_db_input($products_id) . "', '" . (int)$option . "', '" . (int)$value . "')");
              }
            }
          } else {
            tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_quantity = '" . tep_db_input($qty) . "' where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
          }
        }
// Start - CREDIT CLASS Gift Voucher Contribution
        if (tep_session_is_registered('gv_id')) {
          $gv_query = tep_db_query("insert into  " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, customer_id, redeem_date, redeem_ip) values ('" . $gv_id . "', '" . (int)$customer_id . "', now(),'" . $_SERVER['REMOTE_ADDR'] . "')");
          $gv_update = tep_db_query("update " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = '" . $gv_id . "'");
          tep_gv_account_update($customer_id, $gv_id);
          tep_session_unregister('gv_id');
        }
// End - CREDIT CLASS Gift Voucher Contribution
      }

// reset per-session cart contents, but not the database contents
      $this->reset(false);

      $products_query = tep_db_query("select products_id, customers_basket_quantity from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' ORDER BY customers_basket_id asc");
      while ($products = tep_db_fetch_array($products_query)) {
        $this->contents[$products['products_id']] = array('qty' => $products['customers_basket_quantity']);
// attributes
        $attributes_query = tep_db_query("select products_options_id, products_options_value_id from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products['products_id']) . "'");
        while ($attributes = tep_db_fetch_array($attributes_query)) {
          $this->contents[$products['products_id']]['attributes'][$attributes['products_options_id']] = $attributes['products_options_value_id'];
        }
      }

      $this->cleanup();
    }

    function reset($reset_database = false) {
      global $customer_id;

      $this->contents = array();
      $this->total = 0;
      $this->weight = 0;
      $this->content_type = false;

      if (tep_session_is_registered('customer_id') && ($reset_database == true)) {
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "'");
      }

      unset($this->cartID);
      if (tep_session_is_registered('cartID')) tep_session_unregister('cartID');
    }

    function add_cart($products_id, $qty = '1', $attributes = '', $notify = true) {
      global $new_products_id_in_cart, $customer_id;

      $products_id_string = tep_get_uprid($products_id, $attributes);
      $products_id = tep_get_prid($products_id_string);

      if (defined('MAX_QTY_IN_CART') && (MAX_QTY_IN_CART > 0) && ((int)$qty > MAX_QTY_IN_CART)) {
        $qty = MAX_QTY_IN_CART;
      }

      $attributes_pass_check = true;

      if (is_array($attributes)) {
        reset($attributes);
        while (list($option, $value) = each($attributes)) {
          if (!is_numeric($option) || !is_numeric($value)) {
            $attributes_pass_check = false;
            break;
          }
        }
      }

      if (is_numeric($products_id) && is_numeric($qty) && ($attributes_pass_check == true)) {
		  if (USE_PRICES_TO_QTY == 'true') {
			  $check_product_query = tep_db_query("select p.products_status from " . TABLE_PRODUCTS . " p JOIN ".TABLE_PRODUCTS_PLANT." pp USING (products_model) where pp.products_plant_id = '" . (int)$products_id . "'");
			  if (tep_db_num_rows($check_product_query) < 1) {
				  $check_product_query = tep_db_query("select products_status from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
			  }
		  } else {
			$check_product_query = tep_db_query("select products_status from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
		  }
        $check_product = tep_db_fetch_array($check_product_query);

        if (($check_product !== false) && ($check_product['products_status'] == '1')) {
          if ($notify == true) {
            $new_products_id_in_cart = $products_id;
            tep_session_register('new_products_id_in_cart');
          }

          if ($this->in_cart($products_id_string)) {
            $this->update_quantity($products_id_string, $qty, $attributes);
          } else {
            $this->contents[$products_id_string] = array('qty' => (int)$qty);
// insert into database
            if (tep_session_is_registered('customer_id')) tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET . " (customers_id, products_id, customers_basket_quantity, customers_basket_date_added) values ('" . (int)$customer_id . "', '" . tep_db_input($products_id_string) . "', '" . (int)$qty . "', '" . date('Ymd') . "')");

            if (is_array($attributes)) {
              reset($attributes);
              while (list($option, $value) = each($attributes)) {
                $this->contents[$products_id_string]['attributes'][$option] = $value;
// insert into database
                if (tep_session_is_registered('customer_id')) tep_db_query("insert into " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " (customers_id, products_id, products_options_id, products_options_value_id) values ('" . (int)$customer_id . "', '" . tep_db_input($products_id_string) . "', '" . (int)$option . "', '" . (int)$value . "')");
              }
            }
          }

          $this->cleanup();

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
          $this->cartID = $this->generate_cart_id();
        }
      }
    }

    function update_quantity($products_id, $quantity = '', $attributes = '') {
      global $customer_id;

      $products_id_string = tep_get_uprid($products_id, $attributes);
      $products_id = tep_get_prid($products_id_string);

      if (defined('MAX_QTY_IN_CART') && (MAX_QTY_IN_CART > 0) && ((int)$quantity > MAX_QTY_IN_CART)) {
        $quantity = MAX_QTY_IN_CART;
      }

      $attributes_pass_check = true;

      if (is_array($attributes)) {
        reset($attributes);
        while (list($option, $value) = each($attributes)) {
          if (!is_numeric($option) || !is_numeric($value)) {
            $attributes_pass_check = false;
            break;
          }
        }
      }

      if (is_numeric($products_id) && isset($this->contents[$products_id_string]) && is_numeric($quantity) && ($attributes_pass_check == true)) {
		   if ($this->contents[$products_id_string]['qty'] != $quantity) {
			   $this->contents[$products_id_string] = array('qty' => (int)$quantity);
		   }
// update database
        if (tep_session_is_registered('customer_id')) tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_quantity = '" . (int)$quantity . "' where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id_string) . "'");

        if (is_array($attributes)) {
          reset($attributes);
          while (list($option, $value) = each($attributes)) {
            $this->contents[$products_id_string]['attributes'][$option] = $value;
// update database
            if (tep_session_is_registered('customer_id')) tep_db_query("update " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " set products_options_value_id = '" . (int)$value . "' where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id_string) . "' and products_options_id = '" . (int)$option . "'");
          }
        }
      }
    }

    function cleanup() {
      global $customer_id;

      reset($this->contents);
      while (list($key,) = each($this->contents)) {
        if ($this->contents[$key]['qty'] < 1) {
          unset($this->contents[$key]);
// remove from database
          if (tep_session_is_registered('customer_id')) {
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($key) . "'");
            tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($key) . "'");
          }
        }
      }
    }

    function count_contents() {  // get total number of items in cart 
      $total_items = 0;
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $total_items += $this->get_quantity($products_id);
        }
      }

      return $total_items;
    }

    function get_quantity($products_id) {
      if (isset($this->contents[$products_id])) {
        return $this->contents[$products_id]['qty'];
      } else {
        return 0;
      }
    }

    function in_cart($products_id) {
      if (isset($this->contents[$products_id])) {
        return true;
      } else {
        return false;
      }
    }

    function remove($products_id) {
      global $customer_id;

      unset($this->contents[$products_id]);
// remove from database
      if (tep_session_is_registered('customer_id')) {
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int)$customer_id . "' and products_id = '" . tep_db_input($products_id) . "'");
      }

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
      $this->cartID = $this->generate_cart_id();
    }

    function remove_all() {
      $this->reset();
    }

    function get_product_id_list() {
      $product_id_list = '';
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $product_id_list .= ', ' . $products_id;
        }
      }

      return substr($product_id_list, 2);
    }

    function calculate() {
      global $currencies, $_SERVER, $customer_id;
	  //  LINE ADDED - MOD: CREDIT CLASS Gift Voucher Contribution
      $this->total_virtual = 0;

      $this->total = 0;
      $this->weight = 0;
      if (!is_array($this->contents)) return 0;

      reset($this->contents);
      while (list($products_id, ) = each($this->contents)) {
        $qty = $this->contents[$products_id]['qty'];

		// products price
		if (USE_PRICES_TO_QTY == 'true') {
			$product_query = tep_db_query("select p.products_id, p.products_price, pp.plant_price, p.products_discount, p.products_tax_class_id, p.products_weight, pp.plant_maat from " . TABLE_PRODUCTS . " p JOIN ".TABLE_PRODUCTS_PLANT." pp USING (products_model) where pp.products_plant_id = '" . (int)$products_id . "'");
			if (tep_db_num_rows($product_query) < 1) {
			 	$product_query = tep_db_query("select products_id, products_price, products_tax_class_id, products_weight from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
			}
		} else {
			$product_query = tep_db_query("select products_id, products_price, products_tax_class_id, products_weight from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
		}
        if ($product = tep_db_fetch_array($product_query)) {
// Start - CREDIT CLASS Gift Voucher Contribution
          $no_count = 1;
          $gv_query = tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
          $gv_result = tep_db_fetch_array($gv_query);
          if (preg_match('/^GIFT/', $gv_result['products_model'])) {
            $no_count = 0;
          }
// End - CREDIT CLASS Gift Voucher Contribution
          $prid = $product['products_id'];
          $products_tax = tep_get_tax_rate($product['products_tax_class_id']);
		  if (USE_PRICES_TO_QTY == 'true') {
				if (SOAP_STATUS == 'true') {
					if ($this->contents[$products_id]['price'] && !strstr($_SERVER['PHP_SELF'], FILENAME_SHOPPING_CART)) {
						$products_price = $this->contents[$products_id]['price'];
					} else {
						$products_price = showPriceTable($prid, $customer_id, $product['plant_maat'], $qty, $table = false);
					}
				} else {
					$products_price = calculatePrice($product['plant_price'], $qty);
				}
		  } else {
			  if ((SOAP_STATUS == 'true') && (SOAP_PRICE == 'true')) {
				  if ($this->contents[$products_id]['price'] && !strstr($_SERVER['PHP_SELF'], FILENAME_SHOPPING_CART)) {
					  $products_price = $this->contents[$products_id]['price'];
				  } else {
					  $products_price = showCustomerPrice($prid, $customer_id, $qty);
					  $this->contents[$products_id]['price'] = $products_price;
				  }
			  } else {
				 //DISCOUNT
				$discount_price = tep_get_discountprice($product['products_price'], $customer_id, $customer_group, $product['products_id'], $cPath, $product['manufacturers_id']);
				if (($discount_price['lowest']['discount'] > 0) || ($discount_price['lowest']['price'] > 0) && PRICE_BOOK == 'true') {
					if ($new_price = tep_get_products_special_price($product['products_id'])) {
						if ($new_price < $discount_price['lowest']['price']) {
							$products_price = $new_price;
						} else {
							$products_price = $discount_price['lowest']['price'];
						}
					} else {
						$products_price = $discount_price['lowest']['price'];
					}
				} else {
					if ($new_price = tep_get_products_special_price($product['products_id'])) {
						$products_price = $new_price;
					} else {
						$products_price = $product['products_price'];
					}
				}
				if (PRICE_BOOK == 'true') {
					foreach ($discount_price['others'] as $prices) {
						if ($prices['min_amount'] <= $this->contents[$products_id]['qty'] && $prices['price'] < $products_price) {
							$products_price = $prices['price'];
						}
					}
				}
				//END DISCOUNT
			  }
		  }
          $products_weight = $product['products_weight'];
		  if (USE_PRICES_TO_QTY == 'true') {
			  if( isset($product['products_discount']) && strlen($product['products_discount'])>2 ) {
				if( $tranche = explode( ',', $product['products_discount'] ) ) {
				foreach( $tranche as $cle => $trn )
				  if( $qty_px = explode( ':', $trn ) ) {
					if( $qty >= $qty_px[0] )
					  $products_price = $qty_px[1];
				  }
				}
			  }
			  $products_price = $currencies->price_discount($prid,$products_price,$qty);
// Start - CREDIT CLASS Gift Voucher Contribution
       		  $this->total_virtual += tep_add_tax($products_price, $products_tax) * ($qty * $no_count);
// End - CREDIT CLASS Gift Voucher Contribution
			  $this->total += tep_add_tax($products_price, $products_tax) * $qty;
		  } else {
// Start - CREDIT CLASS Gift Voucher Contribution
       		  $this->total_virtual += $currencies->calculate_price($products_price, $products_tax, ($qty * $no_count) );
// End - CREDIT CLASS Gift Voucher Contribution
			  $this->total += $currencies->calculate_price($products_price, $products_tax, $qty);
		  }
// Start - CREDIT CLASS Gift Voucher Contribution
          $this->weight_virtual += ($qty * $products_weight) * $no_count;
// End - CREDIT CLASS Gift Voucher Contribution
          $this->weight += ($qty * $products_weight);
        }

// attributes price
        if (isset($this->contents[$products_id]['attributes'])) {
          reset($this->contents[$products_id]['attributes']);
          while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
            $attribute_price_query = tep_db_query("select options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$prid . "' and options_id = '" . (int)$option . "' and options_values_id = '" . (int)$value . "'");
            $attribute_price = tep_db_fetch_array($attribute_price_query);
			if (USE_PRICES_TO_QTY == 'true') {
				if ($attribute_price['price_prefix'] == '+') {
				  $this->total += $qty * tep_add_tax($attribute_price['options_values_price'], $products_tax);
				} else {
				  $this->total -= $qty * tep_add_tax($attribute_price['options_values_price'], $products_tax);
				}				
			} else {
				if ($attribute_price['price_prefix'] == '+') {
				  $this->total += $currencies->calculate_price($attribute_price['options_values_price'], $products_tax, $qty);
				} else {
				  $this->total -= $currencies->calculate_price($attribute_price['options_values_price'], $products_tax, $qty);
				}
			}
          }
        }
      }
    }

    function attributes_price($products_id) {
      $attributes_price = 0;

      if (isset($this->contents[$products_id]['attributes'])) {
        reset($this->contents[$products_id]['attributes']);
        while (list($option, $value) = each($this->contents[$products_id]['attributes'])) {
          $attribute_price_query = tep_db_query("select options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$products_id . "' and options_id = '" . (int)$option . "' and options_values_id = '" . (int)$value . "'");
          $attribute_price = tep_db_fetch_array($attribute_price_query);
          if ($attribute_price['price_prefix'] == '+') {
            $attributes_price += $attribute_price['options_values_price'];
          } else {
            $attributes_price -= $attribute_price['options_values_price'];
          }
        }
      }

      return $attributes_price;
    }

    function get_products() {
      global $languages_id, $_SERVER, $customer_id;

      if (!is_array($this->contents)) return false;

      $products_array = array();
      reset($this->contents);
      while (list($products_id, ) = each($this->contents)) {
		  if (USE_PRICES_TO_QTY == 'true') {
			  $products_query = tep_db_query("select p.products_id, pp.products_plant_id, pp.plant_price, pp.plant_maat, pd.products_name, p.products_model, p.products_image, p.products_price, p.products_discount, p.products_weight, p.products_tax_class_id from " . TABLE_PRODUCTS . " p JOIN ".TABLE_PRODUCTS_PLANT." pp USING (products_model), " . TABLE_PRODUCTS_DESCRIPTION . " pd where pp.products_plant_id = '" . (int)$products_id . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
			  if (tep_db_num_rows($products_query) < 1) {
				  $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_tax_class_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$products_id . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
			  }

		  } else {
			$products_query = tep_db_query("select p.products_id, pd.products_name, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_tax_class_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$products_id . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
		  }
        if ($products = tep_db_fetch_array($products_query)) {
          $prid = $products['products_id'];
		  if (USE_PRICES_TO_QTY == 'true') {
				//Calculate products price
				if (SOAP_STATUS == 'true') {
					if ($this->contents[$products_id]['price']) {
						$products_price = $this->contents[$products_id]['price'];
					} else {
						$products_price = showPriceTable($prid, $customer_id, $products['plant_maat'], $this->contents[$products_id]['qty'], $table = false);
						$this->contents[$products_id]['price'] = $products_price;
					}
				} else {
					$products_price = calculatePrice($products['plant_price'], $this->contents[$products_id]['qty']);
					if( isset($products['products_discount']) && strlen($products['products_discount'])>2 ) {
						if( $tranche = explode( ',', $products['products_discount'] ) ) {
							foreach( $tranche as $cle => $trn )
							if( $qty_px = explode( ':', $trn ) ) {
								if( $this->contents[$products_id]['qty'] >= $qty_px[0] )
									$products_price = $qty_px[1];
							}
						}
					}
				}
		  } else {
			  if ((SOAP_STATUS == 'true') && (SOAP_PRICE == 'true')) {
				  if ($this->contents[$products_id]['price']) {
					  $products_price = $this->contents[$products_id]['price'];
				  } else {
					  $products_price = showCustomerPrice($prid, $customer_id, $this->contents[$products_id]['qty']);
					  $this->contents[$products_id]['price'] = $products_price;
				  }
			  } else {
				  //DISCOUNT
					$discount_price = tep_get_discountprice($products['products_price'], $customer_id, $customer_group, $products['products_id'], $cPath, $products['manufacturers_id']);
					if (($discount_price['lowest']['discount'] > 0) || ($discount_price['lowest']['price'] > 0) && PRICE_BOOK == 'true') {
						if ($new_price = tep_get_products_special_price($products['products_id'])) {
							if ($new_price < $discount_price['lowest']['price']) {
								$products_price = $new_price;
							} else {
								$products_price = $discount_price['lowest']['price'];
							}
						} else {
							$products_price = $discount_price['lowest']['price'];
						}
					} else {
						if ($new_price = tep_get_products_special_price($products['products_id'])) {
							$products_price = $new_price;
						} else {
							$products_price = $products['products_price'];
						}
					}
					if (PRICE_BOOK == 'true') {
							foreach ($discount_price['others'] as $prices) {
								if ($prices['min_amount'] <= $this->contents[$products_id]['qty'] && $prices['price'] < $products_price) {
									$products_price = $prices['price'];
								}
						}
					}
					//END DISCOUNT
			  }
		  }
		  if (USE_PRICES_TO_QTY == 'true') {
				  //Lets get all sized
				$products_sizes_query = tep_db_query("SELECT plant_description, plant_mc FROM ".TABLE_PRODUCTS_PLANT." WHERE products_plant_id = '".$products['products_plant_id']."'");
				$products_size = tep_db_fetch_array($products_sizes_query);
				$qty_array = getPricesToQty($products_size['plant_price']);
				if ($products['plant_maat'] != '')
				{
					$maat = Translate('Maat').': '.$products['plant_maat'];
					if ($products_size['plant_mc'] == 'hoogte')
					{
						$eenheid = ' cm.';
					}
					elseif ($products_size['plant_mc'] == 'stamomtrek')
					{
						$eenheid = ' stamomtrek';
					}
					elseif ($products_size['plant_mc'] == 'diameter')
					{
						$eenheid = ' diameter';
					}
					else
					{
						$eenheid = '';
					}
				}
				else
				{
					$maat = '';
					$eenheid = '';
				}
				if ($products_size['plant_description'] != '')
				{
					$descr = $products_size['plant_description'];
				}
				else
				{
					$descr = '';
				}
				$products_array[] = array('id' => $prid,
									'size_id' => $products['products_plant_id'],
									'original_name' => $products['products_name'],
                                    'name' => $products['products_name'],
                                    'description' =>'<span style="font-weight: normal;">'.$descr.'</span>',
                                    'maat' =>'<span style="font-weight: normal;">'.$maat.$eenheid.'</span>',
									'size' => $products['plant_maat'],
                                    'model' => $products['products_model'],
                                    'image' => $products['products_image'],
                                    'price' => $products_price,
                                    'quantity' => $this->contents[$products_id]['qty'],
                                    'weight' => $products['products_weight'],
                                    'final_price' => ($products_price + $this->attributes_price($products_id)),
                                    'tax_class_id' => $products['products_tax_class_id'],
                                    'attributes' => (isset($this->contents[$products_id]['attributes']) ? $this->contents[$products_id]['attributes'] : ''));
		  } else {

			  $products_array[] = array('id' => $products_id,
										'name' => $products['products_name'],
										'model' => $products['products_model'],
										'image' => $products['products_image'],
										'price' => $products_price,
										'quantity' => $this->contents[$products_id]['qty'],
										'weight' => $products['products_weight'],
										'final_price' => ($products_price + $this->attributes_price($products_id)),
										'tax_class_id' => $products['products_tax_class_id'],
										'attributes' => (isset($this->contents[$products_id]['attributes']) ? $this->contents[$products_id]['attributes'] : ''));
		  }
        }
      }

      return $products_array;
    }

    function show_total() {
      $this->calculate();

      return $this->total;
    }

    function show_weight() {
      $this->calculate();

      return $this->weight;
    }
// Start - CREDIT CLASS Gift Voucher Contribution
    function show_total_virtual() {
      $this->calculate();

      return $this->total_virtual;
    }

    function show_weight_virtual() {
      $this->calculate();

      return $this->weight_virtual;
    }
// End - CREDIT CLASS Gift Voucher Contribution

    function generate_cart_id($length = 5) {
      return tep_create_random_value($length, 'digits');
    }

    function get_content_type() {
      $this->content_type = false;

      if ( (DOWNLOAD_ENABLED == 'true') && ($this->count_contents() > 0) ) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          if (isset($this->contents[$products_id]['attributes'])) {
            reset($this->contents[$products_id]['attributes']);
            while (list(, $value) = each($this->contents[$products_id]['attributes'])) {
              $virtual_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad where pa.products_id = '" . (int)$products_id . "' and pa.options_values_id = '" . (int)$value . "' and pa.products_attributes_id = pad.products_attributes_id");
              $virtual_check = tep_db_fetch_array($virtual_check_query);

              if ($virtual_check['total'] > 0) {
                switch ($this->content_type) {
                  case 'physical':
                    $this->content_type = 'mixed';

                    return $this->content_type;
                    break;
                  default:
                    $this->content_type = 'virtual';
                    break;
                }
              } else {
                switch ($this->content_type) {
                  case 'virtual':
                    $this->content_type = 'mixed';

                    return $this->content_type;
                    break;
                  default:
                    $this->content_type = 'physical';
                    break;
                }
              }
            }
// Start - CREDIT CLASS Gift Voucher Contribution
          } elseif ($this->show_weight() == 0) {
            reset($this->contents);
            while (list($products_id, ) = each($this->contents)) {
              $virtual_check_query = tep_db_query("select products_weight from " . TABLE_PRODUCTS . " where products_id = '" . $products_id . "'");
              $virtual_check = tep_db_fetch_array($virtual_check_query);
              if ($virtual_check['products_weight'] == 0) {
                switch ($this->content_type) {
                  case 'physical':
                    $this->content_type = 'mixed';

                    return $this->content_type;
                    break;
                  default:
                    $this->content_type = 'virtual';
                    break;
                }
              } else {
                switch ($this->content_type) {
                  case 'virtual':
                    $this->content_type = 'mixed';

                    return $this->content_type;
                    break;
                  default:
                    $this->content_type = 'physical';
                    break;
                }
              }
            }
// End - CREDIT CLASS Gift Voucher Contribution
          } else {
            switch ($this->content_type) {
              case 'virtual':
                $this->content_type = 'mixed';

                return $this->content_type;
                break;
              default:
                $this->content_type = 'physical';
                break;
            }
          }
        }
      } else {
        $this->content_type = 'physical';
      }

      return $this->content_type;
    }

    function unserialize($broken) {
      for(reset($broken);$kv=each($broken);) {
        $key=$kv['key'];
        if (gettype($this->$key)!="user function")
        $this->$key=$kv['value'];
      }
    }
// Start - CREDIT CLASS Gift Voucher Contribution
// amend count_contents to show nil contents for shipping
// as we don't want to quote for 'virtual' item
// GLOBAL CONSTANTS if NO_COUNT_ZERO_WEIGHT is true then we don't count any product with a weight
// which is less than or equal to MINIMUM_WEIGHT
// otherwise we just don't count gift certificates
    function count_contents_virtual() {  // get total number of items in cart disregard gift vouchers
      $total_items = 0;
      if (is_array($this->contents)) {
        reset($this->contents);
        while (list($products_id, ) = each($this->contents)) {
          $no_count = false;
          $gv_query = tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . $products_id . "'");
          $gv_result = tep_db_fetch_array($gv_query);
          if (preg_match('/^GIFT/', $gv_result['products_model'])) {
            $no_count=true;
          }
          if (NO_COUNT_ZERO_WEIGHT == 1) {
            $gv_query = tep_db_query("select products_weight from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($products_id) . "'");
            $gv_result=tep_db_fetch_array($gv_query);
            if ($gv_result['products_weight']<=MINIMUM_WEIGHT) {
              $no_count=true;
            }
          }
          if (!$no_count) $total_items += $this->get_quantity($products_id);
        }
      }
      return $total_items;
    }
// End - CREDIT CLASS Gift Voucher Contribution
  }
?>
