<?php
/*
  $Id: advanced_search_result.php,v 1.72 2003/06/23 06:50:11 project3000 Exp $
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2003 osCommerce
  Released under the GNU General Public License
*/
require_once('includes/application_top.php');
$curr_referer = basename(basename($_SERVER['HTTP_REFERER']));
$url_parts = parse_url($curr_referer);
$curr_referer = $url_parts['path'];
if ($curr_referer != FILENAME_ADVANCED_SEARCH_RESULT)
	$_SESSION['referer'] = $curr_referer;
elseif (!$_SESSION['referer'])
	$_SESSION['referer'] = FILENAME_ADVANCED_SEARCH;
$error = false;
if ( (isset($_GET['keywords']) && empty($_GET['keywords'])) &&
     (isset($_GET['dfrom']) && (empty($_GET['dfrom']) || ($_GET['dfrom'] == DOB_FORMAT_STRING))) &&
     (isset($_GET['dto']) && (empty($_GET['dto']) || ($_GET['dto'] == DOB_FORMAT_STRING))) &&
     (isset($_GET['pfrom']) && !is_numeric($_GET['pfrom'])) &&
     (isset($_GET['pto']) && !is_numeric($_GET['pto'])) ) {
	$error = true;
    $messageStack->add_session('search', Translate('Er moet ten minste 1 zoekcriteria gekozen worden'));
} else {
	$dfrom = '';
    $dto = '';
    $pfrom = '';
    $pto = '';
    $keywords = '';
    if (isset($_GET['dfrom'])) {
      $dfrom = (($_GET['dfrom'] == DOB_FORMAT_STRING) ? '' : $_GET['dfrom']);
    }
    if (isset($_GET['dto'])) {
      $dto = (($_GET['dto'] == DOB_FORMAT_STRING) ? '' : $_GET['dto']);
    }
    if (isset($_GET['pfrom'])) {
      $pfrom = $_GET['pfrom'];
    }
    if (isset($_GET['pto'])) {
      $pto = $_GET['pto'];
    }
    if (isset($_GET['keywords'])) {
      $keywords = strtolower($_GET['keywords']);
	  $keywords = str_replace("'","",$keywords);
    }
    $date_check_error = false;
    if (tep_not_null($dfrom)) {
      if (!tep_checkdate($dfrom, DOB_FORMAT_STRING, $dfrom_array)) {
        $error = true;
        $date_check_error = true;
        $messageStack->add_session('search', Translate('Ongeldige datum'));
      }
    }
    if (tep_not_null($dto)) {
      if (!tep_checkdate($dto, DOB_FORMAT_STRING, $dto_array)) {
        $error = true;
        $date_check_error = true;
        $messageStack->add_session('search', Translate('Ongeldige datum'));
      }
    }
    if (($date_check_error == false) && tep_not_null($dfrom) && tep_not_null($dto)) {
      if (mktime(0, 0, 0, $dfrom_array[1], $dfrom_array[2], $dfrom_array[0]) > mktime(0, 0, 0, $dto_array[1], $dto_array[2], $dto_array[0])) {
        $error = true;
        $messageStack->add_session('search', Translate('Datum van moet lager zijn dan datum tot'));
      }
    }
    $price_check_error = false;
    if (tep_not_null($pfrom)) {
      if (!settype($pfrom, 'double')) {
        $error = true;
        $price_check_error = true;
        $messageStack->add_session('search', Translate('Prijs moet numeriek zijn'));
      }
    }
    if (tep_not_null($pto)) {
      if (!settype($pto, 'double')) {
        $error = true;
        $price_check_error = true;
        $messageStack->add_session('search', Translate('Prijs moet numeriek zijn'));
      }
    }
    if (($price_check_error == false) && is_float($pfrom) && is_float($pto)) {
      if ($pfrom >= $pto) {
        $error = true;
        $messageStack->add_session('search', Translate('Prijs tot moet hoger zijn dan prijs van'));
      }
    }
    if (tep_not_null($keywords)) {
      if (!tep_parse_search_string($keywords, $search_keywords)) {
        $error = true;
        $messageStack->add_session('search', Translate('Ongeldige zoekterm'));
      }
    }
}
if ($dfrom || $dto || $pfrom || $pto || $keywords)
	//ass_clear_selection();
	//Specs Search start//
	//Get all vars and create where clause
	if ($_GET['specs']) {
		$specs_query = base64_decode($_GET['specs']);
		parse_str($specs_query, $specs_array);
		$_GET = array_merge($_GET, $specs_array);
	}
	$specs_clause = '';
	$specs_query_array = '';
	//Next two strings for Z fields
	$group_specs_clause_z1 = '';
	$group_specs_clause_z2 = '';
	for ($i = 65; $i <= 72 || $i == 90; $i++) {
		$group_specs_clause = '';
		for ($i1 = 1; $i1 < 17; $i1++) {
			$var = chr($i).((strlen($i1) == 2) ? $i1 : '0'.$i1);
			if ($_GET[$var]) {
				if ($i != 90)
					$group_specs_clause .= 'k.'.$var.'=1 OR ';
				else {
					if ($i1 <= 4)
						$group_specs_clause_z1 .= 'k.'.$var.'=1 OR ';
					else
						$group_specs_clause_z2 .= 'k.'.$var.'=1 OR ';
				}
				$specs_query_array[] = $var.'=1';
			}
		}
		if ($group_specs_clause && $i != 90) {
			$group_specs_clause = substr($group_specs_clause, 0, -4);
			$specs_clause .= ($specs_clause) ? ' AND ('.$group_specs_clause.')' : '('.$group_specs_clause.')';
			//$_GET['specs'] = $_GET['specs'] = base64_encode(join('&', $specs_query_array));
		} else {
			if ($group_specs_clause_z1) {
				$group_specs_clause_z1 = substr($group_specs_clause_z1, 0, -4);
				$specs_clause .= ($specs_clause) ? ' AND ('.$group_specs_clause_z1.')' : '('.$group_specs_clause_z1.')';
			}
			if ($group_specs_clause_z2) {
				$group_specs_clause_z2 = substr($group_specs_clause_z2, 0, -4);
				$specs_clause .= ($specs_clause) ? ' AND ('.$group_specs_clause_z2.')' : '('.$group_specs_clause_z2.')';
			}
		}
		if ($i == 72)
			$i = 89; //For check Z fields
	}
	//Specs Search end//
	if (empty($dfrom) && empty($dto) && empty($pfrom) && empty($pto) && empty($keywords) && empty($specs_clause)) {
		$error = true;
		$messageStack->add_session('search', Translate('Er moet ten minste 1 zoekcriteria gekozen worden'));
	}
	if ($error == true) {
		tep_redirect(tep_href_link(FILENAME_ADVANCED_SEARCH, tep_get_all_get_params(), 'NONSSL', true, false));
	}
	$breadcrumb->add(Translate('Zoekresultaten'), tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, tep_get_all_get_params(), 'NONSSL', true, false));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td>
	<div class="breadCrumbHolder module">
		<div id="breadCrumbs" class="breadCrumb module">$breadcrumbs$</div>
	</div>
	<div class="chevronOverlay main"></div>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><h1><?php echo sprintf(Translate("Zoekresultaten voor <i>%s</i>"), $_GET['keywords']); ?></h1></td>
      </tr>
      <tr>
        <td>
