<?php
$blocks_query = tep_db_query('SELECT * FROM blocks WHERE row != 0 AND col != 0');
$all_blocks = array();
$maxRow = 0;
$maxCol = 0;
while ($block = tep_db_fetch_array($blocks_query)) {
	if ($maxRow < $block['row']) $maxRow = $block['row'];
	if ($maxCol < $block['col']) $maxCol = $block['col'];
	$all_blocks[$block['row']][$block['col']] = array('name' => $block['name'], 'link' => $block['link'], 'height' => $block['height'], 'width' => $block['width']);
}
echo '<ul id="shortList_links">';
for ($i=1;$i<=$maxRow;$i++) {
	for ($j=1;$j<=$maxCol;$j++) {
		if (isset($all_blocks[$i][$j])) {
			if ($i == $maxRow) {
				echo '<li class="last">';
			} else {
				echo '<li>';
			}
			if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $all_blocks[$i][$j]['link'])) {
				$page_id = explode('_', $all_blocks[$i][$j]['link']);
				$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$page_id[1].'" AND language_id = "'.(int)$languages_id.'"');
				$infopage_name = tep_db_fetch_array($infopage_name_query);
				$name = $infopage_name['infopages_title'];
				echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'">- '.$name.'</a></li>';
			} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $all_blocks[$i][$j]['link'])) {
				$page_id = explode('_', $all_blocks[$i][$j]['link']);
				$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "'.(int)$languages_id.'"');
				$categorie_name = tep_db_fetch_array($categorie_name_query);
				$name = $categorie_name['categories_name'];
				echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'">- '.$name.'</a></li>';
			} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $all_blocks[$i][$j]['link'])) {
				$page_id = explode('_', $all_blocks[$i][$j]['link']);
				$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "'.(int)$languages_id.'"');
				$product_name = tep_db_fetch_array($product_name_query);
				$name = $categorie_name['products_name'];
				echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'">- '.$name.'</a></li>';
			} else {
				if (!empty($all_blocks[$i][$j]['name'])) {
					$name = $all_blocks[$i][$j]['name'];
				}
				echo '<a href="'.tep_href_link($all_blocks[$i][$j]['link']).'" title="'.$name.' - '.STORE_NAME.'">- '.Translate($name).'</a>';
			}
		} else {
			echo '<li class="shortList_empty">&nbsp;</li>';
		}
	}
}
echo '</ul>';
?>