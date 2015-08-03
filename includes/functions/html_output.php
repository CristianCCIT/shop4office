<?php
/*
  $Id: html_output.php,v 1.56 2003/07/09 01:15:48 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

////
// The HTML href link wrapper function
  function tep_href_old_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $force_language_id = false) {
    global $request_type, $session_started, $SID, $languages_id;
	if ($force_language_id !== false) {
		$newlanguages_id = $force_language_id;
	} else {
		$newlanguages_id = $languages_id;
	}

    if (!tep_not_null($page)) {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>');
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</b><br><br>');
    }

    $separator = '?';
    if (tep_not_null($parameters)) {
      $product_name = '';
      switch ($page) {
        case FILENAME_PRODUCT_INFO:
        case FILENAME_DEFAULT:
          $manufacturer_name = '';
          $product_name = '';
          $new_parameter_list = array();
          $cPath_list = array();
          foreach (explode('&', $parameters) as $pair) {
            $pair_array = explode('=', $pair);
            switch ($pair_array[0]) {
              case 'action':
                $link .= $page . '?' . tep_output_string($parameters);
                $separator = '&';
                break 3;
              case 'cPath':
                $parent_id = 0;
				// CONTROLEER ALS ER SEO URL IN DATABASE IS
				if (strstr($pair_array[1], '_')) {
					$cpath_id = end(explode('_', $pair_array[1]));
				} else {
					$cpath_id = $pair_array[1];
				}
				$get_seo_categories_query = tep_db_query("select url from seo_urls where categories_id = '" . (int)$cpath_id . "' AND language_id = '".(int)$newlanguages_id."'");				
				
				if (tep_db_num_rows($get_seo_categories_query)>0)
				{
					$get_seo_categories = tep_db_fetch_array($get_seo_categories_query);
					$cPath_list[]= substr($get_seo_categories['url'], 1);
				}
				else
				{
					$catpath = tep_get_full_cpath($cpath_id);
					foreach (explode('_', $catpath) as $category_id) {
						$category_name_query = tep_db_query("select cd.categories_name from categories_description cd, categories c where cd.categories_id=c.categories_id and cd.categories_id='" . (int)$category_id . "' and cd.language_id='" . (int)$newlanguages_id . "' and c.parent_id='" . (int)$parent_id . "'");
						if ($category_name_array = tep_db_fetch_array($category_name_query))
						{
							$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($category_name_array['categories_name'])))));
							$parent_id = $category_id;
						}
					}
				}
				$seo_type = 'categories_id';
				$seo_val = $category_id;
                break;
              case 'products_id':
				// CONTROLEER ALS ER SEO URL IN DATABASE IS
				$get_seo_product_query = tep_db_query("select url from seo_urls where products_id = '" . (int)$pair_array[1] . "'");
				if (tep_db_num_rows($get_seo_product_query)>0)
				{
					$get_seo_product = tep_db_fetch_array($get_seo_product_query);
					$seo_url_cache = $get_seo_product['url'];
				}
				else
				{
					//ZONIET, SEO URL AANMAKEN
					$seo_url_cache = '';
					$product_name_query = tep_db_query("select p.products_model, pd.products_name from products p JOIN products_description pd using (products_id) where p.products_id='" . (int)$pair_array[1] . "' and pd.language_id='" . (int)$languages_id . "'");
					if ($product_name_array = tep_db_fetch_array($product_name_query))
					{
						if (($seo_type!='categories_id') && (SEO_URL_PRODUCT_LAYERED!='false')) {
							$product_categories_query = tep_db_query("select categories_id from products_to_categories where products_id='" . (int)$pair_array[1] . "'");
							if ($product_categories_array = tep_db_fetch_array($product_categories_query))
							{
								foreach (explode('_', tep_get_full_cpath($product_categories_array['categories_id'])) as $this_category_id) {
									$category_name_query = tep_db_query("select categories_name from categories_description where language_id='" . (int)$languages_id . "' and categories_id='" . (int)$this_category_id . "'");
									while ($category_name_array = tep_db_fetch_array($category_name_query))
									{
										$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($category_name_array['categories_name'])))));
									}
								}
							}
						}
					}
					if (SEO_URL_PRODUCTS_MODEL=='only') {
						$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($product_name_array['products_model'])))));
					} elseif (SEO_URL_PRODUCTS_MODEL!='false') {
						if (SEO_URL_PRODUCTS_MODEL=='after') {
							$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($product_name_array['products_name']))))).'-'.RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($product_name_array['products_model'])))));
						} else {
							$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($product_name_array['products_model']))))).'-'.RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($product_name_array['products_name'])))));
						}
					} else {
						$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($product_name_array['products_name'])))));
					}
				}
				//die($tester);
				//die(print_r($cPath_list));
				$seo_type = 'products_id';
				$seo_val = $pair_array[1];
                break;
              case 'manufacturers_id':
                $manufacturer_name_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id='" . (int)$pair_array[1] . "'");
                if ($manufacturer_array = tep_db_fetch_array($manufacturer_name_query)) {
                  $manufacturer_name = $manufacturer_array['manufacturers_name'];
                }
				$seo_type = 'manufacturers_id';
				$seo_val = $pair_array[1];
				break;
			  case 'filter_id':
			  	$filter_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.(int)$pair_array[1].'" AND language_id = "'.(int)$languages_id.'"');
				if ($filter = tep_db_fetch_array($filter_query)) {
					$filter_name = $filter['categories_name'];
				}
				$seo_type = 'manufacturers_id';
				$seo_val .= '_'.$pair_array[1];
			  	break;
              case '':
                break;
              default:
                if (tep_not_null($pair)) $new_parameter_list[]= $pair;
            }
          }
		  
			if ($seo_url_cache!='')
			{
				unset($cPath_list);
				$cPath_list[]= substr($seo_url_cache, 1);
			}
			else
			{
				if (tep_not_null($manufacturer_name))
				{
					$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode($manufacturer_name))));
				}
				if (tep_not_null($filter_name)) {
					$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode($filter_name))));
				}
				if (tep_not_null($infopage_name))
				{
					if ($p_infopage_parent_name!='')
					{
					$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode($p_infopage_parent_name))));
					}
					if ($infopage_parent_name!='')
					{
					$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode($infopage_parent_name))));
					}
					if ($infopage_name!='')
					{
					$cPath_list[]= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode($infopage_name))));
					}
				}
			}
          $separator = '?';
		  
          $link .= preg_replace('/%2F/', '%20', implode('/', $cPath_list));

          if (tep_not_null($new_parameter_list)) {
            $link .= $separator . implode('&', $new_parameter_list);
            $separator = '&';
          }
          break;
		case FILENAME_INFOPAGE:
          $new_parameter_list = array();
          foreach (explode('&', $parameters) as $pair) {
            global $languages_id;
            $pair_array = explode('=', $pair);
            switch ($pair_array[0]) {
              case 'action':
                $link .= $page . '?' . tep_output_string($parameters);
                $separator = '&';
                break 3;
              case 'page':
			  	$parent_id = 0;
				// CONTROLEER ALS ER SEO URL IN DATABASE IS
				$get_seo_infopage_query = tep_db_query("select url from seo_urls where infopages_id = '" . $pair_array[1] . "'");
				if (tep_db_num_rows($get_seo_infopage_query)>0)
				{
					$get_seo_infopage = tep_db_fetch_array($get_seo_infopage_query);
					$seo_url_cache = $get_seo_infopage['url'];
				}
				else
				{
					//ZONIET, SEO URL AANMAKEN
					$seo_url_cache = '';
					$parent_id_query = tep_db_query("select parent_id from navigatie where link='i_" . $pair_array[1] . "'");
					if ($parent_id_array = tep_db_fetch_array($parent_id_query)) {
						$parent_id = $parent_id_array['parent_id'];
						$infopage_parent_link_query = tep_db_query("select parent_id, link from navigatie where id='" . (int)$parent_id . "'");
						if ($infopage_parent_link_array = tep_db_fetch_array($infopage_parent_link_query)) {
						  $infopage_parent_link = str_replace('i_', '', $infopage_parent_link_array['link']);
							$p_parent_id = 0;
							$p_parent_id_query = tep_db_query("select parent_id from navigatie where link='i_" . $infopage_parent_link . "'");
							if ($p_parent_id_array = tep_db_fetch_array($p_parent_id_query)) {
								$p_parent_id = $p_parent_id_array['parent_id'];
								$p_infopage_parent_link_query = tep_db_query("select parent_id, link from navigatie where id='" . (int)$p_parent_id . "'");
								if ($p_infopage_parent_link_array = tep_db_fetch_array($p_infopage_parent_link_query)) {
								  $p_infopage_parent_link = str_replace('i_', '', $p_infopage_parent_link_array['link']);
								}
							}
							$p_infopage_parent_name_query = tep_db_query("select infopages_title from infopages_text where infopages_id='" . (int)$p_infopage_parent_link . "' and language_id='" . (int)$languages_id . "'");
							if ($p_infopage_parent_name_array = tep_db_fetch_array($p_infopage_parent_name_query)) {
							  $p_infopage_parent_name = RemoveSpecialCharacters(str_replace('-', '+', $p_infopage_parent_name_array['infopages_title']));
							}
						}
					}
					$infopage_parent_name_query = tep_db_query("select infopages_title from infopages_text where infopages_id='" . (int)$infopage_parent_link . "' and language_id='" . (int)$languages_id . "'");
					if ($infopage_parent_name_array = tep_db_fetch_array($infopage_parent_name_query)) {
					  $infopage_parent_name = RemoveSpecialCharacters(str_replace('-', '+', $infopage_parent_name_array['infopages_title']));
					}
					$infopage_name_query = tep_db_query("select infopages_title from infopages_text where infopages_id='" . (int)$pair_array[1] . "' and language_id='" . (int)$languages_id . "'");
					if ($infopage_array = tep_db_fetch_array($infopage_name_query)) {
					  $infopage_name = RemoveSpecialCharacters(str_replace('-', '+', $infopage_array['infopages_title']));
					}
				}
				$seo_type = 'infopages_id';
				$seo_val = $pair_array[1];
                break;
              case '':
                break;
              default:
                if (tep_not_null($pair)) $new_parameter_list[]= $pair;
            }
          }
		  
			if ($seo_url_cache!='')
			{
				unset($cPath_list);
				$cPath_list[]= substr($seo_url_cache, 1);
			}
			else
			{
				if (tep_not_null($manufacturer_name))
				{
					$cPath_list[]= str_replace("+", "-", strtolower(urlencode($manufacturer_name)));
				}
				if (tep_not_null($infopage_name))
				{
					if ($p_infopage_parent_name!='')
					{
					$cPath_list[]= str_replace("+", "-", strtolower(urlencode($p_infopage_parent_name)));
					}
					if ($infopage_parent_name!='')
					{
					$cPath_list[]= str_replace("+", "-", strtolower(urlencode($infopage_parent_name)));
					}
					if ($infopage_name!='')
					{
					$cPath_list[]= str_replace("+", "-", strtolower(urlencode($infopage_name)));
					}
				}
			}
          $separator = '?';
		  
          $link .= preg_replace('/%2F/', '%20', implode('/', $cPath_list));

          if (tep_not_null($new_parameter_list)) {
            $link .= $separator . implode('&', $new_parameter_list);
            $separator = '&';
          }
		break;
        default:
          $link .= $page . '?' . tep_output_string($parameters);
          $separator = '&';
      }
    } else {
      $link .= $page;
      $separator = '?';
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (tep_not_null($SID)) {
        /*FORUM*/
		//$_sid = $SID;
		/*FORUM*/
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {

          $_sid = tep_session_name() . '=' . tep_session_id();
        }
      }
    }
    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);

      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);
      $link = str_replace('+', '-', $link);
      $separator = '?';
    }
	//SAVE TO DB
    if (isset($_sid)) {
      $link .= $separator . $_sid; 
    }
	if ($seo_type=='') { $seo_type = 'navigatie_id'; }

	if (TransformSeoUrl($link) != '/')
	{
		$get_seo_query = tep_db_query("select id from seo_urls where ".$seo_type." = '" . $seo_val . "' AND language_id = '".(int)$newlanguages_id."'");
		if (tep_db_num_rows($get_seo_query)<1)
		{
			tep_db_query("INSERT INTO seo_urls (language_id, ".$seo_type.", url) VALUES ('".$newlanguages_id."', '".$seo_val."', '".TransformSeoUrl($link)."')");
		}
	}
	//SAVE TO DB
    return $link;
  }

