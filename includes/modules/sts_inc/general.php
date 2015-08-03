<?php

/*

$Id: general.php,v 4.5.4 2005/11/03 05:57:21 rigadin Exp $



osCommerce, Open Source E-Commerce Solutions

http://www.oscommerce.com



Copyright (c) 2005 osCommerce



Released under the GNU General Public License



Based on: Simple Template System (STS) - Copyright (c) 2004 Brian Gallagher - brian@diamondsea.com

STS v4.6 by Bill Kellum bkellum (www.soundsgoodpro.com)

*/


// Set $templatedir and $templatepath (aliases) to current template path on web server, allowing for HTTP/HTTPS differences, removing the trailing slash

$sts->template['templatedir'] = substr(((($request_type == 'SSL') ? DIR_WS_HTTPS_CATALOG : DIR_WS_HTTP_CATALOG) . STS_TEMPLATE_DIR),
	0, -1);


$sts->template['htmlparams'] = HTML_PARAMS; // Added in v4.0.7


$sts->template['date'] = strftime(DATE_FORMAT_LONG);

$sts->template['langid'] = $languages_id; // used for images in different languages

$sts->template['language'] = $language;


$sts->template['sid'] = tep_session_name() . '=' . tep_session_id();

$sts->template['cataloglogo'] = '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image(STS_TEMPLATE_DIR . 'images/' . $language . '/header_logo.gif',
		STORE_NAME) . '</a>'; // Modified in v4.3

$sts->template['urlcataloglogo'] = tep_href_link(FILENAME_DEFAULT);


$sts->template['cartlogo'] = '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, '',
		'SSL') . '">' . tep_image(STS_TEMPLATE_DIR . 'images/' . $language . '/header_cart.gif',
		HEADER_TITLE_CART_CONTENTS) . '</a>';


$sts->template['myaccountlogo'] = '<a href=' . tep_href_link(FILENAME_ACCOUNT, '',
		'SSL') . ' class="headerNavigation">' . tep_image(STS_TEMPLATE_DIR . 'images/' . $language . '/header_account.gif',
		HEADER_TITLE_MY_ACCOUNT) . '</a>';


// Get logo from template folder, depending on language. Changed in v4.3

$sts->template['checkoutlogo'] = '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '',
		'SSL') . '">' . tep_image(STS_TEMPLATE_DIR . 'images/' . $language . '/header_checkout.gif',
		HEADER_TITLE_CHECKOUT) . '</a>';


$sts->template['breadcrumbs'] = $breadcrumb->trail(' <span class="raquo"><span>&raquo;</span></span> ');


if (tep_session_is_registered('customer_id')) {

	$sts->template['myaccount'] = '<a href=' . tep_href_link(FILENAME_ACCOUNT, '',
			'SSL') . ' class="headerNavigation">' . HEADER_TITLE_MY_ACCOUNT . '</a>';

	$sts->template['urlmyaccount'] = tep_href_link(FILENAME_ACCOUNT, '', 'SSL');

	$sts->template['logoff'] = '<a href=' . tep_href_link(FILENAME_LOGOFF, '',
			'SSL') . ' class="headerNavigation">' . HEADER_TITLE_LOGOFF . '</a>';

	$sts->template['urllogoff'] = tep_href_link(FILENAME_LOGOFF, '', 'SSL');

	$sts->template['myaccountlogoff'] = $sts->template['myaccount'] . " | " . $sts->template['logoff'];

// Next tags added in v4.3

	$sts->template['loginofflogo'] = '<a href=' . tep_href_link(FILENAME_LOGOFF, '',
			'SSL') . ' class="headerNavigation">' . tep_image(STS_TEMPLATE_DIR . 'images/' . $language . '/header_logoff.gif',
			HEADER_TITLE_LOGOFF) . '</a>';

} else {

	$sts->template['myaccount'] = '<a href=' . tep_href_link(FILENAME_ACCOUNT, '',
			'SSL') . ' class="headerNavigation">' . HEADER_TITLE_MY_ACCOUNT . '</a>';

	$sts->template['urlmyaccount'] = tep_href_link(FILENAME_ACCOUNT, '', 'SSL');

	$sts->template['logoff'] = '';

	$sts->template['urllogoff'] = '';

	$sts->template['myaccountlogoff'] = $sts->template['myaccount'];

// Next tags added in v4.3

	$sts->template['loginofflogo'] = '<a href=' . tep_href_link(FILENAME_LOGIN, '',
			'SSL') . ' class="headerNavigation">' . tep_image(STS_TEMPLATE_DIR . 'images/' . $language . '/header_login.gif',
			HEADER_TITLE_LOGIN) . '</a>';

}

// v4.5: use SSL if possible.

$sts->template['cartcontents'] = '<a href=' . tep_href_link(FILENAME_SHOPPING_CART, '',
		'SSL') . ' class="headerNavigation">' . HEADER_TITLE_CART_CONTENTS . '</a>';

$sts->template['urlcartcontents'] = tep_href_link(FILENAME_SHOPPING_CART, '',
	'SSL');  // A real URL since v4.3, before was same as $cartcontents


$sts->template['checkout'] = '<a href=' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '',
		'SSL') . ' class="headerNavigation">' . HEADER_TITLE_CHECKOUT . '</a>';

$sts->template['urlcheckout'] = tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL');


// Next tags added in v4.3 to display an image according to language and linking to the contact us page.

$sts->template['contactlogo'] = '<a href=' . tep_href_link(FILENAME_CONTACT_US) . ' class="headerNavigation">' . tep_image(STS_TEMPLATE_DIR . 'images/' . $language . '/header_contact_us.gif',
		BOX_INFORMATION_CONTACT) . '</a>';


// Tags generally displayed in the footer. =============================================

// Get the number of requests

require(DIR_WS_INCLUDES . 'counter.php');

$sts->template['numrequests'] = $counter_now . ' ' . FOOTER_TEXT_REQUESTS_SINCE . ' ' . $counter_startdate_formatted;


