<?php
$ticker_query = tep_db_query("SELECT i.infopages_id, it.infopages_preview, i.date_added FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.infopages_status = 1 AND i.type='ticker' AND it.language_id = '".(int)$languages_id."' ORDER BY i.sort_order ASC");
if (tep_db_num_rows($ticker_query) > 0) {
?>
<script type="text/javascript" src="includes/js/jquery.li-scroller.1.0.js"></script>
<script type="text/javascript">
$(function() { 
	$("ul#ticker01").liScroll({travelocity: 0.02});
});
</script>
<ul id="ticker01" class="newsticker">
	<?php
	while ($ticker = tep_db_fetch_array($ticker_query)) {
	?>
	<li><?php echo $ticker['infopages_preview'];?></li>
	<?php    
	}
	?>
</ul>
<?php
}
?>