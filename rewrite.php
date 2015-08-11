<?php 
require_once('includes/application_top.php');
$no_params = explode('?', $_SERVER['REQUEST_URI']);

if ($no_params[0] == DIR_WS_HTTP_CATALOG) {
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
}
$catalogDir = substr(DIR_WS_HTTP_CATALOG, 1);
$urlData = explode('?', $_SERVER['REQUEST_URI'], 2);
/************************/
/*	SET $_GET PARAMS	*/
/************************/
$getParams = $urlData[1];
if (strlen($getParams) > 0) {
	foreach(explode('&', $getParams) as $getData) {
		$getData = explode('=', $getData);
		$_GET[$getData[0]] = $getData[1];
	}
}

$cat_array = array();
$page_array = array();
$prod_array = array();
$man_array = array();

$seo_url = str_replace($catalogDir, '', $urlData[0]);
$url_parts = explode('/', preg_replace(array('#^/#', '#/$#'), '', $seo_url));
$new_seo_url = '';
$count_parts = count($url_parts);
/************************************/
/*	Check if url exists in database	*/
/************************************/
$db_seo_url_query = tep_db_query('SELECT * FROM seo_urls WHERE url = "'.(substr($seo_url, 0, 1) == '/'?substr($seo_url, 1):$seo_url).'" AND language_id ="'.(int)$languages_id.'"');
$db_seo_url = tep_db_fetch_array($db_seo_url_query);
/****************************************/
/*	Go through each part of the url		*/
/****************************************/
foreach($url_parts as $piece=>$part) {
	$duplicate_url = array();
	$uri_part = $part;
	$new_seo_url .= $uri_part.'/';
	$part = trim(decodeUrlPart($part));//function in includes/functions/seo.php
	/****************************************************/
	/*	Check if this part has '+' in it				*/
	/*	if the last characters are a '+' and numeric	*/
	/*	then this was probably a duplicate url			*/
	/*	we have to remove the + and numbers				*/
	/****************************************************/
	if (strstr($uri_part, '+')) {
		$duplicate = trim(end(explode('+', $uri_part)));
		if (preg_match('/^[0-9]+/',$duplicate)) {
			$char_count = 0 - strlen($duplicate);
			$url_query = tep_db_query('SELECT categories_id, products_id, manufacturers_id, infopages_id FROM seo_urls WHERE url = "'.substr($new_seo_url, 0, -1).'"');
			$duplicate_url = tep_db_fetch_array($url_query);
			$part = trim(decodeUrlPart(substr($uri_part, 0, $char_count)));
		}
	}
	/****************************************/
	/*		check if category exists		*/
	/*			with this name				*/
	/****************************************/
	if (isset($duplicate_url['categories_id'])) {
		if ($duplicate_url['categories_id'] != '0') {
			$cat_query = tep_db_query('SELECT c.categories_id, c.parent_id, cd.categories_name FROM categories_description cd, categories c WHERE c.categories_id = cd.categories_id AND cd.language_id = "'.(int)$languages_id.'" AND cd.categories_name LIKE "'.$part.'" AND c.categories_id = "'.$duplicate_url['categories_id'].'"');
		} else {
			$cat_query = tep_db_query('SELECT c.categories_id, c.parent_id, cd.categories_name FROM categories_description cd, categories c WHERE c.categories_id = cd.categories_id AND cd.language_id = "'.(int)$languages_id.'" AND cd.categories_name LIKE "'.$part.'" AND c.categories_id = "0"');
		}
	} else {
		$cat_query = tep_db_query('SELECT c.categories_id, c.parent_id, cd.categories_name FROM categories_description cd, categories c WHERE c.categories_id = cd.categories_id AND cd.language_id = "'.(int)$languages_id.'" AND cd.categories_name LIKE "'.$part.'"');
	}
	if (tep_db_num_rows($cat_query) > 0) {
		while ($categorie = tep_db_fetch_array($cat_query)) {
			//$seo_url_query = tep_db_query('SELECT cpath FROM seo_urls WHERE categories_id = "'.$categorie['categories_id'].'" AND language_id = "'.(int)$languages_id.'" AND duplicate = "1"');
			//if (tep_db_num_rows($seo_url_query) < 1 || $duplicate_url['categories_id'] == $categorie['categories_id']) {
				$cat_array[$categorie['categories_id']] = array('uri_part' =>$uri_part, 'parent' => $categorie['parent_id'], 'name' => $categorie['categories_name']);
			//}
		}
	} else if (!isset($duplicate_url['categories_id']) || $duplicate_url['categories_id'] != '0') {
		/****************************************/
		/*		check for category names		*/
		/*			with '-' in it				*/
		/****************************************/
		$part_parts = explode(' ', $part); //split on 'space' to get all parts
		$count_part_parts = count($part_parts);
		$query_part = '';
		/************************************/
		/*		add every part to query		*/
		/************************************/
		foreach($part_parts as $key=>$aPart) {
			if ($key == '0') {
				$query_part .= ' AND cd.categories_name LIKE "'.$aPart.'%"'; //first part
			} else if (($key-1) == $count_part_parts) {
				$query_part .= ' AND cd.categories_name LIKE "%'.$aPart.'"'; //last part
			} else {
				$query_part .= ' AND cd.categories_name LIKE "%'.$aPart.'%"'; //middle parts
			}
		}
		$catparts_query = tep_db_query('SELECT c.categories_id, c.parent_id, cd.categories_name FROM categories_description cd, categories c WHERE c.categories_id = cd.categories_id AND cd.language_id = "'.(int)$languages_id.'"'.$query_part);
		if (tep_db_num_rows($catparts_query) > 0) {
			while ($catparts = tep_db_fetch_array($catparts_query)) {
				$cat_array[$catparts['categories_id']] = array('uri_part' =>$uri_part, 'parent' => $catparts['parent_id'], 'name' => $catparts['categories_name']);
			}
		}
	}
	
	/****************************************/
	/*		check if infopage exists		*/
	/*			with this name				*/
	/****************************************/
	if (isset($duplicate_url['infopages_id'])) {
		if ($duplicate_url['infopages_id'] != '0') {
			$page_query = tep_db_query('SELECT i.infopages_id, i.parent_id, i.type FROM infopages_text it, infopages i WHERE i.infopages_id = it.infopages_id AND it.language_id = "'.(int)$languages_id.'" AND it.infopages_title LIKE "'.$part.'" AND i.infopages_status = 1 AND i.infopages_id = "'.$duplicate_url['infopages_id'].'"');
		} else {
			$page_query = tep_db_query('SELECT i.infopages_id, i.parent_id, i.type FROM infopages_text it, infopages i WHERE i.infopages_id = it.infopages_id AND it.language_id = "'.(int)$languages_id.'" AND it.infopages_title LIKE "'.$part.'" AND i.infopages_status = 1 AND i.infopages_id = "0"');
		}
	} else {
		$page_query = tep_db_query('SELECT i.infopages_id, i.parent_id, i.type FROM infopages_text it, infopages i WHERE i.infopages_id = it.infopages_id AND it.language_id = "'.(int)$languages_id.'" AND it.infopages_title LIKE "'.$part.'" AND i.infopages_status = 1');
	}
	if (tep_db_num_rows($page_query) > 0) {
		while($infopage = tep_db_fetch_array($page_query)) {
			$page_array[$infopage['infopages_id']] = array('name' => $part, 'parent' => $infopage['parent_id'], 'type' => $infopage['type']);
		}
	}
	
	/****************************************/
	/*		check if product exists			*/
	/*			with this name				*/
	/*	Only if this is the last piece AND	*/
	/*	there are categories in de cat_array*/
	/*					OR					*/
	/*  the SEO_URL_PRODUCT_LAYERED constant*/
	/*  is set to 'false'. Then the url only*/
	/*										*/
	/*	This also checks if the product		*/
	/*	exists in multiple categories		*/
	/****************************************/
	if ((count($cat_array) > 0 && ($count_parts-1) == $piece) || SEO_URL_PRODUCT_LAYERED == 'false') {
		$product_data = transform_uri_part_to_product($part); //function in includes/functions/seo.php
		foreach($product_data as $key=>$value) {
			$prod_array[$key] = $value;
		}
	}
	
	/****************************************/
	/*		check if manufacturer exists	*/
	/*			with this name				*/
	/****************************************/
	if (isset($duplicate_url['manufacturers_id'])) {
		if ($duplicate_url['manufacturers_id'] != '0') {
			$man_query = tep_db_query('SELECT manufacturers_id FROM manufacturers WHERE manufacturers_name LIKE "'.$part.'" AND manufacturers_id = "'.$duplicate_url['manufacturers_id'].'"');
		} else {
			$man_query = tep_db_query('SELECT manufacturers_id FROM manufacturers WHERE manufacturers_id = "0"');
		}
	} else {
		$man_query = tep_db_query('SELECT manufacturers_id FROM manufacturers WHERE manufacturers_name LIKE "'.$part.'"');
	}
	if (tep_db_num_rows($man_query) > 0) {
		$manufacturer = tep_db_fetch_array($man_query);
		$man_array[$manufacturer['manufacturers_id']] = array('name' => $part, 'manufacturers_id' => $manufacturer['manufacturers_id']);
	}
	
	/****************************************/
	/*	Here you can add more posibilities	*/
	/*	Like for dealers, reviews, news, 	*/
	/*	.php pages, ...						*/
	/*	define an array before the foreach	*/
	/*	and add the code to fill it here	*/
	/****************************************/
}

