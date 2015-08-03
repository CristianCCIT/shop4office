<?php DEFINE ('STS_END_CHAR', '$'); ?>
<?php DEFINE ('STS_CONTENT_END_CHAR', '$'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl" $htmlparams$>
<head>
    <base href="<?php echo HTTP_SERVER.DIR_WS_HTTP_CATALOG;?>" />
    $headertags$
    $google_analytics$
</head>
<body class="abocms">
<div id="wrap">
    <div id="main">
        <div class="container">
            <div id="outer_content">
				<?php tep_get_module('banner', true); ?>
                <div id="content">
                    $content$
                </div>
                <div id="right_column">
                    <?php tep_get_module('right', true); ?>
                </div>
            </div>
            <div id="left_column">
                <?php tep_get_module('left', true);	?>
            </div>
        </div>
        <div id="header">
            <div class="container">
                <a href="<?php tep_href_link(FILENAME_DEFAULT); ?>" class="logo"><img src="$templatedir$/images/logo.png" alt="<?php echo STORE_NAME; ?>" /></a>
                <?php tep_get_module('header', true);	?>
                <?php tep_get_module('top', true); ?>
            </div>
        </div>
    </div>
</div>
<div id="footer">
    <div class="container">
        <?php tep_get_module('footer', true); ?>
    </div>
</div>
<script type="text/javascript" >
    $(document).ready(function() {
        $('#header .box.categories ul li').hover(
            function() {
                $(this).find('ul.level_1').show();
            }, function() {
                $(this).find('ul.level_1').hide();
            }
        );
		$('div[class^=newsreadon]').hide();
		$('a[class^=readon]').click(function() {
			var $this = $(this);
			var x = $this.attr("className");
			$('.news' + x).each(function(i, elem) {
				$(elem).slideToggle('400');
			});
			$(this).text($(this).text() == '<?php echo Translate("Lees meer")?>' ? '<?php echo Translate("Lees minder")?>' : '<?php echo Translate("Lees meer")?>');
			return false;
		});
		$("#breadCrumbs").jBreadCrumb();
		$(".gallery a").colorbox({transition:"fade"});
    });
</script>
</body>
</html>