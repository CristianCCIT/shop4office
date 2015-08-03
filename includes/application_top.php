<?php
/* Detect mobile & tablet */
require_once('classes/mobile_detect.php');
$mobile = new Mobile_Detect();
if ($mobile->isMobile() || $mobile->isTablet()) {
  define('mobile', 1);
}

/*
  $Id: application_top.php 1833 2008-01-30 22:03:30Z hpdl $
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2008 osCommerce
  Released under the GNU General Public License
*/
// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
// set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);
// check support for register_globals
  if (function_exists('ini_get') && (ini_get('register_globals') == false) && (PHP_VERSION < 4.3) ) {
    exit('Server Requirement Error: register_globals is disabled in your PHP configuration. This can be enabled in your php.ini configuration file or in the .htaccess file in your catalog directory. Please use PHP 4.3+ if register_globals cannot be enabled on the server.');
  }
// Set the local configuration parameters - mainly for developers
  if (file_exists('includes/local/configure.php')) include('includes/local/configure.php');
// include server parameters
  require('includes/configure.php');
  if (strlen(DB_SERVER) < 1) {
    if (is_dir('install')) {
      header('Location: install/index.php');
    }
  }
// define the project version
  define('PROJECT_VERSION', 'osCommerce Online Merchant v2.2 RC2a');
// some code to solve compatibility issues
  require(DIR_WS_FUNCTIONS . 'compatibility.php');
  include(DIR_WS_CLASSES.'fb.php');
// set the type of request (secure or not)
  $request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';
// set php_self in the local scope
  if (!isset($PHP_SELF)) $PHP_SELF = $HTTP_SERVER_VARS['PHP_SELF'];
  if ($request_type == 'NONSSL') {
    define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
  } else {
    define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
  }
// include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');
// include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');
// customization for the design layout
	define('BOX_WIDTH', 125); // how wide the boxes should be in pixels (default: 125)
// include the database functions
  require(DIR_WS_FUNCTIONS . 'database.php');
// make a connection to the database... now
  tep_db_connect() or die('Unable to connect to database server!');
	if( tep_db_num_rows( tep_db_query("SHOW TABLES LIKE 'gegevens'"))) {
		$shop_query = tep_db_query("select * FROM gegevens"); 	
		$query = tep_db_fetch_array($shop_query);
		define('STORE_NAME', $query['STORE_NAME']);
		define('STORE_OWNER', $query['STORE_NAME']);
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
		define('STORE_REKENINGNR', $query['STORE_REKENINGNR']);
	}
// set the application parameters
  $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = tep_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
  }
// if gzip_compression is enabled, start to buffer the output
  if ( (GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded = extension_loaded('zlib')) && (PHP_VERSION >= '4') ) {
    if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
      if (PHP_VERSION >= '4.0.4') {
        ob_start('ob_gzhandler');
      } else {
        include(DIR_WS_FUNCTIONS . 'gzip_compression.php');
        ob_start();
        ob_implicit_flush();
      }
    } else {
      ini_set('zlib.output_compression_level', GZIP_LEVEL);
    }
  }
// set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
  if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
    if (strlen(getenv('PATH_INFO')) > 1) {
      $GET_array = array();
      $PHP_SELF = str_replace(getenv('PATH_INFO'), '', $PHP_SELF);
      $vars = explode('/', substr(getenv('PATH_INFO'), 1));
      for ($i=0, $n=sizeof($vars); $i<$n; $i++) {
        if (strpos($vars[$i], '[]')) {
          $GET_array[substr($vars[$i], 0, -2)][] = $vars[$i+1];
        } else {
          $_GET[$vars[$i]] = $vars[$i+1];
        }
        $i++;
      }

      if (sizeof($GET_array) > 0) {
        while (list($key, $value) = each($GET_array)) {
          $_GET[$key] = $value;
        }
      }
    }
  }
// define general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'prices.php');
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'seo.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');
  require(DIR_WS_FUNCTIONS . 'newsletter.php');
  if (SOAP_STATUS == 'true') {
	require(DIR_WS_FUNCTIONS . 'soap.php');
	foreach (glob(DIR_WS_MODULES."webservices/*.php") as $filename) {
		include $filename;
	}
  }
  if (USE_PRICES_TO_QTY == 'true') {
	require(DIR_WS_FUNCTIONS . 'plant_functions.php');
  }
  /*FORUM*/
  if ( (FORUM_ACTIVE=='true') && (!strstr($_SERVER['PHP_SELF'], '/forum')) ) {
	define('IN_PHPBB', true);
	define('ROOT_PATH', DIR_FS_CATALOG."forum");
	
	if (!defined('IN_PHPBB') || !defined('ROOT_PATH')) {
		exit();
	}
	
	$phpEx = "php";
	$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : ROOT_PATH . '/';
	include($phpbb_root_path . 'common.' . $phpEx);
  }
  /*FORUM*/

