<?php
/*
  $Id: sitemap_seo.php 1739 2008-12-20 by Jack_mcs

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce
  Portions Copyright 2009 oscommerce-solution.com

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require(DIR_WS_MODULES . FILENAME_SITEMAP_SEO);
$keys_query = tep_db_query('SELECT name, value FROM seo WHERE type="sitemap"');
while ($keys = tep_db_fetch_array($keys_query)) {
	define('SITEMAP_'.strtoupper(str_replace('&euml;', 'e',$keys['name'])), $keys['value']);
}
  
  /****************** DISPLAY CATEGORIES *********************/
  $showCategories = '';
if (SITEMAP_CATEGORIEEN == '1') {
  $showCategories .= '<tr><td><h2>'.Translate('Categorie&euml;n').'</h2></td></tr><tr><td height="10"></td></tr><tr><td class="sitemap">';
  $class = 'category_tree_no_products.php';
  $class = 'category_tree.php';
  require DIR_WS_CLASSES . $class;
  $osC_CategoryTree = new osC_CategoryTree(); 
  $showCategories .= $osC_CategoryTree->buildTree();                
  $showCategories .= '</td></tr>';
}
  /****************** DISPLAY INFOPAGES LINKS *********************/
$showInfoPages = '';
if (SITEMAP_INFOPAGINA == '1') {
$sitemap_list = '';
$countfirst = '';
function tep_count_sitemap_children($nav_id) {
	$navigatie_count = 0;
	$navigatie_query = tep_db_query("select id from navigatie where parent_id = '" . (int)$nav_id . "'");
	while ($navigatie = tep_db_fetch_array($navigatie_query)) {
		$navigatie_count++;
	}
	return $navigatie_count;
}

function getSitemapParentList($parent_id) {
	global $sitemap_list, $countfirst;
	$nav_item_query = tep_db_query('SELECT id, link, custom, sort_order, status, name FROM navigatie WHERE parent_id = "'.$parent_id.'" AND status = "1" ORDER BY sort_order asc');
	$level_count = tep_db_num_rows($nav_item_query);
	$i=1;
	if ($parent_id == '0') {
		$countfirst = $level_count;
	}
	while ($nav_item = tep_db_fetch_array($nav_item_query)) {
		$add = '';
		if ($i == '1' && $parent_id == '0') {
			$add = 'id="home"';
		}
		if ($i == $level_count) {
			$add = 'class="last"';
		}
		$sitemap_list .= '<li '.$add.'>';
		if (!empty($nav_item['custom'])) {
			if (!empty($nav_item['name'])) {
				$name = str_replace('/', ' / ', $nav_item['name']);
			} else {
				$name = str_replace('/', ' / ', $nav_item['custom']);
			}
			$sitemap_list .= '<a href="'.tep_href_link($nav_item['custom']).'" title="'.$name.' - '.STORE_NAME.'" class="'.$level_class_count.'">'.$name.'</a>';
		} else {
			$name = str_replace('/', ' / ', $nav_item['name']);
			if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $nav_item['link'])) {
				$page_id = explode('_', $nav_item['link']);
				$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$page_id[1].'" AND language_id = "1"');
				$infopage_name = tep_db_fetch_array($infopage_name_query);
				$name = str_replace('/', ' / ', $infopage_name['infopages_title']);
				$sitemap_list .= '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="'.$level_class_count.'">'.$name.'</a>';
			} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $nav_item['link'])) {
				$page_id = explode('_', $nav_item['link']);
				$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "1"');
				$categorie_name = tep_db_fetch_array($categorie_name_query);
				$name = str_replace('/', ' / ', $categorie_name['categories_name']);
				$sitemap_list .= '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="'.$level_class_count.'">'.$name.'</a>';
			} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $nav_item['link'])) {
				$page_id = explode('_', $nav_item['link']);
				$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "1"');
				$product_name = tep_db_fetch_array($product_name_query);
				$name = str_replace('/', ' / ', $categorie_name['products_name']);
				$sitemap_list .= '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="'.$level_class_count.'">'.$name.'</a>';
			} else {
				if (!empty($nav_item['name'])) {
					$name = $nav_item['name'];
				}
				$sitemap_list .= '<a href="'.tep_href_link($nav_item['link']).'" title="'.$name.' - '.STORE_NAME.'" class="'.$level_class_count.'">'.$name.'</a>';
			}
		}
		if (tep_count_sitemap_children($nav_item['id']) > 0) {
			$sitemap_list .= '<ul>';
			getSitemapParentList($nav_item['id']);
			$sitemap_list .= '</ul>';
		}
		$sitemap_list .= '</li>';
		$i++;
	}
	return $sitemap_list;
}
$sitemap_list = getSitemapParentList('0');
$countcol = $countfirst - 1;
if ($countcol > 6) {
	$countcol = 6;
}
	$showInfoPages .= '<tr><td class="sitemap"><ul class="primaryNav col'.$countcol.'">';
	$showInfoPages .= $sitemap_list;
	$showInfoPages .= '</ul>';
  /****************** DISPLAY STANDARD PAGES *********************/         
  $showInfoPages .= '<ul id="utilityNav">';
  if (count($pagesArray) > 0)
  {                         
    $pageCount = count($pagesArray); 
    if ($pageCount > 0)  
      for ($b = 0; $b < $pageCount; ++$b)  
        $showInfoPages .= '<li><a title="'. $pagesArray[$b]['anchor_text'] .'" href="' . tep_href_link($pagesArray[$b]['link']) . '">' . $pagesArray[$b]['text'] . '</a></li>';
  }