////
// The HTML image wrapper function
function tep_image($src, $alt = '', $width = '', $height = '', $parameters = '', $just_info = false) {
	if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
		$src = 'images/no-image.jpg';
	}
    $o_width = $width;
    $o_height = $height;

    if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') ) {
		if ($image_size = @getimagesize($src)) {
  			if ($width < $image_size[0] || $height < $image_size[1]) //If we need to resize image
  			{
				$ratio = $image_size[1] / $image_size[0];
				// Set the width and height to the proper ratio
				if (!$width && $height) {
					$ratio = $height / $image_size[1];
					$width = intval($image_size[0] * $ratio);
				} elseif ($width && !$height) {
					$ratio = $width / $image_size[0];
					$height = intval($image_size[1] * $ratio);
				} elseif ($width && $height) {
					$ratio_orig = $image_size[0]/$image_size[1];
					if ($width/$height > $ratio_orig) {
						$width = intval($height*$ratio_orig);
					} else {
						$height = intval($width/$ratio_orig);
					}
				} elseif (!$width && !$height && !$over_ride) {
					$width = $image_size[0];
					$height = $image_size[1];
				}
			}
			else
			{
				$width = $image_size[0];
				$height = $image_size[1];
			}
		} elseif (IMAGE_REQUIRED == 'false') {
			return false;
		}
	}

    if ($width != $image_size[0] || $height != $image_size[1] &&
    	(
    		($o_width == SMALL_THUMBNAIL_WIDTH && $o_height == SMALL_THUMBNAIL_HEIGHT) ||
    		($o_width == MEDIUM_THUMBNAIL_WIDTH && $o_height == MEDIUM_THUMBNAIL_HEIGHT) ||
    		($o_width == SMALL_IMAGE_WIDTH && $o_height == SMALL_IMAGE_HEIGHT) ||
    		($o_width == MEDIUM_IMAGE_WIDTH && $o_height == MEDIUM_IMAGE_HEIGHT)))
		{
			$src = tep_image_resample($src,$width,$height,$o_width,$o_height);
		}

		if ($just_info)
			return Array($width, $height, $src);
	else {
		
		$image = '<img src="' . tep_output_string($src) . '" alt="' . tep_output_string($alt) . '"';
		if (tep_not_null($alt)) {
			$image .= ' title=" ' . tep_output_string($alt) . ' "';
		}
		$image .= ' width="' . tep_output_string($width) . '" height="' . tep_output_string($height) . '"';
		if (tep_not_null($parameters)) $image .= ' ' . $parameters;
		    $image .= '>';
		    return $image;
	}
}
function tep_image_resample($src,$width,$height,$o_width,$o_height) {
	define(JPEGQUALITY, 90);
	define(ALLOWSQUASH,0.10);
	/*create thumbnail folder*/
	$test = preg_split('/\//', $src);
	$i=1;
	foreach ($test as $folder) {
		if ($i<sizeof($test)) {
		$i++;
		$create_folder .= $folder.'/';
		}
	}
	$create_folder .= 'thumbs/';
	if (!is_dir($create_folder)) {
		mkdir($create_folder, 0777);
	}
	/*create thumbnail folder*/
	if ($src=='') {
		return $src;
 	}
	$i = @getimagesize( $src );   // 1-gif (ignore), 2-jpeg, 3-png
	if (!( ($i[2] == 3) || ($i[2] ==2))) {
		return $src;
 	}
	if (filesize($src)>MAX_IMAGE_FILESIZE) {
		$src = 'images/foto/noimage.jpg';
		return $src;
 	}
	if (($scr_w > MAX_IMAGE_WIDTH) || ($scr_h > MAX_IMAGE_HEIGHT)) {
		$src = 'images/foto/noimage.jpg';
		return $src;
 	}
	//We need to add $width and $height to output name, if not small image width and height
	if (!(($o_width == SMALL_IMAGE_WIDTH) && ($o_height == SMALL_IMAGE_HEIGHT)))
		$file = preg_replace("/\.([A-Z]{2,4})$/i", '_'.$width.'x'.$height.".\\1", $src);
	else
		$file = $src;
		
	$file = preg_replace("/\/foto\/([^\/]+)$/i", "/foto/thumbs/\\1", $file);
	$file = preg_replace("/\/gallery\/(\w+)\/([^\/]+)$/i", "/gallery/$1/thumbs/\\2", $file);

	if (is_file( $file ) ) {
		return $file;
	}
	if (is_file( $file ) ) {
		return $file;
	}

	$scr_w	 =  $i[0];
	$scr_h	 = $i[1];
	if (($scr_w * $scr_h * $width * $height) == 0) {
		return $src;
 	}

	$howsquashed = ($width / $height * $scr_h / $scr_w);
	if (((1 / (1 + ALLOWSQUASH)) < $howsquashed) && ($howsquashed < (1 + ALLOWSQUASH))) $simpleway='true';
	$scalefactor = min($width/$scr_w, $height/$scr_h);
	$scaled_w	= (int)($scr_w * $scalefactor);
	$scaled_h	 = (int)($scr_h * $scalefactor);
	$offset_w	= max(0,round(($width - $scaled_w) / 2,0));
	$offset_h	 = max(0,round(($height - $scaled_h) / 2));
 	$dst = DIR_FS_CATALOG.$file;
   	$dstim = @imagecreatetruecolor ($width, $height);
	$background_color = imagecolorallocate ($dstim, 255, 255, 255);
	imagefilledrectangle($dstim, 0, 0, $width, $height, $background_color);
	if ( $i[2] == 2) {
		$srcim = @ImageCreateFromJPEG ($src); // open
	}
	elseif ( $i[2] == 3) {
		$srcim	 = @ImageCreateFromPNG ($src);
	}
	if ($simpleway == 'true') {
		imagecopyresampled ($dstim, $srcim, 0, 0, 0, 0, $width, $height, $scr_w, $scr_h);
	}
	else {
		$intim = @imagecreatetruecolor ($width, $height);
		imagecopyresampled ($intim, $srcim, $offset_w, $offset_h, 0, 0, $scaled_w, $scaled_h, $scr_w, $scr_h);
		imagecopy ( $dstim, $intim, $offset_w, $offset_h, $offset_w, $offset_h, $scaled_w, $scaled_h);
		imagedestroy ($intim);
	}
	if ( $i[2] == 2) {
		imagejpeg ($dstim , $dst , JPEGQUALITY);
	}
	elseif ( $i[2] == 3) {
		imagepng ($dstim , $dst);
	}
	imagedestroy ($srcim);
	imagedestroy ($dstim);
	return $file;                 // Use the newly resampled image
}
// end radders added

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function tep_image_submit($image, $alt = '', $parameters = '') {
    global $language;

    $image_submit = '<input type="image" src="' . tep_output_string(DIR_WS_LANGUAGES . $language . '/images/buttons/' . $image) . '" border="0" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) $image_submit .= ' title=" ' . tep_output_string($alt) . ' "';

    if (tep_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= '>';

    return $image_submit;
  }

////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $parameters = '') {
    global $language;

    return tep_image(DIR_WS_LANGUAGES . $language . '/images/buttons/' . $image, $alt, '', '', $parameters);
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

////
// Output a form
  function tep_draw_form($name, $action, $method = 'post', $parameters = '') {
    $form = '<form name="' . tep_output_string($name) . '" action="' . tep_output_string($action) . '" method="' . tep_output_string($method) . '"';

    if (tep_not_null($parameters)) $form .= ' ' . $parameters;

    $form .= '>';

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $parameters = 'maxlength="40"') {
    return tep_draw_input_field($name, $value, $parameters, 'password', false);
  }

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    $selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    if ( ($checked == true) || ( isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ( ($GLOBALS[$name] == 'on') || (isset($value) && (stripslashes($GLOBALS[$name]) == $value)) ) ) ) {
      $selection .= ' CHECKED';
    }

    if (tep_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= '>';

    return $selection;
  }

////
// Output a form checkbox field
  function tep_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
  }

