<?php
require('includes/application_top.php');
$breadcrumb->add(Translate('Nieuws'), tep_href_link(FILENAME_NEWS));
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
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<div class="npContainer">
				<?
				//Get last 5 newsletters
				$news_query = tep_db_query("SELECT i.infopages_id, i.date_added, it.infopages_title, it.infopages_preview, it.infopages_description
				FROM infopages i JOIN infopages_text it USING (infopages_id)
				WHERE i.type = 'hot_news' AND  (i.date_start <= NOW() || i.date_start IS NULL) AND (i.date_expires >= NOW() OR i.date_expires IS NULL) AND it.language_id = ".(int)$languages_id." ORDER BY i.date_added DESC");
				$count=0;
				while ($news = tep_db_fetch_array($news_query)) {
					$count++;
					?>
					<div class="contentpaneopen">
						<h2><?=$news['infopages_title']?></h2>
						<?=($news['infopages_preview']) ? $news['infopages_preview'] : $news['infopages_description']?>
						<? if ($news['infopages_description'] && $news['infopages_preview']) { ?>
						<div class="newsreadon<?=$news['infopages_id']?> newsextra">
							<?=$news['infopages_description']?>
						</div>
						<div class="readmore"><a class="readon<?=$news['infopages_id']?>" href="#"><?=Translate('Lees meer')?></a></div>
						<? } ?>
					</div>
					<?
					if ($count < tep_db_num_rows($news_query)) {
					?>
					<div class="divider"></div>
					<?
					}
				}
				?>
			</div>
		</td>
	</tr>
</table>
	</td>
<!-- body_text_eof //-->
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>