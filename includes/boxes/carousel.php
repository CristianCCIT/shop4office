<?php
$banners_query = tep_db_query('SELECT it.infopages_title, it.infopages_preview, it.infopages_description, it.infopages_banner, it.infopages_id, i.linkedpage, i.custom_link FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND it.language_id = "'.(int)$languages_id.'" AND i.type="banner" AND (i.date_start IS NULL OR i.date_start < NOW()) AND (i.date_expires IS NULL OR i.date_expires > NOW()) AND i.infopages_status = "1" ORDER BY i.sort_order asc');
if (tep_db_num_rows($banners_query) > 0) {
	echo '<div class="carousel">';
	while ($banners = tep_db_fetch_array($banners_query)) {
		if (file_exists(DIR_FS_CATALOG.DIR_WS_IMAGES.'banners/'.$banners['infopages_banner'])) {
			if (!empty($banners['linkedpage']) || !empty($banners['custom_link'])) {
				if (!empty($banners['custom_link'])) {
					echo '<a href="'.$nav_item['custom'].'" title="'.tep_get_infopages_title($banners['infopages_id']).' - '.STORE_NAME.'">';
				} else {
					if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $banners['linkedpage'])) {
						$page_id = explode('_', $banners['linkedpage']);
						$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$page_id[1].'" AND language_id = "1"');
						$infopage_name = tep_db_fetch_array($infopage_name_query);
						echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.tep_get_infopages_title($banners['infopages_id']).' - '.STORE_NAME.'">';
					} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $banners['linkedpage'])) {
						$page_id = explode('_', $banners['linkedpage']);
						$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "1"');
						$categorie_name = tep_db_fetch_array($categorie_name_query);
						echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.tep_get_infopages_title($banners['infopages_id']).' - '.STORE_NAME.'">';
					} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $banners['linkedpage'])) {
						$page_id = explode('_', $banners['linkedpage']);
						$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "1"');
						$product_name = tep_db_fetch_array($product_name_query);
						echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.tep_get_infopages_title($banners['infopages_id']).' - '.STORE_NAME.'">';
					}
				}
			}
			echo tep_image(DIR_WS_IMAGES.'banners/'.$banners['infopages_banner']);
			if (!empty($banners['linkedpage']) || !empty($banners['custom_link'])) {
				echo '</a>';
			}
		}
	}
	echo '</div>';
	?>
    <?php
}
?>