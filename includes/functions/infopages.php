<?php

function get_infopages_tags ($id) {
    $tag_seperator = ';';

    $query = "SELECT tag_ids FROM infopages WHERE infopages_id = " . $id;
    $resource = tep_db_query($query);
    $temp = tep_db_fetch_array($resource);
    $tag_ids = $temp['tag_ids'];

    $tags = explode($tag_seperator, $tag_ids);

    return $tags;
}

function set_infopages_tags ($tags, $id) {
    $tag_seperator = ';';

    $tag_ids = implode ($tag_seperator, $tags);
    $query = "UPDATE infopages SET tag_ids = '" . $tag_ids . "' WHERE infopages_id = " . $id;
    $resource = tep_db_query($query);
}


function tep_get_infopages_title ($id) {
	global $languages_id;
	$data_query = tep_db_query("SELECT it.infopages_title FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.infopages_id = ".$id." AND it.language_id = ".(int)$languages_id." ORDER BY i.date_added DESC");
	$data = tep_db_fetch_array($data_query);
	/*language fallback*/
	if ((LANGUAGE_FALLBACK=='true') && ($data['infopages_title']=='')) {
		$language_fallback_query = tep_db_query("SELECT it.infopages_title FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.infopages_id = ".$id." AND it.language_id = 1 ORDER BY i.date_added DESC");
		$language_fallback = tep_db_fetch_array($language_fallback_query);
		$data['infopages_title'] = $language_fallback['infopages_title'];
	}
	/*language fallback*/
	$text = $data['infopages_title'];
	return $text;
}
function tep_get_infopages_preview ($id) {
	global $languages_id;
	$data_query = tep_db_query("SELECT it.infopages_preview FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.infopages_id = ".$id." AND it.language_id = ".(int)$languages_id." ORDER BY i.date_added DESC");
	$data = tep_db_fetch_array($data_query);
	/*language fallback*/
	if ((LANGUAGE_FALLBACK=='true') && ($data['infopages_preview']=='')) {
		$language_fallback_query = tep_db_query("SELECT it.infopages_preview FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.infopages_id = ".$id." AND it.language_id = 1 ORDER BY i.date_added DESC");
		$language_fallback = tep_db_fetch_array($language_fallback_query);
		$data['infopages_preview'] = $language_fallback['infopages_preview'];
	}
	/*language fallback*/
	$text = tep_infopage_format($data['infopages_preview']);
	return $text;
}
function tep_get_infopages_description ($id) {
	global $languages_id;
	$data_query = tep_db_query("SELECT it.infopages_description FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.infopages_id = ".$id." AND it.language_id = ".(int)$languages_id." ORDER BY i.date_added DESC");
	$data = tep_db_fetch_array($data_query);
	/*language fallback*/
	if ((LANGUAGE_FALLBACK=='true') && ($data['infopages_description']=='')) {
		$language_fallback_query = tep_db_query("SELECT it.infopages_description FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.infopages_id = ".$id." AND it.language_id = 1 ORDER BY i.date_added DESC");
		$language_fallback = tep_db_fetch_array($language_fallback_query);
		$data['infopages_description'] = $language_fallback['infopages_description'];
	}
	/*language fallback*/
	$text = tep_infopage_format($data['infopages_description']);
	return $text;
}
function tep_get_bb_code ($text) {
	$text = str_replace('[STORE_NAME]', STORE_NAME, $text);
	return $text;
}
function tep_infopage_format ($text) {
	$text = tep_get_bb_code($text);
	$text = tep_infopage_to_seourls($text);
	$text = tep_get_gallery_in_infopage($text);
	$text = tep_get_module_in_infopage($text);
	return $text;
}
function tep_get_infopages_type ($id) {
    $data_query = tep_db_query("select type from infopages where infopages_id = '" . (int)$id . "'");
	$data = tep_db_fetch_array($data_query);
	$output = $data['type'];
	return $output;
}
function tep_get_infopages_display ($id) {
    $data_query = tep_db_query("select display from infopages where infopages_id = '" . (int)$id . "'");
	$data = tep_db_fetch_array($data_query);
	$output = $data['display'];
	return $output;
}
function tep_get_infopages_date_added ($id) {
    $data_query = tep_db_query("select date_added from infopages where infopages_id = '" . (int)$id . "'");
	$data = tep_db_fetch_array($data_query);
	$output = $data['date_added'];
	return $output;
}
function tep_infopage_to_seourls ($text) {
	$infopage_text = $text;
	//$infopage_text = preg_replace('<a href="#(\w+)">', 'a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$_GET['page']).'#$1"', $infopage_text);
	
	
	$i_linked_id = array();
	preg_match_all('/"infopage.php[^*]page=(\w+)"/', $infopage_text, $i_linked_id);
	foreach ($i_linked_id[0] as $key => $value)
	{
		$i_linked_id[0][$key] = '/'.str_replace('?', '[^*]', $value).'/';
	}
	foreach ($i_linked_id[1] as $key => $value)
	{
		$i_linked_id[1][$key] = tep_href_link(FILENAME_INFOPAGE, 'page='.$value);
	}
	$infopage_text = preg_replace($i_linked_id[0], $i_linked_id[1], $infopage_text);
	
	$o_i_linked_id = array();
	preg_match_all('/"infopage.php[^*]pages_id=(\w+)"/', $infopage_text, $o_i_linked_id);
	foreach ($o_i_linked_id[0] as $key => $value)
	{
		$o_i_linked_id[0][$key] = '/'.str_replace('?', '[^*]', $value).'/';
	}
	foreach ($o_i_linked_id[1] as $key => $value)
	{
		$o_i_linked_id[1][$key] = tep_href_link(FILENAME_INFOPAGE, 'page='.$value);
	}
	$infopage_text = preg_replace($o_i_linked_id[0], $o_i_linked_id[1], $infopage_text);
	
	$p_linked_id = array();
	preg_match_all('/product_info.php[^*]products_id=(\w+)/', $infopage_text, $p_linked_id);
	foreach ($p_linked_id[0] as $key => $value)
	{
		$p_linked_id[0][$key] = '/'.str_replace('?', '[^*]', $value).'/';
	}
	foreach ($p_linked_id[1] as $key => $value)
	{
		$p_linked_id[1][$key] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$value);
	}
	$infopage_text = preg_replace($p_linked_id[0], $p_linked_id[1], $infopage_text);
	
	return $infopage_text;
}
function tep_get_module_in_infopage ($text) {
	global $languages_id;
	if (strstr($text, '[box]')) {
		$text = str_replace('<p>', '', $text);
		$text = str_replace('</p>', '', $text);
		$text = trim($text);
		$content = '';
		foreach (explode('[box]', $text) as $group)
		{
			if (!empty($group))
			{
				if (!strstr($group, '[/box]')) {
					$content .= $group;
				} else {
					foreach (explode('[/box]', $group) as $sub_group)
					{
						$thisblockcontent = '';
						if (!strstr($sub_group, '.php')) {
							$content .= $sub_group;
						} else {
							ob_start();
							if ((USE_CACHE == 'true') && empty($SID)) {
								if ($sub_group=='categories.php') {
									echo tep_cache_categories_box();
								} elseif ($sub_group=='manufacturers.php') {
									echo tep_cache_manufacturers_box();
								} else {
									include('includes/boxes/'.$sub_group);
								}
							} else {
								include('includes/boxes/'.$sub_group);
							}  
							$thisblockcontent = ob_get_contents();
							ob_end_clean();
						}
						if (!empty($thisblockcontent)) {
							$content .= '<div class="box '.str_replace('.php', '', $sub_group).'">'.$thisblockcontent.'</div>';
						}
					}
				}
			}
		}
		$text = $content;
	} else {
		$text = $text;
	}
	echo $text;
}
function tep_get_gallery_in_infopage ($text)
{
	preg_match_all('/\[gallery=[\w]*/', $text, $matches);
	foreach($matches as $key=>$value) {
		$matches = $value;
	}
	$functions = array();
	$image_com = '';
	foreach($matches as $key=>$value) {
		$new_value = explode('=', $value);
		$new_value = $new_value[1];		
		$dir = 'images/gallery/'.$new_value;
		if ($handle = opendir($dir)) {
			$images = Array();
			$_images ='';
			$image_com[$new_value] .= '<div class="gallery">';
			while (false !== ($file = readdir($handle))) {
				$matches = array();
				if (preg_match_all('![a-z0-9\-\.\/]+\.(?:jpe?g|png|gif|jpg)!Ui' , $file , $matches)) {
					$images[] = $matches[0][0];
				}
			}
			foreach ($images as $image)	{
				$image_com[$new_value] .= '<a href="'.DIR_WS_CATALOG.'images/gallery/'.$new_value.'/'.$image.'" rel="'.$new_value.'">'.tep_image(DIR_WS_IMAGES.'gallery/'.$new_value.'/'.$image, '', SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT).'</a>'; 
			}
			$image_com[$new_value] .= '</div><div class="clear"></div>';
		}
		$text = str_replace($value.']', $image_com[$new_value], $text);
   }
   return $text;
}
?>