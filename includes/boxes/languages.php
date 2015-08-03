<?php
/*
  $Id: languages.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
  if (!isset($lng) || (isset($lng) && !is_object($lng))) {
    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language;
  }

  $languages_string = '';
  reset($lng->catalog_languages);
    $languages_string .= '<ul>';
  while (list($key, $value) = each($lng->catalog_languages)) {
    $languages_string .= '<li><a href="' . tep_href_link(basename($_SERVER['PHP_SELF']), tep_get_all_get_params(array('language', 'currency')) . 'language=' . $key, 'NONSSL', $add_session_id = true, $value['id']) . '" title="'.$value['name'].'">'.strtoupper($value['code']).'</a></li>';
  }
    $languages_string .= '</ul>';
//die(print('<pre>').print_r($_SERVER).print('</pre>'));
  echo $languages_string;
?>