$sts->template['footer_text'] = FOOTER_TEXT_BODY;


// Get the banner if any

$sts->start_capture();

if ($banner = tep_banner_exists('dynamic', '468x50')) {

	echo tep_display_banner('static', $banner);

}

$sts->stop_capture('banner_only');


// START STS 4.5.4: error & info messages, created in header.php for osCommerce without STS

$sts->start_capture();

if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {

	?>

	<table border="0" width="100%" cellspacing="0" cellpadding="2">

		<tr class="headerError">

			<td class="headerError"><?php echo htmlspecialchars(urldecode($_GET['error_message'])); ?></td>

		</tr>

	</table>

<?php

}

$sts->restart_capture('error_message');

if (isset($_GET['info_message']) && tep_not_null($_GET['info_message'])) {

	?>

	<table border="0" width="100%" cellspacing="0" cellpadding="2">

		<tr class="headerInfo">

			<td class="headerInfo"><?php echo htmlspecialchars($_GET['info_message']); ?></td>

		</tr>

	</table>

<?php

}

$sts->stop_capture('info_message');

// END STS 4.5.4


//header tags

if (tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'seo'"))) {

	$std_meta_query = tep_db_query('SELECT description, name FROM seo');

	while ($std_meta = tep_db_fetch_array($std_meta_query)) {

		if ($std_meta['name'] == 'meta_description') {

			$std_description = $std_meta['description'];

		}

		if ($std_meta['name'] == 'meta_keywords') {

			$std_keywords = $std_meta['description'];

		}

		if ($std_meta['name'] == 'meta_title') {

			$std_title = $std_meta['description'];

		}

	}

} else {

	$std_title = '';

	$std_description = '';

	$std_keywords = '';

}

$std_keywords = explode(', ', $std_keywords);

$canonical_url = '';

$header_tags_array = array();

$keywords = array();

$catname = '';

$keywords_product = '';

$headertags_extra = '';

$headertags_extra_css = '';

$headertags_extra_js = '';


