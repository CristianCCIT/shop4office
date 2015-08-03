<?php
/*
  $Id: html_output.php,v 1.29 2003/06/25 20:32:44 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

////
// The HTML href link wrapper function
  function tep_href_link($page = '', $parameters = '', $connection = 'NONSSL') {
    if ($page == '') {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>Function used:<br><br>tep_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')</b>');
    }
    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_ADMIN;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == 'true') {
        $link = HTTPS_SERVER . DIR_WS_ADMIN;
      } else {
        $link = HTTP_SERVER . DIR_WS_ADMIN;
      }
    } else {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL<br><br>Function used:<br><br>tep_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')</b>');
    }
    if ($parameters == '') {
      $link = $link . $page . '?' . SID;
    } else {
      $link = $link . $page . '?' . $parameters . '&' . SID;
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

    return $link;
  }

  function tep_catalog_href_link($page = '', $parameters = '', $connection = 'NONSSL') {
    if ($connection == 'NONSSL') {
      $link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL_CATALOG == 'true') {
        $link = HTTPS_CATALOG_SERVER . DIR_WS_CATALOG;
      } else {
        $link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL<br><br>Function used:<br><br>tep_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')</b>');
    }
    if ($parameters == '') {
      $link .= $page;
    } else {
      $link .= $page . '?' . $parameters;
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

    return $link;
  }

  function tep_href_sef_link($page = '', $parameters = '', $connection = 'NONSSL', $search_engine_safe = true) {
    global $request_type, $session_started, $SID;

    if (!tep_not_null($page)) {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>');
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</b><br><br>');
    }

    $separator = '?';
    if (tep_not_null($parameters)) {
      $product_name = '';
      switch ($page) {
        case FILENAME_PRODUCT_INFO:
        case FILENAME_INFOPAGE:
        case FILENAME_DEFAULT:
          $manufacturer_name = '';
          $product_name = '';
          $new_parameter_list = array();
          $cPath_list = array();
          foreach (explode('&', $parameters) as $pair) {
            global $languages_id;
            $pair_array = explode('=', $pair);
            switch ($pair_array[0]) {
              case 'action':
                $link .= $page . '?' . tep_output_string($parameters);
                $separator = '&';
                break 3;
              case 'cPath':
                $parent_id = 0;
                foreach (explode('_', $pair_array[1]) as $category_id) {
                  $category_name_query = tep_db_query("select cd.categories_name from categories_description cd, categories c where cd.categories_id=c.categories_id and cd.categories_id='" . (int)$category_id . "' and cd.language_id='" . (int)$languages_id . "' and c.parent_id='" . (int)$parent_id . "'");
                  if ($category_name_array = tep_db_fetch_array($category_name_query)) {
                    $cPath_list[]= str_replace("+", "-", strtolower(urlencode($category_name_array['categories_name'])));
                    $parent_id = $category_id;
                  }
                }
                break;
              case 'page':
			  	$parent_id = 0;
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
						  $p_infopage_parent_name = str_replace('-', '+', $p_infopage_parent_name_array['infopages_title']);
						}
					}
                }
                $infopage_parent_name_query = tep_db_query("select infopages_title from infopages_text where infopages_id='" . (int)$infopage_parent_link . "' and language_id='" . (int)$languages_id . "'");
                if ($infopage_parent_name_array = tep_db_fetch_array($infopage_parent_name_query)) {
                  $infopage_parent_name = str_replace('-', '+', $infopage_parent_name_array['infopages_title']);
                }
                $infopage_name_query = tep_db_query("select infopages_title from infopages_text where infopages_id='" . (int)$pair_array[1] . "' and language_id='" . (int)$languages_id . "'");
                if ($infopage_array = tep_db_fetch_array($infopage_name_query)) {
                  $infopage_name = str_replace('-', '+', $infopage_array['infopages_title']);
                }
                break;
              case 'products_id':
                $product_name_query = tep_db_query("select products_name from products_description where products_id='" . (int)$pair_array[1] . "' and language_id='" . (int)$languages_id . "'");
                if ($product_name_array = tep_db_fetch_array($product_name_query)) {
                  $product_name = $product_name_array['products_name'];
                }
                break;
              case 'manufacturers_id':
                $manufacturer_name_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id='" . (int)$pair_array[1] . "'");
                if ($manufacturer_array = tep_db_fetch_array($manufacturer_name_query)) {
                  $manufacturer_name = $manufacturer_array['manufacturers_name'];
                }
              case '':
                break;
              default:
                if (tep_not_null($pair)) $new_parameter_list[]= $pair;
            }
          }
          if (tep_not_null($manufacturer_name)) {
            $cPath_list[]= str_replace("+", "-", strtolower(urlencode($manufacturer_name)));
          }
          if (tep_not_null($infopage_name)) {
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
          if (tep_not_null($product_name)) {
            $cPath_list[]= str_replace("+", "-", strtolower(urlencode($product_name)));
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

    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);

      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);

      $separator = '?';
    }

    if (isset($_sid)) {
      $link .= $separator . $_sid;
    }

    return $link;
  }
////
// The HTML image wrapper function
/**
*   function tep_image($src, $alt = '', $width = '', $height = '', $params = '') {
*     $image = '<img src="' . $src . '" border="0" alt="' . $alt . '"';
*     if ($alt) {
*       $image .= ' title=" ' . $alt . ' "';
*     }
*     if ($width) {
*       $image .= ' width="' . $width . '"';
*     }
*     if ($height) {
*       $image .= ' height="' . $height . '"';
*     }
*     if ($params) {
*       $image .= ' ' . $params;
*     }
*     $image .= '>';

*     return $image;
*   }
*/
////
// The HTML image wrapper function
  function tep_image($src, $alt = '', $width = '', $height = '', $params = '') {
// BOF: Radders - Automatic Thumbnail Creator // Modded by MaxiDVD 22/01/2004 MS2-3 Admin Check.
  $src = tep_use_resampled_image($src,$width,$height);

    $image = '<img src="' . $src . '" border="0" alt="' . $alt . '"';
    if ($alt) {
      $image .= ' title=" ' . $alt . ' "';
    }
    if ($width) {
      $image .= ' width="' . $width . '"';
    }
    if ($height) {
      $image .= ' height="' . $height . '"';
    }
    if ($params) {
      $image .= ' ' . $params;
    }
    $image .= '>';

    return $image;
  }

