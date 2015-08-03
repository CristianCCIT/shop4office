<?php
/*
  $Id: sitemap_seo.php,v 1.0 2008/12/29
  written by Jack_mcs at www.osocmmerce-solution.com

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osocmmerce-solution.com
  Portions Copyright 2009 oscommerce-solution.com

  Released under the GNU General Public License
*/
 require(DIR_WS_FUNCTIONS . FILENAME_SITEMAP_SEO);
 
 $registeredOnly = tep_session_is_registered('customer_id') ? 1 : 0;
 
 /********************* Find the Manufacturers to add ***********************/
 $manufacturersArray = array();
 $manufacturersProductsArray = array();

$manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
while ($manufacturers = tep_db_fetch_array($manufacturers_query))
{
   $manufacturersProductsArray = array();
   
   if (SITEMAP_SEO_DISPLAY_PRODUCTS_MANUFACTURERS == 'true')
   {
	 $products_query = tep_db_query("select p.products_id, pd.products_name, p2c.categories_id from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id left join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c on p.products_id = p2c.products_id left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id = p2c.products_id where p.products_status = '1' and m.manufacturers_id = '" . (int)$manufacturers['manufacturers_id'] . "'  and pd.language_id = '" . (int)$languages_id . "'");

	 while ($products = tep_db_fetch_array($products_query))
	 {
	   $name = $products['products_name'];
	   $manufacturersProductsArray[] = array('link' => $products['products_id'],
											 'text' => $name,
											 'anchor_text' => $name);
	 }                                        
   }
   
   $name = ucwords($manufacturers['manufacturers_name']);
   $manufacturersArray[] = array('link' => $manufacturers['manufacturers_id'],
								 'text' => $name,
								 'anchor_text' => $name,
								 'productArray' => $manufacturersProductsArray);
}

 /********************* Find the standard pages to add ***********************/
 $pagesArray = array();
 $pages_query = tep_db_query('SELECT `name`, `description` FROM seo WHERE language_id = "'.(int)$languages_id.'" AND value = "0" AND name!="infopage.php"');
 while ($page = tep_db_fetch_array($pages_query))
 {   
   if (tep_not_null($page['description']))
     $name = $page['description'];
   else
     $name = ucwords(str_replace("_", " ", substr($page['name'], 0, strpos($page['name'], "."))));
   
   if (IsViewable(DIR_FS_CATALOG . $page['name'])) {
		$pagesArray[] = array('link' => $page['name'],
        					'text' => $name,
        					'anchor_text' => $name);
   }
 }
?>