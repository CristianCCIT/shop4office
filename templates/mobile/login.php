<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 5/13/2015
 * Time: 1:15 PM
 */

if ($messageStack->size('login') > 0) {
	$errors = $messageStack->output('login', true);
}
?>
	<!-- Mobile version -->
	<div class="panel panel-info mb-20">
		<div class="panel-heading"><?php echo Translate('Nieuwe bezoeker'); ?></div>
		<div class="panel-body">
			<p><?php echo Translate('Ik ben een nieuwe bezoeker'); ?></p>

			<p><?php echo Translate('Door een account aan te maken kunt u sneller winkelen, up to date blijven omtrent uw order status en bijhouden wat u voordien al besteld heeft.'); ?></p>
			<a class="btn btn-info" href="<?php echo FILENAME_CREATE_ACCOUNT; ?>"><i class="fa fa-user"></i> <?php echo Translate('Account aanmaken'); ?></a>
		</div>
	</div>

	<div class="panel panel-success mb-20">
		<div class="panel-heading"><?php echo Translate('Terugkerende klant'); ?></div>
		<div class="panel-body">
			<?php
				if($errors){
					foreach($errors as $error){
						$error = end(explode('&nbsp;', $error['text']));
			?>
						<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle"></i> <?php echo $error; ?></div>
			<?php
					}
				}
			?>
			<p><?php echo Translate('Ik ben een terugkerende klant'); ?>.</p>

			<form class="form" action="<?php echo FILENAME_LOGIN.'?action=process'; ?>" method="post">
				<div class="form-group">
					<label class="control-label" form="email"><?php echo Translate('E-mailadres'); ?></label>
					<input name="email_address" class="form-control" placeholder="email@domain.tld">
				</div>
				<div class="form-group">
					<label class="control-label" form="pass"><?php echo Translate('Wachtwoord'); ?></label>
					<input name="password" type="password" class="form-control">

					<p class="help-block"><a class="text-info" href="<?php echo FILENAME_PASSWORD_FORGOTTEN; ?>"><?php echo Translate('Wachtwoord vergeten?'); ?></a></p>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" name="remember_me"> <?php echo Translate('Automatisch inloggen bij elk bezoek'); ?>
					</label>
				</div>
				<div class="form-group">
					<button class="btn btn-success btn-xs"><i class="fa fa-sign-in"></i> <?php echo Translate('Inloggen'); ?></button>
				</div>
			</form>
		</div>
	</div>
<?php
if ($sts->display_template_output) {
	// Get content here, in case column_right is not called.
	if (!isset($sts->template['content'])) {
		$sts->restart_capture('content');
	}
}
?>