function tep_use_resampled_image($src,&$width,&$height) {

        if ($src=='') {
                return $src;
         }
        if(! ($i = @getimagesize( DIR_FS_DOCUMENT_ROOT. '/' .$src ))) {
                return $src; // can amend to work with other images
         }   // 1-gif (ignore), 2-jpeg, 3-png also ignore any admin images

        if ($width && $height) {
        	if ($i[0] > $i[1])
        	{
        		$ratio = $i[1]/ $i[0];
        		$height = $height*$ratio;
					}
					else
					{
						$ratio = $i[0]/$i[1];
						$width = $width*$ratio;
					}
				}
        if (empty($width) && tep_not_null($height)) {
          $ratio = $height / $i[1];
          $width = $image_size[0] * $ratio;
        } elseif (tep_not_null($width) && empty($height)) {
          $ratio = $width / $i[0];
          $height = $image_size[1] * $ratio;
        } elseif (empty($width) && empty($height)) {
          $width = $i[0];
          $height = $i[1];
        }


        if (!(($width == SMALL_IMAGE_WIDTH) && ($height == SMALL_IMAGE_HEIGHT))) {
                return $src; // can amend to work with other images
         }
        if (!( ($i[2] == 3) || ($i[2] ==2))) {
                return $src;
         }
        $file = eregi_replace( '\.([a-z]{3,4})$', "-{$width}x{$height}.\\1", $src );  // name of resampled image
        if (is_file( DIR_FS_DOCUMENT_ROOT . $file ) ) {
                return $file;
        }
return $src;
}
// EOF: Radders - Automatic Thumbnail Creator // Modded by MaxiDVD 22/01/2004 MS2-3 Admin Check.


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
// Draw a 1 pixel black line
  function tep_black_line() {
    return tep_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '1');
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $params = '') {
    global $language;

    return tep_image(DIR_WS_LANGUAGES . $language . '/images/buttons/' . $image, $alt, '', '', $params);
  }

////
// javascript to dynamically update the states/provinces list when the country is changed
// TABLES: zones
  function tep_js_zone_list($country, $form, $field) {
    $countries_query = tep_db_query("select distinct zone_country_id from " . TABLE_ZONES . " order by zone_country_id");
    $num_country = 1;
    $output_string = '';
    while ($countries = tep_db_fetch_array($countries_query)) {
      if ($num_country == 1) {
        $output_string .= '  if (' . $country . ' == "' . $countries['zone_country_id'] . '") {' . "\n";
      } else {
        $output_string .= '  } else if (' . $country . ' == "' . $countries['zone_country_id'] . '") {' . "\n";
      }

      $states_query = tep_db_query("select zone_name, zone_id from " . TABLE_ZONES . " where zone_country_id = '" . $countries['zone_country_id'] . "' order by zone_name");

      $num_state = 1;
      while ($states = tep_db_fetch_array($states_query)) {
        if ($num_state == '1') $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . PLEASE_SELECT . '", "");' . "\n";
        $output_string .= '    ' . $form . '.' . $field . '.options[' . $num_state . '] = new Option("' . $states['zone_name'] . '", "' . $states['zone_id'] . '");' . "\n";
        $num_state++;
      }
      $num_country++;
    }
    $output_string .= '  } else {' . "\n" .
                      '    ' . $form . '.' . $field . '.options[0] = new Option("' . TYPE_BELOW . '", "");' . "\n" .
                      '  }' . "\n";

    return $output_string;
  }