// set the cookie domain
  $cookie_domain = (($request_type == 'NONSSL') ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN);
  $cookie_path = (($request_type == 'NONSSL') ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH);
// include Customer class
  require(DIR_WS_CLASSES . 'Customer.php');
// include cache functions if enabled
  include(DIR_WS_FUNCTIONS . 'cache.php');
// include shopping cart class
  require(DIR_WS_CLASSES . 'shopping_cart.php');
// include taglcoud
  require(DIR_WS_CLASSES . 'tagcloud.php');
// include navigation history class
  require(DIR_WS_CLASSES . 'navigation_history.php');
// check if sessions are supported, otherwise use the php3 compatible session class
  if (!function_exists('session_start')) {
    define('PHP_SESSION_NAME', 'osCsid');
    define('PHP_SESSION_PATH', $cookie_path);
    define('PHP_SESSION_DOMAIN', $cookie_domain);
	if (SESSION_PATH=='relative') {
		$sessions_path = DIR_FS_CATALOG.SESSION_WRITE_DIRECTORY;
	} else {
		$sessions_path = SESSION_WRITE_DIRECTORY;
	}
    define('PHP_SESSION_SAVE_PATH', $sessions_path);
    include(DIR_WS_CLASSES . 'sessions.php');
  }
// define how the session functions will be used
  require(DIR_WS_FUNCTIONS . 'sessions.php');
// set the session name and save path
  tep_session_name('osCsid');
  tep_session_save_path(SESSION_WRITE_DIRECTORY);
  /*autologin*/
  setcookie("TEMPCOOKIE", "CookieOn", time() + 60 * 60);
  $cookieinfo = $_COOKIE["TEMPCOOKIE"];
  if ($cookieinfo == "CookieOn") {
    global $cookies_on;
    $cookies_on = true;
  }
/*autologin*/
// set the session cookie parameters
   if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, $cookie_path, $cookie_domain);
  } elseif (function_exists('ini_set')) {
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', $cookie_path);
    ini_set('session.cookie_domain', $cookie_domain);
  }
  
// set the session ID if it exists
   if (isset($_POST[tep_session_name()])) {
     tep_session_id($_POST[tep_session_name()]);
   } else if (isset($_GET[tep_session_name()]) && isset($_GET['customer_id'])) {
	   tep_session_id($_GET[tep_session_name()]);
   } elseif ( ($request_type == 'SSL') && isset($_GET[tep_session_name()]) ) {
     tep_session_id($_GET[tep_session_name()]);
   }
// start the session
  $session_started = false;
  if (SESSION_FORCE_COOKIE_USE == 'True') {
    tep_setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30, $cookie_path, $cookie_domain);
    if (isset($_COOKIE['cookie_test'])) {
      tep_session_start();
      $session_started = true;
    }
  } elseif (SESSION_BLOCK_SPIDERS == 'True') {
    $user_agent = strtolower(getenv('HTTP_USER_AGENT'));
    $spider_flag = false;
    if (tep_not_null($user_agent)) {
      $spiders = file(DIR_WS_INCLUDES . 'spiders.txt');
      for ($i=0, $n=sizeof($spiders); $i<$n; $i++) {
        if (tep_not_null($spiders[$i])) {
          if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
            $spider_flag = true;
            break;
          }
        }
      }
    }
    if ($spider_flag == false) {
      tep_session_start();
      $session_started = true;
    }
  } else {
    tep_session_start();
    $session_started = true;
  }
  if ( ($session_started == true) && (PHP_VERSION >= 4.3) && function_exists('ini_get') && (ini_get('register_globals') == false) ) {
    extract($_SESSION, EXTR_OVERWRITE+EXTR_REFS);
  }
// set SID once, even if empty
  $SID = (defined('SID') ? SID : '');
