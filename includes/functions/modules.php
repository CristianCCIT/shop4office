<?php
function tep_get_module ($block, $replace_space=true) {
	global $languages_id, $sts, $currencies, $cart;
	$page = str_replace(DIR_WS_HTTP_CATALOG, '', $_SERVER['PHP_SELF']);
	if ( ($page=='index.php') && (isset($_GET['cPath'])||isset($_GET['manufacturers_id'])) ) {
		$page = 'blablabla';																					   
	}
	$limit_query = " AND (m.elements LIKE '%;i_".$_GET['page'].";%' OR m.elements LIKE '%;p_".$_GET['products_id'].";%' OR m.elements LIKE '%;".$page.";%' OR m.elements = ';' OR m.elements = '')";
	$get_module_query = tep_db_query("SELECT m.modules_id, mt.modules_title, mt.modules_description FROM modules m JOIN modules_text mt USING (modules_id) WHERE m.modules_status = '1'".$limit_query." AND m.block = '".$block."' AND mt.language_id = '".(int)$languages_id."' ORDER BY m.sort_order");
	while ($get_module = tep_db_fetch_array($get_module_query)) {
		/*language fallback*/
		if ((LANGUAGE_FALLBACK=='true') && ($get_module['modules_description']=='')) {
			$language_fallback_query = tep_db_query("select modules_description from modules_text where modules_id = '" . (int)$get_module['modules_id'] . "' and language_id = '1'");
			$language_fallback = tep_db_fetch_array($language_fallback_query);
			$get_module['modules_description'] = $language_fallback['modules_description'];
		}
		/*language fallback*/
		if ($replace_space==true) {
			$get_module['modules_description'] = str_replace('&nbsp;', ' ', $get_module['modules_description']);
		}
		if ($get_module['modules_description']=='<br />') {
			$get_module['modules_description']='';
		}
		$text = tep_infopage_to_seourls($get_module['modules_description']);
		if (strstr($text, '[box]')) {
			$text = str_replace('<p>', '', $text);
			$text = str_replace('</p>', '', $text);
			$text = trim($text);
			preg_match_all('/\[box\]([^\[]*)\[\/box\]/',$text,$matches);
			foreach($matches[0] as $modulekey=>$modulevalue) {
				$content = '';
				ob_start();
				if ((USE_CACHE == 'true') && empty($SID)) {
					if ($matches[1][$modulekey]=='categories.php') {
						echo tep_cache_categories_box();
					} elseif ($matches[1][$modulekey]=='manufacturers.php') {
						echo tep_cache_manufacturers_box();
					} else {
						include(DIR_FS_CATALOG.'includes/boxes/'.$matches[1][$modulekey]);
					}
				} else {
					include(DIR_FS_CATALOG.'includes/boxes/'.$matches[1][$modulekey]);
				}  
				$thisblockcontent = ob_get_contents();
				ob_end_clean();
				if ($matches[1][$modulekey]=='compare.php') {
					if ((PRODUCT_COMPARE=='true') && (($_GET['cPath']) || (strstr($_SERVER['PHP_SELF'], FILENAME_PRODUCT_INFO)))) {
						$content = $thisblockcontent;
					} else {
						$content = '';
					}
				} else {
					$content = '<div class="box '.str_replace('.php', '', $matches[1][$modulekey]).'">'.$thisblockcontent.'</div>';
				}
				$text = str_replace($modulevalue, $content, $text);
			}
		} else {
			if ($text!='') {
			$text = '<div class="box '.tep_get_module_class($get_module['modules_id']).'">'.$text.'</div>';
			}
		}
		echo $text;
	}
}
function tep_get_module_class($modules_id) {
	$get_module_name_query = tep_db_query("SELECT modules_title FROM modules_text WHERE modules_id = '".$modules_id."' AND language_id = '1'");
	$get_module_name = tep_db_fetch_array($get_module_name_query);
	$title = ClassName(strtolower($get_module_name['modules_title']));
	return $title;
}
?>