////
// Output a form
  function tep_draw_form($name, $action, $parameters = '', $method = 'post', $params = '') {
    $form = '<form name="' . tep_output_string($name) . '" action="';
    if (tep_not_null($parameters)) {
      $form .= tep_href_link($action, $parameters);
    } else {
      $form .= tep_href_link($action);
    }
    $form .= '" method="' . tep_output_string($method) . '"';
    if (tep_not_null($params)) {
      $form .= ' ' . $params;
    }
    $form .= '>';

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (isset($GLOBALS[$name]) && ($reinsert_value == true) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    } elseif (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $required = false) {
    $field = tep_draw_input_field($name, $value, 'maxlength="40"', $required, 'password', false);

    return $field;
  }

////
// Output a form filefield
  function tep_draw_file_field($name, $required = false) {
    $field = tep_draw_input_field($name, '', '', $required, 'file');

    return $field;
  }

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $compare = '') {
    $selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    if ( ($checked == true) || (isset($GLOBALS[$name]) && is_string($GLOBALS[$name]) && ($GLOBALS[$name] == 'on')) || (isset($value) && isset($GLOBALS[$name]) && (stripslashes($GLOBALS[$name]) == $value)) || (tep_not_null($value) && tep_not_null($compare) && ($value == $compare)) ) {
      $selection .= ' CHECKED';
    }

    $selection .= '>';

    return $selection;
  }

////
// Output a form checkbox field
  function tep_draw_checkbox_field($name, $value = '', $checked = false, $compare = '') {
    return tep_draw_selection_field($name, 'checkbox', $value, $checked, $compare);
  }

////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $compare = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $compare);
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
    } elseif (isset($GLOBALS[$name]) && is_string($GLOBALS[$name])) {
      $field .= ' value="' . tep_output_string(stripslashes($GLOBALS[$name])) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
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
      $field .= ' '.$values[$i]['params'];

      $field .= '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }
function Translate($text) {
    global $languages_id, $_SERVER;
    $translation_query = tep_db_query('select `translation`, `pages`, `count` from `translation` where `text` = "' . $text . '" and language_id = "' . (int)$languages_id . '"');
    $translation = tep_db_fetch_array($translation_query);
    if (tep_db_num_rows($translation_query) > 0) {

        return $translation['translation'];

    } else {
        // The text is not found in the supplied language. This is the fallback clause.

        // Check whether the standard text has been entered at all in the DB.
        $sql = "SELECT code FROM languages WHERE languages_id = ".$languages_id;
        $query = tep_db_query($sql);
        $result = tep_db_fetch_array($query);
        $language = $result['code'];

        /*echo '<pre>';
        print_r ($result);
        echo '</pre>';

        die();*/

        $sql = "SELECT * FROM translation WHERE `text` = '" . $text . "'";
        $query = tep_db_query($sql);

        if (tep_db_num_rows($query) > 0 ) {
            // It has already been entered into the database. Just not in supplied language.
            translateRequest($text, $language);
        } else {
            // The text has not yet been entered into the database. Place a general translation request.
            translateRequest($text);
        }

        return $text;
    }
}


/**
 * This is a function that places a translationRequest for a specified text
 * and language in the administrator panel. It is fired every time a text is
 * loaded up via Translate() but isn't translated due to a missing translation.
 *
 * Tables:
 * translation_todo
 *
 * @param $text
 *      The text that should be requested for translation
 * @param $lang
 *      The language that is missing
 */
function translateRequest($text, $lang = 'all') {

    $text = tep_db_input ($text);

    // Check if the request hasn't been made yet:

    $sql = "SELECT * FROM translation_request WHERE request_text = '".$text. "' AND language = '".$lang."'";
    $query = tep_db_query($sql);
    if (tep_db_num_rows($query) == 0) {

        if ($lang == 'all') {
            // Translation has not been initialized.

            $sql = "INSERT INTO translation_request (request_text, language) VALUES ('".$text."', 'all')";
            $query = tep_db_query($sql);

            if ($query) {  } else { die ('A mysql-error occured while entering the translation request into the database. Offical error description: ' . mysql_error()); }


        } else {
            // Only for a selected language.

            $sql = "INSERT INTO translation_request (request_text, language) VALUES ('".$text."', '".$lang."')";
            $query = tep_db_query($sql);
            if ($query) {  } else { die ('A mysql-error occured while entering the translation request into the database. Offical error description: ' . mysql_error()); }

        }
    } else {

        // Ignore requests that have already been made.

    }
}
?>