<?php
require_once('includes/application_top.php');
$page = $_GET['page'];
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
                    <div class="content-item <?php echo tep_get_infopages_type($page); ?>">
                        <h1>
							<?php echo tep_get_infopages_title($page) ?>
                        </h1>
                        <?php
						if (tep_get_infopages_type($page)=='rubrieken') {
							?>
                            <span class="intro">
                                <?php echo tep_get_infopages_preview($page); ?>
                            </span>
                            <?php
							if (tep_get_infopages_display($page)!='') {
								foreach(explode("\n", tep_get_infopages_display($page)) as $option){
									$values = explode(":", $option);
									${$values[0].$page} = $values[1];
								}
							}
							if (${'sort_field'.$page}!='') { $default_sort_field = ${'sort_field'.$page}; } else { $default_sort_field = 'i.sort_order'; }
							if (${'sort_mode'.$page}!='') { $default_sort_mode = ${'sort_mode'.$page}; } else { $default_sort_mode = 'asc'; }
							$blog_query = tep_db_query("SELECT i.infopages_id FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.parent_id = '".(int)$page."' AND it.language_id = ".(int)$languages_id." order by ".$default_sort_field." ".$default_sort_mode);
							if (tep_db_num_rows($blog_query)>0)
							{
								$blog_count = 0;
								while ($blog = tep_db_fetch_array($blog_query))
                                {
									if (tep_get_infopages_display($blog['infopages_id'])!='') {
										foreach(explode("\n", tep_get_infopages_display($blog['infopages_id'])) as $option){
											$values = explode(":", $option);
											${$values[0].$blog['infopages_id']} = $values[1];
										}
									}
									$blog_count++;
									$column_width = 100;
									if ($blog_count<=(${'leading'.$page})) {
										$this_column = 'leading';
									} else {
										if ($blog_count==((${'leading'.$page})+1)) {
											$this_column = 0;
										}
										if ($this_column>=(${'columns'.$page})) {
											$this_column = 1;
										} else {
											$this_column++;
										}
										if (${'columns'.$page}>0) {
											$column_width = 100/${'columns'.$page};
										}
									}
									?>
                                    <div class="blog-item blog-column-<?php echo $this_column; ?>" style="width:<?php echo $column_width; ?>%;">
                                    	<div class="blog-item-inner">
                                        	<?php if ((strstr(${'show_field'.$page}, 'infopages_title')) || (${'show_field'.$page}=='')) { ?>
                                            <h2>
                                            	<?php if ((strstr(${'show_field'.$page}, 'infopages_title_link')) || (${'show_field'.$page}=='')) { ?>
                                                <a href="<?php echo tep_href_link(FILENAME_INFOPAGE, 'page='.$blog['infopages_id']); ?>">
                                                <?php } ?>
                                                <span class="title">
                                                <?php echo tep_get_infopages_title($blog['infopages_id']); ?>
                                                </span>
                                            	<?php if ((strstr(${'show_field'.$page}, 'infopages_title_link')) || (${'show_field'.$page}=='')) { ?>
                                                </a>
                                                <?php } ?>
                                                <?php if (strstr(${'show_field'.$page}, 'date_added')) { ?>
													<span class="date"><?php echo date('d M Y', strtotime(tep_get_infopages_date_added($blog['infopages_id']))); ?></span>
                                                <?php } ?>
                                            </h2>
                                            <?php } ?>
                                            <div class="blog-item-text">
                                                <?php
												if ((strstr(${'show_field'.$page}, 'infopages_preview')) || (${'show_field'.$page}=='')) {
												?>
                                                <span class="intro">
													<?php echo tep_get_infopages_preview($blog['infopages_id']); ?>
                                                </span>
                                                <?php
												}
												if (strstr(${'show_field'.$page}, 'infopages_description')) {
													?>
                                                    <span class="full">
                                                		<?php echo tep_get_infopages_description($blog['infopages_id']); ?>
                                                    </span>
												<?php
												}
												if (strstr(${'show_field'.$page}, 'subpages_list')) {
													if (tep_get_infopages_type($blog['infopages_id'])=='rubrieken') {
														$blog_sub_query = tep_db_query("SELECT i.infopages_id FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.parent_id = '".(int)$blog['infopages_id']."' AND it.language_id = ".(int)$languages_id." order by ".$default_sort_field." ".$default_sort_mode);
														if (tep_db_num_rows($blog_sub_query)>0)	{
															?>
															<ul>
															<?php
															while ($blog_sub = tep_db_fetch_array($blog_sub_query)) {
																?>
																<li><a href="<?php echo tep_href_link(FILENAME_INFOPAGE, 'page='.$blog_sub['infopages_id']); ?>"><?php echo tep_get_infopages_title($blog_sub['infopages_id']); ?></a></li>
																<?php
															}
															?>
															</ul>
															<?php
														}
													}
												}
												if ( (strstr(${'show_field'.$page}, 'readmore')) || (${'show_field'.$page}=='') ) {
													if (${'readmore'.$blog['infopages_id']}!='none') {
													?>
													<a href="<?php echo tep_href_link(FILENAME_INFOPAGE, 'page='.$blog['infopages_id']); ?>"><?php echo Translate('Lees meer'); ?>: <?php echo tep_get_infopages_title($blog['infopages_id']); ?></a>
													<?php
													}
												}
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
									if ($blog_count==tep_db_num_rows($blog_query)) {
									?>
                                        <div class="clear"></div>
									<?php
                                    }
								}
							}
						} else {
							?>
                            <div class="content-item">
                                <span class="blog-item-text-intro">
									<?php echo tep_get_infopages_preview($page); ?>
                                </span>
                                <span class="blog-item-text-description">
									<?php echo tep_get_infopages_description($page); ?>
                                </span>
                            </div>
                            <?php
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
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>