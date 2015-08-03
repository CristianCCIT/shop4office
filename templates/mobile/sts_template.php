<?php DEFINE ('STS_END_CHAR', '$'); ?>
<?php DEFINE ('STS_CONTENT_END_CHAR', '$'); ?>

<?php require_once(implode('', array('php', DIRECTORY_SEPARATOR, 'bootstrap.php'))); ?>
<!doctype html>
<html class="no-js" $htmlparams$>
<head>

    <base href="<?php echo HTTP_SERVER.DIR_WS_HTTP_CATALOG;?>" />
    $headertags$

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Shop4office</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <!-- Place favicon.ico in the root directory -->

    <link rel="stylesheet" href="<?php echo www; ?>templates/mobile/css/normalize.css">
    <link rel="stylesheet" href="<?php echo www; ?>templates/mobile/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo www; ?>templates/mobile/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="<?php echo www; ?>templates/mobile/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo www; ?>templates/mobile/css/main.css">
    <script src="<?php echo www; ?>templates/mobile//js/vendor/modernizr-2.8.3.min.js"></script>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js" type="text/javascript"></script>
    <script>window.jQuery || document.write('<script src="<?php echo www; ?>templates/mobile/js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
    <script src="<?php echo www; ?>templates/mobile/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="<?php echo www; ?>templates/mobile/js/plugins.js" type="text/javascript"></script>
    <script src="<?php echo www; ?>templates/mobile/js/main.js" type="text/javascript"></script>
</head>
<body>
<div class="container-fluid">
    <!--[if lt IE 8]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
    <![endif]-->

    <?php loadBox('navigatie') ?>
    <!-- Add your site or application content here -->
    <div class="row">
        <?php loadBox('login'); ?>
        <?php loadBox('shopping_cart'); ?>


        <?php loadBox('manufacturers'); ?>

        <?php if($_SERVER['REQUEST_URI'] != '/'.FILENAME_DEFAULT){ ?>
            <div class="col-xs-12">
                <div class="col-xs-12 mb-20">
                    $content$
                </div>
            </div>
        <?php } else { ?>
            <?php loadBox('banners'); ?>

            <?php
            $homepage_query = tep_db_query('SELECT infopages_id FROM infopages WHERE type = "home"');
            $homepage = tep_db_fetch_array($homepage_query);
            ?>
            <div class="col-xs-12">
                <div class="panel panel-info">
                    <div class="panel-heading"><?php echo tep_get_infopages_title($homepage['infopages_id']); ?></div>
                    <div class="panel-body">
                        <?php echo tep_get_infopages_description($homepage['infopages_id']); ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="col-xs-12 footer">
            <div class="row mb-20">
                <div class="col-xs-12 nav-footer">
                    <a href="infopage.php?page=2">Algemene voorwaarden</a>
                    <a href="specials.php">Aanbiedingen</a>
                    <a href="contact_us.php">Contact</a>
                    <a href="sitemap.php">Sitemap</a>
                    <a href="products_new.php">Nieuwe producten</a>
                    <a href="shopping_cart.php">Winkelwagen</a>
                </div>
            </div>
            <?php loadBox('newsletter'); ?>
        </div>
    </div>
</div>

<!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
$google_analytics$
</body>
</html>