/****************************************/
/*	the order of the following IF's 	*/
/*	determines what type of page has	*/
/*	priority. This is usefull for when	*/
/*	for example an infopage and a 		*/
/*	categorie exists with the same url.	*/
/*	In this example the category gets	*/
/*	gets priority so the infopage will	*/
/*	never be visible as long as they 	*/
/*	have the same url					*/
/****************************************/
/*echo '<pre>';
print_r($db_seo_url);
print_r($prod_array);
print_r($man_array);
print_r($cat_array);
print_r($page_array);
print_r($duplicate_url);
die();*/
if (is_array($db_seo_url) && count($db_seo_url) > 0) {
	if ($db_seo_url['products_id'] > 0) {
		$parameters = seo_url_product($db_seo_url['products_id']);
	} else if ($db_seo_url['manufacturers_id'] > 0) {
		$parameters = seo_url_manufacturer($db_seo_url['manufacturers_id']);
	} else if ($db_seo_url['categories_id'] > 0) {
		$parameters = seo_url_category($db_seo_url['categories_id']);
	} else if ($db_seo_url['infopages_id'] > 0) {
		$parameters = seo_url_infopage($db_seo_url['infopages_id']);
	}
} else {
	/****************************/
	/*	First Products			*/
	/****************************/
	if (count($prod_array) > 0) {
		$parameters = seo_url_product();
	} else
	/****************************/
	/*	Second Manufacturers	*/
	/****************************/
	if (count($man_array) > 0) {
		$parameters = seo_url_manufacturer();
	} else
	/****************************/
	/*	Third categories		*/
	/****************************/
	if (count($cat_array) > 0) {
		$parameters = seo_url_category();
	} else
	/****************************/
	/*	Fourth/last infopages	*/
	/****************************/
	if (count($page_array) > 0) {
		$parameters = seo_url_infopage();
	}
}
/*echo '<pre>';
print_r($parameters);
die();*/
if (count($parameters) > 0) {
	if (isset($parameters['_SERVER'])) {
		foreach($parameters['_SERVER'] as $key=>$value) {
			$_SERVER[$key] = $value;
		}
	}
	if (isset($parameters['_GET'])) {
		foreach($parameters['_GET'] as $key=>$value) {
			$_GET[$key] = $value;
		}
	}
	if (isset($parameters['variables'])) {
		foreach($parameters['variables'] as $key=>$value) {
			${$key} = $value;
		}
	}
	// navigation history
	$navigation->add_current_page();
	// eof navigation history
	require (DIR_WS_CLASSES.'sts.php');
	$sts= new sts();
	$sts->start_capture();
	include($parameters['page']);
	exit();
} else {
	$dirname = dirname($_SERVER['PHP_SELF']);
	if (substr($dirname, -1) != '/' && strlen($dirname) > 0) {
		$dirname .= '/';
	}
	$PHP_SELF_REWRITE = $dirname.FILENAME_DEFAULT;
	$_SERVER['PHP_SELF'] = $PHP_SELF_REWRITE;
	require (DIR_WS_CLASSES.'sts.php');
	$sts= new sts();
	$sts->start_capture();
	include(FILENAME_DEFAULT);
	exit();
}

