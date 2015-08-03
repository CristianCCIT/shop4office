<?php
$check_log_query = tep_db_query("SELECT keyword FROM search_log WHERE results = '1' ORDER BY count LIMIT 5");
if (tep_db_num_rows($check_log_query) > 0) {
	?>
    <div class="box_title">
         <?php echo Translate('Populaire zoektermen'); ?>
    </div>
    <div class="box_content">
        <ul>
        <?php 
        while ($check_log = tep_db_fetch_array($check_log_query)) {
        ?>
            <li><a href="<?php echo tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords='.$check_log['keyword']); ?>"><?php echo $check_log['keyword']; ?></a></li>
        <?php
        }
        ?>
        </ul>
    </div>
	<?php
}
?>