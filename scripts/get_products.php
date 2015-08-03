<?php
/* 
  Script created by ABO Service 
  
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $product_query_raw = "SELECT products_id, products_model FROM " . TABLE_PRODUCTS;
  $product_query = tep_db_query($product_query_raw); 

//  $num_rows = tep_db_num_rows($product_query);
  while ($products = tep_db_fetch_array($product_query)) {
//	  $rows++;
//	    if (strlen($rows) < 2) {
//      $rows = '0' . $rows;
//    }		
	$csv_accum .= $products['products_id'] . ";" . $products['products_model'] . "\n"; 
  }          

  $filename = DIR_FS_CATALOG . '/temp/products.csv';
  $f = fopen($filename, "w");
  fwrite($f, $csv_accum);
  fclose($f);
			
  echo "Productbestand werd aangemaakt: " . $filename;

  require(DIR_WS_INCLUDES . 'application_bottom.php');

?>
