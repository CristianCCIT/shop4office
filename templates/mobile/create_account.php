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
	<div class="panel panel-success mb-20">
		<div class="panel-heading">
			<?php if (CREATE_ACCOUNT_MODE=='Request') { ?>
				<?php echo Translate('Account aanvragen'); ?>
			<?php } else { ?>
				<?php echo Translate('Account aanmaken'); ?>
			<?php } ?>
		</div>
		<div class="panel-body">
			<?php if ($errors) { ?>
				<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle"></i>
				<?php
					echo Translate('Gelieve alle velden correct in te vullen!');
					if ($messageStack->size('email_address_exists') > 0) {
						echo '<br />'.Translate('Het ingegeven e-mailadres bestaat al in ons systeem. Gelieve in te loggen of een account te registreren met een ander e-mailadres');
					}
					if ($messageStack->size('forum_username_exists') > 0) {
						echo '<br />'.Translate('Deze gebruikernaam voor het forum is reeds in gebruik');
					}
				?>
				</div>
			<?php } else { ?>
			<div class="alert alert-info" role="alert"><i class="fa fa-bell-o"></i>
				<?php echo '<a href="', FILENAME_LOGIN, '">',Translate("LET OP: Als u reeds een account heeft aangemaakt, log dan in bij de login pagina"),'</a>'; ?>
			</div>
			<?php } ?>

			<p class="text-danger"><?php echo Translate('* Verplicht veld'); ?></p>

			<?php if ((CREATE_ACCOUNT_MODE=='Request account') || (CREATE_ACCOUNT_MODE=='Moderated access')) { ?>
			<div class="alert alert-warning" role="alert"><i class="fa fa-exclamation"></i>
				<?php echo Translate('Indien u geen geregistreerd lid bent van deze website, maar u toch producten wenst te bestellen, vul hieronder uw gegevens correct in. Deze gegevens worden door ons ontvangen, gecontroleerd en indien mogelijk wordt u toegevoegd aan onze ledenlijst.'); ?>
			</div>
			<?php } ?>

			<form class="form" name="create_account" action="<?php echo FILENAME_CREATE_ACCOUNT; ?>" method="post">
				<?php echo tep_draw_hidden_field('action', 'process');?>
				<div class="form-group">
					<h3><?php echo Translate('Persoonlijke gegevens'); ?></h3>
				</div>
				<div class="form-group <?php if ($messageStack->size('firstname') > 0) { echo 'has_error';}?>">
					<label for="firstname" class="control-label"><?php echo Translate('Voornaam'); ?> <span class="text-danger">*</span></label>
					<input class="form-control" type="text" name="firstname" title="<?php echo sprintf(Translate('Uw voornaam moet minstens %s karakters bevatten'), ENTRY_FIRST_NAME_MIN_LENGTH);?>" condition="<?php echo ENTRY_FIRST_NAME_MIN_LENGTH;?>" value="<?php echo isset($_POST['firstname'])?$_POST['firstname']:'';?>" id="firstname" />

				</div>

				<div class="form-group <?php if ($messageStack->size('lastname') > 0) { echo 'has_error';}?>">
					<label for="lastname" class="control-label"><?php echo Translate('Achternaam'); ?> <span class="text-danger">*</span></label>
					<input class="form-control" type="text" name="lastname" title="<?php echo sprintf(Translate('Uw achternaam moet minstens %s karakters bevatten'), ENTRY_LAST_NAME_MIN_LENGTH);?>" condition="<?php echo ENTRY_LAST_NAME_MIN_LENGTH;?>" value="<?php echo isset($_POST['lastname'])?$_POST['lastname']:'';?>" id="lastname" />
				</div>

				<div class="form-group <?php if ($messageStack->size('email_address') > 0 || ($messageStack->size('email_address_exists') > 0)) { echo 'has_error';}?>">
					<label for="email_address" class="control-label"><?php echo Translate('E-mailadres'); ?> <span class="text-danger">*</span></label>
					<input class="form-control" type="text" name="email_address" title="<?php echo Translate('Gelieve een geldig e-mailadres in te geven');?>" condition="<?php echo ENTRY_LAST_NAME_MIN_LENGTH;?>" value="<?php echo isset($_POST['email_address'])?$_POST['email_address']:'';?>" id="email_address" />
				</div>

				<?php /*FORUM*/ if ((FORUM_ACTIVE=='true') && (FORUM_SYNC_USERS=='true')) { ?>
					<div class="form-group <?php if ($messageStack->size('forum_username') > 0) { echo 'has_error';}?>">
						<label for="forum_username" class="control-label"><?php echo Translate('Forum gebruikersnaam'); ?> <span class="text-danger">*</span></label>
						<input class="form-control" type="text" name="forum_username" title="<?php echo sprintf(Translate('Geen mogelijke gebruikersnaam'), ENTRY_FORUM_USERNAME_MIN_LENGTH);?>" condition="<?php echo ENTRY_FORUM_USERNAME_MIN_LENGTH; ?>" value="<?php echo isset($_POST['forum_username'])?$_POST['forum_username']:'';?>" id="forum_username" />
					</div>
				<?php } ?>

				<?php if (ACCOUNT_COMPANY=='true') { ?>
					<div class="form-group">
						<h3><?php echo Translate('Bedrijfsgegevens'); ?></h3>
					</div>

					<div class="form-group <?php if ($messageStack->size('company') > 0) echo 'has_error';?>">
						<label for="company" class="control-label"><?php echo Translate('Bedrijfsnaam'); ?></label>
						<input class="form-control" type="text" name="company" value="<?php echo (isset($_POST['company']))?$_POST['company']:'';?>" id="company" />
					</div>

					<div class="form-group <?php if ($messageStack->size('btwnr') > 0) echo 'has_error';?>">
						<label for="btwnr" class="control-label"><?php echo Translate('BTW Nummer'); ?></label>
						<input class="form-control" type="text" name="btwnr" placeholder="bv. BE 0000 000 000" title="<?php echo Translate('Vul een geldig btw nummer in!');?>" value="<?php echo (isset($_POST['btwnr']))?$_POST['btwnr']:'';?>" id="btwnr" />
					</div>
				<?php } ?>
				<div class="form-group">
					<h3><?php echo Translate('Adresgegevens'); ?></h3>
				</div>

				<div class="form-group <?php if ($messageStack->size('street_address') > 0) echo 'has_error';?>">
					<label for="street_address" class="control-label"><?php echo Translate('Straat en huisnummer'); ?> <span class="text-danger">*</span></label>
					<input class="form-control" type="text" name="street_address" title="<?php echo Translate('De straatnaam moet minstens 5 karakters lang zijn.');?>" condition="reg[a-zA-Z]\s\d_required" value="<?php echo (isset($_POST['street_address']))?$_POST['street_address']:'';?>" id="street_address" />
				</div>

				<div class="form-group <?php if ($messageStack->size('postcode') > 0) echo 'has_error';?>">
					<label for="postcode" class="control-label"><?php echo Translate('Postcode'); ?> <span class="text-danger">*</span></label>
					<input class="form-control" type="text" name="postcode"  title="<?php echo sprintf(Translate('Uw postcode moet minstens %s karakters bevatten'), ENTRY_POSTCODE_MIN_LENGTH);?>" value="<?php echo (isset($_POST['postcode']))?$_POST['postcode']:'';?>" id="postcode" />
				</div>

				<div class="form-group<?php if ($messageStack->size('city') > 0) echo ' has_error';?>">
					<label for="city" class="control-label"><?php echo Translate('Woonplaats'); ?> <span class="text-danger">*</span></label>
					<input class="form-control" type="text" name="city"  title="<?php echo sprintf(Translate('Uw woonplaats moet minstens %s karakters bevatten'),ENTRY_CITY_MIN_LENGTH);?>" value="<?php echo (isset($_POST['city']))?$_POST['city']:'';?>" id="city" />
				</div>

				<div class="form-group <?php if ($messageStack->size('country') > 0) echo 'has_error';?>">
					<label for="country" class="control-label"><?php echo Translate('Land'); ?> <span class="text-danger">*</span></label>
					<?php echo tep_get_country_list('country', '', ' class="form-control" id="country" title="'.Translate('Gelieve een land uit de lijst te selecteren').'"'); ?>
				</div>

				<div class="form-group <?php if ($messageStack->size('telephone') > 0) echo 'has_error';?>">
					<label for="telephone" class="control-label"><?php echo Translate('Telefoonnummer'); ?> <span class="text-danger">*</span></label>
					<input class="form-control" type="text" name="telephone"  placeholder="bv. 000/00.00.00" value="<?php echo (isset($_POST['telephone']))?$_POST['telephone']:'';?>" id="telephone" />
				</div>

				<div class="form-group<?php if ($messageStack->size('fax') > 0) echo ' has_error';?>">
					<label for="fax" class="control-label"><?php echo Translate('Faxnummer'); ?> </label>
					<input class="form-control" type="text" name="fax"  placeholder="bv. 000/00.00.00" value="<?php echo (isset($_POST['fax']))?$_POST['fax']:'';?>" id="fax" />
				</div>

				<?php if ((CREATE_ACCOUNT_MODE=='Direct access') || (CREATE_ACCOUNT_MODE=='Moderated access')) { ?>
					<div class="form-group <?php if ($messageStack->size('password') > 0) echo 'has_error';?>">
						<label for="password" class="control-label"><?php echo Translate('Wachtwoord'); ?> <span class="text-danger">*</span></label>
						<input class="form-control" type="password" name="password" value="<?php echo (isset($_POST['password']))?$_POST['password']:'';?>" id="password" title="<?php echo sprintf(Translate('Uw paswoord moet minstens %s karakters bevatten'),ENTRY_PASSWORD_MIN_LENGTH);?>" />
					</div>

					<div class="form-group<?php if ($messageStack->size('confirmation') > 0) echo ' has_error';?>">
						<label for="TermsAgree" class="control-label"><?php echo Translate('Wachtwoord bevestigen'); ?> <span class="text-danger">*</span></label>
						<input class="form-control" type="password" name="confirmation" value="<?php echo (isset($_POST['confirmation']))?$_POST['confirmation']:'';?>" id="confirmation" />


					</div>
				<?php } ?>

				<div class="form-group">
					<label for="confirmation" class="control-label">
						<input class="checkbox-inline" type="checkbox" name="TermsAgree" id="TermsAgree" />
						<?php
							$termsAgree = sprintf(Translate("Ik heb de <a href='%s' target='_blank'>algemene voorwaarden</a> gelezen en ga hiermee akkoord:"), tep_href_link('conditions_modal.php'));
							if (CONDITIONS_CREATE_ACCOUNT == 'Link') {
								echo $termsAgree;
							}else{
								echo strip_tags($termsAgree);
							}
						?>
					</label>

				</div>
				<div class="form-group">
					<button class="btn btn-success btn-xs"><i class="fa fa-sign-in"></i> <?php echo Translate('Ga verder'); ?></button>
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