<?php
require_once('includes/application_top.php');
//Get page info
$page_query = tep_db_query("SELECT infopages_id FROM infopages WHERE infopages_status = 1 AND type ='privacy'");
if (tep_db_num_rows($page_query) == 0) tep_redirect(tep_href_link(FILENAME_DEFAULT));
$page = tep_db_fetch_array($page_query);
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="100%">
			<div class="content-item">
				<h1><?php echo tep_get_infopages_title($page['infopages_id']);?></h1>
				<?php echo tep_get_infopages_description($page['infopages_id']);?>
			</div>
				
		</td>
	</tr>
</table>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>