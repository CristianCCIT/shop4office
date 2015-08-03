<?php
/* 
  Script created by ABO Service 
  
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $manuf_query_raw = "SELECT manufacturers_id, manufacturers_name FROM " . TABLE_MANUFACTURERS;
  $manuf_query = tep_db_query($manuf_query_raw); 

//  $num_rows = tep_db_num_rows($product_query);
  while ($manufs = tep_db_fetch_array($manuf_query)) {
//	  $rows++;
//	    if (strlen($rows) < 2) {
//      $rows = '0' . $rows;
//    }		
	$csv_accum .= $manufs['manufacturers_id'] . ";" . $manufs['manufacturers_name'] . "\n"; 
  }          

  $filename = DIR_FS_CATALOG . '/temp/merken.csv';
  $f = fopen($filename, "w");
  fwrite($f, $csv_accum);
  fclose($f);
			
  echo "Bestand merken werd aangemaakt: " . $filename;

  require(DIR_WS_INCLUDES . 'application_bottom.php');

?>