<?php
//infopages search
$ip_content = '';
$results_infopages_query = tep_db_query("SELECT i.infopages_id, i.type, i.parent_id, it.infopages_title, it.infopages_preview, it.infopages_description FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.infopages_status = 1 AND (it.infopages_title LIKE '%".$keywords."%' OR it.infopages_preview LIKE '%".$keywords."%' OR it.infopages_description LIKE '%".$keywords."%') AND it.language_id = ".(int)$languages_id." AND (i.type='pages' OR i.type='home' OR i.type='conditions') ORDER BY i.date_added ASC LIMIT 7");
if (tep_db_num_rows($results_infopages_query) > 0) {
	//log
	$check_log_query = tep_db_query("SELECT id, count FROM search_log WHERE keyword = '".$keywords."'");
	if (tep_db_num_rows($check_log_query) > 0) {
		$check_log = tep_db_fetch_array($check_log_query);
		$newcount = $check_log['count']+1;
		tep_db_query("UPDATE search_log SET count = '".$newcount."', results = '1' WHERE id = '".(int)$check_log['id']."'");
	}
	else
	{
		tep_db_query("INSERT INTO search_log (id, keyword, count, results) VALUES ('', '".$keywords."', 1, 1)");
	}
	//log
	$ip_content = '<ul id="search_ips">';
	$i=0;
	while ($results_infopages = tep_db_fetch_array($results_infopages_query)) {
			$i++;
			if ($i%2) { $li_class="odd"; } else { $li_class="even"; }
			$parent_query = tep_db_query("SELECT i.infopages_id, it.infopages_title FROM infopages i JOIN infopages_text it USING (infopages_id) WHERE i.infopages_status = 1 AND i.infopages_id = '".$results_infopages['parent_id']."' AND it.language_id = ".(int)$languages_id);
			$parent = tep_db_fetch_array($parent_query);
			$ip_content .= '<li class="'.$li_class.'"><a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$results_infopages['infopages_id'], 'SSL').'" title="';
			if ($parent['infopages_title'] != '') {
				$ip_content .= $parent['infopages_title'].' - ';
			}
			$ip_content .= $results_infopages['infopages_title'];
			$ip_content .= '"><span><strong>'.$results_infopages['infopages_title'].'</strong></span></a>';
			if ($parent['infopages_title'] != '') {
				$ip_content .= ' &nbsp;<i>('.$parent['infopages_title'].')</i>';
			}
			$ip_content .= '<div class="ips_preview">'.substr(strip_tags(tep_infopage_to_seourls($results_infopages['infopages_description'])),0, 320).'...</div></li>';
	}
	$ip_content .= '</ul>';
}
else
{
	//log
	$check_log_query = tep_db_query("SELECT id, count FROM search_log WHERE keyword = '".$keywords."'");
	if (tep_db_num_rows($check_log_query) > 0) {
		$check_log = tep_db_fetch_array($check_log_query);
		$newcount = $check_log['count']+1;
		tep_db_query("UPDATE search_log SET count = '".$newcount."', results = '0' WHERE id = '".(int)$check_log['id']."'");
	}
	else
	{
		tep_db_query("INSERT INTO search_log (id, keyword, count, results) VALUES ('', '".$keywords."', 1, 0)");
	}
	//log
}
//infopages search
  $select_str = "select distinct m.manufacturers_id, p.products_id, pd.products_name, pd.products_description, p.products_price, p.products_quantity, p.products_image, p.products_opt1, p.products_opt2, p.products_opt3, p.products_opt4, p.products_opt5, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price ";
  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    $select_str .= ", SUM(tr.tax_rate) as tax_rate ";
  }
  $from_str = "from ((" . TABLE_PRODUCTS . " p) left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd) left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id";
  if ($specs_clause)
  	$from_str .= " join ".TABLE_PRODUCTS_KENMERKEN." k on p.products_model = k.products_model";
	$from_str .= ", " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c";
  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    if (!tep_session_is_registered('customer_country_id')) {
      $customer_country_id = STORE_COUNTRY;
      $customer_zone_id = STORE_ZONE;
    }
    $from_str .= " left join " . TABLE_TAX_RATES . " tr on p.products_tax_class_id = tr.tax_class_id left join " . TABLE_ZONES_TO_GEO_ZONES . " gz on tr.tax_zone_id = gz.geo_zone_id and (gz.zone_country_id is null or gz.zone_country_id = '0' or gz.zone_country_id = '" . (int)$customer_country_id . "') and (gz.zone_id is null or gz.zone_id = '0' or gz.zone_id = '" . (int)$customer_zone_id . "')";
  }
  $where_str = " where p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id ";
  if (isset($_GET['categories_id']) && tep_not_null($_GET['categories_id'])) {
    if (isset($_GET['inc_subcat']) && ($_GET['inc_subcat'] == '1')) {
      $subcategories_array = array();
      tep_get_subcategories($subcategories_array, $_GET['categories_id']);
      $where_str .= " and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and (p2c.categories_id = '" . (int)$_GET['categories_id'] . "'";
      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $where_str .= " or p2c.categories_id = '" . (int)$subcategories_array[$i] . "'";
      }
      $where_str .= ")";
    } else {
      $where_str .= " and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$_GET['categories_id'] . "'";
    }
  }
  if (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
    $where_str .= " and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'";
  }
  if ($specs_clause)
  	$where_str .= " and (".$specs_clause.")";
  if (isset($search_keywords) && (sizeof($search_keywords) > 0)) {
    $where_str .= " and (";
    for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
      switch ($search_keywords[$i]) {
        case '(':
        case ')':
        case 'and':
        case 'or':
          $where_str .= " " . $search_keywords[$i] . " ";
          break;
        default:
          $keyword = tep_db_prepare_input($search_keywords[$i]);
          $where_str .= "(pd.products_name like '%" . tep_db_input($keyword) . "%' or p.products_model like '%" . tep_db_input($keyword) . "%' or pd.products_description like '%" . tep_db_input($keyword) . "%' or m.manufacturers_name like '%" . tep_db_input($keyword) . "%' or EXISTS (SELECT products_name FROM ".TABLE_PRODUCTS_DESCRIPTION." WHERE products_id = p.products_id AND language_id = 2 AND products_name LIKE '%".$keyword."%')";
          if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) $where_str .= " or pd.products_description like '%" . tep_db_input($keyword) . "%'";
          $where_str .= ')';
          break;
      }
    }
    $where_str .= " )";
  }
  if (tep_not_null($dfrom)) {
    $where_str .= " and p.products_date_added >= '" . tep_date_raw($dfrom) . "'";
  }
  if (tep_not_null($dto)) {
    $where_str .= " and p.products_date_added <= '" . tep_date_raw($dto) . "'";
  }
  if (tep_not_null($pfrom)) {
    if ($currencies->is_set($currency)) {
      $rate = $currencies->get_value($currency);
      $pfrom = $pfrom / $rate;
    }
  }
  if (tep_not_null($pto)) {
    if (isset($rate)) {
      $pto = $pto / $rate;
    }
  }
  if (DISPLAY_PRICE_WITH_TAX == 'true') {
    if ($pfrom > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) >= " . (double)$pfrom . ")";
    if ($pto > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) <= " . (double)$pto . ")";
  } else {
    if ($pfrom > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) >= " . (double)$pfrom . ")";
    if ($pto > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) <= " . (double)$pto . ")";
  }
  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    $where_str .= " group by p.products_id, tr.tax_priority";
  }