// verify the ssl_session_id if the feature is enabled
  if ( ($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started == true) ) {
    $ssl_session_id = getenv('SSL_SESSION_ID');
    if (!tep_session_is_registered('SSL_SESSION_ID')) {
      $SESSION_SSL_ID = $ssl_session_id;
      tep_session_register('SESSION_SSL_ID');
    }
    if ($SESSION_SSL_ID != $ssl_session_id) {
      tep_session_destroy();
      tep_redirect(tep_href_link(FILENAME_SSL_CHECK));
    }
  }
// verify the browser user agent if the feature is enabled
  if (SESSION_CHECK_USER_AGENT == 'True') {
    $http_user_agent = getenv('HTTP_USER_AGENT');
    if (!tep_session_is_registered('SESSION_USER_AGENT')) {
      $SESSION_USER_AGENT = $http_user_agent;
      tep_session_register('SESSION_USER_AGENT');
    }
    if ($SESSION_USER_AGENT != $http_user_agent) {
      tep_session_destroy();
      tep_redirect(tep_href_link(FILENAME_LOGIN));
    }
  }
// verify the IP address if the feature is enabled
  if (SESSION_CHECK_IP_ADDRESS == 'True') {
    $ip_address = tep_get_ip_address();
    if (!tep_session_is_registered('SESSION_IP_ADDRESS')) {
      $SESSION_IP_ADDRESS = $ip_address;
      tep_session_register('SESSION_IP_ADDRESS');
    }
    if ($SESSION_IP_ADDRESS != $ip_address) {
      tep_session_destroy();
      tep_redirect(tep_href_link(FILENAME_LOGIN));
    }
  }
// create the shopping cart & fix the cart if necesary
  if (tep_session_is_registered('cart') && is_object($cart)) {
    if (PHP_VERSION < 4) {
      $broken_cart = $cart;
      $cart = new shoppingCart;
      $cart->unserialize($broken_cart);
    }
  } else {
    tep_session_register('cart');
    $cart = new shoppingCart;
  }
// include currencies class and create an instance
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
// include the mail classes
  require(DIR_WS_CLASSES . 'mime.php');
  require(DIR_WS_CLASSES . 'email.php');
// set the language
  if (!tep_session_is_registered('language') || isset($_GET['language'])) {
    if (!tep_session_is_registered('language')) {
      tep_session_register('language');
      tep_session_register('languages_id');
      tep_session_register('languages_code');
    }
    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language();
    if (isset($_GET['language']) && tep_not_null($_GET['language'])) {
      $lng->set_language($_GET['language']);
    } else {
      $lng->get_browser_language();
	  if (empty($lng)) {
        $lng->set_language(DEFAULT_LANGUAGE);
      }
    }
    $language = $lng->language['directory'];
    $languages_id = $lng->language['id'];
    $languages_code = $lng->language['code'];
  }
// include the language translations
  require(DIR_WS_LANGUAGES . $language . '.php');
  	$translate_text_query = tep_db_query('SELECT `text`, `translation` FROM translation_text WHERE language_id = "'.(int)$languages_id.'"');
	while ($translate_text = tep_db_fetch_array($translate_text_query)) {
		define($translate_text['text'], $translate_text['translation']);
	}
