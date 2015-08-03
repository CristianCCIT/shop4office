<?php
require('includes/application_top.php');
$_GET['fatal'] = str_replace('<br>', "\n", $_GET['fatal']);
tep_mail('Error reporting', 'dieter@aboservice.be', 'Error', 'test', 'error op site', 'test@aboservice.be');
//error_log($_POST['fatal'].$_SERVER['SERVER_NAME'].$_SERVER['HTTP_REFERER'],1,'dieter@aboservice.be');
echo $_GET['fatal'];

echo 'Er is een fout opgetreden.';
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>