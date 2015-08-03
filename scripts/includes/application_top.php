<?php
// Start the clock for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

// Set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);

// Disable use_trans_sid as tep_href_link() does this manually
  if (function_exists('ini_set')) {
    ini_set('session.use_trans_sid', 0);
  }

// Set the local configuration parameters - mainly for developers
  if (file_exists('includes/local/configure.php')) include('includes/local/configure.php');

// Include application configuration parameters
  require('includes/configure.php');

// Define the project version
  define('PROJECT_VERSION', 'Preview Release 2.2-MS1');

// Used in the "Backup Manager" to compress backups
  define('LOCAL_EXE_GZIP', '/usr/bin/gzip');
  define('LOCAL_EXE_GUNZIP', '/usr/bin/gunzip');
  define('LOCAL_EXE_ZIP', '/usr/local/bin/zip');
  define('LOCAL_EXE_UNZIP', '/usr/local/bin/unzip');

// define the filenames used in the project
  define('FILENAME_DEFAULT', 'index.php');
  define('FILENAME_INFOPAGES', 'infopages.php');
  define('FILENAME_SECTIONS', 'sections.php');
  define('FILENAME_AJAX', 'ajax.php');
  define('FILENAME_LOGIN', 'login.php');
  define('FILENAME_CHANGE_PASSWORD', 'change_password.php');
  define('FILENAME_NEWSLETTERS', 'infopages.php');
  define('FILENAME_RIGHT_COLUMN', 'right_column.php');
  define('FILENAME_LEFT_COLUMN', 'left_column.php');
  define('FILENAME_QUIZ', 'quiz.php');
  define('FILENAME_CATALOG_ACCOUNT_HISTORY_INFO', 'account_history_info.php');
// define the database table names used in the project
  define('TABLE_ADDRESS_BOOK', 'address_book');
  define('TABLE_ADDRESS_FORMAT', 'address_format');
  define('TABLE_BANNERS', 'banners');
  define('TABLE_BANNERS_HISTORY', 'banners_history');
  define('TABLE_CATEGORIES', 'categories');
  define('TABLE_CATEGORIES_DESCRIPTION', 'categories_description');
  define('TABLE_CONFIGURATION', 'configuration');
  define('TABLE_CONFIGURATION_GROUP', 'configuration_group');
  define('TABLE_COUNTRIES', 'countries');
  define('TABLE_CURRENCIES', 'currencies');
  define('TABLE_CUSTOMERS', 'customers');
  define('TABLE_CUSTOMERS_BASKET', 'customers_basket');
  define('TABLE_CUSTOMERS_BASKET_ATTRIBUTES', 'customers_basket_attributes');
  define('TABLE_CUSTOMERS_INFO', 'customers_info');
  define('TABLE_LANGUAGES', 'languages');
  define('TABLE_MANUFACTURERS', 'manufacturers');
  define('TABLE_MANUFACTURERS_INFO', 'manufacturers_info');
  define('TABLE_NEWSLETTERS', 'newsletters');
  define('TABLE_ORDERS', 'orders');
  define('TABLE_ORDERS_PRODUCTS', 'orders_products');
  define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', 'orders_products_attributes');
  define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', 'orders_products_download');
  define('TABLE_ORDERS_STATUS', 'orders_status');
  define('TABLE_ORDERS_STATUS_HISTORY', 'orders_status_history');
  define('TABLE_ORDERS_TOTAL', 'orders_total');
  define('TABLE_PRODUCTS', 'products');
  define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
  define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');
  define('TABLE_PRODUCTS_DESCRIPTION', 'products_description');
  define('TABLE_PRODUCTS_NOTIFICATIONS', 'products_notifications');
  define('TABLE_PRODUCTS_OPTIONS', 'products_options');
  define('TABLE_PRODUCTS_OPTIONS_VALUES', 'products_options_values');
  define('TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS', 'products_options_values_to_products_options');
  define('TABLE_PRODUCTS_TO_CATEGORIES', 'products_to_categories');
  define('TABLE_REVIEWS', 'reviews');
  define('TABLE_REVIEWS_DESCRIPTION', 'reviews_description');
  define('TABLE_SESSIONS', 'sessions');
  define('TABLE_SPECIALS', 'specials');
  define('TABLE_TAX_CLASS', 'tax_class');
  define('TABLE_TAX_RATES', 'tax_rates');
  define('TABLE_GEO_ZONES', 'geo_zones');
  define('TABLE_ZONES_TO_GEO_ZONES', 'zones_to_geo_zones');
  define('TABLE_WHOS_ONLINE', 'whos_online');
  define('TABLE_ZONES', 'zones');
  define('TABLE_VENDORS', 'vendors');
  define('TABLE_OPENINGSUREN', 'openingsuren');
  //Quiz
  define('TABLE_QUIZES', 'quizes');
  define('TABLE_QUIZES_NAMES', 'quizes_names');
  define('TABLE_QUIZ_QUESTIONS', 'quiz_questions');
  define('TABLE_QUIZ_QUESTIONS_TEXT', 'quiz_questions_text');
  define('TABLE_QUIZ_ANSWERS', 'quiz_answers');
  define('TABLE_QUIZ_ANSWERS_TEXT', 'quiz_answers_text');
  define('TABLE_QUIZ_TRACK', 'quiz_track');
	define('TABLE_CATEGORIES_TO_MANUFACTURERS', 'categories_to_manufacturers');
	define('TABLE_INFOPAGES', 'infopages');
	define('TABLE_INFOPAGES_TEXT', 'infopages_text');
	define('TABLE_ADMIN', 'admin');
	define('TABLE_IMAGES_CAROUSEL', 'images_carousel');
	define('TABLE_BOXES', 'boxes');