function seo_url_product($products_id = 0) {
	global $prod_array, $cat_array, $languages_id, $breadcrumb, $request_type, $url_parts, $count_parts, $breadcrumb;
	reset($prod_array);
	$products_id = $prod_array[key($prod_array)]['products_id'];
	$products_name = $prod_array[key($prod_array)]['name'];
	if (count($prod_array) > 1) {
		/************************************/
		/*	Product has multiple categories	*/
		/************************************/
		$cPath_array = array();
		/********************************************/
		/*	Get db cPath for every parent categorie	*/
		/********************************************/
		foreach($prod_array as $key=>$value) {
			$cPath_array[$key] = tep_get_category_tree_db($value['parent'], 'parents');//function in includes/functions/seo.php
		}
		/****************************************************/
		/*	check wich database cPath is equal to url cPath	*/
		/****************************************************/
		$check_cPath_array = array();
		foreach($prod_array as $prod_id=>$value) {
			foreach($cat_array as $cat_id=>$cat_data) {
				foreach($cPath_array[$prod_id] as $key=>$cpath_data) {
					if ($cpath_data['categories_id'] == $cat_id && $cpath_data['parent_id'] == $cat_data['parent']) {
						$check_cPath_array[$prod_id][] = $cpath_data;
					}
				}
			}
		}
		/************************************************************/
		/*	work with the tree that fits the most in the url tree	*/
		/************************************************************/
		$biggest_tree_count = 0;
		$biggest_tree = '';
		foreach($check_cPath_array as $cat_id=>$tree) {
			if (count($tree) > $biggest_tree_count) {
				$biggest_tree_count = count($tree);
				$biggest_tree = $cat_id;
			}
		}
		$check_cPath_array = $check_cPath_array[$biggest_tree];
		if (count($check_cPath_array) > 0) {
			if (count($check_cPath_array) != count($cat_array)) {
				/************************************************/
				/*		Only a part of the tree is equal		*/
				/*		Get the tree that fits the most			*/
				/************************************************/
				$check_cPath_array = array_reverse($cPath_array[$biggest_tree]);
				reset($check_cPath_array);
				$current_category_id = $biggest_tree;
				$cPath = '';
				foreach($check_cPath_array as $data) {
					$cPath .= $data['categories_id'].'_';
				}
				$cPath = substr($cPath, 0, -1);
				tep_db_query('DELETE FROM seo_urls WHERE products_id = "'.$products_id.'"');
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ".tep_href_link('product_info.php', 'products_id='.$products_id, $request_type, true, false, $cPath));
				exit();
			} else {
				/************************************************/
				/*		this array contains the right cPath		*/
				/************************************************/
				end($check_cPath_array); // make sure array pointer is at last element
				$current_category_id = $check_cPath_array[key($check_cPath_array)]['categories_id'];
			}
		} else {
			/****************************************************************/
			/*	None of the database cPath array matches the one in the url	*/
			/*	Use the first off the possible trees	*/
			/****************************************************************/
			reset($cPath_array);
			$check_cPath_array = array_reverse($cPath_array[key($cPath_array)]);
			end($check_cPath_array); 
			$current_category_id = $check_cPath_array[key($check_cPath_array)]['categories_id'];
		}
	} else {
		/************************************/
		/*	Product has only 1 category		*/
		/************************************/
		foreach($prod_array as $value) {
			$current_category_id = $value['parent'];
		}
		$cPath_array = array();
		$cPath_array = tep_get_category_tree_db($current_category_id, 'parents');//function in includes/functions/seo.php
		/********************************************************************/
		/*	check if url cPath is equal to database cPath					*/
		/*	if not, product has moved										*/
		/*	First remove the extra categories by checking the parent ids	*/
		/********************************************************************/
		$rcat_array = array_reverse($cat_array, true);
		$new_cat_array = array();
		$count = 0;
		$parent_id = 0;
		if ($count_parts == 1) {
			foreach($rcat_array as $key=>$value) {
				if ($value['parent'] == '0') {
					$new_cat_array[$key] = $value;
				}
			}
		} else {
			$new_cat_array[$current_category_id] = $rcat_array[$current_category_id];
			$parent_id = $rcat_array[$current_category_id]['parent'];
			foreach($rcat_array as $key=>$value) {
				$count++;
				if($key == $parent_id) {
					$new_cat_array[$key] = $value;
					$parent_id = $value['parent'];
				}
			}
		}
		$cat_array = array_reverse($new_cat_array, true);
		/********************************/
		/*	extra categories deleted	*/
		/********************************/
		$check_cPath_array = array();
		foreach($cat_array as $cat_id=>$cat_data) {
			foreach($cPath_array as $key=>$cpath_data) {
				if ($cpath_data['categories_id'] == $cat_id && $cpath_data['parent_id'] == $cat_data['parent']) {
					$check_cPath_array[] = $cpath_data;
				}
			}
		}
		if (count($check_cPath_array) != count($cat_array)) {
			/************************************/
			/*	Product is moved to other cat	*/
			/*	remove seo urls from database	*/
			/*	for this product and create new	*/
			/************************************/
			tep_db_query('DELETE FROM seo_urls WHERE products_id = "'.$products_id.'"');
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: ".tep_href_link('product_info.php', 'products_id='.$products_id, $request_type, true, false));
			exit();
		}
	}
	
	$cPath = '';
	foreach($check_cPath_array as $data) {
		$breadcrumb->add($data['categories_name'], tep_href_link('index.php', 'cPath='.$data['categories_id'], $request_type, true, false));
		$cPath .= $data['categories_id'].'_';
	}
	$cPath = substr($cPath, 0, -1);
	$breadcrumb->add($products_name);
	$parameters = array();
	$dirname = dirname($_SERVER['PHP_SELF']);
	if (substr($dirname, -1) != '/' && strlen($dirname) > 0) {
		$dirname .= '/';
	}
	$parameters['_SERVER'] = array('PHP_SELF' => $dirname.'product_info.php');
	$parameters['_GET'] = array('products_id' => $products_id);
	$parameters['page'] = 'product_info.php';
	$parameters['variables'] = array('current_category_id' => $current_category_id, 'cPath' => $cPath);
	return $parameters;
}

