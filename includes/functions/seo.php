<?php
function tep_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $force_language_id = false, $cPath = '') {
	global $request_type, $session_started, $SID, $languages_id;
	if ($force_language_id !== false) {
		$newlanguages_id = $force_language_id;
	} else {
		$newlanguages_id = $languages_id;
	}
	/********************/
	/*	We need a page	*/
	/********************/
	if (!tep_not_null($page)) {
		echo '<font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br>';
		echo '<pre>';
		/********************/
		/*	easy to debug	*/
		/********************/
		print_r(debug_backtrace());
		die();
	}
	
	if ($connection == 'NONSSL') {
		$link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
	} elseif ($connection == 'SSL') {
		if (ENABLE_SSL == true) {
			$link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
		} else {
			$link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
		}
	} else {
		echo '<font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</b><br>';
		/********************/
		/*	easy to debug	*/
		/********************/
		print_r(debug_backtrace());
		die();
	}
	
	$separator = '?';
	$seolink = '';
	if (tep_not_null($parameters)) {
		$parameters = explode('&', $parameters);
		$new_parameter_list = array();
		switch ($page) {
			case 'product_info.php':

				foreach($parameters as $pair) {
					$pair_array = explode('=', $pair);
					switch ($pair_array[0]) {
						case 'products_id':
							/****************************/
							/*	check if seo url exists	*/
							/****************************/
							$seo_query = tep_db_query('SELECT url FROM seo_urls WHERE products_id = "'.$pair_array[1].'"'.(!empty($cPath)?' AND cpath = "'.$cPath.'"':'').' AND language_id = "'.(int)$newlanguages_id.'"');
							if (tep_db_num_rows($seo_query) > 0) {
								/********************/
								/*	seo url exists	*/
								/********************/
								$seo_url = tep_db_fetch_array($seo_query);
								$seolink .= $seo_url['url'];

							} else {
								/****************************/
								/*	seo url doesn't exist	*/
								/****************************/
								if (!empty($cPath)) {
									/************************************************************/
									/*	this way, we can force the category structure			*/
									/*	can be necessary for products in multiple categories	*/
									/************************************************************/
									$cpath_array = explode('_', $cPath);
									foreach($cpath_array as $key=>$value) {
										/********************************************/
										/*	check if url exists for this category	*/
										/*	usefull for working with duplicate urls	*/
										/********************************************/
										$seo_url_query = tep_db_query('SELECT url FROM seo_urls WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');
										if (tep_db_num_rows($seo_url_query) > 0) {
											$seo_url = tep_db_fetch_array($seo_url_query);
											$seolink = $seo_url['url'].'/';
										} else {
											$cat_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');
											$category = tep_db_fetch_array($cat_query);
											$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($category['categories_name']))))).'/';
										}
									}
								} else  {
									/************************************************/
									/*	Get the category structure for this produt	*/
									/************************************************/
									$cat_query = tep_db_query('SELECT categories_id FROM products_to_categories WHERE products_id = "'.$pair_array[1].'"');
									$products_cat = tep_db_fetch_array($cat_query);
									$cpath_array = array_reverse(tep_get_category_tree_db($products_cat['categories_id'], 'parents'));
									foreach($cpath_array as $value) {
										/********************************************/
										/*	check if url exists for this category	*/
										/*	usefull for working with duplicate urls	*/
										/********************************************/
										$seo_url_query = tep_db_query('SELECT url FROM seo_urls WHERE categories_id = "'.$value['categories_id'].'" AND language_id = "'.(int)$newlanguages_id.'"');
										if (tep_db_num_rows($seo_url_query) > 0) {
											$seo_url = tep_db_fetch_array($seo_url_query);
											$seolink = $seo_url['url'].'/';
										} else {
											$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($value['categories_name']))))).'/';
										}
									}
								}
								$prod_query = tep_db_query('SELECT pd.products_name, p.products_model FROM products p, products_description pd WHERE p.products_id = pd.products_id AND p.products_id = "'.$pair_array[1].'" AND pd.language_id = "'.(int)$newlanguages_id.'"');
								$product = tep_db_fetch_array($prod_query);
								$products_name = RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($product['products_name'])))));



                                                                $product['products_model'] = str_replace("/","",$product['products_model']);
                                                                $product['products_model'] = str_replace("-","",$product['products_model']);

								$products_model = RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($product['products_model'])))));


								if (SEO_URL_PRODUCTS_MODEL != 'false') {
									if (SEO_URL_PRODUCTS_MODEL == 'only') {
										$seolink .= $products_model;
									} else if (SEO_URL_PRODUCTS_MODEL == 'after') {
										$seolink .= $products_name.'-'.$products_model;
									} else if (SEO_URL_PRODUCTS_MODEL == 'before') {
										$seolink .= $products_model.'-'.$products_name;
									} else {
                                        $seolink .= $products_name;
                                    }
								} else {
									$seolink .= $products_name;
								}
								/****************************/
								/*	add seo url to database	*/
								/****************************/
								$seolink = add_seo_url_to_db($newlanguages_id, '', $pair_array[1], '', '', '', $seolink, $cPath, '');
							}
							break;
						case '':
							break;
						default:
							if (!empty($pair_array[1])) {
								$new_parameter_list[] = $pair_array[0].'='.$pair_array[1];
							}
							break;
					}
				}
				$link .= preg_replace('/%2F/', '%20', $seolink);
				if (!empty($new_parameter_list)) {
					$link .= $separator . implode('&', $new_parameter_list);
					$separator = '&';
				}
				break;
			case 'index.php':
				foreach($parameters as $pair) {
					$pair_array = explode('=', $pair);
					switch ($pair_array[0]) {
						case 'action':
                            if (is_array($parameters)) {
                                $parameters = implode("&", $parameters);
                            }
							$link .= $page . '?' . tep_output_string($parameters);
							$separator = '&';
							break 3;
						case 'manufacturers_id':
							/************************************************/
							/*	check if there is a filter_id				*/
							/************************************************/
							$filter_id = '';
							foreach($parameters as $params) {
								$params_data = explode('=', $params);
								if (in_array('filter_id', $params_data)) {
									$filter_id = $params_data[1];
								}
							}
							/****************************/
							/*	check if seo url exists	*/
							/****************************/
							$seo_query = tep_db_query('SELECT url FROM seo_urls WHERE manufacturers_id = "'.$pair_array[1].'"'.(!empty($cPath)?' AND cpath = "'.$cPath.'"':'').(!empty($filter_id)?' AND filter_id = "'.$filter_id.'"':'').' AND language_id = "'.(int)$newlanguages_id.'"');
							if (tep_db_num_rows($seo_query) > 0) {
								/********************/
								/*	seo url exists	*/
								/********************/
								$seo_url = tep_db_fetch_array($seo_query);
								$seolink .= $seo_url['url'];
							} else {
								/****************************/
								/*	seo url doesn't exist	*/
								/****************************/
								$man_query = tep_db_query('SELECT manufacturers_name FROM manufacturers WHERE manufacturers_id = "'.$pair_array[1].'"');
								$manufacturer = tep_db_fetch_array($man_query);
								$manufacturers_name = $manufacturer['manufacturers_name'];
								$cpath_array = array();
								if (!empty($cPath)) {
									/****************************************************************/
									/*	this way, we can force the category structure				*/
									/*	can be necessary for manufacturers in multiple categories	*/
									/****************************************************************/
									if (SEO_URL_MAN_CAT == 'full cpath') {
										$cpath_array = explode('_', $cPath);
									} else {
										$cpath_array = end(explode('_', $cPath));
									}
								} else {
									/****************************************/
									/*	if there is a filter_id, 			*/
									/*	get category, full path or singular	*/
									/****************************************/
									if (!empty($filter_id)) {
										if (SEO_URL_MAN_CAT == 'full cpath') {
											$thiscat_array = array_reverse(tep_get_category_tree_db($filter_id, 'parents'));
											foreach($thiscat_array as $thiscat) {
												$cpath_array[] = $thiscat['categories_id'];
											}
										} else {
											$cpath_array[] = $filter_id;
										}
									}
								}
								if (SEO_URL_MAN_POS == 'after') {
									foreach($cpath_array as $key=>$value) {
											$cat_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');
											$category = tep_db_fetch_array($cat_query);
											$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($category['categories_name']))))).'/';
									}
									$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($manufacturers_name)))));
								} else {
									$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($manufacturers_name))))).'/';
									foreach($cpath_array as $key=>$value) {
											$cat_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');
											$category = tep_db_fetch_array($cat_query);
											$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($category['categories_name']))))).'/';
									}
									$seolink = substr($seolink, 0, -1);
								}
								/****************************/
								/*	add seo url to database	*/
								/****************************/
								$seolink = add_seo_url_to_db($newlanguages_id, '', '', $pair_array[1], '', '', $seolink, $cPath, $filter_id);
							}
							break;
						case 'cPath':
							/************************************************/
							/*	check if there is a filter_id				*/
							/************************************************/
							$filter_id = '';

							foreach($parameters as $params) {

								$params_data = explode('=', $params);

								if (in_array('filter_id', $params_data)) {

									$filter_id = $params_data[1];

								}

							}

							/****************************/

							/*	check if seo url exists	*/

							/****************************/

							if (strstr($pair_array[1], '_')) {

								$categories_id = end(explode('_', $pair_array[1]));

							} else {

								$categories_id = $pair_array[1];

							}

							$seo_query = tep_db_query('SELECT url FROM seo_urls WHERE categories_id = "'.$categories_id.'"'.(!empty($cPath)?' AND cpath = "'.$cPath.'"':'').(!empty($filter_id)?' AND filter_id = "'.$filter_id.'"':'').' AND language_id = "'.(int)$newlanguages_id.'"');

							if (tep_db_num_rows($seo_query) > 0) {

								/********************/

								/*	seo url exists	*/

								/********************/

								$seo_url = tep_db_fetch_array($seo_query);

								$seolink .= $seo_url['url'];

							} else {

								/****************************/

								/*	seo url doesn't exist	*/

								/****************************/

								$cat_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$categories_id.'"');

								$category = tep_db_fetch_array($cat_query);

								$categories_name = $category['categories_name'];

								$cpath_array = array();

								if (!empty($cPath)) {

									/************************************************************/

									/*	this way, we can force the category structure			*/

									/*	can be necessary for categories in multiple categories	*/

									/************************************************************/

									$cpath_array = explode('_', $cPath);

								} else {

									$catpath = tep_get_full_cpath($categories_id);

									$cpath_array = explode('_', $catpath);

								}

								/****************************************/

								/*	if there is a filter_id, 			*/

								/*	get category, full path or singular	*/

								/****************************************/

								if (!empty($filter_id)) {

									if (SEO_URL_MAN_CAT == 'singular') {

										$cpath_array[] = $filter_id;

									}

									if (SEO_URL_MAN_POS == 'after') {

										foreach($cpath_array as $key=>$value) {

											/********************************************/

											/*	check if url exists for this category	*/

											/*	usefull for working with duplicate urls	*/

											/********************************************/

											$seo_url_query = tep_db_query('SELECT url FROM seo_urls WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');

											if (tep_db_num_rows($seo_url_query) > 0) {

												$seo_url = tep_db_fetch_array($seo_url_query);

												$seolink = $seo_url['url'].'/';

											} else {

												$cat_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');

												$category = tep_db_fetch_array($cat_query);

												$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($category['categories_name']))))).'/';

											}

										}

										$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($manufacturers_name)))));

									} else {

										$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($manufacturers_name))))).'/';

										foreach($cpath_array as $key=>$value) {

											/********************************************/

											/*	check if url exists for this category	*/

											/*	usefull for working with duplicate urls	*/

											/********************************************/

											$seo_url_query = tep_db_query('SELECT url FROM seo_urls WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');

											if (tep_db_num_rows($seo_url_query) > 0) {

												$seo_url = tep_db_fetch_array($seo_url_query);

												$seolink = $seo_url['url'].'/';

											} else {

												$cat_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');

												$category = tep_db_fetch_array($cat_query);

												$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($category['categories_name']))))).'/';

											}

										}

										$seolink = substr($seolink, 0, -1);

									}

								} else {

									/********************/

									/*	No filter_id	*/

									/********************/

									foreach($cpath_array as $key=>$value) {

										/********************************************/

										/*	check if url exists for this category	*/

										/*	usefull for working with duplicate urls	*/

										/********************************************/

										$seo_url_query = tep_db_query('SELECT url FROM seo_urls WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');

										if (tep_db_num_rows($seo_url_query) > 0) {

											$seo_url = tep_db_fetch_array($seo_url_query);

											$seolink = $seo_url['url'].'/';

										} else {

											$cat_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');

											$category = tep_db_fetch_array($cat_query);

											$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($category['categories_name']))))).'/';

										}

									}

									$seolink = substr($seolink, 0, -1);

								}

								/****************************/

								/*	add seo url to database	*/

								/****************************/

								$seolink = add_seo_url_to_db($newlanguages_id, $categories_id, '', '', '', '', $seolink, $cPath, $filter_id);

							}

							break;

						case 'filter_id':

						case '':

							break;

						default:

							if (!empty($pair_array[1])) {

								$new_parameter_list[] = $pair_array[0].'='.$pair_array[1];

							}

							break;

					}

				}

				$link .= preg_replace('/%2F/', '%20', $seolink);

				if (tep_not_null($new_parameter_list)) {

					$link .= $separator . implode('&', $new_parameter_list);

					$separator = '&';

				}

				break;

			case 'infopage.php':

				foreach($parameters as $pair) {

					$pair_array = explode('=', $pair);

					switch ($pair_array[0]) {

						case 'action':

							$link .= $page . '?' . tep_output_string($parameters);

							$separator = '&';

							break 3;

						case 'page':

							/****************************/

							/*	check if seo url exists	*/

							/****************************/

							$seo_query = tep_db_query('SELECT url FROM seo_urls WHERE infopages_id = "'.$pair_array[1].'"'.(!empty($cPath)?' AND cpath = "'.$cPath.'"':'').' AND language_id = "'.(int)$newlanguages_id.'"');

							if (tep_db_num_rows($seo_query) > 0) {

								/********************/

								/*	seo url exists	*/

								/********************/

								$seo_url = tep_db_fetch_array($seo_query);

								$seolink .= $seo_url['url'];

							} else {

								/****************************/

								/*	seo url doesn't exist	*/

								/****************************/

								$page_query = tep_db_query('SELECT i.infopages_id, i.type, it.infopages_title, i.parent_id FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND it.language_id = "'.(int)$newlanguages_id.'" AND i.infopages_id = "'.$pair_array[1].'"');

								$infopage = tep_db_fetch_array($page_query);

								$infopage_title = $infopage['infopages_title'];

								$infopage_id = $infopage['infopages_id'];

								$infopage_type = $infopage['type'];

								$infopage_parent = $infopage['parent_id'];

								$cpath_array = array();

								if (!empty($cPath)) {

									/****************************************************/

									/*	this way, we can force the infopage structure	*/

									/****************************************************/

									$cpath_array = explode('_', $cPath);

									foreach($cpath_array as $key=>$value) {

										/********************************************/

										/*	check if url exists for this page		*/

										/*	usefull for working with duplicate urls	*/

										/********************************************/

										$seo_url_query = tep_db_query('SELECT url FROM seo_urls WHERE infopages_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');

										if (tep_db_num_rows($seo_url_query) > 0) {

											$seo_url = tep_db_fetch_array($seo_url_query);

											$seolink = $seo_url['url'].'/';

										} else {

											$page_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$value.'" AND language_id = "'.(int)$newlanguages_id.'"');

											$infopage = tep_db_fetch_array($page_query);

											$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($infopage['infopages_title']))))).'/';

										}

									}

									$seolink = substr($seolink, 0, -1);

								} else {

									$navpath = tep_get_navigation_tree($newlanguages_id, 'i_'.$infopage_id);

									if (count($navpath) > 0) {

										reset($navpath);

										$navpath = array_reverse($navpath[key($navpath)]);

										foreach($navpath as $nav_data) {

											$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($nav_data['name']))))).'/';

										}

										$seolink = substr($seolink, 0, -1);

									} else {

										$pagepath = array_reverse(tep_get_infopages_tree($infopage_id));

										foreach($pagepath as $page_data) {

											$seolink .= RemoveUnwantedCharacters(str_replace("+", "-", strtolower(urlencode(trim($page_data['infopages_title']))))).'/';

										}

										$seolink = substr($seolink, 0, -1);

									};

								}

								/****************************/

								/*	add seo url to database	*/

								/****************************/

								$seolink = add_seo_url_to_db($newlanguages_id, '', '', '', $infopage_id, '', $seolink, $cPath, '');

								

							}

							break;

						case '':

							break;

						default:

							if (!empty($pair_array[1])) {

								$new_parameter_list[] = $pair_array[0].'='.$pair_array[1];

							}

							break;

					}

				}

				$link .= preg_replace('/%2F/', '%20', $seolink);

				if (tep_not_null($new_parameter_list)) {

					$link .= $separator . implode('&', $new_parameter_list);

					$separator = '&';

				}

				break;

			default:

				$link .= $page . '?'.implode('&', $parameters);

				$separator = '&';

				break;

		}

	} else {

		$link .= $page;

		$separator = '?';

	}

	while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

	// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined

	if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {

		if (tep_not_null($SID)) {

			/*FORUM*/

			//$_sid = $SID;

			/*FORUM*/

		} elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {

			if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {

				$_sid = tep_session_name() . '=' . tep_session_id();

			}

		}

	}

	//SAVE TO DB

	if (isset($_sid)) {

		$link .= $separator . $_sid; 

	}

	//SAVE TO DB

	return $link;

}