switch (true) {

	case (basename($_SERVER['PHP_SELF']) === FILENAME_PRODUCT_INFO):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_PRODUCT_PRINT):

		$producttags_query = tep_db_query('SELECT p.products_model, pd.products_name, pd.products_description, pd.products_technical, pd.meta_title, pd.meta_keywords, pd.meta_description, p2c.categories_id, m.manufacturers_name FROM products p, products_description pd, products_to_categories p2c, manufacturers m WHERE p.products_id = pd.products_id AND p.products_id = p2c.products_id AND p.manufacturers_id = m.manufacturers_id AND p.products_id = "' . $_GET['products_id'] . '" AND pd.language_id = "' . (int)$languages_id . '"');

		$producttags = tep_db_fetch_array($producttags_query);

		$manufacturer = $producttags['manufacturers_name'];

		// Canonical URL add-on

		if ($_GET['products_id'] != '') {

			$canonical_url = tep_href_link(basename($_SERVER['PHP_SELF']), 'products_id=' . (int)$_GET['products_id'],
				'NONSSL', false);

		}

		// get keywords

		$defaultKeywords = explode(', ', $producttags['meta_keywords']);

		if ($_GET['products_id']) {

			$keywords[] = strtolower($producttags['products_name']);

			$keywords[] = strtolower($producttags['products_model']);

			$keywords[] = strtolower($manufacturer);

			$cPath = tep_get_product_path($_GET['products_id']);

			$cPath_array = tep_parse_category_path($cPath);

			if (isset($cPath_array)) {

				for ($i = 0, $n = sizeof($cPath_array); $i < $n; $i++) {

					$categories_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$cPath_array[$i] . "' and language_id = '" . (int)$languages_id . "'");

					if (tep_db_num_rows($categories_query) > 0) {

						$categories = tep_db_fetch_array($categories_query);

						if ($categories['categories_name']) {

							$keywords[] = strtolower(trim($categories['categories_name']));

							$catname = strtolower(trim($categories['categories_name'])) . ' - ' . $catname; // get categories for title

						}

					}

				}

			}

		}

		$catname = substr($catname, 0, -3);

		$defaultKeywords = array_unique($defaultKeywords);

		if (count($defaultKeywords) >= 20) {

			for ($i = 0; $i < 20; $i++) {

				if ($defaultKeywords[$i] != '') {

					$keywords_product .= $defaultKeywords[$i] . ', ';

				}

			}

		} else {

			$allKeywords = array_merge($defaultKeywords, $keywords);

			$allKeywords = array_unique($allKeywords);

			if (count($allKeywords) > 20) {

				for ($i = 0; $i < 20; $i++) {

					if ($allKeywords[$i]) {
						$keywords_product .= $allKeywords[$i] . ', ';
					}

				}

			} else {

				foreach ($allKeywords as $cld => $separated) {

					if ($separated) {
						$keywords_product .= $separated . ', ';
					}

				}

			}

		}

		// EOF Get keywords

		// get Meta description

		if ($producttags['meta_description'] != '') {

			$meta_description = $producttags['meta_description'];

		} else {

			$meta_description = $producttags['products_description'];

		}


		// EOF get meta Description

		if ($producttags['meta_title'] != '') {

			$header_tags_array['title'] = $producttags['products_name'] . ' - ' . $producttags['meta_title'] . ' - ' . STORE_NAME;

		} else {

			if (basename($_SERVER['PHP_SELF']) === FILENAME_PRODUCT_PRINT) {

				$header_tags_array['title'] = $producttags['products_name'] . ' - ' . $manufacturer . ' ' . $producttags['products_model'] . ' ' . Translate('op') . ' ' . STORE_NAME;

				$headertags_extra_css .= ' <link rel="stylesheet" type="text/css" href="$templatedir$/css/style_printfriendly.css" />' . "\n";
				$headertags_extra_css .= ' <link rel="stylesheet" type="text/css" href="$templatedir$/css/style_print.css" media="print" />' . "\n";

			} else {

				$header_tags_array['title'] = $producttags['products_name'] . ' - ' . $manufacturer . ' ' . $producttags['products_model'] . ' - ' . $catname . ' ' . Translate('op') . ' ' . STORE_NAME;

			}

		}

		$header_tags_array['keywords'] = substr($keywords_product, 0, -2);

		$header_tags_array['desc'] = $meta_description;

		//Get extra meta info

		if (tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'seo'"))) {

			$defaultTags_query = tep_db_query('SELECT * FROM seo');

			while ($defaultTags = tep_db_fetch_array($defaultTags_query)) {

				if ($defaultTags['name'] == 'meta_language' && $defaultTags['value'] == '1') {

					$langName = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

					$headertags_extra .= ' <meta http-equiv="Content-Language" content="' . $langName[0] . '" />' . "\n"; // gives the language for this page

				}

				if ($defaultTags['name'] == 'meta_google' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="googlebot" content="all" />' . "\n";
				} //google will index, follow and archive the page

				if ($defaultTags['name'] == 'meta_noodp' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noodp" />' . "\n";
				} //force search engines to use this description meta tag instead of theres.

				if ($defaultTags['name'] == 'meta_noydir' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="slurp" content="noydir" />' . "\n";
				} // force yahoo slurp to use this description meta tag instead of yahoo directory

				if ($defaultTags['name'] == 'meta_revisit' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="revisit-after" content="1 days" />' . "\n";
				} // Ask the search engine to revisit the page after 1 day.

				if ($defaultTags['name'] == 'meta_robots' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noindex, nofollow" />' . "\n";
				} // all search engines will index and follow the page.

				if ($defaultTags['name'] == 'meta_unspam' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />' . "\n";
				} //to avoid spambots.

				if ($defaultTags['name'] == 'meta_replyto' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="Reply-to" content="' . STORE_OWNER_EMAIL_ADDRESS . '" />' . "\n";
				} // globaal contact adres for the website.

				if ($defaultTags['name'] == 'meta_canonical' && $defaultTags['value'] == '1') {
					$headertags_extra .= (tep_not_null($canonical_url) ? ' <link rel="canonical" href="' . $canonical_url . '" />' . "\n" : ' <link rel="canonical" href="' . GetCanonicalURL() . '" />' . "\n");
				} // prevent duplicated content for search engines (google, live, yahoo). The canonical url gives the right page link to search engines

			}

		}

		$headertags_extra_css .= ' <link rel="stylesheet" type="text/css" href="' . substr(((($request_type == 'SSL') ? DIR_WS_HTTPS_CATALOG : DIR_WS_HTTP_CATALOG) . STS_TEMPLATE_DIR),
				0, -1) . '/css/jquery.rating.css" />' . "\n";
		$headertags_extra_css .= ' <link rel="stylesheet" type="text/css" href="' . substr(((($request_type == 'SSL') ? DIR_WS_HTTPS_CATALOG : DIR_WS_HTTP_CATALOG) . STS_TEMPLATE_DIR),
				0, -1) . '/css/style_review.css" />' . "\n";
		$headertags_extra_js .= ' <script type="text/javascript" src="includes/js/jquery.rating.js"></script>' . "\n";

		// EOF extra meta info

		break;

	case (basename($_SERVER['PHP_SELF']) === FILENAME_INFOPAGE):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CONDITIONS):

		// Canonical URL add-on

		if ($_GET['page'] != '') {

			$canonical_url = tep_href_link(basename($_SERVER['PHP_SELF']), 'page=' . (int)$_GET['page'], 'NONSSL',
				false);

		}

		$infopagestags_query = tep_db_query('SELECT it.infopages_title, it.meta_title, it.meta_keywords, it.meta_description FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND it.infopages_id = "' . $_GET['page'] . '" AND it.language_id = "' . (int)$languages_id . '"');

		$infopagestags = tep_db_fetch_array($infopagestags_query);

		// get keywords

		$defaultKeywords = explode(', ', $infopagestags['meta_keywords']);

		$keywords[] = strtolower($infopagestags['products_name']);

		$defaultKeywords = array_unique($defaultKeywords);

		if (count($defaultKeywords) >= 20) {

			for ($i = 0; $i < 20; $i++) {

				if ($defaultKeywords[$i] != '') {

					$keywords_product .= $defaultKeywords[$i] . ', ';

				}

			}

		} else {

			$count_extra_words = 20 - count($defaultKeywords);

			foreach ($defaultKeywords as $cld => $separated) {

				if ($separated) {
					$keywords_product .= $separated . ', ';
				}

			}

			for ($i = 0; $i < $count_extra_words; $i++) {

				if ($std_keywords[$i]) {
					$keywords_product .= $std_keywords[$i] . ', ';
				}

			}

		}

		// EOF Get keywords

		// get Meta description

		$meta_description = $infopagestags['meta_description'];

		if ($meta_description == '' || strlen($meta_description) < 100) {

			$meta_description = $meta_description . ' ' . $std_description;

		}

		// EOF get meta Description


		if ($infopagestags['meta_title'] != '') {

			$header_tags_array['title'] = $infopagestags['meta_title'] . ' - ' . STORE_NAME;

		} else {

			if ($infopagestags['infopages_title'] != '') {

				$header_tags_array['title'] = $infopagestags['infopages_title'] . ' - ' . STORE_NAME;

			} else {

				$header_tags_array['title'] = $std_title;

			}

		}

		$header_tags_array['keywords'] = substr($keywords_product, 0, -2);

		$header_tags_array['desc'] = $meta_description;

		//Get extra meta info

		if (tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'seo'"))) {

			$defaultTags_query = tep_db_query('SELECT * FROM seo');

			while ($defaultTags = tep_db_fetch_array($defaultTags_query)) {

				if ($defaultTags['name'] == 'meta_language' && $defaultTags['value'] == '1') {

					$langName = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

					$headertags_extra .= ' <meta http-equiv="Content-Language" content="' . $langName[0] . '" />' . "\n"; // gives the language for this page

				}

				if ($defaultTags['name'] == 'meta_google' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="googlebot" content="all" />' . "\n";
				} //google will index, follow and archive the page

				if ($defaultTags['name'] == 'meta_noodp' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noodp" />' . "\n";
				} //force search engines to use this description meta tag instead of theres.

				if ($defaultTags['name'] == 'meta_noydir' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="slurp" content="noydir" />' . "\n";
				} // force yahoo slurp to use this description meta tag instead of yahoo directory

				if ($defaultTags['name'] == 'meta_revisit' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="revisit-after" content="1 days" />' . "\n";
				} // Ask the search engine to revisit the page after 1 day.

				if ($defaultTags['name'] == 'meta_robots' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noindex, nofollow" />' . "\n";
				} // all search engines will index and follow the page.

				if ($defaultTags['name'] == 'meta_unspam' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />' . "\n";
				} //to avoid spambots.

				if ($defaultTags['name'] == 'meta_replyto' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="Reply-to" content="' . STORE_OWNER_EMAIL_ADDRESS . '" />' . "\n";
				} // globaal contact adres for the website.

				if ($defaultTags['name'] == 'meta_canonical' && $defaultTags['value'] == '1') {
					$headertags_extra .= (tep_not_null($canonical_url) ? ' <link rel="canonical" href="' . $canonical_url . '" />' . "\n" : ' <link rel="canonical" href="' . GetCanonicalURL() . '" />' . "\n");
				} // prevent duplicated content for search engines (google, live, yahoo). The canonical url gives the right page link to search engines

			}

		}

		// EOF extra meta info

		break;

	case (basename($_SERVER['PHP_SELF']) === FILENAME_COMPARE):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_COMPARE_PRINT):

		$category_info_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$_GET['cc'] . "' and language_id = '" . (int)$languages_id . "'");

		$category_info = tep_db_fetch_array($category_info_query);

		$header_tags_array['title'] = $category_info['categories_name'] . ' ' . Translate('Vergelijken');

		if (basename($_SERVER['PHP_SELF']) === FILENAME_COMPARE_PRINT) {
			$headertags_extra_css .= ' <link rel="stylesheet" type="text/css" href="$templatedir$/css/style_printfriendly.css" />' . "\n";
			$headertags_extra_css .= ' <link rel="stylesheet" type="text/css" href="$templatedir$/css/style_print.css" media="print" />' . "\n";
		}

		break;

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_EDIT):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_HISTORY):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_HISTORY_INFO):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_NEWSLETTERS):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_NOTIFICATIONS):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_PASSWORD):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ADDRESS_BOOK):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_404):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CONTACT_US):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ADDRESS_BOOK_PROCESS):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ADVANCED_SEARCH):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ADVANCED_SEARCH_RESULT):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_ALSO_PURCHASED_PRODUCTS):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_CONFIRMATION):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_PAYMENT):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_PAYMENT_ADDRESS):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_PROCESS):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_SHIPPING):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_SHIPPING_ADDRESS):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_SUCCESS):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_COOKIE_USAGE):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CREATE_ACCOUNT):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_CREATE_ACCOUNT_SUCCESS):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_LOGIN):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_LOGOFF):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_PASSWORD_FORGOTTEN):

	case (basename($_SERVER['PHP_SELF']) === FILENAME_SHOPPING_CART):


		$header_tags_array['title'] = HEADING_TITLE . ' - ' . TITLE;

		$header_tags_array['title'] = $std_title;

		$header_tags_array['desc'] = $std_description;

		for ($i = 0; $i < 20; $i++) {

			if ($std_keywords[$i]) {
				$keywords_product .= $std_keywords[$i] . ', ';
			}

		}

		$header_tags_array['keywords'] = $keywords_product;

		if ((basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT) || (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_EDIT) || (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_NEWSLETTERS) || (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_NOTIFICATIONS) || (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_PASSWORD)) {

			$header_tags_array['page_title'] = Translate('Mijn Account');

			$header_tags_array['title'] = Translate('Mijn Account') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_HISTORY) || (basename($_SERVER['PHP_SELF']) === FILENAME_ACCOUNT_HISTORY_INFO)) {

			$header_tags_array['page_title'] = Translate('Bestelgeschiedenis');

			$header_tags_array['title'] = Translate('Bestelgeschiedenis') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_ADDRESS_BOOK) || (basename($_SERVER['PHP_SELF']) === FILENAME_ADDRESS_BOOK_PROCESS)) {

			$header_tags_array['page_title'] = Translate('Adresboek');

			$header_tags_array['title'] = Translate('Adresboek') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_ADVANCED_SEARCH) || (basename($_SERVER['PHP_SELF']) === FILENAME_ADVANCED_SEARCH_RESULT)) {

			$header_tags_array['page_title'] = Translate('Zoeken');

			$header_tags_array['title'] = Translate('Zoeken') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_CONFIRMATION) || (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_PAYMENT) || (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_PAYMENT_ADDRESS) || (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_PROCESS) || (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_SHIPPING) || (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_SHIPPING_ADDRESS) || (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_SUCCESS) || (basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT)) {

			$header_tags_array['page_title'] = Translate('Afrekenen');
			$header_tags_array['title'] = Translate('Afrekenen') . ' ' . Translate('op') . ' ' . STORE_NAME;

			$headertags_extra_css .= ' <link rel="stylesheet" type="text/css" href="' . substr(((($request_type == 'SSL') ? DIR_WS_HTTPS_CATALOG : DIR_WS_HTTP_CATALOG) . STS_TEMPLATE_DIR),
					0, -1) . '/css/style_checkout.css" />' . "\n";
			$headertags_extra_js .= ' <script type="text/javascript" language="javascript" src="ext/jQuery/jQuery.ajaxq.js"></script>' . "\n";
			$headertags_extra_js .= ' <script type="text/javascript" language="javascript" src="ext/jQuery/jQuery.pstrength.js"></script>' . "\n";
			$headertags_extra_js .= ' <script type="text/javascript" language="javascript" src="includes/checkout/checkout.js"></script>' . "\n";

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_CREATE_ACCOUNT) || (basename($_SERVER['PHP_SELF']) === FILENAME_CREATE_ACCOUNT_SUCCESS)) {

			$header_tags_array['page_title'] = Translate('Account aanvragen');

			$header_tags_array['title'] = Translate('Account aanvragen') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_LOGIN)) {

			$header_tags_array['page_title'] = Translate('Login');

			$header_tags_array['title'] = Translate('Login') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_LOGOFF)) {

			$header_tags_array['page_title'] = Translate('Log uit');

			$header_tags_array['title'] = Translate('Log uit') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_PASSWORD_FORGOTTEN)) {

			$header_tags_array['page_title'] = Translate('Wachtwoord vergeten?');

			$header_tags_array['title'] = Translate('Wachtwoord vergeten?') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_SHOPPING_CART)) {

			$header_tags_array['page_title'] = Translate('Winkelwagen');

			$header_tags_array['title'] = Translate('Winkelwagen') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_404)) {

			$header_tags_array['page_title'] = Translate('Pagina niet Gevonden');

			$header_tags_array['title'] = Translate('Onbekende pagina') . ' ' . Translate('op') . ' ' . STORE_NAME;

		} elseif ((basename($_SERVER['PHP_SELF']) === FILENAME_CONTACT_US)) {

			$header_tags_array['page_title'] = Translate('Contacteer Ons');

			$header_tags_array['title'] = STORE_NAME . ' - ' . Translate('Contacteer ons');

		}

		//Get extra meta info

		if (tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'seo'"))) {

			$defaultTags_query = tep_db_query('SELECT * FROM seo');

			while ($defaultTags = tep_db_fetch_array($defaultTags_query)) {

				if ($defaultTags['name'] == 'meta_language' && $defaultTags['value'] == '1') {

					$langName = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

					$headertags_extra .= ' <meta http-equiv="Content-Language" content="' . $langName[0] . '" />' . "\n"; // gives the language for this page

				}

				if ($defaultTags['name'] == 'meta_robots' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noodp, noindex, nofollow" />' . "\n";
				}

				if ($defaultTags['name'] == 'meta_revisit' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="revisit-after" content="1 days" />' . "\n";
				} // Ask the search engine to revisit the page after 1 day.

				if ($defaultTags['name'] == 'meta_unspam' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />' . "\n";
				} //to avoid spambots.

				if ($defaultTags['name'] == 'meta_replyto' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="Reply-to" content="' . STORE_OWNER_EMAIL_ADDRESS . '" />' . "\n";
				} // globaal contact adres for the website.

				if ($defaultTags['name'] == 'meta_canonical' && $defaultTags['value'] == '1') {
					$headertags_extra .= (tep_not_null($canonical_url) ? ' <link rel="canonical" href="' . $canonical_url . '" />' . "\n" : ' <link rel="canonical" href="' . GetCanonicalURL() . '" />' . "\n");
				} // prevent duplicated content for search engines (google, live, yahoo). The canonical url gives the right page link to search engines

			}

		}

		// EOF extra meta info

		break;

	case ((basename($_SERVER['PHP_SELF']) === FILENAME_DEFAULT || $_SERVER['PHP_SELF'] == '/') && (!isset($_GET['cPath'])) && (!isset($_GET['manufacturers_id']))):

		// Canonical URL add-on

		$canonical_url = tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false);

		$infopagestags_query = tep_db_query('SELECT it.infopages_title, it.meta_title, it.meta_keywords, it.meta_description FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND i.type = "home" AND it.language_id = "' . (int)$languages_id . '"');

		$infopagestags = tep_db_fetch_array($infopagestags_query);

		// get keywords

		$defaultKeywords = explode(', ', $infopagestags['meta_keywords']);

		$defaultKeywords = array_unique($defaultKeywords);

		if (count($defaultKeywords) >= 20) {

			for ($i = 0; $i < 20; $i++) {

				if ($defaultKeywords[$i] != '') {

					$keywords_product .= $defaultKeywords[$i] . ', ';

				}

			}

		} else {

			$count_extra_words = 20 - count($defaultKeywords);

			foreach ($defaultKeywords as $cld => $separated) {

				if ($separated) {
					$keywords_product .= $separated . ', ';
				}

			}

			for ($i = 0; $i < $count_extra_words; $i++) {

				if ($std_keywords[$i]) {
					$keywords_product .= $std_keywords[$i] . ', ';
				}

			}

		}

		// EOF Get keywords

		// get Meta description

		$meta_description = $infopagestags['meta_description'];

		if ($meta_description == '' || strlen($meta_description) < 100) {

			$meta_description = $meta_description . ' ' . $std_description;

		}

		// EOF get meta Description


		if ($infopagestags['meta_title'] != '') {

			$header_tags_array['title'] = $infopagestags['meta_title'] . ' - ' . STORE_NAME;

		} else {

			if ($std_title != '') {

				$header_tags_array['title'] = $std_title;

			} else {

				$header_tags_array['title'] = $infopagestags['infopages_title'] . ' - ' . STORE_NAME;

			}

		}

		$header_tags_array['keywords'] = substr($keywords_product, 0, -2);

		$header_tags_array['desc'] = $meta_description;

		//Get extra meta info

		if (tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'seo'"))) {

			$defaultTags_query = tep_db_query('SELECT * FROM seo');

			while ($defaultTags = tep_db_fetch_array($defaultTags_query)) {

				if ($defaultTags['name'] == 'meta_language' && $defaultTags['value'] == '1') {

					$langName = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

					$headertags_extra .= ' <meta http-equiv="Content-Language" content="' . $langName[0] . '" />' . "\n"; // gives the language for this page

				}

				if ($defaultTags['name'] == 'meta_google' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="googlebot" content="all" />' . "\n";
				} //google will index, follow and archive the page

				if ($defaultTags['name'] == 'meta_noodp' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noodp" />' . "\n";
				} //force search engines to use this description meta tag instead of theres.

				if ($defaultTags['name'] == 'meta_noydir' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="slurp" content="noydir" />' . "\n";
				} // force yahoo slurp to use this description meta tag instead of yahoo directory

				if ($defaultTags['name'] == 'meta_revisit' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="revisit-after" content="1 days" />' . "\n";
				} // Ask the search engine to revisit the page after 1 day.

				if ($defaultTags['name'] == 'meta_robots' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noindex, nofollow" />' . "\n";
				} // all search engines will index and follow the page.

				if ($defaultTags['name'] == 'meta_unspam' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />' . "\n";
				} //to avoid spambots.

				if ($defaultTags['name'] == 'meta_replyto' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="Reply-to" content="' . STORE_OWNER_EMAIL_ADDRESS . '" />' . "\n";
				} // globaal contact adres for the website.

				if ($defaultTags['name'] == 'meta_canonical' && $defaultTags['value'] == '1') {
					$headertags_extra .= (tep_not_null($canonical_url) ? ' <link rel="canonical" href="' . $canonical_url . '" />' . "\n" : ' <link rel="canonical" href="' . GetCanonicalURL() . '" />' . "\n");
				} // prevent duplicated content for search engines (google, live, yahoo). The canonical url gives the right page link to search engines

			}

		}
		$headertags_extra_css .= ' <link rel="stylesheet" type="text/css" href="' . substr(((($request_type == 'SSL') ? DIR_WS_HTTPS_CATALOG : DIR_WS_HTTP_CATALOG) . STS_TEMPLATE_DIR),
				0, -1) . '/css/style_home.css" />' . "\n";


		// EOF extra meta info

		break;

	//CATEGORIES

	case ((basename($_SERVER['PHP_SELF']) === FILENAME_DEFAULT) && (isset($_GET['cPath'])) && ($_GET['cPath'] != '')):

		if (strstr($_GET['cPath'], '_')) {

			$categorie = end(explode('_', $_GET['cPath']));

		} else {

			$categorie = $_GET['cPath'];

		}

		$categorytags_query = tep_db_query('SELECT cd.categories_name, cd.meta_title, cd.meta_keywords, cd.meta_description FROM categories_description cd WHERE cd.categories_id = "' . $categorie . '" AND cd.language_id = "' . (int)$languages_id . '"');

		$categorytags = tep_db_fetch_array($categorytags_query);

		// Canonical URL add-on

		$canonical_url = tep_href_link(basename($_SERVER['PHP_SELF']), 'cPath=' . (int)$_GET['cPath'], 'NONSSL', false);

		// get keywords

		$defaultKeywords = explode(', ', $categorytags['meta_keywords']);

		$defaultKeywords = array_unique($defaultKeywords);

		if (count($defaultKeywords) >= 20) {

			for ($i = 0; $i < 20; $i++) {

				if ($defaultKeywords[$i] != '') {

					$keywords_product .= $defaultKeywords[$i] . ', ';

				}

			}

		} else {

			$allKeywords = array_merge($defaultKeywords, $keywords);

			$allKeywords = array_unique($allKeywords);

			if (count($allKeywords) > 20) {

				for ($i = 0; $i < 20; $i++) {

					if ($allKeywords[$i]) {
						$keywords_category .= $allKeywords[$i] . ', ';
					}

				}

			} else {

				foreach ($allKeywords as $cld => $separated) {

					if ($separated) {
						$keywords_category .= $separated . ', ';
					}

				}

			}

		}

		// EOF Get keywords

		// get Meta description

		if ($categorytags['meta_description'] != '') {

			$meta_description = $categorytags['meta_description'];

		} else {

			$meta_description = $categorytags['categories_name'] . ' ' . Translate('op') . ' ' . STORE_NAME;

		}

		// EOF get meta Description


		if ($categorytags['meta_title'] != '') {

			$header_tags_array['title'] = $categorytags['meta_title'] . ' - ' . STORE_NAME;

		} else {

			$header_tags_array['title'] = $categorytags['categories_name'] . ' ' . Translate('op') . ' ' . STORE_NAME;

		}

		$header_tags_array['keywords'] = substr($keywords_category, 0, -2);

		$header_tags_array['desc'] = $meta_description;

		//Get extra meta info

		if (tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'seo'"))) {

			$defaultTags_query = tep_db_query('SELECT * FROM seo');

			while ($defaultTags = tep_db_fetch_array($defaultTags_query)) {

				if ($defaultTags['name'] == 'meta_language' && $defaultTags['value'] == '1') {

					$langName = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

					$headertags_extra .= ' <meta http-equiv="Content-Language" content="' . $langName[0] . '" />' . "\n"; // gives the language for this page

				}

				if ($defaultTags['name'] == 'meta_google' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="googlebot" content="all" />' . "\n";
				} //google will index, follow and archive the page

				if ($defaultTags['name'] == 'meta_noodp' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noodp" />' . "\n";
				} //force search engines to use this description meta tag instead of theres.

				if ($defaultTags['name'] == 'meta_noydir' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="slurp" content="noydir" />' . "\n";
				} // force yahoo slurp to use this description meta tag instead of yahoo directory

				if ($defaultTags['name'] == 'meta_revisit' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="revisit-after" content="1 days" />' . "\n";
				} // Ask the search engine to revisit the page after 1 day.

				if ($defaultTags['name'] == 'meta_robots' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noindex, nofollow" />' . "\n";
				} // all search engines will index and follow the page.

				if ($defaultTags['name'] == 'meta_unspam' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />' . "\n";
				} //to avoid spambots.

				if ($defaultTags['name'] == 'meta_replyto' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="Reply-to" content="' . STORE_OWNER_EMAIL_ADDRESS . '" />' . "\n";
				} // globaal contact adres for the website.

				if ($defaultTags['name'] == 'meta_canonical' && $defaultTags['value'] == '1') {
					$headertags_extra .= (tep_not_null($canonical_url) ? ' <link rel="canonical" href="' . $canonical_url . '" />' . "\n" : ' <link rel="canonical" href="' . GetCanonicalURL() . '" />' . "\n");
				} // prevent duplicated content for search engines (google, live, yahoo). The canonical url gives the right page link to search engines

			}

		}

		// EOF extra meta info

		break;

	//MERKEN

	case ((basename($_SERVER['PHP_SELF']) === FILENAME_DEFAULT) && (isset($_GET['manufacturers_id'])) && ($_GET['manufacturers_id'] != '')):

		$manufacturertags_query = tep_db_query("SELECT m.manufacturers_name, md.meta_title, md.meta_keywords, md.meta_description FROM manufacturers m LEFT JOIN manufacturers_description md on  m.manufacturers_id = md.manufacturers_id WHERE m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'");

		$manufacturertags = tep_db_fetch_array($manufacturertags_query);

		// Canonical URL add-on

		if ($_GET['manufacturers_id'] != '') {

			$canonical_url = tep_href_link(basename($_SERVER['PHP_SELF']),
				'manufacturers_id=' . (int)$_GET['manufacturers_id'], 'NONSSL', false);

		}

		// get keywords

		$defaultKeywords = explode(', ', $manufacturertags['meta_keywords']);

		$defaultKeywords = array_unique($defaultKeywords);

		if (count($defaultKeywords) >= 20) {

			for ($i = 0; $i < 20; $i++) {

				if ($defaultKeywords[$i] != '') {

					$keywords_manufacturer .= $defaultKeywords[$i] . ', ';

				}

			}

		} else {

			$allKeywords = array_merge($defaultKeywords, $keywords);

			$allKeywords = array_unique($allKeywords);

			if (count($allKeywords) > 20) {

				for ($i = 0; $i < 20; $i++) {

					if ($allKeywords[$i]) {
						$keywords_manufacturer .= $allKeywords[$i] . ', ';
					}

				}

			} else {

				foreach ($allKeywords as $cld => $separated) {

					if ($separated) {
						$keywords_manufacturer .= $separated . ', ';
					}

				}

			}

		}

		// EOF Get keywords

		// get Meta description

		if ($manufacturertags['meta_description'] != '') {

			$meta_description = $manufacturertags['meta_description'];

		} else {

			$meta_description = $manufacturertags['manufacturers_name'] . ' ' . Translate('op') . ' ' . STORE_NAME;

		}

		// EOF get meta Description


		if ($manufacturertags['meta_title'] != '') {

			$header_tags_array['title'] = $manufacturertags['meta_title'] . ' - ' . STORE_NAME;

		} else {

			$header_tags_array['title'] = $manufacturertags['manufacturers_name'] . ' ' . Translate('op') . ' ' . STORE_NAME;

		}

		$header_tags_array['keywords'] = substr($keywords_manufacturer, 0, -2);

		$header_tags_array['desc'] = $meta_description;

		//Get extra meta info

		if (tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'seo'"))) {

			$defaultTags_query = tep_db_query('SELECT * FROM seo');

			while ($defaultTags = tep_db_fetch_array($defaultTags_query)) {

				if ($defaultTags['name'] == 'meta_language' && $defaultTags['value'] == '1') {

					$langName = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

					$headertags_extra .= ' <meta http-equiv="Content-Language" content="' . $langName[0] . '" />' . "\n"; // gives the language for this page

				}

				if ($defaultTags['name'] == 'meta_google' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="googlebot" content="all" />' . "\n";
				} //google will index, follow and archive the page


				if ($defaultTags['name'] == 'meta_noydir' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="slurp" content="noydir" />' . "\n";
				} // force yahoo slurp to use this description meta tag instead of yahoo directory

				if ($defaultTags['name'] == 'meta_revisit' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="revisit-after" content="1 days" />' . "\n";
				} // Ask the search engine to revisit the page after 1 day.

				if ($defaultTags['name'] == 'meta_robots' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noindex, nofollow" />' . "\n";
				} // all search engines will index and follow the page.

				if ($defaultTags['name'] == 'meta_unspam' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />' . "\n";
				} //to avoid spambots.

				if ($defaultTags['name'] == 'meta_replyto' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="Reply-to" content="' . STORE_OWNER_EMAIL_ADDRESS . '" />' . "\n";
				} // globaal contact adres for the website.

				if ($defaultTags['name'] == 'meta_canonical' && $defaultTags['value'] == '1') {
					$headertags_extra .= (tep_not_null($canonical_url) ? ' <link rel="canonical" href="' . $canonical_url . '" />' . "\n" : ' <link rel="canonical" href="' . GetCanonicalURL() . '" />' . "\n");
				} // prevent duplicated content for search engines (google, live, yahoo). The canonical url gives the right page link to search engines

			}

		}

		// EOF extra meta info

		break;

	default:

		$header_tags_array['title'] = HEADING_TITLE . ' - ' . TITLE;

		$header_tags_array['title'] = $std_title;

		$header_tags_array['desc'] = $std_description;

		for ($i = 0; $i < 20; $i++) {

			if ($std_keywords[$i]) {
				$keywords_product .= $std_keywords[$i] . ', ';
			}

		}

		$header_tags_array['keywords'] = $keywords_product;

		//Get extra meta info

		if (tep_db_num_rows(tep_db_query("SHOW TABLES LIKE 'seo'"))) {

			$defaultTags_query = tep_db_query('SELECT * FROM seo');

			while ($defaultTags = tep_db_fetch_array($defaultTags_query)) {

				if ($defaultTags['name'] == 'meta_language' && $defaultTags['value'] == '1') {

					$langName = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

					$headertags_extra .= ' <meta http-equiv="Content-Language" content="' . $langName[0] . '" />' . "\n"; // gives the language for this page

				}

				if ($defaultTags['name'] == 'meta_google' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="googlebot" content="all" />' . "\n";
				} //google will index, follow and archive the page

				if ($defaultTags['name'] == 'meta_noodp' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noodp" />' . "\n";
				} //force search engines to use this description meta tag instead of theres.

				if ($defaultTags['name'] == 'meta_noydir' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="slurp" content="noydir" />' . "\n";
				} // force yahoo slurp to use this description meta tag instead of yahoo directory

				if ($defaultTags['name'] == 'meta_revisit' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="revisit-after" content="1 days" />' . "\n";
				} // Ask the search engine to revisit the page after 1 day.

				if ($defaultTags['name'] == 'meta_robots' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="robots" content="noindex, nofollow" />' . "\n";
				} // all search engines will index and follow the page.

				if ($defaultTags['name'] == 'meta_unspam' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />' . "\n";
				} //to avoid spambots.

				if ($defaultTags['name'] == 'meta_replyto' && $defaultTags['value'] == '1') {
					$headertags_extra .= ' <meta name="Reply-to" content="' . STORE_OWNER_EMAIL_ADDRESS . '" />' . "\n";
				} // globaal contact adres for the website.

				if ($defaultTags['name'] == 'meta_canonical' && $defaultTags['value'] == '1') {
					$headertags_extra .= (tep_not_null($canonical_url) ? ' <link rel="canonical" href="' . $canonical_url . '" />' . "\n" : ' <link rel="canonical" href="' . GetCanonicalURL() . '" />' . "\n");
				} // prevent duplicated content for search engines (google, live, yahoo). The canonical url gives the right page link to search engines

			}

		}

		// EOF extra meta info

		break;

}

