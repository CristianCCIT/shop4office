<?php
/* 
  snel leegmaken db (producten)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  mysql_query("TRUNCATE TABLE `products`");
  mysql_query("TRUNCATE TABLE `products_description`");
  mysql_query("TRUNCATE TABLE `categories`");
  mysql_query("TRUNCATE TABLE `categories_description`");
  mysql_query("TRUNCATE TABLE `manufacturers`");
  mysql_query("TRUNCATE TABLE `products_attributes`");
  mysql_query("TRUNCATE TABLE `products_options`");
  mysql_query("TRUNCATE TABLE `products_options_values`");
  mysql_query("TRUNCATE TABLE `products_options_values_to_products_options`");
  mysql_query("TRUNCATE TABLE `products_to_categories`");
  mysql_query("TRUNCATE TABLE `specials`");
  mysql_query("TRUNCATE TABLE `reviews`");
  mysql_query("TRUNCATE TABLE `reviews_description`");
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'specifications'"))) {
	  mysql_query("TRUNCATE TABLE `specifications`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'productspecs'"))) {
	  mysql_query("TRUNCATE TABLE `productspecs`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'specsdescription'"))) {
 	 mysql_query("TRUNCATE TABLE `specsdescription`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'products_cross'"))) {
 	 mysql_query("TRUNCATE TABLE `products_cross`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'products_favorites'"))) {
 	 mysql_query("TRUNCATE TABLE `products_favorites`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'products_kenmerken'"))) {
 	 mysql_query("TRUNCATE TABLE `products_kenmerken`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'products_plant'"))) {
 	 mysql_query("TRUNCATE TABLE `products_plant`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'ajax_products'"))) {
 	 mysql_query("TRUNCATE TABLE `ajax_products`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'devices'"))) {
 	 mysql_query("TRUNCATE TABLE `devices`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'devices_to_products'"))) {
 	 mysql_query("TRUNCATE TABLE `devices_to_products`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'products_attributes_download'"))) {
 	 mysql_query("TRUNCATE TABLE `products_attributes_download`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'products_notifications'"))) {
 	 mysql_query("TRUNCATE TABLE `products_notifications`");
  }
  
  if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'seo_urls'"))) {
 	 mysql_query("DELETE FROM `seo_urls` WHERE categories_id <> '0'");
 	 mysql_query("DELETE FROM `seo_urls` WHERE products_id <> '0'");
 	 mysql_query("DELETE FROM `seo_urls` WHERE manufacturers_id <> '0'");
  }
			 
  require(DIR_WS_INCLUDES . 'application_bottom.php');
  
  echo "Databank (producten en aanverwanten) werd leeggemaakt, met uitzondering tabellen categories en categories_description.";
  
$dir = DIR_FS_CATALOG.'var/cache';
if ($handle = opendir($dir)) {
	/* This is the correct way to loop over the directory. */
	while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != "..") {
			chmod($dir.'/'.$file, 0777);
			unlink($dir.'/'.$file);
		}
	}			
	closedir($handle);
}
?>