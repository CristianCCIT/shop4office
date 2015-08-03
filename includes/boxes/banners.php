<?php
$banners_query = tep_db_query('SELECT it.infopages_title, it.infopages_preview, it.infopages_description, it.infopages_banner, it.infopages_id, i.linkedpage, i.custom_link FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND it.language_id = "'.(int)$languages_id.'" AND i.type="banner" AND (i.date_start IS NULL OR i.date_start < NOW()) AND (i.date_expires IS NULL OR i.date_expires > NOW()) AND i.infopages_status = "1" ORDER BY i.sort_order asc');
if (tep_db_num_rows($banners_query) > 0) {
	echo '<div id="bannerbox">';
	echo '<div id="banners">';
	echo '<div id="banners_img"></div>';
	echo '<div id="banners_title_box"><div id="banners_title_box_shadow"></div></div>';
	//echo '<div id="banners_text_box"></div>';
	while ($banners = tep_db_fetch_array($banners_query)) {
		echo '<div id="banner_'.$banners['infopages_id'].'">';
			echo '<div class="banner_title">';
			if (!empty($banners['linkedpage']) || !empty($banners['custom_link'])) {
				if (!empty($banners['custom_link'])) {
					echo '<a href="'.tep_href_link($nav_item['custom']).'" title="'.$banners['infopages_title'].' - '.STORE_NAME.'">';
				} else {
					if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $banners['linkedpage'])) {
						$page_id = explode('_', $banners['linkedpage']);
						$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$page_id[1].'" AND language_id = "1"');
						$infopage_name = tep_db_fetch_array($infopage_name_query);
						echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$banners['infopages_title'].' - '.STORE_NAME.'">';
					} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $banners['linkedpage'])) {
						$page_id = explode('_', $banners['linkedpage']);
						$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "1"');
						$categorie_name = tep_db_fetch_array($categorie_name_query);
						echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$banners['infopages_title'].' - '.STORE_NAME.'">';
					} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $banners['linkedpage'])) {
						$page_id = explode('_', $banners['linkedpage']);
						$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "1"');
						$product_name = tep_db_fetch_array($product_name_query);
						echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$banners['infopages_title'].' - '.STORE_NAME.'">';
					}
				}
			}
			echo $banners['infopages_title'];
			if ($banners['infopages_preview'] != '') {
				echo '<div class="banner_short">'.$banners['infopages_preview'].'</div>';
			}
			if (!empty($banners['linkedpage']) || !empty($banners['custom_link'])) {
				echo '</a>';
			}
			echo '</div>';
			echo '<div class="banner_descr"><span>'.$banners['infopages_title'].'</span>'.$banners['infopages_description'].'</div>';
			echo tep_image(DIR_WS_IMAGES.'banners/'.$banners['infopages_banner']);
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';
?>
<script type="text/javascript" language="javascript">
	var totalheight = 0;
	var selector = 0;
	var prev_selector = 0;
	var title_box_top = 0;
	var title_top = 0;
	var title_selector_top = 0;
	var qBanners = 0;
	var title_selector_height = 0;
	var banners_text_height = 0;
	var banners_text_top = 0;
	var banners_height = parseInt($('#banners').css('height'));
	var banners_img_width = parseInt($('#banners_img').css('width'));
	var banners_text_width = 0;
	var banners_text_left = 0;
	// function to calculate actual width of element
	function visualHeight (element) {
		var thisHtml = element.html();
		$('#bannersruler').html(thisHtml);
		 var thisHeight = $('#bannersruler').height();
		$('#bannersruler').html('');
		return thisHeight;
	}
	function visualWidth (element) {
		var thisHtml = element.html();
		$('#bannersruler').html(thisHtml);
		 var thisHeight = $('#bannersruler').width();
		$('#bannersruler').html('');
		return thisHeight;
	}
	function getSelectorTop () {
		prev_selector = selector;
		selector++;
		if (selector == qBanners) {
			selector =  0;
		}
		title_top = $('#banners_title_box').children('div.banner_title').eq(selector).offset().top;
		title_selector_height = $('#banners_title_box').children('div.banner_title').eq(selector).height() + 15;
		title_selector_top = title_top - title_box_top - 3;
		return title_selector_top;
	}
	function getTextBoxTop () {
		banners_text_width = visualWidth($('#banners_text_box').children('div.banner_descr').eq(selector));
		banners_text_height = $('#banners_text_box').children('div.banner_descr').eq(selector).height();
		banners_text_top = banners_height - banners_text_height - 25;
		banners_text_left = banners_img_width - banners_text_width - 20;
		return banners_text_width;
	}
	function loop () {
		$('#banners_img :first-child').fadeOut(500).next('img').fadeIn(500).end().appendTo('#banners_img');
		title_selector_top = getSelectorTop();
		$('#banners_title_selector').animate({'margin-top': title_selector_top, 'height': title_selector_height}, 500);
		$('#banners_title_box').children('div.banner_title').eq(selector).children('a').animate({'color': '#fff'}, 500);
		$('#banners_title_box').children('div.banner_title').eq(selector).animate({'color': '#fff'}, 500);
		$('#banners_title_box').children('div.banner_title').eq(prev_selector).children('a').animate({'color': '#423837'}, 500);
		$('#banners_title_box').children('div.banner_title').eq(prev_selector).animate({'color': '#423837'}, 500);
		banners_text_width = getTextBoxTop();
		$('#banners_text_box').animate({'width': banners_text_width, 'margin-left': banners_text_left, 'margin-top': banners_text_top, 'height': banners_text_height}, 500)
		$('#banners_text_box').children('div.banner_descr').eq(prev_selector).hide();
		$('#banners_text_box').children('div.banner_descr').eq(selector).show();
	}
	$(document).ready(function(){
		$('#banners').prepend('<span id="bannersruler"></span>');				   
		qBanners = $('#banners').children('div[id^=banner_]').length;
		$('#banners_img').html(
			$('#banners').children('div[id^=banner_]').children('img')
		);
		$('#banners_text_box').html(
			$('#banners').children('div[id^=banner_]').children('.banner_descr')
		);
		$('#banners_title_box').append(
			$('#banners').children('div[id^=banner_]').children('div.banner_title').show()
		);
		$('#banners_title_box').children('div.banner_title').each(function(){
			totalheight += visualHeight($(this));
		});
		var boxheight = $('#banners_title_box').height() - (qBanners * 20);
		var marginTop = (boxheight - totalheight) / qBanners;
		$('div.banner_title').css({'margin-top' : Math.round(marginTop)});
		$('#banners_title_box').append('<img id="banners_title_selector" src="<?php echo STS_TEMPLATE_DIR;?>/images/banners/banners_title_selector.png" width="265" />');
		title_box_top = $('#banners_title_box').offset().top;
		title_top = $('#banners_title_box').children('div.banner_title').eq(0).offset().top;
		title_selector_top = title_top - title_box_top - 3;
		$('#banners_title_selector').css({'margin-top': title_selector_top});
		title_selector_height = $('#banners_title_box').children('div.banner_title').eq(0).height() + 15;
		$('#banners_title_selector').height(title_selector_height);
		$('#banners_title_box').children('div.banner_title').eq(0).css({'color': '#fff'});
		$('#banners_title_box').children('div.banner_title').eq(0).children('a').css({'color': '#fff'});
		$('#banners_text_box :first-child').show();
		banners_text_height = $('#banners_text_box').children('div.banner_descr').eq(0).height();
		banners_text_top = banners_height - banners_text_height - 25;
		banners_text_width = visualWidth($('#banners_text_box').children('div.banner_descr').eq(0));
		banners_text_left = banners_img_width - banners_text_width - 20;
		$('#banners_text_box').css({'margin-top': banners_text_top, 'margin-left': banners_text_left, 'width': banners_text_width});
		$('#banners_img img:gt(0)').hide();
		var theloop = setInterval("loop()", 5000);
		$('#banners').hover( 
			function() {
				clearInterval(theloop);
			},
			function() {
				loop();
				theloop = setInterval("loop()", 5000);
			}
		);
	});
</script>
<?php
}
?>