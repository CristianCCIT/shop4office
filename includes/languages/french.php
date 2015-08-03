<?php
function jc_get_system() {
	$sys = php_uname();
	if (stristr($sys, "Linux"))
		$system = "linux";
	if (stristr($sys, "Windows"))
		$system = "windows";
	if (stristr($sys, "FreeBSD"))
		$system = "freebsd";
	if (stristr($sys, "Macintosh"))
		$system = "macintosh";
	return $system;	
}

$system = jc_get_system();
switch ($system) {
	case "freebsd":
	case "macintosh":
		@setlocale(LC_TIME, "fr_FR");
		break;
	case "windows":
		@setlocale(LC_TIME, "fr");
		break;
	default:
		@setlocale(LC_TIME, "fr_FR");
		break;
}	
define('DATE_FORMAT_SHORT', '%d/%m/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A %d %B %Y'); // this is used for strftime()
define('DATE_FORMAT', 'd/m/Y'); // this is used for date()
define('PHP_DATE_TIME_FORMAT', 'd/m/Y H:i:s'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');
function tep_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 3, 2) . substr($date, 0, 2);
  }
}
// if USE_DEFAULT_LANGUAGE_CURRENCY is true, use the following currency, instead of the applications default currency (used when changing language)
define('LANGUAGE_CURRENCY', 'EUR');
// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr" lang="fr"');
// charset for web pages and emails
define('CHARSET', 'utf-8');
?>