<?php
$balloons_query = tep_db_query('SELECT i.infopages_id, it.infopages_title, it.infopages_advantage, it.infopages_preview, it.infopages_description FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND i.type = "hot_news" AND (i.date_start IS NULL OR i.date_start < NOW()) AND (i.date_expires IS NULL OR i.date_expires > NOW()) AND it.language_id = "'.(int)$languages_id.'" ORDER BY i.sort_order asc LIMIT 8');
while ($balloons = tep_db_fetch_array($balloons_query)) {
	switch ($balloons['infopages_advantage']) {
		case 'Update':
			echo '<a  href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$balloons['infopages_id']).'"><div class="news_balloon_update"><span>Update: </span>'.$balloons['infopages_title'];
			echo '<div class="news_balloon_update_arrow_border"></div>';
			echo '<div class="news_balloon_arrow_border_shadow"></div>';
			echo '<div class="news_balloon_arrow"></div>';
			echo '</div></a>';
			break;
		case 'Tip':
			echo '<a  href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$balloons['infopages_id']).'"><div class="news_balloon_tip"><span>Tip: </span>'.$balloons['infopages_title'];
			echo '<div class="news_balloon_tip_arrow_border"></div>';
			echo '<div class="news_balloon_arrow_border_shadow"></div>';
			echo '<div class="news_balloon_arrow"></div>';
			echo '</div></a>';
			break;
		case 'Nieuws':
			echo '<a  href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$balloons['infopages_id']).'"><div class="news_balloon_news"><span>Nieuws: </span>'.$balloons['infopages_title'];
			echo '<div class="news_balloon_news_arrow_border"></div>';
			echo '<div class="news_balloon_arrow_border_shadow"></div>';
			echo '<div class="news_balloon_arrow"></div>';
			echo '</div></a>';
			break;
		case 'Reactie':
			echo '<a  href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$balloons['infopages_id']).'"><div class="news_balloon_reaction">'.$balloons['infopages_preview'];
			echo '<div class="news_balloon_reaction_arrow_border"></div>';
			echo '<div class="news_balloon_arrow_border_shadow"></div>';
			echo '<div class="news_balloon_arrow"></div>';
			echo '</div></a>';
			break;
		default:
			echo '<a  href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$balloons['infopages_id']).'"><div class="news_balloon_default">'.$balloons['infopages_title'];
			echo '<div class="news_balloon_default_arrow_border"></div>';
			echo '<div class="news_balloon_arrow_border_shadow"></div>';
			echo '<div class="news_balloon_arrow"></div>';
			echo '</div></a>';
			break;
	}
}
?>
<script type="text/javascript" language="javascript">
$(document).ready(function() {
	var total_height = 0,
		thisHtml = '',
		thisHeight = 0;
		extraHeight = 0;
	$('#news_balloons').children('a').children('div').each(function(index, value) {
		thisHeight = value['offsetHeight'];
		total_height += thisHeight;
		extraHeight = parseInt($(this).css('margin-bottom'));
		total_height += extraHeight;
		if (total_height > $('#news_balloons').height()) {
			$(this).css({'display': 'none'});
			total_height -= thisHeight;
			total_height -= extraHeight;
		}
	});
});
</script>