// currency
  if (!tep_session_is_registered('currency') || isset($_GET['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && (LANGUAGE_CURRENCY != $currency) ) ) {
    if (!tep_session_is_registered('currency')) tep_session_register('currency');
    if (isset($_GET['currency']) && $currencies->is_set($_GET['currency'])) {
      $currency = $_GET['currency'];
    } else {
      $currency = (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
    }
  }
// navigation history
if (!tep_session_is_registered('navigation') || !is_object($navigation)) {
	tep_session_register('navigation');
	$navigation = new navigationHistory;
}
//eof navigation history

//Customer
if (isset($customer_id) && $customer_id > 0) {
	$Customer = new Customer((int)$customer_id);
} else {
	$Customer = new Customer(null);
}
  
// Shopping cart actions
  if (isset($_GET['action'])) {
// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled
    if ($session_started == false) {
      tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
    }

    if (DISPLAY_CART == 'true') {
      $goto =  FILENAME_SHOPPING_CART;
      $parameters = array('action', 'cPath', 'products_id', 'pid');
    } else {
      $goto = basename($PHP_SELF);
      if ($_GET['action'] == 'buy_now') {
        $parameters = array('action', 'pid', 'products_id');
      } else {
        $parameters = array('action', 'pid');
      }
    }
    switch ($_GET['action']) {
		case 'remove_product' : 
			$cart->remove($_GET['products_id']);
		tep_redirect(tep_href_link($goto));
		break;
      // customer wants to update the product quantity in their shopping cart
      case 'update_product' : for ($i=0, $n=sizeof($_POST['products_id']); $i<$n; $i++) {
                                if (in_array($_POST['products_id'][$i], (is_array($_POST['cart_delete']) ? $_POST['cart_delete'] : array()))) {
                                  $cart->remove($_POST['products_id'][$i]);
                                } else {
                                  if (PHP_VERSION < 4) {
                                    // if PHP3, make correction for lack of multidimensional array.
                                    reset($_POST);
                                    while (list($key, $value) = each($_POST)) {
                                      if (is_array($value)) {
                                        while (list($key2, $value2) = each($value)) {
                                          if (preg_match ("/(.*)\]\[(.*)/", $key2, $var)) {
                                            $id2[$var[1]][$var[2]] = $value2;
                                          }
                                        }
                                      }
                                    }
                                    $attributes = ($id2[$_POST['products_id'][$i]]) ? $id2[$_POST['products_id'][$i]] : '';
                                  } else {
                                    $attributes = ($_POST['id'][$_POST['products_id'][$i]]) ? $_POST['id'][$_POST['products_id'][$i]] : '';
                                  }
                                  $cart->add_cart($_POST['products_id'][$i], $_POST['cart_quantity'][$i], $attributes, false);
                                }
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
      // customer adds a product from the products page
      case 'add_product' :    if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
                                $cart->add_cart($_POST['products_id'], $cart->get_quantity(tep_get_uprid($_POST['products_id'], $_POST['id']))+1, $_POST['id']);
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
      // customer adds a product from the products page
      case 'add_multiple_products' :    if (isset($_POST['products_id']) && is_array($_POST['products_id'])) {
		  						foreach($_POST['products_id'] as $ids) {
									if (substr($ids, 0, 1) == '_' ) {
										$ids = substr($ids, 1);
		                                $cart->add_cart($ids, $cart->get_quantity(tep_get_uprid($ids, $_POST['id']))+1, $_POST['id']);
									} else if (strstr($ids, '{') && strstr($ids, '}')) {
										$options = explode('{', $ids);
										$prodId = $options[0];
										$attributes = array();
										unset($options[0]);
										foreach($options as $option) {
											$option = explode('}', $option);
											$attributes['id'][$option[0]] = $option[1]; 
										}
										$cart->add_cart($prodId, $cart->get_quantity(tep_get_uprid($prodId, $attributes['id']))+1, $attributes['id']);
									} else {
										$cart->add_cart($ids, $cart->get_quantity(tep_get_uprid($ids, ''))+1, '');
									}
								}
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
      // performed by the 'buy now' button in product listings and review page
      case 'buy_now' :        if (isset($_GET['products_id'])) {
                                if (tep_has_product_attributes($_GET['products_id'])) {
                                  tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['products_id']));
                                } else {
                                  $cart->add_cart($_GET['products_id'], $cart->get_quantity($_GET['products_id'])+1);
                                }
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
      case 'notify' :         if (tep_session_is_registered('customer_id')) {
                                if (isset($_GET['products_id'])) {
                                  $notify = $_GET['products_id'];
                                } elseif (isset($_GET['notify'])) {
                                  $notify = $_GET['notify'];
                                } elseif (isset($_POST['notify'])) {
                                  $notify = $_POST['notify'];
                                } else {
                                  tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'notify'))));
                                }
                                if (!is_array($notify)) $notify = array($notify);
                                for ($i=0, $n=sizeof($notify); $i<$n; $i++) {
                                  $check_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . $notify[$i] . "' and customers_id = '" . $customer_id . "'");
                                  $check = tep_db_fetch_array($check_query);
                                  if ($check['count'] < 1) {
                                    tep_db_query("insert into " . TABLE_PRODUCTS_NOTIFICATIONS . " (products_id, customers_id, date_added) values ('" . $notify[$i] . "', '" . $customer_id . "', now())");
                                  }
                                }
                                tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'notify'))));
                              } else {
                                $navigation->set_snapshot();
                                tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
                              }
                              break;
      case 'notify_remove' :  if (tep_session_is_registered('customer_id') && isset($_GET['products_id'])) {
                                $check_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . $_GET['products_id'] . "' and customers_id = '" . $customer_id . "'");
                                $check = tep_db_fetch_array($check_query);
                                if ($check['count'] > 0) {
                                  tep_db_query("delete from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . $_GET['products_id'] . "' and customers_id = '" . $customer_id . "'");
                                }
                                tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action'))));
                              } else {
                                $navigation->set_snapshot();
                                tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
                              }
                              break;
      case 'cust_order' :     if (tep_session_is_registered('customer_id') && isset($_GET['pid'])) {
                                if (tep_has_product_attributes($_GET['pid'])) {
                                  tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['pid']));
                                } else {
                                  $cart->add_cart($_GET['pid'], $cart->get_quantity($_GET['pid'])+1);
                                }
                              }
                              tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
                              break;
    }
  }

