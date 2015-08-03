<?php
chdir('../../../../..');
require_once('includes/application_top.php');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
	<meta charset="utf-8">
	<title><?php echo STORE_NAME;?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Le styles -->
	<style type="text/css">
		<?php
		include('includes/modules/checkout/assets/css/bootstrap.min.css');
		include('includes/modules/checkout/assets/css/bootstrap-responsive.min.css');
		include('includes/modules/checkout/assets/css/style.css');
		?>
	</style>
	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="span6">
				<div class="logo"><?php echo tep_image(HTTP_SERVER.DIR_WS_HTTP_CATALOG.DIR_WS_IMAGES.'mail/logo.jpg');?></div>
			</div>
			<div class="span6">
				<?php tep_get_module('checkout_top');?>
			</div>
		</div>
		<div class="row">
			<div class="span8">
				<div class="step active">
					<div class="step_title"><?php echo Translate('Betaling via beveiligde Ogone server');?></div>
				</div>
				<div class="well">
				$$$PAYMENT ZONE$$$
				</div>
			</div>
			<div class="span4 active summary">
				<?php tep_get_module('checkout_right');?>
			</div>
		</div>
		<div class="row">
			<div class="span12">
				<?php tep_get_module('checkout_bottom');?>
			</div>
		</div>
	</div>
</body>
</html> 