////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $parameters);
  }

////
// Output a form textarea field
  function tep_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . tep_output_string($name) . '" wrap="' . tep_output_string($wrap) . '" cols="' . tep_output_string($width) . '" rows="' . tep_output_string($height) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= stripslashes($GLOBALS[$name]);
    } elseif (tep_not_null($text)) {
      $field .= $text;
    }

    $field .= '</textarea>';

    return $field;
  }

////
// Output a form hidden field
  function tep_draw_hidden_field($name, $value = '', $parameters = '') {
    $field = '<input type="hidden" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    } elseif (isset($GLOBALS[$name])) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }

////
// Hide form elements
  function tep_hide_session_id() {
    global $session_started, $SID;

    if (($session_started == true) && tep_not_null($SID)) {
      return tep_draw_hidden_field(tep_session_name(), tep_session_id());
    }
  }

////
// Output a form pull down menu
  function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . tep_output_string($name) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && isset($GLOBALS[$name])) $default = stripslashes($GLOBALS[$name]);

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' SELECTED';
      }
		if (isset($values[$i]['label'])) {
			$field .= ' label="'.$values[$i]['label'].'" ';
		}
      $field .= '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

////
// Creates a pull-down list of countries
  function tep_get_country_list($name, $selected = '', $parameters = '') {
    $countries_array = array(array('id' => '', 'text' => Translate('Maak uw keuze')));
    $countries = tep_get_countries();

    for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
      $countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
    }

    return tep_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
  }
    function GetCanonicalURL() {
  $parts = explode("&", $_SERVER['QUERY_STRING']);
  $cnt = count($parts);
  
  if ($cnt == 1 && basename($_SERVER['PHP_SELF']) === FILENAME_DEFAULT) //home page
     return tep_href_link('/', $args, 'NONSSL', false);

  $args = tep_get_all_get_params(array('action','currency', tep_session_name(),'sort','page'));
  return tep_href_link(basename($_SERVER['PHP_SELF']), $args, 'NONSSL', false);
}

