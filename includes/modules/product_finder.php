<table cellspacing="0" cellpadding="0" width="100%" border="0">
    <tr>
        <td>
            <?php
			$ch_opt = 10; //checkboxes/option
			$q_price = 3; //max price options
			$char_len = 10; //max characters in string before grouping
			echo tep_draw_form('ext_search', tep_href_link(FILENAME_AJAX_SEARCH, ($_GET['page']) ? 'page='.$_GET['page'] : ''), 'get', 'id="ext_search"');
			echo tep_draw_hidden_field('hk', $specification_group);
			echo tep_draw_hidden_field('search', '1');
			echo tep_draw_hidden_field('cPath', $cPath);
			echo tep_draw_hidden_field('sort', $_GET['sort']);
			$checkboxes = array(); //just checkboxes
			$checkboxes_s = array(); //sliders
			$checkboxes_m = array(); //manufacturers
			/*PRODUCT LIST SPECS*/
			$product_list_specs_values = array();
			/*PRODUCT LIST SPECS*/
			$get_cpath = end(explode('_', $_GET['cPath']));
			$product_models = array();
			$get_product_models_query = tep_db_query('SELECT DISTINCT p.products_model FROM products p, products_to_categories ptc WHERE p.products_id = ptc.products_id AND ptc.categories_id = "'.$get_cpath.'"');
			while ($get_product_models = tep_db_fetch_array($get_product_models_query)) {
						$product_models[] = $get_product_models['products_model'];
			}
			$product_models = '"'.implode('","', $product_models).'"';
			
			//manufacturers
			if (SHOW_MAN_SPECS == 'true' && !strstr(EXCLUDE_MAN_SPECS, ';'.$get_cpath.';')) {
				$manufacts = array();
				$manufacturers_query = tep_db_query('SELECT DISTINCT m.manufacturers_name, m.manufacturers_id FROM products p, manufacturers m WHERE p.manufacturers_id = m.manufacturers_id AND p.products_model IN ('.$product_models.') ORDER BY m.manufacturers_name asc');
				if(tep_db_num_rows($manufacturers_query) > 0) {
					$checkboxes_m[] = array('text' => Translate('Merken'),
											'type' => 'title');
				}
				while ($manufacturers_name = tep_db_fetch_array($manufacturers_query)) {
					$manChecked = false;
					if (isset($_GET['manufacturers']) && is_array($_GET['manufacturers'])) {
						if (in_array($manufacturers_name['manufacturers_id'], $_GET['manufacturers'])) {
							$manChecked = 'true';
						}
					}
					$checkboxes_m[] = array('name' => "manufacturers[]",
											'value' => $manufacturers_name['manufacturers_id'],
											'checked' => $manChecked,
											'text' => $manufacturers_name['manufacturers_name'],
											'type' => 'checkbox');					
				}
			}
			//end manufacturers

			//dynamic selections
			$subkenmerk_query = tep_db_query('SELECT value, subkenmerk FROM specifications WHERE hoofdkenmerk = "'.$specification_group.'" AND subkenmerk != "*" AND search = "1" AND language_id = "'.(int)$languages_id.'" ORDER BY subkenmerk');
				while ($subkenmerk = tep_db_fetch_array($subkenmerk_query)) {
					$productspecies_query = tep_db_query('SELECT DISTINCT ps.value FROM productspecs ps, specsdescription sd WHERE sd.subkenmerk = ps.value AND ps.products_model IN ('.$product_models.') AND sd.language_id = "'.(int)$languages_id.'" AND ps.hoofdkenmerk = "'.$specification_group.'" AND ps.subkenmerk = "'.$subkenmerk['subkenmerk'].'" ORDER BY ps.subkenmerk');
					if (tep_db_num_rows($productspecies_query) > 1) {
						$subkenmerk['subkenmerk'] = trim($subkenmerk['subkenmerk']);
						if (strstr($subkenmerk['subkenmerk'], '_')) { // extra opties voor S => Slider, M => niet samenvoegen, D => Dual slider, E => explode range of values
							$extraOptions = end(explode('_', $subkenmerk['subkenmerk']));
						} else {
							$extraOptions = '';
						}
						if (strstr($extraOptions, 'S') || strstr($extraOptions, 'D') || strstr($extraOptions, 'ED')) { //controle is slider of niet
							$checkboxes_s[] = array('text' => $subkenmerk['value'],
													'type' => 'title',
													'name' => $subkenmerk['subkenmerk'].'[]');
						} else {
							$checkboxes[] = array('text' => $subkenmerk['value'],
													'type' => 'title');
						}
						/*PRODUCT LIST SPECS*/
						$product_list_specs_values[] = $subkenmerk['subkenmerk'];
						/*PRODUCT LIST SPECS*/
						$select_options = array();
						$new_options = array();
						$final_options = array();
						$final_options2 = array();
						$result = array();
						$productspecs_query = tep_db_query('SELECT value FROM productspecs WHERE hoofdkenmerk = "'.$specification_group.'" AND products_model IN ('.$product_models.') AND subkenmerk = "'.$subkenmerk['subkenmerk'].'" ORDER BY subkenmerk');
						while ($productspecs = tep_db_fetch_array($productspecs_query)) {
							$specsdescription_query = tep_db_query('SELECT value FROM specsdescription WHERE subkenmerk = "'.$productspecs['value'].'" AND language_id = "'.(int)$languages_id.'"');
							$specsdescription = tep_db_fetch_array($specsdescription_query);
							$select_options[$productspecs['value']] = $specsdescription['value'];
						}
						asort($select_options);
						foreach ($select_options as $key=>$value) {
							if (!empty($value)) {
								$value = strtolower($value);
								$values = explode(' ', $value);
								if (is_numeric($values[0])) {
									$value = $values[0];
								} else {
									$value = str_replace(' ', '', $value);
								}
								$new_options[$key] = $value;
							}
						}
						asort($new_options);
						$select_options = '';
						$final_options = array();
						foreach ($new_options as $key=>$value) {
							if (!strstr($extraOptions, 'E')) {
								$value = str_replace(',', '.', $value);
							}
							$final_options[$value].=$key.';';
						}
						foreach ($final_options as $key=>$value) {
							$values = explode(';', $value);
							$values = array_unique($values);
							$value = implode("*", $values);
							$value =substr($value, 0, -1);
							$value = '*'.$value;
							$final_options[$key] = $value;
						}
						$result = $final_options;
						ksort($result);
						$count_options = count($result);
						if (strstr($extraOptions, 'E')) {
							$newresult = array();
							foreach ($result as $key=>$value) {
								if (strstr($key, ',')) {
									$values = explode(',', $key);
									foreach ($values as $counter=>$thevalue) {
										if (strstr($thevalue, '-')) {
											$thevalues = explode('-', $thevalue);
											if (count($thevalues) > 1) {
												for($i=$thevalues[0];$i<=$thevalues[1];$i++) {
													$newresult[$i] .= '!'.$value;
												}
											} else {
												$newresult[$thevalues[0]] .= '!'.$value;
											}
										} else {
											$newresult[$thevalue] .= '!'.$value;
										}
									}
								} else {
									$values = explode('-', $key);
									if (count($values) > 1 && is_int($values[0]) && is_int($values[1])) {
										for($i=$values[0];$i<=(int)$values[1];$i++) {
											$newresult[$i] .= '!'.$value;
										}
									} else {
										if (isset($values[1])) {
											$values[0] .= '-'.$values[1];
										}
										$newresult[trim($values[0])] .= '!'.$value;
									}
								}
							}
							ksort($newresult);
							$result = $newresult;
						}
						if (!strstr($extraOptions, 'M')) { //controle moet dit worden gesplitst of niet?
							if (strlen(key($result)) < $char_len) {
								if ($count_options > $ch_opt) {
									$new_count_options = ceil($count_options / 5);
									$i = 0;
									$new_result = array();
									$new_result_key = '';
									$new_result_value = '#';
									foreach ($result as $key=>$value) {
										if ($i < $new_count_options) {
											if ($i == 0) {
												$new_result_key = $key;
												$new_result_value .= $value;
											}
											if ($i == ($new_count_options -1)) {
												$new_result_key .= ' -> '.$key;
											}
											$new_result_value .= '*'.$value;
											$i++;
										} else {
											$new_result[$new_result_key] = $new_result_value;
											$new_result_key = '';
											$new_result_value = '#';
											$i=1;
											$new_result_key = $key;
											$new_result_value .= $value;
										}
									}
									$last_result_key = end(array_keys($result));
									$last_result_value = end(array_values($result));
									$new_result_key .= ' -> '.$last_result_key;
									$new_result_value .= '*'.$last_result_value;
									$new_result[$new_result_key] = $new_result_value;
									$result = $new_result;
								}
							}
						}
						foreach ($result as $key=>$value) {
							if (substr($value, 0, 1) == '!') {
								$value = str_replace('!*', '*', $value);
							} else if (substr($value, 0, 1) == '*') {
								$values = explode('*', $value);
								unset($values[0]);
								$productspecs_value_query = tep_db_query('SELECT value FROM specsdescription WHERE subkenmerk = "'.$values[1].'" AND language_id = "'.(int)$languages_id.'"');
								while ($productspecs_value = tep_db_fetch_array($productspecs_value_query)) {
									$key = $productspecs_value['value'];
								}
							} else if (substr($value, 0, 1) == '#') {
								$value = substr($value, 1);
								$values = explode('*', $value);
								$end_value = end(explode('*', $value));
								unset($values[0]);
								$productspecs_value_query = tep_db_query('SELECT value FROM specsdescription WHERE subkenmerk = "'.$values[1].'" AND language_id = "'.(int)$languages_id.'"');
								while ($productspecs_value = tep_db_fetch_array($productspecs_value_query)) {
									$key = $productspecs_value['value'];
								}
								$productspecs_value_query = tep_db_query('SELECT value FROM specsdescription WHERE subkenmerk = "'.$end_value.'" AND language_id = "'.(int)$languages_id.'"');
								while ($productspecs_value = tep_db_fetch_array($productspecs_value_query)) {
									$key .= ' -> '.$productspecs_value['value'];
								}
							} else {
								$productspecs_value_query = tep_db_query('SELECT value FROM specsdescription WHERE subkenmerk = "'.$value.'" AND language_id = "'.(int)$languages_id.'"');
								while ($productspecs_value = tep_db_fetch_array($productspecs_value_query)) {
									$key = $productspecs_value['value'];
								}
							}
							$optionType = 'checkbox';
							$optionChecked = false;
							if (isset($_GET[$subkenmerk['subkenmerk']]) && is_array($_GET[$subkenmerk['subkenmerk']])) {
								if (in_array($value, $_GET[$subkenmerk['subkenmerk']])) {
									$optionChecked = 'true';
								}
							}
							if (strstr($extraOptions, 'S') || strstr($extraOptions, 'D')) {
								if (strstr($extraOptions, 'D')) {
									$optionType = 'dualslider';
								} else {
									$optionType = 'slider';
								}
								$checkboxes_s[] = array('name' => $subkenmerk['subkenmerk'].'[]',
														'value' => $value,
														'checked' => $optionChecked,
														'text' => $key,
														'type' => $optionType);
							} else {
								$checkboxes[] = array('name' => $subkenmerk['subkenmerk'].'[]',
														'value' => $value,
														'checked' => $optionChecked,
														'text' => $key,
														'type' => $optionType);
							}
						}
					}
				}
				//end dynamic selections
				//Prices 
				if (SHOW_PRICE_SPECS == 'true' && !strstr(EXCLUDE_PRICE_SPECS, ';'.$get_cpath.';') ) {
					$products_prices = array();
					$select_products_price_query = tep_db_query('SELECT IF(s.status, s.specials_new_products_price, p.products_price) as final_price FROM products p LEFT JOIN specials s ON p.products_id = s.products_id WHERE p.products_model IN ('.$product_models.') ORDER BY final_price asc');
					while ($select_products_price = tep_db_fetch_array($select_products_price_query)) {
						$products_prices[] = $select_products_price['final_price'];
					}
					$products_prices = array_unique($products_prices);
					$prices_dropdown = array();
					if (count($products_prices) > 2) {
						$first_price_value = floor(reset($products_prices));
						$last_price_value = ceil(end($products_prices));
						$price_difference = $last_price_value - $first_price_value;
						$price_add = floor($price_difference/10);
						$all_prices = $first_price_value;
						for ($i=0;$i<10;$i++) {
							if (ShowSliderPrices() == 'true') {
								$prices_dropdown[] = array('id' => $all_prices.'&euro;', 'text' => $all_prices.'&euro;');
								$all_prices = $all_prices + $price_add;
							} else {
								$prices_dropdown[] = array('id' => $all_prices.'&euro;', 'text' => Translate('Prijs'), 'label' => Translate('Budget'));
								$all_prices = $all_prices + $price_add;
							}
						}
						if (ShowSliderPrices() == 'true') {
							$prices_dropdown[] = array('id' => $last_price_value.'&euro;', 'text' => $last_price_value.'&euro;');
						} else {
							$prices_dropdown[] = array('id' => $last_price_value.'&euro;', 'text' => Translate('Prijs'), 'label' => Translate('High Quality'));
						}
					}
				}
				//End prices
				?>
				<link rel="Stylesheet" href="<?php echo STS_TEMPLATE_DIR;?>/css/ui.slider.extras.css" type="text/css" />
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td style="height: 5px;" colspan="2">
                        </td>
                    </tr>
                    <tr>
                        <td>
							<div class="box product-finder">
                                <div class="box-title">
									<span><?php echo Translate('Zoekopties'); ?></span>
                                    <a href="#" id="unCheckAll"><?php echo Translate('Verwijder selecties'); ?></a>
                                </div>
                                <table cellpadding="0" cellspacing="0" width="100%" id="options_box_table">
                                    <tr>
                                        <td valign="top">
                                            <div class="OptContainer">
												<?php
												$columns = 1;
												$width = 170;
												$count_m = count($checkboxes_m) - 1;
												if (MANUFACTURER_COLUMNS == 'true' && !strstr(EXCLUDE_MAN_COLUMNS, ';'.$get_cpath.';') ) {
													if ($count_m >= 15) {
														$columns = 3;
													} else if ($count_m >= 10) {
														$columns = 2;
													}
												}
												if ($columns > 1) {
													$items_per_column = ceil(round($count_m / $columns));
													$width = $columns * 170;
												}
												?>
                                                <div class="opt_box" style="width: <?php echo $width;?>px;">
                                                    <ul class="checkbox_per_row" style="width: <?php echo $width;?>px;">
                                                        <?php
															$count = 0;
                                                            foreach ($checkboxes_m as $key=>$value) {
                                                                if ($value['type']=='title') {
                                                                    echo '<li class="checkbox_title">'.$value['text'].'</li>';
																	echo '<li><ul style="float:left;width:170px;">';
                                                                } else if ($value['type']=='checkbox') {
                                                                    echo '<li class="checkbox_text">'.tep_draw_checkbox_field($value['name'], $value['value'], $checked = $value['checked'], $parameters = 'id="'.$value['name'].'_'. $value['value'].'"');
                                                                    echo '<label for="'.$value['name'].'_'. $value['value'].'">&nbsp;'.$value['text']."</label></li>\n";
                                                                }
																$count++;
																if ($count > $items_per_column) {
																	echo '</ul><ul style="float:left;width:170px;">';
																	$count = 1;
																}
                                                            }
															echo '</ul></li>';
                                                        ?>
                                                    </ul>
                                                </div>
												<?php
												if (count($checkboxes) > 0) {
												?>
                                                <div class="opt_box">
                                                    <ul class"checkbox_per_row">
                                                        <?php
                                                        $i = 0;
                                                        foreach ($checkboxes as $key=>$value) {
                                                            if ($value['type']=='title') {
                                                                if ($i > 0) {
                                                                    echo '</ul></div>';
                                                                    echo '<div class="opt_box"><ul class"checkbox_per_row">';
                                                                }
                                                                    echo '<li class="checkbox_title">'.$value['text'].'</li>';
                                                                $i++;
                                                            } else if ($value['type']=='checkbox') {
                                                                echo '<li class="checkbox_text">'.tep_draw_checkbox_field($value['name'], $value['value'], $checked = $value['checked'], $parameters = 'id="'.$checkboxes[$i]['name'].'_'.$checkboxes[$i]['value'].'"');
                                                                echo '<label for="'.$checkboxes[$i]['name'].'_'.$checkboxes[$i]['value'].'">&nbsp;'.$value['text']."</label></li>\n";
                                                                $i++;
                                                            }
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                                <?php
												}
												/*PRODUCT LIST SPECS*/
												if (PRODUCT_LIST_SPECS!=0) {
													for ($s=0 ; $s<PRODUCT_LIST_SPECS_COUNT; $s++) {
														if ($product_list_specs_values[$s]!='') {
														echo tep_draw_hidden_field('PRODUCT_LIST_SPECS_'.$s, $product_list_specs_values[$s]);
														}
													}
												}
												/*PRODUCT LIST SPECS*/
												if (count($checkboxes_s) > 0) {
												?>
                                                <div class="opt_box">
                                                    <?php
                                                    $sliders = array();
                                                    if (is_array($checkboxes_s)) {
                                                        foreach ($checkboxes_s as $key=>$value) {
                                                            $get_prop = substr($value['name'], 0, -2);
                                                            if (!isset($_GET[$get_prop])) {
                                                                $sliders[] = $value['name'];
                                                            }
                                                        }
                                                        $sliders = array_unique($sliders);
                                                        $i=0;
                                                        foreach ($sliders as $value) {
                                                            $drop_down = array();
                                                            foreach ($checkboxes_s as $param) {
                                                                if ($value == $param['name']) {
                                                                    if ($param['type'] == 'title') {
                                                                        if ($i > 0) {
                                                                            echo '</div>';
                                                                            echo '<div class="opt_box" style="width:120px; height: 60px;">';
                                                                        }
                                                                        echo '<span class="slider_title">'.$param['text'].'</span>';
                                                                        $i++;
                                                                    } else {
																		if (strstr($value, '_EDS')) {
																			$type = $param['type'];
																			$drop_down[] = array('id' => $param['value'], 'text' => $param['text'], 'label' => $param['text']);
																		} else {
	                                                                        $type = $param['type'];
    	                                                                    $this_value = $param['value'];
        	                                                                $all_values = '';
            	                                                            foreach ($checkboxes_s as $key) {
                	                                                            if ($value == $key['name'] && ($key['type'] == 'slider' || $key['type'] == 'dualslider')) {
                    	                                                            if ($key['value'] == $this_value) {
                        	                                                            $all_values = '';
                            	                                                    }
                                	                                                $all_values .= '*'.$key['value'];
                                    	                                        }
                                        	                                }
                                            	                            $all_values = str_replace('**', '*', substr($all_values, 1));
                                                	                        $label = (int)$param['text'];
                                                    	                    $drop_down[] = array('id' => $all_values, 'text' => $param['text'], 'label' => $label);
																		}
                                                                    }
                                                                }
                                                            }
                                                            if ($type == 'dualslider') {
                                                                $first_value = $drop_down[0]['id'];
                                                                $last_value = $drop_down[count($drop_down) - 1]['id'];
                                                                echo tep_draw_pull_down_menu(substr($value, 0, -2).'_from', $drop_down, (isset($_GET[substr($value, 0, -2).'_from']) ? $_GET[substr($value, 0, -2).'_from'] : $first_value), 'id="'.substr($value, 0, -2).'_from"');
                                                                echo tep_draw_pull_down_menu(substr($value, 0, -2).'_to', $drop_down, (isset($_GET[substr($value, 0, -2).'_to']) ? $_GET[substr($value, 0, -2).'_to'] : $last_value), 'id="'.substr($value, 0, -2).'_to"');
                                                                $javascript_sliders[] = substr($value, 0, -2).'_from, select#'.substr($value, 0, -2).'_to';
                                                            } else {
                                                                echo tep_draw_pull_down_menu($value, $drop_down, (isset($_GET[$value]) ? $_GET[$value] : ''), 'id="'.substr($value, 0, -2).'"');
                                                                $javascript_sliders[] = substr($value, 0, -2);
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </div>
												<?php
												}
												?>
                                            </div>
                                            <?php

											if ((SHOW_PRICE_SPECS == 'true' && !strstr(EXCLUDE_PRICE_SPECS, ';'.$get_cpath.';') && count($prices_dropdown) > 2)) {

                                            ?>

                                            <div id="price_slider_title">

                                                <p><?php echo Translate('Prijsklasse:'); ?></p>

                                            </div>

                                            <div id="price_slider">

                                                <?php

                                                echo tep_draw_pull_down_menu('price_from', $prices_dropdown, (isset($_GET['price_from']) ? $_GET['price_from'] : $first_price_value), 'id="price_from"');

                                                echo tep_draw_pull_down_menu('price_to', $prices_dropdown, (isset($_GET['price_to']) ? $_GET['price_to'] : $last_price_value), 'id="price_to"');

                                                ?>

                                            </div>

                                            <?php

                                            }

                                            ?>
                                        </td>
                                    </tr>
                                </table>
							</div>
						</td>
					</tr>
				</table>
			</form>
        </td>
    </tr>
</table>
<script type="text/javascript" src="includes/js/jquery.cooquery.min.js"></script>
<script type="text/javascript" src="includes/js/selectToUISlider.jQuery.js"></script>
<script type="text/javascript">

/* <![CDATA[ */

function waitingProductsLoad() {

	var $pr = jQuery("#products");

	if (!$pr.children().is('div.overflow')) {

		var width = $pr.width();

		var height = $pr.height();

		var $div = $pr.prepend('<div class="overflow"><span><?php echo Translate('Even geduld, Uw selectie wordt geladen.');?></span></div>').find('div.overflow');

		$div.width(width);

		$div.height(height);

	}

}

function fixToolTipColor(){

	//grab the bg color from the tooltip content - set top border of pointer to same

	jQuery('.ui-tooltip-pointer-down-inner').each(function(){

		var bWidth = jQuery('.ui-tooltip-pointer-down-inner').css('borderTopWidth');

		var bColor = jQuery(this).parents('.ui-slider-tooltip').css('backgroundColor')

		jQuery(this).css('border-top', bWidth+' solid '+bColor);

	});	

}

jQuery(document).ready(function(){

	var $form = jQuery("#ext_search");

	jQuery('input[name="page"]', $form).remove();

	$form.submit(function(){

		$form =jQuery(this);

		//Replace values of data for price checkboxes

		var params = $form.serializeArray();

		jQuery.setCookie("kenmerken_<?php echo str_replace('=', '_', $_GET['cPath']);?>", $form.serialize(), { 

			duration: 1

		});

		var $prices = jQuery(":checkbox[name='p[]'][checked]", $form);

		var price_elem = 0;

		jQuery.ajax({

			url: $form.attr('action'),

			beforeSend: waitingProductsLoad,

			data: params,

			success: function(data){

				jQuery("#products").html(data);

			}

		});

		return false;

	});

	jQuery("input[type=checkbox]", $form).attr('checked', false);	

	var cookie = jQuery.readCookie("kenmerken_<?php echo str_replace('=', '_', $_GET['cPath']);?>");

	if (cookie) {

		var cookies = cookie.split('&');

		for (var i=0;i<cookies.length;i++) {

			var values = cookies[i].split('=');

			var key = values[0].replace('%5B%5D', "\\[\\]");

			var waarde = values[1].replace('%E2%82%AC', '');

			jQuery('input[name='+key+']', $form).each(function() {

				if (jQuery(this).val() == waarde) {

					jQuery(this).attr('checked', 'true');

				}

			});

			jQuery('select[name='+key+'] option', $form).each(function() {

				if (jQuery(this).val() == waarde) {

					jQuery(this).attr('selected', 'selected');

				} else {

					jQuery(this).attr('selected', false);

				}

			});			

			if (key == 'page') {

				$form.append('<input type="hidden" name="page" value="'+waarde+'" />');

			}

			//TESTEN

			if (key.indexOf('PRODUCT_LIST_SPECS')>=0) {

				jQuery("input[name="+key+"]").val(waarde);

			}

			<?php

		if ((SHOW_PRICE_SPECS == 'true' && !strstr(EXCLUDE_PRICE_SPECS, ';'.$get_cpath.';') && count($prices_dropdown) > 2)) {

			?>

			if (key == 'price_from') {

				jQuery('#price_from option').each(function(){

					var pfval = jQuery(this).val();

					pfval = parseInt(pfval);

					if (pfval == waarde) {

						jQuery(this).attr('selected', 'selected');

					} else {

						jQuery(this).attr('selected', false);

					}

				});

			}

			if (key == 'price_to') {

				jQuery('#price_to option').each(function(){

					var ptval = jQuery(this).val();

					ptval = parseInt(ptval);

					if (ptval == waarde) {

						jQuery(this).attr('selected', 'selected');

					} else {

						jQuery(this).attr('selected', false);

					}

				});

			}

			<?php

			}

			?>

		}

		$form.submit();

	}

	jQuery(":checkbox", $form).click(function(){

		$form.submit();

	});

	jQuery("a#unCheckAll").click(function(){

		jQuery("#ext_search").find('input[type=checkbox]').attr('checked', false);

		$form.submit();

		return false;

	});

	jQuery(function(){

		<?php

		if ((SHOW_PRICE_SPECS == 'true' && !strstr(EXCLUDE_PRICE_SPECS, ';'.$get_cpath.';') && count($prices_dropdown) > 2)) {

			if (ShowSliderPrices() == 'true') {

				$show_labels = 10;

			} else {

				$show_labels = 2;

			}

		?>

		jQuery('select#price_from, select#price_to').selectToUISlider({

			labels: <?php echo $show_labels; ?>,

			<?php if (ShowSliderPrices() == 'false') { ?>

			labelSrc: 'label',

			<?php } ?>

			sliderOptions: {

				change:function(e, ui) {

					$form.submit();

				}

			}

		}).hide();

		<?php

		}

		if (isset($javascript_sliders) && is_array($javascript_sliders)) {

			foreach ($javascript_sliders as $value) {

		?>

		jQuery('select#<?=$value?>').selectToUISlider({

			labels: 5,

			labelSrc: 'label',

			sliderOptions: {

				change:function(e, ui) {

					$form.submit();

				}

			}

		}).hide();

		<?php

			}

		}

		?>

		fixToolTipColor();

	});

	jQuery(".pages a").live('click', function(){

		var href = jQuery(this).attr('href').split('&');

		for(var i in href) {

			if (href[i].indexOf('page=') >= 0) {

				var page = href[i].split('=');

			}

		}

		if (jQuery("input[name=page]").length == 0) {

			$form.append('<input type="hidden" name="page" value="'+page[1]+'" />');

		} else {

			jQuery("input[name=page]").val(page[1]);

		}

		$form.submit();

		return false;

	});

	jQuery("a.productListing-heading").live('click', function(){

		var href = jQuery(this).attr('href').split('&');

		for(var i in href) {

			if (href[i].indexOf('sort=') >= 0) {

				var page = href[i].split('=');

			}

		}

		if (jQuery("input[name=sort]").length == 0) {

			$form.append('<input type="hidden" name="page" value="'+page[1]+'" />');

		} else {

			jQuery("input[name=sort]").val(page[1]);

		}

		$form.submit();

		return false;

	});

	jQuery("select[name^=PRODUCT_LIST_SPECS]").livequery('change', function(){

		var select_name = this.name;

		if (jQuery("input[name="+select_name+"]").length == 0) {

			$form.append('<input type="hidden" name="'+select_name+'" value="'+jQuery(this).val()+'" />');

		} else {

			jQuery("input[name="+select_name+"]").val(jQuery(this).val());

		}

		$form.submit();

		return false;

	});

	jQuery("select[name=ppp]").live('change', function(){

		if (jQuery("input[name=ppp]").length == 0) {

			$form.append('<input type="hidden" name="ppp" value="'+jQuery(this).val()+'" />');

		} else {

			jQuery("input[name=ppp]").val(jQuery(this).val());

		}

		$form.submit();

		return false;

	});

});

/* ]]> */

</script>