function tep_get_metatitle($cat_id) {

    global $languages_id;

	if ($_GET['manufacturers_id'])

	{

		$m_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$_GET['manufacturers_id']. "'");

		$m = tep_db_fetch_array($m_query);

		$title = $m['manufacturers_name']._TOESTEL_NODIG.' '.FULL_CATALOG_FROM.$m['manufacturers_name'].SEARCH_ON.STORE_NAME;

	}

	elseif($_GET['products_id'])

	{

		if (strstr($_SERVER[ 'PHP_SELF' ], 'product_print.php' ))

		{

			$p_query = tep_db_query("select products_model, manufacturers_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$_GET['products_id']. "'");

			$p = tep_db_fetch_array($p_query);

			$m_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$p['manufacturers_id'] . "'");

			$m = tep_db_fetch_array($m_query);

			$c_query = tep_db_query("select c.parent_id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_id = '" . (int)$_GET['products_id'] . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");

			$c = tep_db_fetch_array($c_query);

			$title = $m['manufacturers_name'].' Type: '.substr($p['products_model'], 2).'.'._DETAILFICHE.$m['manufacturers_name'].' '.trim($c['name']).' Type: '.substr($p['products_model'], 2)._OFFERED_BY.STORE_NAME;

		}

		else

		{

			$p_query = tep_db_query("select products_model, manufacturers_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$_GET['products_id']. "'");

			$p = tep_db_fetch_array($p_query);

			$m_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$p['manufacturers_id'] . "'");

			$m = tep_db_fetch_array($m_query);

			$c_query = tep_db_query("select c.parent_id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_id = '" . (int)$_GET['products_id'] . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");

			$c = tep_db_fetch_array($c_query);

			$cp_query = tep_db_query("select cd.categories_name as name_parent from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . $c['parent_id'] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");

			$cp = tep_db_fetch_array($cp_query);

			$title = substr($p['products_model'], 2)._FOUND.sprintf(INFORMATION_ABOUT, $m['manufacturers_name'].' '.substr($p['products_model'], 2).' '.trim($c['name']).' '.trim($cp['name_parent'])).SEARCH_ON.STORE_NAME;

		}

	}

	elseif (strstr($_SERVER[ 'PHP_SELF' ], 'compare_window.php' ))

	{

		while (list($key, $value) = each($_GET))

		{

			if (substr($key,0,8) == 'columns_')

			{

				if ($value > 0) $columns[] .= $value;

			} 

		}

		for ($k = 0; $k < count($columns); $k++)

		{

			$p_query = tep_db_query("select products_model, manufacturers_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$columns[$k]. "'");

			$p = tep_db_fetch_array($p_query);

			$m_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$p['manufacturers_id'] . "'");

			$m = tep_db_fetch_array($m_query);

			$product .= substr($p['products_model'], 2).' '.$m['manufacturers_name'].' - ';

		}

		$title = _COMPARE.substr($product, 0, -2).SEARCH_ON.STORE_NAME;

	}

	else

	{

		$c_query = tep_db_query("select cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$cat_id . "' and cd.categories_id = '" . (int)$cat_id . "' and cd.language_id = '" . (int)$languages_id . "'");

		$c = tep_db_fetch_array($c_query);

		$cssc_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$c['parent_id'] . "'");

		$cssc = tep_db_fetch_array($cssc_query);

		if ($cssc['parent_id'])

		{

		$title = trim($c['categories_name'])._FOUND.' '.sprintf(COMPARE_ALL, strtolower(trim($c['categories_name']))).SEARCH_ON.STORE_NAME;

		}

		elseif ($c['parent_id'])

		{

		$title = trim($c['categories_name'])._NODIG.' '.sprintf(CHOOSE_FROM_ASSORT, strtolower(trim($c['categories_name']))).STORE_NAME;

		} else {

		$title = trim($c['categories_name'])._SEARCHING.' '.sprintf(BIG_ASSORT, strtolower(trim($c['categories_name']))).STORE_NAME;

		}

	}

    return $title;

}



function TransformSeoUrl ($url) {

	$url = strip_tags($url);

	$url = str_replace(HTTP_SERVER.DIR_WS_HTTP_CATALOG, '/', $url);

	$urlsplit = explode('?', $url);

	$url = $urlsplit[0];

	$url = RemoveSpecialCharacters($url);

	return $url;

}

function RemoveSpecialCharacters ($text) {

	$text = str_replace('@', 'a', $text);

	$text = str_replace('�', 'a', $text);

	$text = str_replace('�', 'a', $text);

	$text = str_replace('�', 'a', $text);

	$text = str_replace('�', 'a', $text);

	$text = str_replace('�', 'c', $text);

	$text = str_replace('�', 'e', $text);

	$text = str_replace('�', 'e', $text);

	$text = str_replace('�', 'e', $text);

	$text = str_replace('�', 'e', $text);

	$text = str_replace('�', 'i', $text);

	$text = str_replace('�', 'i', $text);

	$text = str_replace('�', 'n', $text);

	$text = str_replace('�', 'o', $text);

	$text = str_replace('�', 'o', $text);

	$text = str_replace('�', 'o', $text);

	$text = str_replace('�', 'u', $text);

	$text = str_replace('�', 'u', $text);

	$text = str_replace('�', 'u', $text);

	

	//URL SPECIFIC

	$text = str_replace('!', '', $text);

	$text = str_replace('+', '-', $text);

	$text = str_replace(':', '', $text);

	$text = str_replace(' & ', '-'.Translate('en').'-', $text);

	$text = str_replace('(', '', $text);

	$text = str_replace(')', '', $text);

	$text = str_replace("'", '', $text);

	$text = str_replace('"', '', $text);

	return $text;

}

function RemoveUnwantedCharacters ($text) {

	$text = str_replace('/', '-', $text);

	$text = str_replace('%2f', '-', $text);

	$text = str_replace('%2b', '', $text);

	$text = str_replace('%2c', '', $text);

	$text = str_replace('%26quot%3b', '', $text);

	$text = str_replace('%26prime%3b', '', $text);

	$text = str_replace('%28', '', $text);

	$text = str_replace('%29', '', $text);

	$text = str_replace('"', '', $text);

	$text = str_replace("'", "", $text);

	if (strpos($text, '--'))

	$text = RemoveUnwantedCharacters(str_replace('--', '-', $text));

	return $text;

}

function decodeUrlPart($string) {

	return urldecode(preg_replace(array('/[-]/', '/%20/'), array(' ', '%2F'), $string));

}

function transform_uri_part_to_product($string) {
	global $languages_id;
	$prod_array = array();
	if (SEO_URL_PRODUCTS_MODEL == 'false') {
		$prod_query = tep_db_query('SELECT pd.products_id, p2c.categories_id FROM products_description pd, products_to_categories p2c WHERE pd.products_id = p2c.products_id AND pd.language_id = "'.(int)$languages_id.'" AND pd.products_name LIKE "'.$string.'"');
		if (tep_db_num_rows($prod_query) > 0) {
			while ($product = tep_db_fetch_array($prod_query)) {
				$prod_array[$product['categories_id']] = array('name' => $string, 'parent' => $product['categories_id'], 'uri_part' => $string, 'products_id' => $product['products_id']);
			}
		}
	} else if (SEO_URL_PRODUCTS_MODEL == 'only') {
		$prod_query = tep_db_query('SELECT pd.products_name, p.products_id, p2c.categories_id FROM products p, products_description pd, products_to_categories p2c WHERE p.products_id = pd.products_id AND p.products_id = p2c.products_id AND p.products_model LIKE "'.$string.'"');
		if (tep_db_num_rows($prod_query) > 0) {
			while ($product = tep_db_fetch_array($prod_query)) {
				$prod_array[$product['categories_id']] = array('name' => $product['products_name'], 'parent' => $product['categories_id'], 'uri_part' => $string, 'products_id' => $product['products_id']);
			}
		}
	} else if (SEO_URL_PRODUCTS_MODEL == 'after') {
		$string_parts = explode(' ', $string);
		$prod_model = end($string_parts);
		$prod_info = get_product_info_from_model($prod_model);
		if (empty($prod_info) && count($string_parts)> 1) {
			$count_parts = count($string_parts);
			$counter = 0;
			while (empty($prod_info) && ($count_parts<=$counter)) {
				$counter++;
				$prod_model = prev($string_parts).' '.$prod_model;
				$prod_info = get_product_info_from_model($prod_model);
			}
		}
		foreach($prod_info as $key=>$value) {
			$prod_array[$key] = $value;
		}
	} else if (SEO_URL_PRODUCTS_MODEL == 'before') {
		$string_parts = explode(' ', $string);
		$prod_model = $string_parts[0];
		$prod_info = get_product_info_from_model($prod_model);
		if (empty($prod_info)) {
			$prod_model = '';
			reset($string_parts);
			while (empty($prod_info)) {
				$prod_model = $prod_model.' '.next($string_parts);
				$prod_info = get_product_info_from_model($prod_model);
			}
		}
		foreach($prod_info as $key=>$value) {
			$prod_array[$key] = $value;
		}
	}
	return $prod_array;
}

function get_product_info_from_model($model) {

	global $languages_id;

	$prod_array = array();

	$parent_id = '';

	$prod_query = tep_db_query('SELECT pd.products_name, p.products_id, p2c.categories_id FROM products p, products_description pd, products_to_categories p2c WHERE p.products_id = pd.products_id AND p.products_id = p2c.products_id AND pd.language_id = "'.(int)$languages_id.'" AND p.products_model LIKE "'.$model.'"');

	if (tep_db_num_rows($prod_query) > 0) {

		while ($product = tep_db_fetch_array($prod_query)) {

			$parent_id = $product['categories_id'];

			$prod_array[$product['categories_id']] = array('name' => $product['products_name'], 'parent' => $product['categories_id'], 'uri_part' => $string, 'products_id' => $product['products_id']);

		}

	}

	return $prod_array;

}

function tep_get_category_tree_db($category_id, $type) {

	global $languages_id;

	$categories = array();

	if ($type == 'parents') {

		$parent_query = tep_db_query('SELECT cd.categories_name, cd.categories_id, c.parent_id FROM categories c, categories_description cd WHERE c.categories_id = cd.categories_id AND cd.language_id = "'.(int)$languages_id.'" AND c.categories_id = "'.$category_id.'"');

		while ($parent = tep_db_fetch_array($parent_query)) {

			$categories[] = array('categories_name' => $parent['categories_name'], 'parent_id' => $parent['parent_id'], 'categories_id' => $parent['categories_id']);

			if ($parent['parent_id'] != '0') {

				$parent_categorie = tep_get_category_tree_db($parent['parent_id'], $type);

				foreach($parent_categorie as $key=>$value) {

					$categories[] = array('categories_name' => $value['categories_name'], 'parent_id' => $value['parent_id'], 'categories_id' => $value['categories_id']);

				}

			}

		}

	}

	return $categories;

}

function add_seo_url_to_db($language_id, $categories_id = '', $products_id = '', $manufacturers_id = '', $infopages_id = '', $navigation_id = '', $seo_url, $cpath = '', $filter_id = '', $duplicate = false) {
    if (!empty($seo_url)) {
	$where_query = '';

	$add_insert_query = '';

	$add_insert_values_query = '';

	if ($categories_id != '') {

		$where_query .= ' AND categories_id = "'.$categories_id.'"';

		$add_insert_query .= ', categories_id';

		$add_insert_values_query .= ', "'.$categories_id.'"';

	}

	if ($products_id != '') {

		$where_query .= ' AND products_id = "'.$products_id.'"';

		$add_insert_query .= ', products_id';

		$add_insert_values_query .= ', "'.$products_id.'"';

	}

	if ($manufacturers_id != '') {

		$where_query .= ' AND manufacturers_id = "'.$manufacturers_id.'"';

		$add_insert_query .= ', manufacturers_id';

		$add_insert_values_query .= ', "'.$manufacturers_id.'"';

	}

	if ($infopages_id != '') {

		$where_query .= ' AND infopages_id = "'.$infopages_id.'"';

		$add_insert_query .= ', infopages_id';

		$add_insert_values_query .= ', "'.$infopages_id.'"';

	}

	if ($navigation_id != '') {

		$where_query .= ' AND navigation_id = "'.$navigation_id.'"';

		$add_insert_query .= ', navigation_id';

		$add_insert_values_query .= ', "'.$navigation_id.'"';

	}
        
        if ((!empty($cpath) || !empty($filter_id) || !empty($where_query)) && (!empty($seo_url))) {

/*            $redirect_query = tep_db_query('SELECT * FROM seo_urls WHERE url != "'.$seo_url.'" AND cpath = "'.$cpath.'" AND filter_id = "'.$filter_id.'"'.$where_query);

            if (tep_db_num_rows($redirect_query) > 0) {

                    while($redirect = tep_db_fetch_array($redirect_query)) {

                            $change_query = tep_db_query('SELECT id FROM 301_redirects WHERE new = "'.$redirect['url'].'"');

                            while ($change = tep_db_fetch_array($change_query)) {

                                    tep_db_query('UPDATE 301_redirects SET new = "'.$seo_url.'" WHERE id = "'.$change['id'].'"');

                            }

                            tep_db_query('INSERT INTO 301_redirects (old, new, date_created) VALUES("'.$redirect['url'].'", "'.$seo_url.'", NOW())');

                            tep_db_query('DELETE FROM seo_urls WHERE id = "'.$redirect['id'].'"');

                    }

            }*/


            $check_query = tep_db_query('SELECT id FROM seo_urls WHERE url = "'.$seo_url.'"');

            if (tep_db_num_rows($check_query) > 0) {

                    $last_chars = end(explode('+', $seo_url));

                    if (is_numeric($last_chars) && strlen($last_chars) < 3) {

                            $strlen = strlen($last_chars);

                            $last_chars++;

                            $seo_url = substr($seo_url, 0, -$strlen).$last_chars;

                    } else {

                            $seo_url .= '+2';

                    }

                    $seo_url = add_seo_url_to_db($language_id, $categories_id, $products_id, $manufacturers_id, $infopages_id, $navigation_id, $seo_url, $cpath, $filter_id, true);

            } else {

                    tep_db_query('INSERT INTO seo_urls (language_id, url, cpath, filter_id, duplicate'.$add_insert_query.') VALUES("'.(int)$language_id.'", "'.$seo_url.'", "'.$cPath.'", "'.$filter_id.'", "'.$duplicate.'"'.$add_insert_values_query.')');

            }
        }

	return $seo_url;
    }

}

function tep_get_infopages_tree($infopage_id) {

	global $languages_id;

	$pages_array = array();

	$page_query = tep_db_query('SELECT i.parent_id, i.type, it.infopages_title, i.infopages_id FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND it.language_id = "'.(int)$languages_id.'" AND i.infopages_id = "'.$infopage_id.'"');

	if (tep_db_num_rows($page_query)>0) {

		$page = tep_db_fetch_array($page_query);

		$pages_array[] = $page;

		if ($page['parent_id'] != '0') {

			$parent_tree = tep_get_infopages_tree($page['parent_id']);

			foreach($parent_tree as $key=>$value){

				$pages_array[] = $value;

			}

		}

	}

	return $pages_array;

}



function tep_get_navigation_tree($languages_id, $link = '', $custom = '') {

	$nav_array = array();

	if ($link != '') {

		$nav_query = tep_db_query('SELECT id, parent_id FROM navigatie WHERE link = "'.$link.'" AND status = 1');

		$item_id = explode('_', $link);

		while ($nav = tep_db_fetch_array($nav_query)) {

			if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $link)) { //infopage

				$page_query = tep_db_query('SELECT infopages_id, infopages_title FROM infopages_text WHERE language_id = "'.(int)$languages_id.'" AND infopages_id = "'.$item_id[1].'"');

				$infopage = tep_db_fetch_array($page_query);

				$nav_array[$nav['id']][] = array('name' => $infopage['infopages_title'], 'type' => 'infopage', 'id' => $infopage['infopages_id'], 'page' => 'infopage.php', 'get_param' => 'page='.$infopage['infopages_id']);

			} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $nav_item['link'])) { //category

				$cat_query = tep_db_query('SELECT categories_id, categories_name FROM categories_description WHERE language_id = "'.(int)$languages_id.'" AND categories_id = "'.$item_id[1].'"');

				$category = tep_db_fetch_array($cat_query);

				$nav_array[$nav['id']][] = array('name' => $category['categories_name'], 'type' => 'category', 'id' => $category['categories_id'], 'page' => 'index.php', 'get_param' => 'cPath='.$category['categories_id']);

			} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $nav_item['link'])) { //product

				$prod_query = tep_db_query('SELECT products_name, products_id FROM products_description WHERE language_id = "'.(int)$languages_id.'" AND products_id = "'.$item_id[1].'"');

				$product = tep_db_fetch_array($prod_query);

				$nav_array[$nav['id']][] = array('name' => $product['products_name'], 'type' => 'infopage', 'id' => $product['products_id'], 'page' => 'product_info.php', 'get_param' => 'products_id='.$product['products_id']);

			}

			if ($nav['parent_id'] != 0) {

				$parent_query = tep_db_query('SELECT link, custom FROM navigatie WHERE id = "'.$nav['parent_id'].'"');

				if (tep_db_num_rows($parent_query) > 0) {

					$parent = tep_db_fetch_array($parent_query);

					$parent_array = tep_get_navigation_tree($languages_id, $parent['link'], $parent['custom']);

					foreach($parent_array as $nav_id=>$nav_data) {

						foreach($nav_data as $key=>$value) {

							$nav_array[$nav['id']][] = $value;

						}

					}

				}

			}

		}

	} else  if ($custom != '') {

		$nav_query = tep_db_query('SELECT id, parent_id, name FROM navigatie WHERE custom = "'.$custom.'" AND status = 1');

		while ($nav = tep_db_fetch_array($nav_query)) {

			$nav_array[$nav['id']][] = array('name' => $nav['name'], 'type' => 'custom', 'id' => '', 'page' => $custom, 'get_params' => '');

			if ($nav['parent_id'] != 0) {

				$parent_query = tep_db_query('SELECT link, custom FROM navigatie WHERE id = "'.$nav['parent_id'].'"');

				if (tep_db_num_rows($parent_query) > 0) {

					$parent = tep_db_fetch_array($parent_query);

					$parent_array = tep_get_navigation_tree($languages_id, $parent['link'], $parent['custom']);

					foreach($parent_array as $nav_id=>$nav_data) {

						foreach($nav_data as $key=>$value) {

							$nav_array[$nav['id']][] = $value;

						}

					}

				}

			}

		}

	}

	return $nav_array;

}

?>