<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>

<?php
echo "define('HTTP_SERVER', 'http://".$_SERVER['HTTP_HOST']."');<br />";
echo "define('HTTPS_SERVER', 'http://".$_SERVER['HTTP_HOST']."');<br />";
echo "define('ENABLE_SSL', false);<br />";
echo "define('HTTP_COOKIE_DOMAIN', '".$_SERVER['HTTP_HOST']."');<br />";
echo "define('HTTPS_COOKIE_DOMAIN', '".$_SERVER['HTTP_HOST']."');<br />";
echo "define('HTTP_COOKIE_PATH', '".str_replace('getroot.php', '', $_SERVER['REQUEST_URI'])."');<br />";
echo "define('HTTPS_COOKIE_PATH', '".str_replace('getroot.php', '', $_SERVER['REQUEST_URI'])."');<br />";
echo "define('DIR_WS_HTTP_CATALOG', '".str_replace('getroot.php', '', $_SERVER['REQUEST_URI'])."');<br />";
echo "define('DIR_WS_HTTPS_CATALOG', '".str_replace('getroot.php', '', $_SERVER['REQUEST_URI'])."');<br />";
echo "define('DIR_WS_IMAGES', 'images/');<br />";
echo "define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');<br />";
echo "define('DIR_WS_INCLUDES', 'includes/');<br />";
echo "define('DIR_WS_BOXES', DIR_WS_INCLUDES . 'boxes/');<br />";
echo "define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');<br />";
echo "define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');<br />";
echo "define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');<br />";
echo "define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');<br />";
echo "<br />";
echo "define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');<br />";
echo "define('DIR_FS_CATALOG', '".$_SERVER['DOCUMENT_ROOT'].str_replace('getroot.php', '', $_SERVER['REQUEST_URI'])."');<br />";
echo "define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');<br />";
echo "define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');<br />";
echo "<br />";
echo "define('DB_SERVER', 'localhost');<br />";
echo "define('DB_SERVER_USERNAME', 'username');<br />";
echo "define('DB_SERVER_PASSWORD', 'password');<br />";
echo "define('DB_DATABASE', 'database');<br />";
echo "define('USE_PCONNECT', 'false');<br />";
echo "define('STORE_SESSIONS', 'mysql');<br />";
echo "// Nieuwsbrief instellingen<br />";
echo "define('PHPLIST_DB_SERVER', 'localhost');<br />";
echo "define('PHPLIST_LIST_DB', '');<br />";
echo "define('PHPLIST_TABLE_PREFIX', 'phplist_'); //if a table prefix is used give it here (if none, leave blank)<br />";
echo "define('PHPLIST_DB_USER', '');<br />";
echo "define('PHPLIST_DB_PASSWORD', '');<br />";
echo "define('PHPLIST_SPAGE', '1'); //the number of the subscribepage (must have been created in phplist)<br />";
echo "define('PHPLIST_HTMLEMAIL', '1'); //if to send html email (1) text email (0)<br />";
echo "define('PHPLIST_LISTNUMBERS', '1');//more list => example: 1;2;3;4;5;9<br />";
?>
</body>
</html>