$showInfoPages .= '</ul></td></tr>';
}
  /****************** DISPLAY MANUFACTURERS *********************/
  $showManufacturers = '';
if (SITEMAP_FABRIKANTEN == '1') {
  if (count($manufacturersArray) > 0) 
  {  
    $showManufacturers .= '<tr><td><h2>'.Translate('Fabrikanten').'</h2></td></tr><tr><td height="10"></td></tr><tr><td class="sitemap"><ul id="manufacturers_sitemap">';
    $pageCount = count($manufacturersArray);
    for ($i = 0; $i < $pageCount; ++$i) {
  		$showManufacturers .= '<li><a class="sitemap" href="' . tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $manufacturersArray[$i]['link']) .'" title="' . $manufacturersArray[$i]['anchor_text'] . '">' . $manufacturersArray[$i]['text'] . '</a></li>';
      $cnt = count($manufacturersArray[$i]['productArray']);

      if ($cnt > 0)
      {
        $showManufacturers .= '<ul>';
        for ($p = 0; $p < $cnt; ++$p)
        {
          $pA = $manufacturersArray[$i]['productArray'][$p]; //makes it more readable
     		 $showManufacturers .= '<li><a class="sitemapProducts" href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $pA['link']) .'" title="' . $pA['anchor_text'] . '">' . $pA['text'] . '</a></li>';
        }
        $showManufacturers .= '</ul>';
      } 
    }  
    $showManufacturers .= '</ul></td></tr>';
  }     
}
  /****************** BUILT THE DISPLAY  *********************/
  $leftColDisplay = array();
  $sortOrderArray = array(array('sortkey' => '1', 'module' => $showInfoPages),
                          array('sortkey' => '2', 'module' => $showManufacturers),
						  array('sortkey' => '3', 'module' => $showCategories));
  
  foreach($sortOrderArray as $key)
  {
    if (tep_not_null($key['module']))
    {                 
        $leftColDisplay[] = $key;    
    }
  }
                                                 
  usort($leftColDisplay, "SortOnKeys");
 
  $breadcrumb->add(Translate('Sitemap'), tep_href_link(FILENAME_SITEMAP_SEO));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
  <title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td>
				<h1><?php echo Translate('Sitemap'); ?></h1>
			</td>
		</tr>
		<tr>
			<td style="height:40px;"></td>
		</tr>
		<tr>
        	<td valign="top">
				<table border="0" cellpadding="0">
					<?php
					for ($i = 0; $i < count($leftColDisplay); ++$i)
					echo $leftColDisplay[$i]['module'];
					?>
            	</table>
			</td>
		</tr>
 
 
    </table></td>
<!-- body_text_eof //-->
    <td valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
