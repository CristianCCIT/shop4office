<?php
$core_links = array();
$extra_links_query = tep_db_query('SELECT name, page FROM extra_links');
while ($extra_links = tep_db_fetch_array($extra_links_query)) {
	$core_links[$extra_links['page']] = $extra_links['name'];
}
$banners_query = tep_db_query('SELECT it.infopages_title, it.infopages_banner, it.infopages_id, i.linkedpage, i.custom_link FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND it.language_id = "'.(int)$languages_id.'" AND i.type="banner" AND (i.date_start IS NULL OR i.date_start < NOW()) AND (i.date_expires IS NULL OR i.date_expires > NOW()) AND i.infopages_status = "1" ORDER BY i.sort_order asc');
if (tep_db_num_rows($banners_query) > 0) {
	echo '<div id="banners">';
	while ($banners = tep_db_fetch_array($banners_query)) {
		echo '<div id="banner_'.$banners['infopages_id'].'">';
			if (!empty($banners['linkedpage']) || !empty($banners['custom_link'])) {
				if (!empty($banners['custom_link'])) {
					echo '<a href="'.tep_href_link($nav_item['custom']).'" title="'.$banners['infopages_title'].' - '.STORE_NAME.'">';
				} else {
					if (isset($core_links[$banners['linkedpage']])) {
						echo '<a href="'.tep_href_link($banners['linkedpage']).'" title="'.$core_links[$banners['linkedpage']].' - '.STORE_NAME.'">';
					} else if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $banners['linkedpage'])) {
						$page_id = explode('_', $banners['linkedpage']);
						echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$banners['infopages_title'].' - '.STORE_NAME.'">';
					} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $banners['linkedpage'])) {
						$page_id = explode('_', $banners['linkedpage']);
						echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$banners['infopages_title'].' - '.STORE_NAME.'">';
					} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $banners['linkedpage'])) {
						$page_id = explode('_', $banners['linkedpage']);
						echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$banners['infopages_title'].' - '.STORE_NAME.'">';
					}
				}
			}
			echo tep_image(DIR_WS_IMAGES.'banners/'.$banners['infopages_banner'], $banners['banners_title']);
			echo '<span class="banner_overlay"></span>';
			if (!empty($banners['linkedpage']) || !empty($banners['custom_link'])) {
				echo '</a>';
			}
		echo '</div>';
	}
	echo '</div>';
?>
<script type="text/javascript" language="javascript">
	if ($('#banners').children('div').length > 1) {
		$('#banners div:gt(0)').hide();
		setInterval(function(){
			$('#banners div:first-child').fadeOut(2500).next('div').fadeIn(2500).end().appendTo('#banners');
		},4000);
	}
</script>
<?php
}
?>