$sts->template['headertags'] = ' <meta http-equiv="Content-Type" content="text/html; charset=' . CHARSET . '" />' . "\n" .

	$sts->template['headertags'] .= ' <title>' . convert_to_entities($header_tags_array['title']) . '</title>' . "\n"; // can only have a maximum of 66 characters

if ($header_tags_array['desc'] != '') {

	$sts->template['headertags'] .= ' <meta name="Description" content="' . $header_tags_array['desc'] . '" />' . "\n";

}

if ($header_tags_array['keywords'] != '') {

	$sts->template['headertags'] .= ' <meta name="Keywords" content="' . $header_tags_array['keywords'] . '" />' . "\n";

}

$sts->template['headertags'] .= $headertags_extra;

//css

$sts->template['headertags'] .= ' <link rel="shortcut icon" href="' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . '/favicon.ico" type="image/x-icon" />' . "\n";
$sts->template['headertags'] .= ' <link rel="stylesheet" type="text/css" href="' . substr(((($request_type == 'SSL') ? DIR_WS_HTTPS_CATALOG : DIR_WS_HTTP_CATALOG) . STS_TEMPLATE_DIR),
		0, -1) . '/css/style.css" />' . "\n";
$sts->template['headertags'] .= ' <link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/base/jquery-ui.css" />' . "\n";
$sts->template['headertags'] .= $headertags_extra_css;
//js
$sts->template['headertags'] .= ' <script type="text/javascript" src="https://www.google.com/jsapi"></script>' . "\n";
$sts->template['headertags'] .= ' <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>' . "\n";
$sts->template['headertags'] .= ' <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js"></script>' . "\n";
$sts->template['headertags'] .= ' <script type="text/javascript" src="' . substr(((($request_type == 'SSL') ? DIR_WS_HTTPS_CATALOG : DIR_WS_HTTP_CATALOG) . STS_TEMPLATE_DIR),
		0, -1) . '/js/plugins/form_validation.js"></script>' . "\n";
