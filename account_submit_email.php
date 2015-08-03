<?php
require('includes/application_top.php');
if (!tep_session_is_registered('customer_id')) {
	$navigation->set_snapshot();
	tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}
if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
	$process = true;
    $email_address = tep_db_prepare_input($_POST['email_address']);
    $error = false;
    if (tep_validate_email($email_address) == false) {
      $error = true;
      $messageStack->add('email_address', Translate('Gelieve een geldig e-mailadres in te geven'));
    } else {
		if (!isset($_POST['id'])) {
			$check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
			$check_email = tep_db_fetch_array($check_email_query);
			if ($check_email['total'] > 0) {
				$error = true;
				$messageStack->add('email_address_exists', Translate('Het ingegeven e-mailadres bestaat al in ons systeem. Gelieve in te loggen of een ander e-mailadres in te geven'));
			}
		}
    }
    if ($error == false) {
		tep_db_query("update ".TABLE_CUSTOMERS." set customers_email_address = '".$email_address."', customers_username = '".$customers_email_address."' where customers_id = '".(int)$customer_id."'");
		tep_session_unregister('customers_username');
		tep_session_unregister('customers_email_address');
		$customers_username = $customers_email_address;
		$customers_email_address = $email_address;
		tep_session_register('customers_username');
		tep_session_register('customers_email_address');
		$messageStack->add('account_submit_email', Translate('Uw e-mailadres werd succesvol toegevoegd, u kunt nu verder gaan'), 'success');
	}
}
$breadcrumb->add(Translate('Mijn account'), tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(Translate('E-mailadres invullen'));
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td>
            <div class="breadCrumbHolder module">
                <div id="breadCrumbs" class="breadCrumb module">$breadcrumbs$</div>
            </div>
            <div class="chevronOverlay main"></div>
            <h1><?php echo Translate('E-mailadres invullen'); ?></h1>
            <?php
			echo tep_draw_form('account_submit_email', tep_href_link(FILENAME_ACCOUNT_SUBMIT_EMAIL, '', 'SSL'), 'post', 'id="validate_form"');
			echo tep_draw_hidden_field('action', 'process');
			if ($messageStack->size('account_submit_email') > 0) {
				echo $messageStack->output('account_submit_email');
			}
			if ($error) {
				echo '<div class="message error" style="text-align:center;"><img width="10" border="0" height="10" alt="" src="images/icons/error.gif">';
				echo Translate('Gelieve alle velden correct in te vullen!');
				if ($messageStack->size('email_address_exists') > 0) {
					echo '<br />'.Translate('Het ingegeven e-mailadres bestaat al in ons systeem. Gelieve in te loggen of een account te registreren met een ander e-mailadres');
				}
				echo '</div>';
			}
			if ($messageStack->size('account_submit_email') == 0) {
			?>
            <div style="padding:10px 0;">
            <?php echo Translate('Uw e-mailadres is ons nog onbekend. Om de correcte opvolging van bestellingen te kunnen garanderen, vragen wij u een e-mailadres op te geven.'); ?>
            </div>
			<script type="text/javascript" src="includes/js/form_validation.js"></script>
			<table cellpadding="5" cellspacing="5" width="100%">
				<tr>
					<td><label for="email_address" class="formLabel"><?php echo Translate('E-mailadres'); ?>:</label></td>
				</tr>
				<tr>
					<td><input class="inputbox<?php if (($messageStack->size('email_address') > 0) || ($messageStack->size('email_address_exists') > 0)) { echo ' error';}?>" type="text" name="email_address" size="30" style="width:200px;" title="<?php echo Translate('Gelieve een geldig e-mailadres in te geven');?>" condition="email_required" value="<?php echo $user_email_address;?>" id="email_address" tabindex="2" /><span class="inputRequirement">&nbsp;*</span></td>
				</tr>
				<tr>
					<td height="20"></td>
				</tr>
				<tr>
					<td><input type="submit" value="<?php echo Translate('Ga verder'); ?>" class="formbutton button-a" id="create_account_submit" /></td>
				</tr>
                <?php
			} else {
				?>
                <br /><br /><a href="<?php echo tep_href_link(FILENAME_DEFAULT); ?>" class="button-a"><?php echo Translate('Ga verder'); ?></a>
                <?php
			}
			?>
			</table>
			</form>
        </td>
    </tr>
</table>

<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>