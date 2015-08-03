<?php
require_once('includes/application_top.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
    <tr>
        <td width="<?php echo BOX_WIDTH; ?>" valign="top">
            <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
            </table>
        </td>
        <!-- body_text //-->
        <td width="100%" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
						<?php
						$infopages_query = tep_db_query("SELECT * FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.type = 'error_404' AND  (i.date_start <= NOW() || i.date_start IS NULL) AND (i.date_expires >= NOW() OR i.date_expires IS NULL) AND it.language_id = ".(int)$languages_id." ORDER BY i.sort_order ASC LIMIT 1");
						$count = 0;
						while ($frontpage = tep_db_fetch_array($infopages_query)) {
							$count++;
							echo '<h1 class="error_title">'.$frontpage['infopages_title'].'</h1>';
							echo '<div class="item ';
							if ($count % 2) {
								echo 'odd';
							} else {
								echo 'even';
							} 
							echo '">';
								 echo $frontpage['infopages_description'];
							echo '</div>';
						}
						?>
                    </td>
                </tr>
            </table>
		</td>
        <!-- body_text_eof //-->
        <td width="<?php echo BOX_WIDTH; ?>" valign="top">
            <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
            <!-- right_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
            <!-- right_navigation_eof //-->
            </table>
        </td>
    </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>