// customization for the design layout
  define('BOX_WIDTH', 210); // how wide the boxes should be in pixels (default: 125)

// Define how do we update currency exchange rates
// Possible values are 'oanda' 'xe' or ''
  define('CURRENCY_SERVER_PRIMARY', 'oanda');
  define('CURRENCY_SERVER_BACKUP', 'xe');

// include the database functions
  require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
  tep_db_connect() or die('Unable to connect to database server!');

// set application wide parameters
	if( tep_db_num_rows( tep_db_query("SHOW TABLES LIKE 'gegevens'"))) {
	$shop_query = tep_db_query("select * FROM gegevens"); 	
	$query = tep_db_fetch_array($shop_query);
	define('STORE_NAME', $query['STORE_NAME']);
	define('STORE_OWNER_EMAIL_ADDRESS', $query['STORE_EMAIL']);
	define('STORE_STREET_ADDRESS', $query['STORE_STREET']);
	define('STORE_POSTCODE', $query['STORE_POSTCODE']);
	define('STORE_CITY', $query['STORE_CITY']);
	define('STORE_PROVINCE', $query['STORE_PROVINCE']);
	define('STORE_COUNTRY_ID', $query['STORE_COUNTRY_ID']);
	define('STORE_TELEPHONE', $query['STORE_PHONE']);
	define('STORE_FAX', $query['STORE_FAX']);
	define('STORE_WEBSITE', $query['STORE_WEBSITE']);
	define('STORE_IMAGE', $query['STORE_IMAGE']);
	define('STORE_BTW', $query['STORE_BTW']);
	define('STORE_RPR', $query['STORE_RPR']);
	define('STORE_PART_OFF', $query['STORE_PART_OFF']);
	define('STORE_LAT', $query['STORE_LAT']);
	define('STORE_LNG', $query['STORE_LNG']);
	}

  $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION . '');
  while ($configuration = tep_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }
  	if( tep_db_num_rows( tep_db_query("SHOW TABLES LIKE 'gegevens'"))) {
  	define('STORE_NAME', $query['STORE_NAME']);
	define('STORE_OWNER_EMAIL_ADDRESS', $query['STORE_EMAIL']);
	define('STORE_STREET_ADDRESS', $query['STORE_STREET']);
	define('STORE_POSTCODE', $query['STORE_POSTCODE']);
	define('STORE_CITY', $query['STORE_CITY']);
	define('STORE_PROVINCE', $query['STORE_PROVINCE']);
	define('STORE_COUNTRY_ID', $query['STORE_COUNTRY_ID']);
	define('STORE_TELEPHONE', $query['STORE_PHONE']);
	define('STORE_FAX', $query['STORE_FAX']);
	define('STORE_WEBSITE', $query['STORE_WEBSITE']);
	define('STORE_IMAGE', $query['STORE_IMAGE']);
	define('STORE_BTW', $query['STORE_BTW']);
	define('STORE_RPR', $query['STORE_RPR']);
	define('STORE_PART_OFF', $query['STORE_PART_OFF']);
	define('STORE_LAT', $query['STORE_LAT']);
	define('STORE_LNG', $query['STORE_LNG']);
	}
  //#################Local store settings###################//
  //Connect to local DB
  tep_db_connect(DB_SERVER_LOCAL, DB_SERVER_USERNAME_LOCAL, DB_SERVER_PASSWORD_LOCAL, DB_DATABASE_LOCAL, 'db_link_local');
  //Get all available categories
 /* $categories_query = tep_db_query("SELECT DISTINCT categories_id FROM ".TABLE_CATEGORIES_TO_MANUFACTURERS, 'db_link_local');
  $c_filter = Array();
  while ($category = tep_db_fetch_array($categories_query))
  {
  	$c_filter[] = $category['categories_id'];
	}
  $c_filter_list = join(',', $c_filter);

  //Get all available manufacturers
  $manu_query = tep_db_query("SELECT DISTINCT manufacturers_id FROM ".TABLE_CATEGORIES_TO_MANUFACTURERS, 'db_link_local');
  $m_filter = Array();
  while ($manu = tep_db_fetch_array($manu_query))
  	$m_filter[] = $manu['manufacturers_id'];
  $m_filter_list = join(',', $m_filter);

  //Get manufacturer-category pairs
  $c2m_query = tep_db_query("SELECT * FROM ".TABLE_CATEGORIES_TO_MANUFACTURERS, 'db_link_local');
  $c2m_filter = Array();
  while ($c2m = tep_db_fetch_array($c2m_query))
  	$c2m_filter[] = '('.$c2m['manufacturers_id'].', '.$c2m['categories_id'].')';
  $c2m_list = join(',', $c2m_filter);*/
  //########################################################//