$sts->template['headertags'] .= ' <script type="text/javascript" src="includes/js/jquery.colorbox-min.js"></script>' . "\n";
$sts->template['headertags'] .= ' <script type="text/javascript" src="includes/js/jquery.jBreadCrumb.1.1.js"></script>' . "\n";
$sts->template['headertags'] .= ' <script type="text/javascript" src="includes/js/jquery.cooquery.min.js"></script>' . "\n";
$sts->template['headertags'] .= ' <script type="text/javascript" src="includes/js/jquery.tablesorter.min.js"></script>' . "\n";
$sts->template['headertags'] .= ' <script type="text/javascript" src="includes/js/jquery.livequery.min.js"></script>' . "\n";
$sts->template['headertags'] .= $headertags_extra_js;
// EOF Header tags


//Google Analytics

$tracking_script = '';

$tracking_code_query = tep_db_query("SELECT description FROM seo WHERE name = 'google_analytics'");

if (tep_db_num_rows($tracking_code_query) > 0) {

	$tracking_code = tep_db_fetch_array($tracking_code_query);

	if ($tracking_code['description'] != '') {

		$track_order = '';

		$track_products = '';

		$track_function = '';

		if ((basename($_SERVER['PHP_SELF']) === FILENAME_CHECKOUT_SUCCESS) && (GA_ECOMMERCE == 'true')) {

			require(DIR_WS_FUNCTIONS . 'analytics.php');

			$track_order = AnalyticsTrackOrder();

			$track_products = AnalyticsTrackProducts();

			$track_function = "_gaq.push(['_trackTrans']);";

		}


		$tracking_script = $order_analytics . "

		<script type=\"text/javascript\">

		  var _gaq = _gaq || [];

		  _gaq.push(['_setAccount', '" . $tracking_code['description'] . "']);

		  _gaq.push(['_trackPageview']);

		  _gaq.push(['_trackPageLoadTime']);

		  " . $track_order . "

		  " . $track_products . "

		  " . $track_function . "

		

		  (function() {

			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;

			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';

			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);

		  })();

		

		</script>";

	}

}

$sts->template['google_analytics'] = $tracking_script;

//Google Analytics

?>