// include the who's online functions
  require(DIR_WS_FUNCTIONS . 'whos_online.php');
  tep_update_whos_online();

// include the password crypto functions
  require(DIR_WS_FUNCTIONS . 'password_funcs.php');

// include validation functions (right now only email address)
  require(DIR_WS_FUNCTIONS . 'validations.php');
  
  if ( (isset($customers_email_address)) && ($customers_email_address!='')  && (tep_validate_email($customers_email_address)==false) ) {
	  if ( (!strstr($_SERVER['PHP_SELF'], FILENAME_ACCOUNT_SUBMIT_EMAIL)) && (!strstr($_SERVER['PHP_SELF'], FILENAME_LOGOFF)) ) {
		  tep_redirect(tep_href_link(FILENAME_ACCOUNT_SUBMIT_EMAIL));
	  }
  }

// split-page-results
  require(DIR_WS_CLASSES . 'split_page_results.php');

// infobox
  require(DIR_WS_CLASSES . 'boxes.php');

// auto activate and expire banners
  require(DIR_WS_FUNCTIONS . 'banner.php');
  tep_activate_banners();
  tep_expire_banners();

// auto expire special products
  require(DIR_WS_FUNCTIONS . 'specials.php');
  tep_expire_specials();
  require(DIR_WS_FUNCTIONS . 'modules.php');
  require(DIR_WS_FUNCTIONS . 'navigatie.php');
  require(DIR_WS_FUNCTIONS . 'infopages.php');
  require(DIR_WS_FUNCTIONS . 'specifications.php');

// calculate category path
  if (isset($_GET['cPath'])) {
    $cPath = $_GET['cPath'];
  } elseif (isset($_GET['products_id']) && !isset($_GET['manufacturers_id'])) {
    $cPath = tep_get_product_path($_GET['products_id']);
  } else {
    $cPath = '';
  }

  if (tep_not_null($cPath)) {
    $cPath_array = tep_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
  } else {
    $current_category_id = 0;
  }
  
  /*autologin*/
  if ($cookies_on == true) {
    if (ALLOW_AUTOLOGON == 'true') {                                // Is Autologon enabled?
      if (basename($_SERVER['PHP_SELF']) != FILENAME_LOGIN) {                  // yes
        if (!tep_session_is_registered('customer_id')) {
          include('includes/modules/autologon.php');
    	}
      }
    } else {
      setcookie("email_address", "", time() - 3600, $cookie_path);  //no, delete email_address cookie
      setcookie("password", "", time() - 3600, $cookie_path);       //no, delete password cookie
    }
  }
  /*autologin*/

// include the breadcrumb class and start the breadcrumb trail
  require(DIR_WS_CLASSES . 'breadcrumb.php');
  $breadcrumb = new breadcrumb;
  $breadcrumb->add(Translate('Home'), tep_href_link(FILENAME_DEFAULT));
  // START STS 4.5.8
  if (!strstr($_SERVER['SCRIPT_NAME'], 'rewrite.php') && !strstr($_SERVER['SCRIPT_NAME'], 'checkout.php')) {
	  require (DIR_WS_CLASSES.'sts.php');
	  $sts= new sts();
	  $sts->start_capture();
  }
  // END STS 4.5.8

// initialize the message stack for output messages
  require(DIR_WS_CLASSES . 'message_stack.php');
  $messageStack = new messageStack;
require_once(DIR_WS_CLASSES . 'cross_selling.php');
// set which precautions should be checked
  define('WARN_INSTALL_EXISTENCE', 'true');
  define('WARN_CONFIG_WRITEABLE', 'true');
  define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
  define('WARN_SESSION_AUTO_START', 'true');
  define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');
  // LINE ADDED - MOD: CREDIT CLASS Gift Voucher Contribution
  require(DIR_WS_FUNCTIONS . 'add_ccgvdc_application_top.php');  // ICW CREDIT CLASS Gift Voucher Addittion
?>