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
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_SITEMAP_SEO);
  
  $pPath = tep_get_product_path($_GET['products_id']); 
  if (strpos($pPath, "_") !== FALSE) 
  {
	$parts = explode("_", $pPath); 
	$cID = $parts[count($parts) - 1]; 
  }
  else 
    $cID = $pPath;

  $category_query = tep_db_query("select c.categories_id, cd.categories_name from " . TABLE_CATEGORIES . " c inner join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id where c.categories_id = '" . (int)$cID . "' and cd.language_id = '" . (int)$languages_id . "' LIMIT 1");
  $category = tep_db_fetch_array($category_query); 

  $products_query = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p inner join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p.products_id != '" . $_GET['products_id'] . "' and p2c.categories_id = '" . (int)$cID . "' and pd.language_id = '" . (int)$languages_id . "'");

  if (tep_db_num_rows($products_query))
  {  
     $catLink = '<a class="sitemap_indvidual_hdg" href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['categories_id']) . '">' . $category['categories_name'] . '</a>';
                
     echo '<tr><td><table border="0" cellpadding="0">';
     echo '<tr><td class="sitemap_indvidual_hdg" colspan="2">'.sprintf(TEXT_CATEGORY_NAME, $catLink) . '</td></tr>';
     while ($prods = tep_db_fetch_array($products_query))
     {
       echo '<tr><td width="10">&nbsp;</td><td class="sitemap_indvidual"><a class="sitemap_indvidual" href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $prods['products_id']) . '">' . $prods['products_name'] . '</a></td></tr>';
     }
     echo '</table></td></tr>';
  }

                 

?>
