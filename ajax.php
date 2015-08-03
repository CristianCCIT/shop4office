<?php
ob_start();
require('includes/application_top.php');
if ($_SESSION['login'])
{
	$_SESSION['IsAuthorized'] = 'true';
	$_SESSION['CKFinder_UserRole'] = "admin";
	setcookie('CKFinder_UserRole', 'admin', time()+3600);
}

include($_GET['path']);

ob_end_flush();
?>