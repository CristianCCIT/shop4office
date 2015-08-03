<?php
require_once('includes/application_top.php');
$page_query = tep_db_query("SELECT infopages_id FROM infopages WHERE infopages_status = 1 AND type ='condition'");
if (tep_db_num_rows($page_query) == 0) tep_redirect(tep_href_link(FILENAME_DEFAULT));
$page = tep_db_fetch_array($page_query);
?>
<table border="0" width="100%" cellspacing="3" cellpadding="3">
	<tr>
		<td width="100%" valign="top">
			<h1><?php echo tep_get_infopages_title($page['infopages_id']);?></h1>
			<?php echo tep_get_infopages_description($page['infopages_id']);?>
		</td>
	</tr>
</table>
<?php
require(DIR_WS_INCLUDES . 'footer.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>