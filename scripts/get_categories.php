<?php
/* 
  Script created by ABO Service 
  
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  
  $cat_query_raw = "SELECT c.categories_id, c.parent_id, cd.categories_name FROM " . TABLE_CATEGORIES . " c
  	 	LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON c.categories_id = cd.categories_id 
		WHERE cd.language_id = 1";
  $cat_query = tep_db_query($cat_query_raw); 

//  $num_rows = tep_db_num_rows($product_query);
  while ($cats = tep_db_fetch_array($cat_query)) {
//	  $rows++;
//	    if (strlen($rows) < 2) {
//      $rows = '0' . $rows;
//    }		
	$csv_accum .= $cats['categories_id'] . ";" . strip_tags(html_entity_decode($cats['categories_name'])) . ";" . $cats['parent_id'] . "\n"; 
  }    
  $csv_accum = str_replace('&prime;', "'", $csv_accum);

  $filename = DIR_FS_CATALOG . '/temp/categories.csv';
  $f = fopen($filename, "w");
  fwrite($f, strip_tags(html_entity_decode($csv_accum)));
  fclose($f);
			
  echo "Bestand categories werd aangemaakt: " . $filename;

  require(DIR_WS_INCLUDES . 'application_bottom.php');

?>