function tep_draw_pull_down_menu_seo($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . tep_output_string($name) . '" id="cakesize" onChange="cakesz();"';
    if (tep_not_null($parameters)) $field .= ' ' . $parameters;
    $field .= '>';
    if (empty($default) && isset($GLOBALS[$name])) $default = stripslashes($GLOBALS[$name]);
    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' SELECTED';
      }
      $field .= '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';
    if ($required == true) $field .= TEXT_FIELD_REQUIRED;
    return $field;
  }
function convert_to_entities($text) {
	$ent = array(
		'©'=>'&#169;',
		'®'=>'&#174;',
		'²'=>'&#178;',
		'¼'=>'&#188;',
		'½'=>'&#189;',
		'¾'=>'&#190;',
		'À'=>'&#192;',
		'Á'=>'&#193;',
		'Â'=>'&#194;',
		'Ã'=>'&#195;',
		'Ä'=>'&#196;',
		'Å'=>'&#197;',
		'Æ'=>'&#198;',
		'Ç'=>'&#199;',
		'È'=>'&#200;',
		'É'=>'&#201;',
		'Ê'=>'&#202;',
		'Ë'=>'&#203;',
		'Ì'=>'&#204;',
		'Í'=>'&#205;',
		'Î'=>'&#206;',
		'Ï'=>'&#207;',
		'Ð'=>'&#208;',
		'Ñ'=>'&#209;',
		'Ò'=>'&#210;',
		'Ó'=>'&#211;',
		'Ô'=>'&#212;',
		'Õ'=>'&#213;',
		'Ö'=>'&#214;',
		'Ø'=>'&#216;',
		'Ù'=>'&#217;',
		'Ú'=>'&#218;',
		'Û'=>'&#219;',
		'Ü'=>'&#220;',
		'Ý'=>'&#221;',
		'Þ'=>'&#222;',
		'ß'=>'&#223;',
		'à'=>'&#224;',
		'á'=>'&#225;',
		'â'=>'&#226;',
		'ã'=>'&#227;',
		'ä'=>'&#228;',
		'å'=>'&#229;',
		'æ'=>'&#230;',
		'ç'=>'&#231;',		
		'è'=>'&#232;',
		'é'=>'&#233;',
		'ê'=>'&#234;',
		'ë'=>'&#235;',
		'ì'=>'&#236;',
		'í'=>'&#237;',
		'î'=>'&#238;',
		'ï'=>'&#239;',
		'ð'=>'&#240;',
		'ñ'=>'&#241;',
		'ò'=>'&#242;',
		'ó'=>'&#243;',
		'ô'=>'&#244;',
		'õ'=>'&#245;',
		'ö'=>'&#246;',
		'ø'=>'&#248;',
		'ù'=>'&#249;',
		'ú'=>'&#250;',
		'û'=>'&#251;',
		'ü'=>'&#252;',
		'ý'=>'&#253;',
		'þ'=>'&#254;',
		'ÿ'=>'&#255;'
	);
	$text = strtr($text, $ent); 
	return $text;
}
function str_replace_once($str_pattern, $str_replacement, $string){
	if (strpos($string, $str_pattern) !== false){
		$occurrence = strpos($string, $str_pattern);
		return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern));
	}
	return $string;
}
function transformText($text, $leesmeer) {
	$count = substr_count($text, '<readmore>');
	for ($i=1;$i<=$count;$i++) {
		$text = str_replace_once('<readmore>', '<div class="newsreadon'.$i.'">', $text);
		$text = str_replace_once('</readmore>', '</div><div class="readmore"><a class="readon'.$i.'" href="#">'.$leesmeer.'</a></div>', $text);
	}
	return $text;
}
function shorten_text($text, $length, $lines = 1) {
	$text = str_replace('<br />', '<br>', $text);
	$text = strip_tags($text, '<br>');
	if ($length > 0) {
		if (strlen($text) > $length) {
			$split_text = explode('<br>', $text);
			$new_text = '';
			for ($i=0;$i<$lines;$i++) {
				$new_text .= $split_text[$i].'<br />';
			}
			$new_text = substr($new_text, 0, -6);
			$new_length = $length + ($lines*6)-6; //add <br /> length
			if (strlen($new_text) > $new_length) {
				$new_text = substr($new_text, 0, $new_length);
				while (substr($new_text, -6) == '<br />') {
					$new_text = substr($new_text, 0, -6);
				}
				$text_space = strrpos($new_text, ' ');
				$text_komma = strrpos($new_text, ',');
				if ($text_space > $text_komma) {
					return substr($new_text, 0, $text_space).'...';
				} else {
					return substr($new_text, 0, $text_komma).'...';
				}
			} else {
				return $new_text;
			}
		} else {
			return $text;
		}
	} else {
		$split_text = explode('<br>', $text);
		$new_text = '';
		for ($i=0;$i<$lines;$i++) {
			$new_text .= $split_text[$i].'<br />';
		}
		$new_text = substr($new_text, 0, -6);
		return $new_text;
	}
}
function get_product_attributes($products_id) {
	global $languages_id, $currencies;
	$string = '';
	$products_options_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='".$products_id."' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' order by popt.products_options_name");
	if (tep_db_num_rows($products_options_query) > 0) {
		$string .= '<div class="product-attributes">';
		while ($products_options_name = tep_db_fetch_array($products_options_query)) {
			$products_options_array = array();
			$products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . $products_id . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "'");
			while ($products_options = tep_db_fetch_array($products_options_query)) {
				$products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name']);
				if ($products_options['options_values_price'] != '0') {
					$products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . $products_options['price_prefix'] . $currencies->display_price($products_options['options_values_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) .') ';
				}
			}
			if (isset($cart->contents[$_GET['products_id']]['attributes'][$products_options_name['products_options_id']])) {
				$selected_attribute = $cart->contents[$_GET['products_id']]['attributes'][$products_options_name['products_options_id']];
			} else {
				$selected_attribute = false;
			}
			$string .= '<div class="product-attribute">';
			$string .= $products_options_name['products_options_name'].': ';
			if (count($products_options_array) > 1) {
				$string .= tep_draw_pull_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute);
			} else {
				$string .= str_replace(')', '', str_replace('(', '',$products_options_array[0]['text']));
				$string .= tep_draw_hidden_field('id[' . $products_options_name['products_options_id'] . ']', $products_options_array[0]['id']);
			}
			$string .= '</div>';
		}
		$string .= '</div>';
	}
	return $string;
}
function tep_get_url_data() {
	global $_SERVER, $REQUEST_TYPE, $_GET, $languages_id, $cPath;
	$url_data = array();
	if ($REQUEST_TYPE == 'SSL') {
		$comparison_array = explode('/', HTTPS_SERVER . DIR_WS_HTTPS_CATALOG, 4);
	} else {
		$comparison_array = explode('/', HTTP_SERVER . DIR_WS_HTTP_CATALOG, 4);
	}
	$comparison = $comparison_array[3];
	$parts = explode('?', str_replace($comparison, '', $_SERVER['REQUEST_URI']), 2);
	if (sizeof($parts) == 2) {
		$parameters = explode('&', $parts[1]);
		foreach ($parameters as $pair) {
			$pieces = explode('=', $pair);
			$_GET[$pieces[0]] = $pieces[1];
			$url_data['get'][$pieces[0]] = $pieces[1];
		}
	}
	$get_seo_item_query = tep_db_query("select categories_id, products_id, manufacturers_id, infopages_id from seo_urls where url = '" . $parts[0] . "' AND language_id = '".(int)$languages_id."'");
	if (tep_db_num_rows($get_seo_item_query)>0) {
		$get_seo_item = tep_db_fetch_array($get_seo_item_query);
		if ((int)$get_seo_item['categories_id'] > 0) { //categorie
			$category_query = tep_db_query("select categories_id, parent_id from categories where categories_id='" . $get_seo_item['categories_id'] . "'");
			$category_array = tep_db_fetch_array($category_query);
			$cPath = tep_get_full_cpath($category_array['categories_id']);
			$current_category_id = $category_array['categories_id'];
			$url_data['get']['cPath'] = $cPath;
			$url_data['page'] = FILENAME_DEFAULT;
		} else if ((int)$get_seo_item['products_id'] > 0) { //product
			$product_query = tep_db_query("select pd.products_id, pd.products_name from products_description pd, products_to_categories p2c, products p where p.products_id = pd.products_id and p.products_status = '1' and pd.products_id=p2c.products_id and pd.products_id='" . $get_seo_item['products_id'] . "'" . $parent_where_string);
			$product_array = tep_db_fetch_array($product_query);
			$cPath = tep_get_product_path($product_array['products_id']);
			$url_data['get']['products_id'] = $product_array['products_id'];
			$url_data['page'] = FILENAME_PRODUCT_INFO;
		} else if ((int)$get_seo_item['manufacturers_id'] > 0) { //manufacturer
			if (strstr($get_seo_item['manufacturers_id'], '_')) {
				$seo_item_ids = explode('_', $get_seo_item['manufacturers_id']);
				$manufacturers_id = $seo_item_ids[0];
			} else {
				$manufacturers_id = $get_seo_item['manufacturers_id'];
			}
			$manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from manufacturers where manufacturers_id='" . $manufacturers_id . "'");
			$manufacturers_array = tep_db_fetch_array($manufacturers_query);
			if (isset($seo_item_ids[1])) {
				$filter_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.(int)$seo_item_ids[1].'" AND language_id = "'.(int)$languages_id.'"');
				$filter = tep_db_fetch_array($filter_query);
				$_GET['filter_id'] = $seo_item_ids[1];
			}
			$url_data['get']['manufacturers_id'] = $manufacturers_array['manufacturers_id'];
			$url_data['page'] = FILENAME_DEFAULT;
		} else if ((int)$get_seo_item['infopages_id'] > 0) {
			$test_query = tep_db_query("select it.infopages_id from infopages i, infopages_text it where i.infopages_id = it.infopages_id AND i.infopages_id = '" . $get_seo_item['infopages_id'] . "'");
			$infopages_array = tep_db_fetch_array($test_query);
			$url_data['get']['page'] = $infopages_array['infopages_id'];
			$url_data['page'] = FILENAME_INFOPAGE;
		}
	}
	return $url_data;
}
?>