// initialize the logger class
  require(DIR_WS_CLASSES . 'logger.php');

// include shopping cart class
  require(DIR_WS_CLASSES . 'shopping_cart.php');

// some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');

// check to see if php implemented session management functions - if not, include php3/php4 compatible session class
  if (!function_exists('session_start')) {
    define('PHP_SESSION_NAME', 'sID');
    define('PHP_SESSION_SAVE_PATH', '/tmp');

    include(DIR_WS_CLASSES . 'sessions.php');
  }

// define how the session functions will be used
  require(DIR_WS_FUNCTIONS . 'sessions.php');
  tep_session_name('osCAdminsID');

// lets start our session
  tep_session_start();
  if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, substr(DIR_WS_ADMIN, 0, -1));
  }

// language
  require(DIR_WS_FUNCTIONS . 'languages.php');
  if ( (!$language) || ($HTTP_GET_VARS['language']) ) {
    if (!$language) {
      tep_session_register('language');
      tep_session_register('languages_id');
    }

    $language = tep_get_languages_directory($HTTP_GET_VARS['language']);
    if (!$language) $language = tep_get_languages_directory(DEFAULT_LANGUAGE);
  }

// include the language translations
  require(DIR_WS_LANGUAGES . $language . '.php');
  $current_page = explode('\?', basename($_SERVER['PHP_SELF']));
  if (file_exists(DIR_WS_LANGUAGES . $language . '/' . $current_page)) {
    include(DIR_WS_LANGUAGES . $language . '/' . $current_page);
  }

// define our general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');

// define our localization functions
  require(DIR_WS_FUNCTIONS . 'localization.php');

// setup our boxes
  require(DIR_WS_CLASSES . 'table_block.php');
  require(DIR_WS_CLASSES . 'box.php');

// initialize the message stack for output messages
  require(DIR_WS_CLASSES . 'message_stack.php');
  $messageStack = new messageStack;

// split-page-results
  require(DIR_WS_CLASSES . 'split_page_results.php');

// entry/item info classes
  require(DIR_WS_CLASSES . 'object_info.php');

// email classes
  require(DIR_WS_CLASSES . 'mime.php');
  require(DIR_WS_CLASSES . 'email.php');

// calculate category path
  $cPath = $HTTP_GET_VARS['cPath'];
  if (strlen($cPath) > 0) {
    $cPath_array = explode('_', $cPath);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
  } else {
    $current_category_id = 0;
  }

// default open navigation box
  if (!tep_session_is_registered('selected_box')) {
    tep_session_register('selected_box');
    $selected_box = 'configuration';
  }
  if ($HTTP_GET_VARS['selected_box']) {
    $selected_box = $HTTP_GET_VARS['selected_box'];
  }

// the following cache blocks are used in the Tools->Cache section
// ('language' in the filename is automatically replaced by available languages)
  $cache_blocks = array(array('title' => TEXT_CACHE_CATEGORIES, 'code' => 'categories', 'file' => 'categories_box-language.cache', 'multiple' => true),
                        array('title' => TEXT_CACHE_MANUFACTURERS, 'code' => 'manufacturers', 'file' => 'manufacturers_box-language.cache', 'multiple' => true),
                        array('title' => TEXT_CACHE_ALSO_PURCHASED, 'code' => 'also_purchased', 'file' => 'also_purchased-language.cache', 'multiple' => true)
                       );

// check if a default currency is set
  if (!defined('DEFAULT_CURRENCY')) {
    $messageStack->add(ERROR_NO_DEFAULT_CURRENCY_DEFINED, 'error');
  }

// check if a default language is set
  if (!defined('DEFAULT_LANGUAGE')) {
    $messageStack->add(ERROR_NO_DEFAULT_LANGUAGE_DEFINED, 'error');
  }

define('MY_CACHE_DIR', str_replace('/public_html', '/public_html/cache', DIR_FS_CATALOG));
if ($_SESSION['login'] != '')
{
	$access_query = tep_db_query("SELECT access_level FROM admin WHERE login = '".$_SESSION['login']."'", 'db_link_local');
	$access = tep_db_fetch_array($access_query);
	define('ACCESS_LEVEL', $access['access_level']);
}
foreach ($_GET as $key => $value ) {
	${$key}	= $value;
}
?>