function seo_url_manufacturer() {
	global $man_array, $languages_id, $breadcrumb, $request_type, $url_parts, $count_parts, $breadcrumb;
	reset($man_array);
	if (count($man_array) > 1) {
		/************************************************************/
		/*	If more then 1 manufacturers name is found				*/
		/*	use the one depending on the constant SEO_URL_MAN_POS	*/
		/************************************************************/
		if (SEO_URL_MAN_POS == 'before') {
			$man_array = $man_array[key($man_array)];
		} else if (SEO_URL_MAN_POS == 'after') {
			end($man_array);
			$man_array = $man_array[key($man_array)];
		} else {
			/********************************************************************/
			/*	use 'before' as default for when the SEO_URL_MAN_POS isn't known*/
			/********************************************************************/
			$man_array = $man_array[key($man_array)];
		}
	} else {
		$man_array = $man_array[key($man_array)];
	}
	$manufacturers_id = $man_array['manufacturers_id'];
	$manufacturers_name = $man_array['name'];
	if (count($cat_array) > 0) {
		/************************************************/
		/*	Filter_id is set. Only show products from	*/
		/*	this manufacturer AND this categorie		*/
		/************************************************/
		end($cat_array);
		$filter_id = key($cat_array);
		$_GET['filter_id'] = $filter_id;
		if (sizeof($navigation->path) > 0) {
			/********************************************/
			/*	User was already surfing on this site	*/
			/********************************************/
			$last_nav_item = sizeof($navigation->path) - 1;
			if (isset($navigation->path[$last_nav_item]['get']['cPath']) && ($navigation->path[$last_nav_item]['get']['cPath'] == $filter_id || strstr($navigation->path[$last_nav_item]['get']['cPath'], '_'.$filter_id))) {
				/************************************/
				/*	Last viewed page was a category	*/
				/*	breadcrumb starts with category	*/
				/************************************/
				
				/************************************/
				/*	full cpath in the breadcrumb	*/
				/************************************/
				$filter_id_parents = array_reverse(tep_get_category_tree_db($filter_id, 'parents'));//function in includes/functions/seo.php
				foreach($filter_id_parents as $cat_data) {
					$breadcrumb->add($cat_data['categories_name'], tep_href_link('index.php', 'manufacturers_id='.$manufacturers_id.'&filter_id='.$cat_data['categories_id'], $request_type, true, false));
				}
				$breadcrumb->add($manufacturers_name, tep_href_link('index.php', 'manufacturers_id='.$manufacturers_id, $request_type, true, false));
			} else {
				/****************************************/
				/*	Last viewed page was not a category	*/
				/*	breadcrumb starts with manufacturer	*/
				/****************************************/
				$breadcrumb->add($manufacturers_name, tep_href_link('index.php', 'manufacturers_id='.$manufacturers_id, $request_type, true, false));
				/************************************/
				/*	full cpath in the breadcrumb	*/
				/************************************/
				$filter_id_parents = array_reverse(tep_get_category_tree_db($filter_id, 'parents'));//function in includes/functions/seo.php
				foreach($filter_id_parents as $cat_data) {
					$breadcrumb->add($cat_data['categories_name'], tep_href_link('index.php', 'manufacturers_id='.$manufacturers_id.'&filter_id='.$cat_data['categories_id'], $request_type, true, false));
				}
			}
		} else {
			/********************************************/
			/*	User was not surfing on this site		*/
			/*	this should be his first page view		*/
			/*	show manufacturer first then categories	*/
			/********************************************/
			$breadcrumb->add($manufacturers_name, tep_href_link('index.php', 'manufacturers_id='.$manufacturers_id, $request_type, true, false));
			/************************************/
			/*	full cpath in the breadcrumb	*/
			/************************************/
			$filter_id_parents = array_reverse(tep_get_category_tree_db($filter_id, 'parents'));//function in includes/functions/seo.php
			foreach($filter_id_parents as $cat_data) {
				$breadcrumb->add($cat_data['categories_name'], tep_href_link('index.php', 'manufacturers_id='.$manufacturers_id.'&filter_id='.$cat_data['categories_id'], $request_type, true, false));
			}
		}
	} else {
		$breadcrumb->add($manufacturers_name);
	}
	$parameters = array();
	$dirname = dirname($_SERVER['PHP_SELF']);
	if (substr($dirname, -1) != '/' && strlen($dirname) > 0) {
		$dirname .= '/';
	}
	$parameters['_SERVER'] = array('PHP_SELF' => $dirname.'index.php');
	$parameters['_GET'] = array('manufacturers_id' => $manufacturers_id);
	$parameters['page'] = 'index.php';
	return $parameters;
}
function seo_url_category($category_id = 0) {
	global $cat_array, $languages_id, $breadcrumb, $request_type, $url_parts, $count_parts, $breadcrumb;
	if ($category_id > 0) {
		$new_cat_array = array();
		$new_cat_array[$category_id] = $cat_array[$category_id];
		$parent_id = $cat_array[$category_id]['parent'];
		while ($parent_id > 0) {
			$new_cat_array[$parent_id] = $cat_array[$parent_id];
			$parent_id = $cat_array[$parent_id]['parent'];
		}
		$cPath = '';
		$cat_array = array_reverse($new_cat_array, true);
		end($cat_array);
		$current_category_id = key($cat_array);
		reset($cat_array);
		foreach($cat_array as $cat_id=>$data) {
			$cPath .= $cat_id.'_';
			$breadcrumb->add($data['name'], tep_href_link('index.php', 'cPath='.substr($cPath, 0, -1), $request_type, true, false));
		}
		$cPath = substr($cPath, 0, -1);
	} else {
		if (count($cat_array) != count($url_parts)) {
			if (count($cat_array) > count($url_parts)) {
				/********************************************************************/
				/*	There is a category name in the url that has multiple instances	*/
				/*	Remove the extra category by checking the parent ids			*/
				/********************************************************************/
				$rcat_array = array_reverse($cat_array, true);
				$new_cat_array = array();
				$count = 0;
				$parent_id = 0;
				if ($count_parts == 1) {
					foreach($rcat_array as $key=>$value) {
						if ($value['parent'] == '0') {
							$new_cat_array[$key] = $value;
						}
					}
				} else {
					foreach($rcat_array as $key=>$value) {
						$count++;
						if ($count == 1) {
							$new_cat_array[$key] = $value;
							$parent_id = $value['parent'];
						} else {
							if($key == $parent_id) {
								$new_cat_array[$key] = $value;
								$parent_id = $value['parent'];
							}
						}
					}
				}
				$cat_array = array_reverse($new_cat_array, true);
				end($cat_array);
				$current_category_id = key($cat_array);
				$cPath = '';
				reset($cat_array);
				foreach($cat_array as $cat_id=>$data) {
					$cPath .= $cat_id.'_';
					$breadcrumb->add($data['name'], tep_href_link('index.php', 'cPath='.substr($cPath, 0, -1), $request_type, true, false));
				}
				$cPath = substr($cPath, 0, -1);
			} else {
				/************************************************************/
				/*	There is a category name in the url that doesn't exist	*/
				/*	Reverse array, to start with the lowest level			*/
				/************************************************************/
				$rcat_array = array_reverse($cat_array, true);
				$rurl_parts = array_reverse($url_parts, true);
				foreach($rcat_array as $key=>$value) {
					if (in_array($value['uri_part'], $rurl_parts)) {
						$lowest_cat_id = $key;
						break;
					}
				}
				/****************************************************/
				/*	tree structure of lowest level category found	*/
				/****************************************************/
				$cpath_data = array_reverse(tep_get_category_tree_db($lowest_cat_id, 'parents'));//function in includes/functions/seo.php
				$cPath = '';
				foreach($cpath_data as $value) {
					$cPath .= $value['categories_id'].'_';
				}
				/********************************************/
				/*	Redirect to lowest level category found	*/
				/********************************************/
				header("HTTP/1.0 404 Not Found");
				header("Location: ".tep_href_link('index.php', 'cPath='.substr($cPath, 0, -1), $request_type, true, false));
				exit();
			}
		} else {
			/********************/
			/*	All looks good	*/
			/********************/
			end($cat_array);
			$current_category_id = key($cat_array);
			$cPath = '';
			reset($cat_array);
			foreach($cat_array as $cat_id=>$data) {
				$cPath .= $cat_id.'_';
				$breadcrumb->add($data['name'], tep_href_link('index.php', 'cPath='.substr($cPath, 0, -1), $request_type, true, false));
			}
			$cPath = substr($cPath, 0, -1);
		}
	}
	$parameters = array();
	$dirname = dirname($_SERVER['PHP_SELF']);
	if (substr($dirname, -1) != '/' && strlen($dirname) > 0) {
		$dirname .= '/';
	}
	$parameters['_SERVER'] = array('PHP_SELF' => $dirname.'index.php');
	$parameters['_GET'] = array('cPath' => $cPath);
	$parameters['page'] = 'index.php';
	$parameters['variables'] = array('current_category_id' => $current_category_id, 'cPath' => $cPath);
	return $parameters;
}