$listing_sql = $select_str . $from_str . $where_str . $order_str;
echo $ip_content;
if (tep_db_num_rows(tep_db_query($listing_sql)) > 0 ) {
		//log
	$check_log_query = tep_db_query("SELECT id, count FROM search_log WHERE keyword = '".$keywords."'");
	if (tep_db_num_rows($check_log_query) > 0) {
		$check_log = tep_db_fetch_array($check_log_query);
		$newcount = $check_log['count']+1;
		tep_db_query("UPDATE search_log SET count = '".$newcount."', results = '1' WHERE id = '".(int)$check_log['id']."'");
	}
	else
	{
		tep_db_query("INSERT INTO search_log (id, keyword, count, results) VALUES ('', '".$keywords."', 1, 1)");
	}
	//log
	$display_class = PRODUCT_LISTING_MODULE_VIEW;
	$listing_type = 'search_results';
	require(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING);
}
else
{
	//log
	$check_log_query = tep_db_query("SELECT id, count FROM search_log WHERE keyword = '".$keywords."'");
	if (tep_db_num_rows($check_log_query) > 0) {
		$check_log = tep_db_fetch_array($check_log_query);
		$newcount = $check_log['count']+1;
		tep_db_query("UPDATE search_log SET count = '".$newcount."', results = '0' WHERE id = '".(int)$check_log['id']."'");
	}
	else
	{
		tep_db_query("INSERT INTO search_log (id, keyword, count, results) VALUES ('', '".$keywords."', 1, 0)");
	}
	//log
	
}
?>
		</td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
</table>
</td></tr></table>
</td>
<!-- body_text_eof //-->
<td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
</table></td>
</tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>