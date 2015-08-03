<?php

require_once('includes/application_top.php');

// the following cPath references come from application_top.php

$category_depth = 'top';

if (isset($cPath) && tep_not_null($cPath)) {

	$category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "'");

	$category_parent = tep_db_fetch_array($category_parent_query);

	if ($category_parent['total'] > 0) {

		$category_depth = 'nested'; // navigate through the categories

	} else {

		$category_depth = 'products'; // category has no products, but display the 'no products' message

	}

}



?>

<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">

<html <?php echo HTML_PARAMS; ?>>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">

<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">

<link rel="stylesheet" type="text/css" href="stylesheet.css">

</head>

<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">

<!-- header //-->

<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<!-- header_eof //-->

<!-- body //-->

<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">

  <!-- left_navigation //-->

  <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>

  <!-- left_navigation_eof //-->

</table>

<!-- body_text //-->

<?php

  if ($category_depth == 'nested') {

    $category_query = tep_db_query("select cd.categories_name, c.categories_image, cd.top, cd.bottom from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "'");

    $category = tep_db_fetch_array($category_query);

?>

<table border="0" width="100%" cellspacing="0" cellpadding="0">

	<tr>

		<td>

			<?php

			if ($category['top'] != '') {

			echo '<div style="margin-bottom: 10px;">';

			echo $category['top'];

			echo '</div>';

			}

			?>

			<div class="box subcategories">

                <div class="box-title"><?php echo Translate('Subcategorieen'); ?></div>

                <table cellpadding="0" cellspacing="0" width="100%" class="subcategories_box_table">

                <?php

                $categories_query = tep_db_query("SELECT c.categories_id, cd.categories_name, c.parent_id FROM categories c, categories_description cd WHERE c.parent_id = '".(int)$current_category_id."' AND c.categories_id = cd.categories_id AND cd.language_id = '".(int)$languages_id."' ORDER BY c.sort_order, cd.categories_name");

                $i = 0;

                while ($categories = tep_db_fetch_array($categories_query)) {

                    $cPath_new = tep_get_path($categories['categories_id']);

                    if (!empty($categories['specification_group'])) {

                        $cPath_new .= '&hk='.$categories['specification_group'];

                    }

                    if ($i == 0) {

                        echo '<tr><td style="height: 20px; width: 33%;"> <a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '" class="category_link" title="'.STORE_NAME.' - '.$category['categories_name'].' &raquo; '.$categories['categories_name'].'"><span class="raquo"><span>&raquo;</span></span> ' . $categories['categories_name'] . '</a></td>' . "\n";

                        $i++;

                    } else if ($i == 1) {

                        echo '<td style="height: 20px; width: 33%;"> <a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '" class="category_link" title="'.STORE_NAME.' - '.$category['categories_name'].' &raquo; '.$categories['categories_name'].'"><span class="raquo"><span>&raquo;</span></span> ' . $categories['categories_name'] . '</a></td>' . "\n";

                        $i++;

                    } else if ($i == 2) {

                        echo '<td style="height: 20px; width: 33%;"> <a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '" class="category_link" title="'.STORE_NAME.' - '.$category['categories_name'].' &raquo; '.$categories['categories_name'].'"><span class="raquo"><span>&raquo;</span></span> ' . $categories['categories_name'] . '</a></td></tr>' . "\n";

                        $i++;

                    }

                    if ($i == 3) {

                        $i = 0;

                    }

                }

                if ($i == 2) {

                    echo '<td style="width: 33%;"></td></tr>';

                }

                if ($i == 1) {

                    echo '<td style="width: 33%;"></td><td style="width: 33%;"></td></tr>';

                }

                ?>

                </table>

			</div>

			<?php

			$new_products_category_id = $current_category_id;

			include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS);

			if ($category['bottom'] != '') {

			echo '<div style="margin-top: 10px;">';

			echo $category['bottom'];

			echo '</div>';

			}

			?>

		</td>

	</tr>

</table>

<?php



} elseif ($category_depth == 'products' || isset($_GET['manufacturers_id'])) {

	if (PRODUCT_LISTING_MODULE_VIEW == 'grid') {

		$listtype = 'GRID';

		$listtypenot = 'LIST';

	} else {

		$listtype = 'LIST';

		$listtypenot = 'GRID';

	}

	if (SHOW_SPECS == 'false' || strstr(EXCLUDE_SPECS, ';'.$current_category_id.';')) {

		$specification_group = '';

	} else {

		$specification_group_query = tep_db_query("SELECT specification_group FROM categories WHERE categories_id = '".(int)$current_category_id."'");

		$sg = tep_db_fetch_array($specification_group_query);

		$specification_group = trim($sg['specification_group']);

	}

	/*PRODUCT LIST SPECS*/

	if (PRODUCT_LIST_SPECS!=0) {

		$checkboxes = array();

		$product_models = array();

		$get_product_models_query = tep_db_query('SELECT DISTINCT p.products_model FROM products p, products_to_categories ptc WHERE p.products_id = ptc.products_id AND ptc.categories_id = "'.(int)$current_category_id.'"');

		while ($get_product_models = tep_db_fetch_array($get_product_models_query)) {

			$product_models[] = $get_product_models['products_model'];

		}

		$product_models = '"'.implode('","', $product_models).'"';



		$subkenmerk_query = tep_db_query('SELECT value, subkenmerk FROM specifications WHERE hoofdkenmerk = "'.$specification_group.'" AND subkenmerk != "*" AND search = "1" AND language_id = "'.(int)$languages_id.'" ORDER BY subkenmerk');

		while ($subkenmerk = tep_db_fetch_array($subkenmerk_query)) {

			$productspecies_query = tep_db_query('SELECT DISTINCT ps.value FROM productspecs ps, specsdescription sd WHERE sd.subkenmerk = ps.value AND ps.products_model IN ('.$product_models.') AND sd.language_id = "'.(int)$languages_id.'" AND ps.hoofdkenmerk = "'.$specification_group.'" AND ps.subkenmerk = "'.$subkenmerk['subkenmerk'].'" ORDER BY ps.subkenmerk');

			$test_val = 'SELECT DISTINCT ps.value FROM productspecs ps, specsdescription sd WHERE sd.subkenmerk = ps.value AND ps.products_model IN ('.$product_models.') AND sd.language_id = "'.(int)$languages_id.'" AND ps.hoofdkenmerk = "'.$specification_group.'" AND ps.subkenmerk = "'.$subkenmerk['subkenmerk'].'" ORDER BY ps.subkenmerk<br />';

			if (tep_db_num_rows($productspecies_query) > 1) {

				$subkenmerk['subkenmerk'] = trim($subkenmerk['subkenmerk']);

				$checkboxes[] = array('id' => $subkenmerk['subkenmerk'],'text' => $subkenmerk['value']);

			}

		}

	}

	/*PRODUCT LIST SPECS*/

	$list_config_query = tep_db_query("SELECT configuration_key, configuration_value FROM configuration WHERE configuration_group_id = '19' AND configuration_value <> 0 AND configuration_key LIKE '%".$listtype."%' ORDER BY configuration_value");

	if (PRODUCT_LIST_SPECS_COUNT>0) {

		$total_columns = tep_db_num_rows($list_config_query)+(PRODUCT_LIST_SPECS_COUNT-1);

	} else {

		$total_columns = tep_db_num_rows($list_config_query);

	}

	if ($total_columns > 0) {

		$define_list = array();

	    $select_column_list = '';

		$listing_sql_suff = '';

		$column_list=0;

		$sort_order = substr($_GET['sort'], 1);

		$sort_col = substr($_GET['sort'], 0 , 1);

		while ($list_config = tep_db_fetch_array($list_config_query)) {

			$column_list++;

			if ($list_config['configuration_key']=='PRODUCT_'.$listtype.'_SPECS') {

				for ($s=0 ; $s<PRODUCT_LIST_SPECS_COUNT; $s++) {

					$config_array[] = array($list_config['configuration_key'], $column_list);

					if ($s<(PRODUCT_LIST_SPECS_COUNT-1)) {

						$column_list++;

					}

				}

			} else {

				$config_array[] = array($list_config['configuration_key'], $column_list);

			}

		}

		$column_list=0;

		foreach ($config_array as $key => $value) {

			if (($value[0] == 'PRODUCT_'.$listtype.'_PRICE') || ($value[0] == 'PRODUCT_'.$listtype.'_BUY_NOW') || ($value[0] == 'PRODUCT_'.$listtype.'_QUANTITY')) {

				if (CanShop() == 'false') {

					$value[1]=0;

				} else if (USE_PRICES_TO_QTY == 'true') {

					$value[1]=0;

				}

			}

			if ($value[1]>0) {

				$column_list++;

				if ($value[0]=='PRODUCT_'.$listtype.'_MODEL') {

					$select_column_list .= 'p.products_model, ';

					$default_sort = 'p.products_model';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_NAME') {

					$select_column_list .= 'pd.products_name, ';

					$default_sort = 'pd.products_name';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "pd.products_name " . ($sort_order == 'd' ? 'desc' : '') . "";

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_DESCRIPTION') {

					$default_sort = 'pd.products_description';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "pd.products_description " . ($sort_order == 'd' ? 'desc' : '') . "";

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_TECHNICAL') {

					$select_column_list .= 'pd.products_technical, ';

					$default_sort = 'pd.products_technical';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "pd.products_technical " . ($sort_order == 'd' ? 'desc' : '') . "";

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_MANUFACTURER') {

					$select_column_list .= 'm.manufacturers_name, ';

					$default_sort = 'm.manufacturers_name';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_QUANTITY') {

					$select_column_list .= 'p.products_quantity, ';

					$default_sort = 'p.products_quantity';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_quantity " . ($sort_order == 'd' ? 'desc' : '');

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_IMAGE') {

					$select_column_list .= 'p.products_image, ';

					$default_sort = 'p.products_image';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_image desc";

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_WEIGHT') {

					$select_column_list .= 'p.products_weight, ';

					$default_sort = 'p.products_weight';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_weight " . ($sort_order == 'd' ? 'desc' : '');

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_PRICE') {

					$default_sort = 'p.products_price';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_price " . ($sort_order == 'd' ? 'desc' : '');

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT1') {

					$select_column_list .= 'p.products_opt1, ';

					$default_sort = 'p.products_opt1';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_opt1 " . ($sort_order == 'd' ? 'desc' : '');

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT2') {

					$select_column_list .= 'p.products_opt2, ';

					$default_sort = 'p.products_opt2';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_opt2 " . ($sort_order == 'd' ? 'desc' : '');

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT3') {

					$select_column_list .= 'p.products_opt3, ';

					$default_sort = 'p.products_opt3';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_opt3 " . ($sort_order == 'd' ? 'desc' : '');

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT4') {

					$select_column_list .= 'p.products_opt4, ';

					$default_sort = 'p.products_opt4';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_opt4 " . ($sort_order == 'd' ? 'desc' : '');

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT5') {

					$select_column_list .= 'p.products_opt5, ';

					$default_sort = 'p.products_opt5';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "p.products_opt5 " . ($sort_order == 'd' ? 'desc' : '');

					}

				} else if ($value[0]=='PRODUCT_'.$listtype.'_SPECS') {
					//MXL SD.VALUE
					$default_sort = 'final_price';

					if ($sort_col == $column_list) {

						$listing_sql_suff .= "final_price " . ($sort_order == 'd' ? 'desc' : '');

					}

				} 

				$product_list_default_sort = str_replace($listtypenot, $listtype, PRODUCT_LIST_DEFAULT_SORT);

				if ($product_list_default_sort==$value[0]) {

					$default_sort_order = $default_sort;

				}

			}

		}

	}

	/*PRODUCT LIST SPECS*/

	if (PRODUCT_LIST_SPECS!=0) {

		if ($sort_col!='') {

			$col_id = $sort_col-PRODUCT_LIST_SPECS;

			if ($col>(PRODUCT_LIST_SPECS-1)) {

				$col_id = 0;

			}

			$search_subkenmerk = $chosen_spec_column[$col_id];

		} else {

			$search_subkenmerk = $chosen_spec_column[0];

		}



		if ($search_subkenmerk!='') {

			$specs_tables = " left join productspecs ps on p.products_model = ps.products_model left join specsdescription sd on ps.value = sd.subkenmerk";

			$specs_vwd = " and ps.subkenmerk = '".$search_subkenmerk."' and ps.hoofdkenmerk = '".$specification_group."' and sd.language_id = '".(int)$languages_id."'";

		}

	} else {

		$specs_tables = "";

		$specs_vwd = "";

	}

	/*PRODUCT LIST SPECS*/

	if (isset($_GET['manufacturers_id'])) {

		if (isset($_GET['filter_id']) && tep_not_null($_GET['filter_id'])) {

			$listing_sql .= "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id".$specs_tables.", " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$_GET['filter_id'] . "'".$specs_vwd;

		} else {

			$listing_sql .= "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id".$specs_tables.", " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'".$specs_vwd;

		}

	} else {

		if (isset($_GET['filter_id']) && tep_not_null($_GET['filter_id'])) {

			$listing_sql .= "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from (" . TABLE_PRODUCTS . " p) left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id".$specs_tables.", " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['filter_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'".$specs_vwd;

		} else {

			$listing_sql .= "select DISTINCT " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id".$specs_tables." left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'".$specs_vwd;

		}

	}

	if ( (!isset($_GET['sort'])) || (!ereg('^[1-8][ad]$', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > $total_columns) ) {



        if ($default_sort_order != '') {

            $listing_sql .= " order by ".$default_sort_order;

        }

    } else {

        if ($listing_sql_suff != "") {

            $listing_sql .= " order by ".$listing_sql_suff;

        }

	}

?>

<table border="0" width="100%" cellspacing="0" cellpadding="0">

	<tr>

		<td>

			<div class="breadCrumbHolder module">

				<div id="breadCrumbs" class="breadCrumb module">$breadcrumbs$</div>

			</div>

			<div class="chevronOverlay main"></div>

			<?php

			if (isset($_GET['manufacturers_id'])) {

				$top_text_query = tep_db_query("select top from manufacturers_description where manufacturers_id = '".(int)$_GET['manufacturers_id']."' and language_id = '".(int)$languages_id."'");

				$top_text = tep_db_fetch_array($top_text_query);

				if ($top_text['top'] != '') {

					echo '<div style="margin-bottom: 10px;">';

					echo $top_text['top'];

					echo '</div>';

				}

			} else {

				$top_text_query = tep_db_query("select top from ".TABLE_CATEGORIES_DESCRIPTION." where categories_id = '".(int)$current_category_id."' and language_id = '".(int)$languages_id."'");

				$top_text = tep_db_fetch_array($top_text_query);

				if ($top_text['top'] != '') {

					echo '<div style="margin-bottom: 10px;">';

					echo $top_text['top'];

					echo '</div>';

				}

			}

			?>

		</td>

	</tr>

	<?php

	if ($specification_group == '') {

	// optional Product List Filter

		if (PRODUCT_LIST_FILTER == "1") {

			if (isset($_GET['manufacturers_id'])) {

				$filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' order by cd.categories_name";

			} else {

				$filterlist_sql= "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by m.manufacturers_name";

			}

			$filterlist_query = tep_db_query($filterlist_sql);

			if (tep_db_num_rows($filterlist_query) > 1) {

				$filter = tep_draw_form('filter', tep_href_link(FILENAME_DEFAULT, tep_get_all_get_params(array('filter_id', 'sort'))), 'get') . Translate('Toon') . '&nbsp;';

				if (isset($_GET['manufacturers_id'])) {

					$options = array(array('id' => '', 'text' => Translate('Alle categorieen')));

					$filter .= tep_draw_hidden_field('sort', $_GET['sort']);

					while ($filterlist = tep_db_fetch_array($filterlist_query)) {

						$count_query = "SELECT COUNT(p2c.products_id) FROM ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_TO_CATEGORIES." p2c WHERE p.products_status ='1' and p.manufacturers_id = '".$_GET['manufacturers_id']."' and p.products_id = p2c.products_id and p2c.categories_id = '".$filterlist['id']."' GROUP BY p2c.categories_id"; 

						$count_result = tep_db_query($count_query) or die(mysql_error());

						$count_manufacturers = tep_db_fetch_array($count_result);

						$options[] = array('id' => $filterlist['id'], 'text' => $filterlist['name'].'('.$count_manufacturers['COUNT(p2c.products_id)'].')');

					}

				} else {

					$filter .= tep_draw_hidden_field('cPath', $cPath);

					$options = array(array('id' => '', 'text' => Translate('Alle merken')));

					$filter .= tep_draw_hidden_field('sort', $_GET['sort']);

					while ($filterlist = tep_db_fetch_array($filterlist_query)) {

						$count_query = "SELECT COUNT(p.manufacturers_id) FROM ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_TO_CATEGORIES." p2c WHERE p.products_status ='1' and p.manufacturers_id = '".$filterlist['id']."' and p.products_id = p2c.products_id and p2c.categories_id = '".(int)$current_category_id."' GROUP BY p.manufacturers_id"; 

						$count_result = tep_db_query($count_query) or die(mysql_error());

						$count_manufacturers = tep_db_fetch_array($count_result);

						$options[] = array('id' => $filterlist['id'], 'text' => $filterlist['name'].'('.$count_manufacturers['COUNT(p.manufacturers_id)'].')');

					}

				}

				$filter .= tep_draw_pull_down_menu('filter_id', $options, (isset($_GET['filter_id']) ? $_GET['filter_id'] : ''), 'onchange="this.form.submit()"');

				$filter .= '</form>' . "\n";

			}

		}

	} else {

	?>

	<tr>

		<td>

        	<?php 

			if (PRODUCT_COMPARE=='true') { ?>

        	<div id="compare_window"></div>

			<?php

			}

			echo tep_cache_product_finder();

			?>

		</td>

	</tr>

	<?php

	}

	?>

	<tr>

		<td height="10"></td>

	</tr>

	<tr>

		<td id="products">

			<?php

			$display_class = PRODUCT_LISTING_MODULE_VIEW;

			$listing_type = 'product_listing';

            include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING);

			?></td>

	</tr>

	<tr>

		<td height="10"></td>

	</tr>

	<tr>

		<td>

		<?php

		if (isset($_GET['manufacturers_id'])) {

			$bottom_text_query = tep_db_query("select bottom from manufacturers_description where manufacturers_id = '".(int)$_GET['manufacturers_id']."' and language_id = '".(int)$languages_id."'");

			$bottom_text = tep_db_fetch_array($bottom_text_query);

			if ($bottom_text['bottom'] != '') {

				echo '<div style="margin-top: 10px;">';

				echo $bottom_text['bottom'];

				echo '</div>';

			}

		} else {

			$bottom_text_query = tep_db_query("select bottom from ".TABLE_CATEGORIES_DESCRIPTION." where categories_id = '".(int)$current_category_id."' and language_id = '".(int)$languages_id."'");

			$bottom_text = tep_db_fetch_array($bottom_text_query);

			if ($bottom_text['bottom'] != '') {

				echo '<div style="margin-top: 10px;">';

				echo $bottom_text['bottom'];

				echo '</div>';

			}

		}

		?>

		</td>

	</tr>

</table>

<?php

} else { // default page

?>

		<table border="0" width="100%" cellspacing="0" cellpadding="0">

			<tr>

				<td valign="top">

					<?php

					$homepage_query = tep_db_query('SELECT infopages_id FROM infopages WHERE type = "home"');

					$homepage = tep_db_fetch_array($homepage_query);

					echo '<div class="contentpaneopen">

					<h1>'.tep_get_infopages_title($homepage['infopages_id']).'</h1>';

					echo '<p>'.tep_get_infopages_description($homepage['infopages_id']).'</p></div>';

					?>

				</td>

			</tr>

            <tr>

            	<td>

					<?php include(DIR_WS_MODULES . FILENAME_HOME_PRODUCTS); ?>

                </td>

            </tr>

		</table>

<?php

	} 

?>

<!-- body_text_eof //-->

<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">

  <!-- right_navigation //-->

  <?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>

  <!-- right_navigation_eof //-->

</table>

<!-- body_eof //-->

<!-- footer //-->

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>

<!-- footer_eof //-->

</body>

</html>

<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