function seo_url_infopage($infopage_id = 0) {
	global $page_array, $languages_id, $breadcrumb, $request_type, $url_parts, $count_parts;
	if ($infopage_id > 0) {
		$new_page_array = array();
		$new_page_array[$infopage_id] = $page_array[$infopage_id];
		$parent_id = $page_array[$infopage_id]['parent'];
		while ($parent_id > 0) {
			$new_page_array[$parent_id] = $page_array[$parent_id];
			$parent_id = $page_array[$parent_id]['parent'];
		}
		$page_array = array_reverse($new_page_array, true);
		end($page_array);
		reset($page_array);
		foreach($page_array as $page_id=>$data) {
			$breadcrumb->add($data['name'], tep_href_link('infopage.php', 'page='.$page_id, $request_type, true, false));
			$page = $page_id;
		}
	} else {
		end($page_array);	
		$nav_path = tep_get_navigation_tree($languages_id, 'i_'.key($page_array), '');
		if (count($nav_path) > 0) {
			$cpath_array = array();
			/********************************************************/
			/*	In navigation => this one has priority				*/
			/*	If the pages are in the navigation,					*/
			/*	You're sure the site owner wants it to be visible	*/
			/********************************************************/
			if (count($nav_path) > 1) {
				/********************************************************/
				/*	This page is more then once used in the navigation	*/
				/********************************************************/
				/****************************************************/
				/*	check wich database cPath is equal to url cPath	*/
				/****************************************************/
				$check_cPath_array = array();
				foreach($nav_path as $nav_id=>$value) {
					foreach($value as $key=>$data) {
						foreach($page_array as $page_id=>$page_data) {
							if ($data['id'] == $page_id) {
								$check_cPath_array[$nav_id][] = $data;
							}
						}
					}
				}
				/************************************************************/
				/*	work with the tree that fits the most in the url tree	*/
				/************************************************************/
				$biggest_tree_count = 0;
				$biggest_tree = '';
				foreach($check_cPath_array as $nav_id=>$tree) {
					if (count($tree) > $biggest_tree_count) {
						$biggest_tree_count = count($tree);
						$biggest_tree = $nav_id;
					}
				}
				$check_cPath_array = $check_cPath_array[$biggest_tree];
				$page_tree = array_reverse($nav_path[$biggest_tree]);
			} else {
				/********************************************************/
				/*	This page is only one time used in the navigation	*/
				/********************************************************/
				$page_tree = array_reverse($nav_path[key($nav_path)]);
			}
			if (count($page_tree) == $count_parts) {
				foreach($page_tree as $page_info) {
					$breadcrumb->add($page_info['name'], tep_href_link('infopage.php', 'page='.$page_info['id'], $request_type, true, false));
					$page = $page_info['id'];
				}
			} else {
				end($page_array);
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ".tep_href_link('infopage.php', 'page='.key($page_array), $request_type, true, false));
				exit();
			}
		} else {
			/********************************************/
			/*	This page is not used in the navigation	*/
			/*	next we filter out the last page => the	*/
			/*	one that isn't a parent					*/
			/********************************************/
			$last_page_array = $page_array;
			$new_page_array = array();
			foreach($last_page_array as $key=>$value) {
				foreach($page_array as $cat_id=>$cat_data) {
					if ($key == $cat_data['parent']) {
						$new_page_array[$key] = $value;
						unset($last_page_array[$key]);
						break;
					}
				}
			}
			/********************************************/
			/*	check if we have the right page array	*/
			/********************************************/
			if (count($new_page_array) == count($url_parts)) {
				$page_array = $new_page_array;
			} else {
				/********************/
				/*	page(s) missing	*/
				/********************/
				$missing = count($url_parts)-count($new_page_array);
				$rurl_parts = array_reverse($url_parts);
				$missing_items = array();
				for($x=0;$x<$missing;$x++) {
					$missing_items[] = trim(decodeUrlPart($rurl_parts[$x]));//function in includes/functions/seo.php
				}
				foreach($missing_items as $title) {
					foreach($last_page_array as $key=>$value) {
						if (!in_array($title, $value)) {
							unset($last_page_array[$key]);
						}
					}
				}
				/********************************************/
				/*	add the last page to the new page array	*/
				/********************************************/
				foreach($last_page_array as $key=>$value) {
					$new_page_array[$key] = $value;
				}
				$final_page_array = array();
				if (count($new_page_array) > count($url_parts)) {
					$count_array_item = 0;
					foreach($new_page_array as $key=>$value) {
						$count_array_item++;
						if ($count_array_item > count($url_parts)) {
							unset($new_page_array[$key]);
						}
					}
				}
				$page_array = $new_page_array;
			}
			/****************************/
			/*	set to last array key	*/

			/****************************/
			end($page_array);
			$page_tree = tep_get_infopages_tree(key($page_array));//function in includes/functions/seo.php
			if (count($page_tree) == count($page_array)) {
				$rpage_tree = array_reverse($page_tree);
				foreach($rpage_tree as $page_info) {
					$breadcrumb->add($page_info['infopages_title'], tep_href_link('infopage.php', 'page='.$page_info['infopages_id'], $request_type, true, false));
					$page = $page_info['infopages_id'];
				}
			} else {
				if (count($page_tree) < count($page_array)) {
					$rpage_tree = array_reverse($page_tree);
					foreach($rpage_tree as $page_info) {
						$breadcrumb->add($page_info['infopages_title'], tep_href_link('infopage.php', 'page='.$page_info['infopages_id'], $request_type, true, false));
						$page = $page_info['infopages_id'];
					}
				} else {
					reset($page_tree);
					header("HTTP/1.1 301 Moved Permanently");
					header("Location: ".tep_href_link('infopage.php', 'page='.$page_tree[key($page_tree)]['infopages_id'], $request_type, true, false));
					exit();
				}
			}
		}
	}
	$parameters = array();
	$dirname = dirname($_SERVER['PHP_SELF']);
	if (substr($dirname, -1) != '/' && strlen($dirname) > 0) {
		$dirname .= '/';
	}
	$parameters['_SERVER'] = array('PHP_SELF' => $dirname.'/infopage.php');
	$parameters['_GET'] = array('page' => $page);
	$parameters['page'] = 'infopage.php';
	return $parameters;
}
?>