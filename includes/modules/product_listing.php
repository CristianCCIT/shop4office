<?php
if ($display_class == 'grid') {
	$listtype = 'GRID';
	$listtypenot = 'LIST';
} else {
	$listtype = 'LIST';
	$listtypenot = 'GRID';
}


if (!tep_session_is_registered('products_per_search_page')) {
	tep_session_register('products_per_search_page');
}
if (!isset($products_per_search_page) || !is_integer($products_per_search_page) || ($products_per_search_page < 1)) {
	$products_per_search_page = MAX_DISPLAY_SEARCH_RESULTS;
}
if (isset($_GET['ppp']) && is_numeric($_GET['ppp']) && ($_GET['ppp'] > 0)) {
	$products_per_search_page = intval($_GET['ppp']);
}
$ppp_list = array();
for ($i = 1; $i <= 10; $i++) {
	$ppp = intval($i * MAX_DISPLAY_SEARCH_RESULTS);
	$ppp_list[] = array('id' => $ppp, 'text' => $ppp);
}
if (strstr($_SERVER['PHP_SELF'], FILENAME_AJAX_SEARCH) || $specification_group != '') {
	$numprodform = tep_draw_form('prod_per_page',
			tep_href_link(basename($_SERVER['PHP_SELF']), tep_get_all_get_params(array('ppp', 'page'))),
			'get') . Translate('Aantal producten op een pagina') . ': ' . tep_draw_pull_down_menu('ppp', $ppp_list,
			$products_per_search_page, 'class="smallText"') . '</form>';
} else {
	$numprodform = tep_draw_form('prod_per_page',
			tep_href_link(basename($_SERVER['PHP_SELF']), tep_get_all_get_params(array('ppp', 'page'))),
			'get') . Translate('Aantal producten op een pagina') . ': ' . tep_draw_pull_down_menu('ppp', $ppp_list,
			$products_per_search_page, 'class="smallText" onchange="this.form.submit()"');
	foreach ($_GET as $key => $value) {
		if ($key != 'cPath' && $key != 'ppp') {
			$numprodform .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
		}
	}
	$numprodform .= '</form>';
}
if ($results_limit != '') {
	$query_limit = $results_limit;
} else {
	$query_limit = $products_per_search_page;
}
$listing_split = new splitPageResults($listing_sql, $query_limit, 'p.products_id');
if ($listing_split->number_of_rows > 0) {
	if (($listing_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'))) {
		if (PRODUCT_LIST_PRINT == 'true') {
			?>
			<script type="text/javascript" src="includes/js/jquery.jqprint.0.3.js"></script>
			<script type="text/javascript">
				$(document).ready(function () {
					$("#print_all").click(function () {
						var param = $("#print_all").attr('class');
						$.ajax({
							url: 'print_catalog.php?cPath=' + param,
							context: document.body,
							success: function (data) {
								$("#print_result").html(data);
								$('#print_result').jqprint();
							}
						});
						return false;
					});
				});
			</script>
		<?php
		}
		if (($listing_type == 'product_listing') || ($listing_type == 'specials_listing') || ($listing_type == 'products_new_listing') || ($listing_type == 'search_results')) {
			?>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td align="left" class="pages">
						<?php echo $listing_split->display_count(Translate('Product <b>%d</b> tot <b>%d</b> (van <b>%d</b> producten)')); ?>
						<br/>
						<?php echo Translate('Paginas') . ': ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS,
								tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?>
					</td>
					<td align="right">
						<?php
						if (PRODUCT_LIST_FILTER == "1") {
							echo $filter;
						}
						?>
						<?php echo $numprodform; ?>
						<?php
						if (PRODUCT_LIST_PRINT == 'true') {
							if ($current_category_id != '0') {
								?>
								<a href="#" id="print_all"
								   class="<?php echo $current_category_id; ?>"><?php echo Translate('Catalogus afdrukken'); ?></a>
								<div id="print_result"></div>
							<?php }
						}
						?>
					</td>
				</tr>
				<tr>
					<td colspan="2" height="15">
				</tr>
			</table>
		<?php
		}
	}

	/*SPECS*/
	if (PRODUCT_LIST_SPECS != 0) {
		$checkboxes = array();
		$product_models = array();
		$get_product_models_query = tep_db_query('SELECT DISTINCT p.products_model FROM products p, products_to_categories ptc WHERE p.products_id = ptc.products_id AND ptc.categories_id = "' . (int)$current_category_id . '"');
		while ($get_product_models = tep_db_fetch_array($get_product_models_query)) {
			$product_models[] = $get_product_models['products_model'];
		}
		$product_models = '"' . implode('","', $product_models) . '"';

		$subkenmerk_query = tep_db_query('SELECT value, subkenmerk FROM specifications WHERE hoofdkenmerk = "' . $specification_group . '" AND subkenmerk != "*" AND search = "1" AND language_id = "' . (int)$languages_id . '" ORDER BY subkenmerk');
		while ($subkenmerk = tep_db_fetch_array($subkenmerk_query)) {
			$productspecies_query = tep_db_query('SELECT DISTINCT ps.value FROM productspecs ps, specsdescription sd WHERE sd.subkenmerk = ps.value AND ps.products_model IN (' . $product_models . ') AND sd.language_id = "' . (int)$languages_id . '" AND ps.hoofdkenmerk = "' . $specification_group . '" AND ps.subkenmerk = "' . $subkenmerk['subkenmerk'] . '" ORDER BY ps.subkenmerk');
			$test_val = 'SELECT DISTINCT ps.value FROM productspecs ps, specsdescription sd WHERE sd.subkenmerk = ps.value AND ps.products_model IN (' . $product_models . ') AND sd.language_id = "' . (int)$languages_id . '" AND ps.hoofdkenmerk = "' . $specification_group . '" AND ps.subkenmerk = "' . $subkenmerk['subkenmerk'] . '" ORDER BY ps.subkenmerk<br />';
			if (tep_db_num_rows($productspecies_query) > 1) {
				$subkenmerk['subkenmerk'] = trim($subkenmerk['subkenmerk']);
				$checkboxes[] = array('id' => $subkenmerk['subkenmerk'], 'text' => $subkenmerk['value']);
			}
		}
	}
	/*SPECS*/
	$extra_limit = "";
	if ((CanShop() == 'false') || (USE_PRICES_TO_QTY == 'true')) {
		$extra_limit .= " AND configuration_key NOT IN ('PRODUCT_" . $listtype . "_PRICE','PRODUCT_" . $listtype . "_BUY_NOW','PRODUCT_" . $listtype . "_QUANTITY')";
	}
	if ((PRODUCT_COMPARE != 'true') || ($listing_type != 'product_listing') || ($_GET['cPath'] == '')) {
		$extra_limit .= " AND configuration_key NOT IN ('PRODUCT_" . $listtype . "_COMPARE')";
	}
	$extra_limit .= " AND configuration_key NOT LIKE '%_" . $listtypenot . "_%'";
	$columns_query = "SELECT configuration_key, configuration_value FROM configuration WHERE configuration_group_id = '19' AND configuration_value <> 0" . $extra_limit . " ORDER BY configuration_value";
	$list_config_query = tep_db_query($columns_query);
	if ((PRODUCT_LIST_SPECS_COUNT > 0) && (constant('PRODUCT_' . $listtype . '_SPECS') != 0)) {
		$specs_col_count = 0;
		for ($s = 0; $s < PRODUCT_LIST_SPECS_COUNT; $s++) {
			if ($checkboxes[$s]['id'] != '') {
				$specs_col_count++;
			}
		}
		if ($checkboxes[0]['id'] != '') {
			$total_columns = tep_db_num_rows($list_config_query) + ($specs_col_count - 1);
		} else {
			$total_columns = tep_db_num_rows($list_config_query) - 1;
		}
	} else {
		$total_columns = tep_db_num_rows($list_config_query);
	}
	if ($total_columns > 0) {
		?>
		<div class="box product-listing <?php echo $listing_type; ?> <?php echo $display_class; ?>">
			<?php
			if ($listing_type == 'home_products') {
				echo '<div class="box_title">' . Translate('Nieuw in ons Assortiment') . '</div>';
			}
			?>
			<?php if (($listing_type == 'product_listing') || ($listing_type == 'specials_listing') || ($listing_type == 'products_new_listing') || ($listing_type == 'search_results')) { ?>
				<div class="box-title product-list-heading">
					<?php
					$define_list = array();
					$select_column_list = '';
					$listing_sql_suff = '';
					$column_list = 0;
					if ($display_class == 'grid') {
						?>
						<div class="column xxx column-sortby"><?php echo Translate('Sorteren op'); ?></div><?php
					}
					while ($list_config = tep_db_fetch_array($list_config_query)) {
						if ($list_config['configuration_value'] > 0) {
							$column_list++;
							$column_class = '';
							if ($column_list == 1) {
								$column_class .= ' first';
							} else {
								if ($column_list == $total_columns) {
									$column_class .= ' last';
								}
							}
							$column_width = (100 / $total_columns);
							if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_MODEL') {
								$lc_text = Translate('Model');
							} else {
								if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_NAME') {
									$lc_text = Translate('Product');
									$column_width = $column_width + ($column_width / 2);
									if (PRODUCT_COMPARE == 'true') {
										$column_width = $column_width + $column_width_compare;
									}
								} else {
									if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_DESCRIPTION') {
										$lc_text = Translate('Omschrijving');
									} else {
										if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_TECHNICAL') {
											$lc_text = Translate('Technische info');
										} else {
											if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_MANUFACTURER') {
												$lc_text = Translate('Merk');
											} else {
												if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_QUANTITY') {
													$lc_text = Translate('Hoeveelheid');
												} else {
													if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_IMAGE') {
														$lc_text = Translate('Afbeelding');
														$column_width = $column_width / 2;
													} else {
														if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_WEIGHT') {
															$lc_text = Translate('Gewicht');
														} else {
															if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_PRICE') {
																$lc_text = Translate('Prijs');
															} else {
																if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_BUY_NOW') {
																	$lc_text = Translate('Kopen');
																} else {
																	if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT1') {
																		$lc_text = Translate('Optieveld 1');
																	} else {
																		if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT2') {
																			$lc_text = Translate('Optieveld 2');
																		} else {
																			if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT3') {
																				$lc_text = Translate('Optieveld 3');
																			} else {
																				if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT4') {
																					$lc_text = Translate('Optieveld 4');
																				} else {
																					if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT5') {
																						$lc_text = Translate('Optieveld 5');
																					} else {
																						if (($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_COMPARE') && (PRODUCT_COMPARE == 'true')) {
																							$lc_text = Translate('Vergelijk');
																							$column_width = $column_width / 2;
																							$column_width_compare = $column_width;
																							$column_class .= ' element-c';
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
							if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_SPECS') {
								for ($s = 0; $s < PRODUCT_LIST_SPECS_COUNT; $s++) {
									if ($checkboxes[$s]['id'] != '') {
										?>
										<div class="column xxxx column-<?php echo $column_list . $column_class; ?>"
										     <?php if ($display_class == 'list') { ?>style="width:<?php echo $column_width; ?>%;"<?php } ?>>
											<div class="column_inner">
												<?php
												if ((PRODUCT_LIST_HEADING == 'dynamic') && ($display_class == 'list')) {
													if (isset($chosen_spec_column[$s])) {
														$default_selected = $chosen_spec_column[$s];
													} else {
														$default_selected = $checkboxes[$s]['id'];
													}
													echo tep_draw_pull_down_menu('PRODUCT_' . $listtype . '_SPECS_' . $s,
															$checkboxes, $default_selected,
															' style="width:65%;"') . tep_create_sort_heading($_GET['sort'],
															$column_list, '');
												} else {
													if (PRODUCT_LIST_HEADING != 'static') {
														echo tep_create_sort_heading($_GET['sort'], $column_list,
															$checkboxes[$s]['text'], true);
													} else {
														?><span
															class="static"><?php echo $checkboxes[$s]['text']; ?></span><?php
													}
												}
												?>
											</div>
										</div>
									<?php
									}
									if ($s < (PRODUCT_LIST_SPECS_COUNT - 1)) {
										$column_list++;
									}
								}
								?>
							<?php

							} else {
								?>
								<div class="column ttt column-<?php echo $column_list . $column_class; ?>"
								     <?php if ($display_class == 'list') { ?>style="width:<?php echo $column_width; ?>%;"<?php } ?>>
									<div class="column_inner">
										<?php
										if (($list_config['configuration_key'] != 'PRODUCT_' . $listtype . '_IMAGE') && ($list_config['configuration_key'] != 'PRODUCT_' . $listtype . '_BUY_NOW') && ($list_config['configuration_key'] != 'PRODUCT_' . $listtype . '_COMPARE') && (PRODUCT_LIST_HEADING != 'static') && ($listing_type != 'specials_listing') && ($listing_type != 'products_new_listing') && ($listing_type != 'search_results')) {
											echo tep_create_sort_heading($_GET['sort'], $column_list, $lc_text);
										} elseif (($list_config['configuration_key'] == 'PRODUCT_LIST_COMPARE') && (PRODUCT_COMPARE_HEADING_LINK == 'true') && (PRODUCT_COMPARE == 'true')) {
											$category_info_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$compare_category . "' and language_id = '" . (int)$languages_id . "'");
											$category_info = tep_db_fetch_array($category_info_query);
											$compare_category = end(explode('_', $_GET['cPath']));
											?>
											<span class="static"><a
													href="<?php echo tep_href_link(FILENAME_COMPARE_PRINT,
														'cc=' . $compare_category); ?>"
													<?php if (PRODUCT_COMPARE_VIEW == 'popup') { ?>onClick="window.open('<?php echo tep_href_link(FILENAME_COMPARE_PRINT,
														'cc=' . $compare_category,
														'NONSSL'); ?>', '<?php echo $category_info['categories_name'] . ' ' . Translate('vergelijken'); ?>','scrollbars=yes,resizable=yes,status=yes,width=800,height=600'); return false"<?php } ?>
													target="_blank" id="compare_heading"
													title="<?php echo Translate('Klik om de geselecteerde producten te vergelijken'); ?>"><?php echo $lc_text; ?></a></span>
										<?php
										if (PRODUCT_COMPARE_VIEW == 'content') { ?>
											<script type="text/javascript">
												/* <![CDATA[ */
												jQuery(document).ready(function () {
													jQuery("#compare_heading").click(function () {
														jQuery.ajax({
															url: '<?php echo tep_href_link(FILENAME_AJAX_SEARCH, 'mode=compare&cc='.$compare_category); ?>',
															success: function (data) {
																jQuery("#compare_window").html(data);
															}
														});
														jQuery("#compare_window").show('slow');
														return false;
													});
												});
												/* ]]> */
											</script>
										<?php } elseif (PRODUCT_COMPARE_VIEW == 'lightbox') { ?>
											<script type="text/javascript">
												/* <![CDATA[ */
												jQuery(document).ready(function () {
													jQuery("#compare_heading").colorbox({
														href: "<?php echo tep_href_link(FILENAME_AJAX_SEARCH, 'mode=compare&cc='.$compare_category); ?>",
														width: "850px",
														height: "85%"
													});
												});
												/* ]]> */
											</script>
										<?php }
										} else {
											?>
											<span class="static"><?php echo $lc_text; ?></span>
										<?php
										}
										?>
									</div>
								</div>
							<?php
							}
						}
					}
					?>
				</div>
			<?php
			}
			$rows = 0;
			$listing_query = tep_db_query($listing_split->sql_query);
			/*echo $listing_split->number_of_rows;*/
			while ($listing = tep_db_fetch_array($listing_query)) {
				/*language fallback*/
				if (LANGUAGE_FALLBACK == 'true') {
					$language_fallback_query = tep_db_query("select products_name, products_description, products_technical from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$listing['products_id'] . "' and language_id = '1'");
					$language_fallback = tep_db_fetch_array($language_fallback_query);
					if ($listing['products_name'] == '') {
						$listing['products_name'] = $language_fallback['products_name'];
					}
					if ($listing['products_description'] == '') {
						$listing['products_description'] = $language_fallback['products_description'];
					}
					if ($listing['products_technical'] == '') {
						$listing['products_technical'] = $language_fallback['products_technical'];
					}
				}
				/*language fallback*/
				$rows++;
				$item_class = '';
				if ($rows % 2) {
					$item_class .= ' even';
				} else {
					$item_class .= ' odd';
				}
				if ($rows == 1) {
					$item_class .= ' first';
				}
				if ($rows == $listing_split->number_of_rows) {
					$item_class .= ' last';
				}
				if ($rows % PRODUCT_LISTING_GRID_COLUMNS == 0) {
					$item_class .= ' lastofrow';
				}
				if ($display_class == 'grid') {
					$product_width = (100 / PRODUCT_LISTING_GRID_COLUMNS);
					$extra_style = ' style="width:' . $product_width . '%;' . tep_get_product_style($listing['products_id']) . '"';
				} else {
					if (tep_get_product_style($listing['products_id']) != '') {
						$extra_style = ' style="' . tep_get_product_style($listing['products_id']) . '"';
					} else {
						$extra_style = '';
					}
				}
				//DISCOUNT
				if (USE_PRICES_TO_QTY == 'false' && PRICE_BOOK == 'true') { //added here so this can be used add the whole page
					$cPath = tep_get_product_path($listing['products_id']);
					$discount_price = tep_get_discountprice($listing['products_price'], $customer_id, $customer_group,
						$listing['products_id'], $cPath, $listing['manufacturers_id']);
					$discount = false;
					if (PRICE_BOOK_STAR_PRODUCT_LISTING == 'true') {
						if ($discount_price['lowest']['discount'] > 0 && $listing['specials_new_products_price'] > $discount_price['lowest']['price']) {
							$discount = $discount_price['lowest']['discount'];
						}
					}
				}
				//END DISCOUNT
				?>
				<div class="product<?php echo $item_class; ?>"<?php echo $extra_style; ?>>
					<div class="product-inner">
						<?php
						$cur_row = sizeof($list_box_contents) - 1;
						$list_config_query = tep_db_query($columns_query);
						if ($total_columns > 0) {
							$define_list = array();
							$select_column_list = '';
							$listing_sql_suff = '';
							$column_list = 0;
							while ($list_config = tep_db_fetch_array($list_config_query)) {
								if (($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_PRICE') || ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_BUY_NOW') || ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_QUANTITY')) {
									if (CanShop() == 'false') {
										$list_config['configuration_value'] = 0;
									} else {
										if (USE_PRICES_TO_QTY == 'true') {
											$list_config['configuration_value'] = 0;
										}
									}
								}
								if ($list_config['configuration_value'] > 0) {
									$column_list++;
									$column_class = '';
									if ($column_list == 1) {
										$column_class .= ' first';
									}
									$column_width = (100 / $total_columns);
									if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_IMAGE') {
										$column_width = $column_width / 2;
									} elseif ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_NAME') {
										$column_width = $column_width + ($column_width / 2);
									}
									if (PRODUCT_COMPARE == 'true') {
										if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_COMPARE') {
											$column_width = $column_width / 2;
											$column_width_compare = $column_width;
										} elseif ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_NAME') {
											$column_width = $column_width + $column_width_compare;
										}
									}
									if ($list_config['configuration_key'] != 'PRODUCT_' . $listtype . '_SPECS') {
										?>
										<div
											class="column x column-<?php echo $column_list . $column_class; ?>"<?php if ($display_class == 'list') { ?> style="width:<?php echo $column_width; ?>%;"<?php } ?>>
											<div class="column_inner">
												<?php

												if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_MODEL') {
													?>
													<div class="product-model"><a
															href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO,
																'products_id=' . $listing['products_id']); ?>"><?php echo $listing['products_model']; ?></a>
													</div>
												<?php
												} else {
													if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_NAME') {
														?>
														<div class="product-name"><h2><a
																	href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO,
																		'products_id=' . $listing['products_id']); ?>"><?php echo $listing['products_name']; ?></a>
															</h2></div>
													<?php
													} else {
														if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_DESCRIPTION') {
															?>
															<div
																class="product-description"><?php echo substr($listing['products_description'],
																	0, 100); ?></div>
														<?php
														} else {
															if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_TECHNICAL') {
																?>
																<div
																	class="product-technical"><?php echo $listing['products_technical']; ?></div>
															<?php
															} else {
																if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_MANUFACTURER') {
																	?>
																	<div
																		class="product-manufacturer"><?php echo $listing['manufacturers_name']; ?></div>
																<?php
																} else {
																	if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_QUANTITY') {
																		if ((SOAP_STATUS == 'true') && (SOAP_STOCK == 'true')) {
																			$voorraad = GetStockMaat($listing['products_id'],
																				'', SOAP_STOCK_TYPE);
																		} else {
																			$voorraad = $listing['products_quantity'];
																		}
																		if ($voorraad > 0) {
																			?>
																			<div
																				class="product-quantity available"><?php echo Translate('Beschikbaar'); ?></div>
																		<?php
																		} else {
																			?>
																			<div
																				class="product-quantity unavailable"><?php echo Translate('Niet beschikbaar'); ?></div>
																		<?php
																		}
																	} else {
																		if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_IMAGE') {
																			if ($display_class == 'list') {
																				$image_width = MEDIUM_THUMBNAIL_WIDTH;
																				$image_height = MEDIUM_THUMBNAIL_HEIGHT;
																			} else {
																				$image_width = SMALL_IMAGE_WIDTH;
																				$image_height = SMALL_IMAGE_HEIGHT;
																			}
																			?>
																			<div class="product-image"><a
																					href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO,
																						'products_id=' . $listing['products_id']); ?>"><?php echo tep_image(DIR_WS_IMAGES . $listing['products_image'],
																						$listing['products_name'],
																						$image_width,
																						$image_height); ?></a></div>
																			<?php
																			echo tep_get_product_stars($listing['products_id'],
																				$display_class, $discount);
																		} else {
																			if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_WEIGHT') {
																				?>
																				<div
																					class="product-quantity"><?php echo $listing['products_weight']; ?></div>
																			<?php
																			} else {
																				if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_PRICE') {
																					//DISCOUNT
																					if ($discount_price['lowest']['discount'] > 0 && PRICE_BOOK == 'true') {
																						if (tep_not_null($listing['specials_new_products_price'])) {
																							$new_price = $listing['specials_new_products_price'];
																							if ($new_price < $discount_price['lowest']['price']) {
																								$products_price = '<span class="oldprice">';
																								$products_price .= $currencies->display_price($listing['products_price'],
																									tep_get_tax_rate($listing['products_tax_class_id']));
																								$products_price .= '</span><br />';
																								$products_price .= '<span class="specialprice">';
																								$products_price .= $currencies->display_price($new_price,
																									tep_get_tax_rate($listing['products_tax_class_id']));
																								$products_price .= '</span>';
																							} else {
																								$products_price = '<span class="oldprice">';
																								$products_price .= $currencies->display_price($listing['products_price'],
																									tep_get_tax_rate($listing['products_tax_class_id']));
																								$products_price .= '</span><br />';
																								$products_price .= '<span class="specialprice">';
																								$products_price .= $currencies->display_price($discount_price['lowest']['price'],
																									tep_get_tax_rate($listing['products_tax_class_id']));
																								$products_price .= '</span>';
																							}
																						} else {
																							$products_price = '<span class="oldprice">';
																							$products_price .= $currencies->display_price($listing['products_price'],
																								tep_get_tax_rate($listing['products_tax_class_id']));
																							$products_price .= '</span><br />';
																							$products_price .= '<span class="specialprice">';
																							$products_price .= $currencies->display_price($discount_price['lowest']['price'],
																								tep_get_tax_rate($listing['products_tax_class_id']));
																							$products_price .= '</span>';
																						}
																					} else {
																						if (tep_not_null($listing['specials_new_products_price'])) {
																							$products_price = '<span class="oldprice">' . $currencies->display_price($listing['products_price'],
																									tep_get_tax_rate($listing['products_tax_class_id'])) . '</span> <span class="specialprice">' . $currencies->display_price($listing['specials_new_products_price'],
																									tep_get_tax_rate($listing['products_tax_class_id'])) . '</span>';
																						} else {
																							$products_price = '<span class="yourprice">' . $currencies->display_price($listing['products_price'],
																									tep_get_tax_rate($listing['products_tax_class_id'])) . '</span>';
																						}
																					}
																					echo '<div class="product-price">';
																					echo $products_price;
																					echo '</div>';
																					//END DISCOUNT
																				} else {
																					if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_BUY_NOW') {
																						?>
																						<div class="product-buy-now">
																							<a href="<?php echo tep_href_link(FILENAME_DEFAULT,
																								tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing['products_id']); ?>"
																							   class="button-a"><?php echo Translate('Voeg toe aan winkelwagen'); ?></a>
																						</div>
																					<?php
																					} else {
																						if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT1') {
																							?>
																							<div
																								class="product-opt products_opt1"><?php echo $listing['products_opt1']; ?></div>
																						<?php
																						} else {
																							if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT2') {
																								?>
																								<div
																									class="product-opt products_opt2"><?php echo $listing['products_opt2']; ?></div>
																							<?php
																							} else {
																								if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT3') {
																									?>
																									<div
																										class="product-opt products_opt3"><?php echo $listing['products_opt3']; ?></div>
																								<?php
																								} else {
																									if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT4') {
																										?>
																										<div
																											class="product-opt products_opt4"><?php echo $listing['products_opt4']; ?></div>
																									<?php
																									} else {
																										if ($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_OPT5') {
																											?>
																											<div
																												class="product-opt products_opt5"><?php echo $listing['products_opt5']; ?></div>
																										<?php
																										} else {
																											if (($list_config['configuration_key'] == 'PRODUCT_' . $listtype . '_COMPARE') && (PRODUCT_COMPARE == 'true') && ($listing_type == 'product_listing') && ($_GET['cPath'] != '')) {
																												?>
																												<div
																													class="product-opt products_compare">
																													<?php
																													$is_checked = '';
																													$compare_category = end(explode('_',
																														$_GET['cPath']));
																													if ($_COOKIE['compare_' . $compare_category]) {
																														$cookie_val = $_COOKIE['compare_' . $compare_category];
																														if (strstr($cookie_val,
																															'_' . $listing['products_id'])) {
																															$is_checked = ' checked';
																														}
																													}
																													?>
																													<input
																														type="checkbox"
																														id="compare_<?php echo $listing['products_id']; ?>"
																														name="<?php echo $listing['products_name']; ?>"<?php echo $is_checked; ?> />
																												</div>
																											<?php
																											}
																										}
																									}
																								}
																							}
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
												}
												?>
											</div>
										</div>
									<?php
									} else {
										if ($checkboxes[0]['id'] != '') {
											for ($s = 0; $s < $specs_col_count; $s++) {
												?>
												<div
													class="column xx column-<?php echo $column_list . $column_class; ?>"<?php if ($display_class == 'list') { ?> style="width:<?php echo $column_width; ?>%;"<?php } ?>>
													<div class="column_inner">
														<div class="product-spec">
															<?php
															if (isset($chosen_spec_column[$s])) {
																$subkenmerk = $chosen_spec_column[$s];
															} else {
																$subkenmerk = $checkboxes[$s]['id'];
															}
															echo show_product_spec($listing['products_id'],
																$specification_group, $subkenmerk, $display_class);
															?>
														</div>
													</div>
												</div>
												<?php
												if ($s < ($specs_col_count - 1)) {
													$column_list++;
												}
											}
										}
									}

								}
							}
							if ($display_class == 'list') {
								?>
								<div class="clear"></div>
							<?php
							}
						}
						?>
					</div>
				</div>
			<?php
			}
			if ($display_class == 'grid') {
				echo '<div class="clear"></div>';
			}
			?>
		</div>
	<?php
	}
	if (($listing_type == 'product_listing') || ($listing_type == 'specials_listing') || ($listing_type == 'products_new_listing') || ($listing_type == 'search_results')) {
		if (($listing_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3'))) {
			?>
			<table border="0" width="100%" cellspacing="0" cellpadding="2">
				<tr>
					<td class="smallTextpaging"><?php echo $listing_split->display_count(Translate('Product <b>%d</b> tot <b>%d</b> (van <b>%d</b> producten)')); ?></td>
					<td class="smallTextpaging pages"
					    align="right"><?php echo Translate('Paginas') . ': ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS,
								tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
				</tr>
			</table>
		<?php
		}
	}
} else {
	if ($listing_type == 'product_listing') {
		echo Translate('Geen producten');
	}
}
if ((PRODUCT_COMPARE == 'true') && ($listing_type == 'product_listing') && ($_GET['cPath'] != '')) {
	?>
	<script type="text/javascript">
		/* <![CDATA[ */
		var compare_category = <?php echo end(explode('_', $_GET['cPath'])); ?>;
		function toggleListCheckbox() {
			if (jQuery.readCookie("compare_" + compare_category)) {
				var cookie = jQuery.readCookie("compare_" + compare_category);
			} else {
				var cookie = '';
			}
			var checkboxId = jQuery(this).attr('id');
			var checkboxName = jQuery(this).attr('name');
			jQuery('#compare_list .compare_empty').remove();
			if (jQuery(this).is(':checked')) {
				if (jQuery('#compare_list').children().length >=<?php echo PRODUCT_COMPARE_MAX; ?>) {
					alert('<?php echo sprintf(Translate('U kan maximum %s producten vergelijken.'), PRODUCT_COMPARE_MAX) ?>');
					jQuery(this).attr('checked', false);
				} else {
					jQuery('#compare_list').append('<li class="' + checkboxId + '">' + checkboxName + '<a href="#" id="compare_delete_' + checkboxId.replace('compare_', '') + '" class="compare_delete"><?php echo Translate('Verwijderen'); ?></a></li>');
					jQuery.setCookie("compare_" + compare_category, cookie + '_' + checkboxId.replace('compare_', ''), {
						duration: <?php echo PRODUCT_COMPARE_COOKIE_DURATION; ?>,
						path: '<?php echo HTTP_COOKIE_PATH; ?>'
					});
				}
			} else {
				jQuery('#compare_list .' + checkboxId).remove();
				jQuery.setCookie("compare_" + compare_category, cookie.replace('_' + checkboxId.replace('compare_', ''), ''), {
					duration: <?php echo PRODUCT_COMPARE_COOKIE_DURATION; ?>,
					path: '<?php echo HTTP_COOKIE_PATH; ?>'
				});
			}
			if (jQuery('#compare_list').children().length == 0) {
				jQuery('#compare_list').append('<li class="compare_empty"><?php echo Translate('Er zijn nog geen producten geselecteerd.'); ?></li>').show('slow');
			}
			if (jQuery('#compare_list').children().length >= 2) {
				jQuery('.box.compare .compare_button').show('slow');
			} else {
				jQuery('.box.compare .compare_button').hide('slow');
			}
		}
		jQuery(document).ready(function () {
			jQuery(":checkbox", jQuery('#products')).change(toggleListCheckbox);
		});
		/* ]]> */
	</script>
<?php
}
?>