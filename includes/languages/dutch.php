<?php
setlocale(LC_TIME, 'nl_NL');
define('DATE_FORMAT_SHORT', '%d/%m/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A %d %B, %Y'); // this is used for strftime()
define('DATE_FORMAT', 'd/m/Y'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');
function tep_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
  }
}
// if USE_DEFAULT_LANGUAGE_CURRENCY is true, use the following currency, instead of the applications default currency (used when changing language)
define('LANGUAGE_CURRENCY', 'EUR');
// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr" lang="nl"');
// charset for web pages and emails
define('CHARSET', 'UTF-8');
define('VISUAL_VERIFY_CODE_CHARACTER_POOL', 'abcdefhkmnpstwxyACDEFGHKMNPSTWXY34567');  //no zeros or O
?>