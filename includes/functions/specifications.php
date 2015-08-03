<?php
function show_product_spec($id, $hoofdkenmerk, $subkenmerk, $display_type) {
	global $languages_id;
	$model_query = tep_db_query('SELECT products_model FROM products WHERE products_id = "'.$id.'"');
	$model = tep_db_fetch_array($model_query);
	$return = '';
	$specs_query = tep_db_query('SELECT DISTINCT sd.value as waarde, s.value as kenmerk FROM productspecs ps, specifications s, specsdescription sd WHERE ps.hoofdkenmerk = s.hoofdkenmerk AND ps.subkenmerk = s.subkenmerk AND s.language_id = "'.(int)$languages_id.'" AND ps.products_model = "'.$model['products_model'].'" AND sd.subkenmerk = ps.value AND ps.hoofdkenmerk = "'.$hoofdkenmerk.'" AND ps.subkenmerk = "'.$subkenmerk.'" AND sd.language_id = "'.(int)$languages_id.'" ORDER BY ps.subkenmerk');
	$specs = tep_db_fetch_array($specs_query);
	if ($display_type=='grid') {
		return '<span class="kenmerk-titel">'.$specs['kenmerk'].'</span>: <span class="kenmerk-waarde">'.$specs['waarde'].'</span>';
	} else {
		return '<span class="kenmerk-waarde">'.$specs['waarde'].'</span>';
	}
}
function show_specific_specs($id, $limit = 0, $all = false, $tags = false) {
	global $languages_id;
	$model_query = tep_db_query('SELECT products_model FROM products WHERE products_id = "'.$id.'"');
	$model = tep_db_fetch_array($model_query);
	if ($limit > 0) {
		$limit_query = ' LIMIT '.$limit;
	} else {
		$limit_query = '';
	}
	if ($all) {
		$all_query = '';
	} else {
		$all_query = 's.homepage = "1" AND ';
	}
	$return = '';
	$specs_query = tep_db_query('SELECT DISTINCT s.value as title, sd.value as waarde FROM productspecs ps, specifications s, specsdescription sd WHERE ps.hoofdkenmerk = s.hoofdkenmerk AND ps.subkenmerk = s.subkenmerk AND s.language_id = "'.(int)$languages_id.'" AND '.$all_query.'ps.products_model = "'.$model['products_model'].'" AND sd.subkenmerk = ps.value AND sd.language_id = "'.(int)$languages_id.'" ORDER BY ps.subkenmerk'.$limit_query);
	while ($specs = tep_db_fetch_array($specs_query)) {
		if ($tags) {
			$return .= $specs['title'].' '.$specs['waarde'].'<br />';
		} else {
			$return .= $specs['title'].' '.$specs['waarde'].'<br />';
		}
	}
	return $return;
}
function random_technical_description ($technical, $limit = 0, $showtitle = true, $showtags = true, $showimage = false) {
	$technical = preg_replace("/<([^kt\/])/", "$1", $technical); 
	$technical = preg_replace("/([^kt])>/", "$1", $technical); 
	if ($showimage == true) {
		$technical = str_replace('t_yes', '<img src="images/technical/yes.gif" alt="'.Translate('Ja').'" />', $technical);
		$technical = str_replace('t_no', '<img src="images/technical/no.gif" alt="'.Translate('Nee').'" />', $technical);
	} else {
		$technical = str_replace('t_yes', Translate('Ja'), $technical);
		$technical = str_replace('t_no', Translate('Nee'), $technical);
	}
	$pos = strpos($technical, '</t>');
	if ($pos === false) {
		if ($limit < 1) {
			//$technical_description .= '<div class="kenmerk">'.$value['kenmerk'].'</div>';
			$splitkenmerk = explode('</k>', $technical);
			//shuffle($splitkenmerk); geen shuffle wanneer er geen titels zijn!!!
			foreach($splitkenmerk as $kenmerk){
				$kenmerk = str_replace('<k>', '', $kenmerk);
				if (!empty($kenmerk)) {
					$kenmerksplit = explode(':',$kenmerk, 2);
					if ($showtags == true) {
						$technical_description .= '<tr class="data"><td class="first">'.$kenmerksplit[0].': </td><td class="last">'.$kenmerksplit[1].'</td></tr>';
					} else {
						$technical_description .= $kenmerksplit[0].': '.$kenmerksplit[1].', ';
					}
				}
			}
		} else {
			$i=0;
			$splitkenmerk = explode('</k>', $technical);
			shuffle($splitkenmerk);
			foreach ($splitkenmerk as $kenmerk) {
				$kenmerk = str_replace('<k>', '', $kenmerk);
				if (!empty($kenmerk)) {
					$kenmerksplit = explode(':',$kenmerk, 2);
					if ($showtags == true) {
							if ($kenmerksplit[1] == '') {
								$technical_description .= '';
							} else {
								$technical_description .= '<tr class="data"><td class="first">'.$kenmerksplit[0].': </td><td class="last">'.$kenmerksplit[1].'</td></tr>';
							}
					} else {
						$technical_description .= $kenmerksplit[0].': '.$kenmerksplit[1].', ';
					}
					$i++;
					if ($i >= $limit) {
						return $technical_description;
					}
				}
			}
		}
		if (($technical_description!='') && ($showtags == true)) {
			$technical_description = '<div class="box"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="data-table">'.$technical_description.'</table></div>';
		}
		return $technical_description;
	} else {
		$explodefortitle = explode('<t>', $technical);
		$description_array = array();
		foreach ($explodefortitle as $group) {
			if (!empty($group)) {
				$group_split = explode('</t>', $group);
				if ($group_split[1] == '') {
					$description_array[] = array('title'=>'', 'kenmerk'=>$group_split[0]);
				} else {
					$description_array[] = array('title'=>$group_split[0], 'kenmerk'=>$group_split[1]);
				}
			}
		}
		$technical_description = '';
		if ($limit < 1) {
			foreach ($description_array as $value) {
				if($showtitle == true) {
					if ($showtags == true) {
						if ($value['title'] != '') {
							$technical_description .= '<tr class="title"><td class="first" colspan="2">'.$value['title'].'</td></tr>';
						}
					} else {
						$technical_description .= $value['title'].': ';	
					}
				}
				//$technical_description .= '<div class="kenmerk">'.$value['kenmerk'].'</div>';
				$splitkenmerk = explode('</k>', $value['kenmerk']);
				shuffle($splitkenmerk);
				foreach($splitkenmerk as $kenmerk){
					$kenmerk = str_replace('<k>', '', $kenmerk);
					if (!empty($kenmerk)) {
						$kenmerksplit = explode(':',$kenmerk, 2);
						if ($showtags == true) {
							if ($kenmerksplit[1] == '') {
								$technical_description .= '';
							} else {
								$technical_description .= '<tr class="data"><td class="first">'.$kenmerksplit[0].': </td><td class="last">'.$kenmerksplit[1].'</td></tr>';
							}
						} else {
							$technical_description .= $kenmerksplit[0].': '.$kenmerksplit[1].', ';
						}
					}
				}
			}
		} else {
			$i=0;
			foreach ($description_array as $value) {
				if($showtitle == true) {
					if ($showtags == true) {
						$technical_description .= '<tr class="title"><td class="first" colspan="2">'.$value['title'].'</td></tr>';
					} else {
						$technical_description .= $value['title'].' ';
					}
				}
				//$technical_description .= '<div class="kenmerk">'.$value['kenmerk'].'</div>';
				$splitkenmerk = explode('</k>', $value['kenmerk']);
				shuffle($splitkenmerk);
				foreach ($splitkenmerk as $kenmerk) {
					$kenmerk = str_replace('<k>', '', $kenmerk);
					if (!empty($kenmerk)) {
						$kenmerksplit = explode(':',$kenmerk, 2);
						if ($showtags == true) {
							$technical_description .= '<tr class="data"><td class="first">'.$kenmerksplit[0].': </td><td class="last">'.$kenmerksplit[1].'</td></tr>';
						} else {
							$technical_description .= $kenmerksplit[0].': '.$kenmerksplit[1].', ';
						}
						$i++;
						if ($i >= $limit) {
							return $technical_description;
						}
					}
				}
			}
		}
		if (($technical_description!='') && ($showtags == true)) {
			$technical_description = '<div class="box"><table width="100%" border="0" cellspacing="0" cellpadding="0" class="data-table">'.$technical_description.'</table></div>';
		}
		return $technical_description;
	}
}
function StructuredSpecificationsTable($model, $showtooltip = true, $showicons = true) {
	global $languages_id;
	$productspecs_query = tep_db_query("SELECT * FROM productspecs WHERE products_model = '".$model."' order by subkenmerk");
	$product_id_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_model = '".$model."'");
	$product_id = tep_db_fetch_array($product_id_query);
	$categories_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '".(int)$product_id['products_id']."'");
	$categories = tep_db_fetch_array($categories_query);
	$count=0;
	if (tep_db_num_rows($productspecs_query)>0) {
		$text .= '<div class="box">';
		$text .= '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="data-table">';
		$text .= '<tr class="title">';
		if (PRODUCT_COMPARE=='true') {
			$text .= '<td>'.Translate('Technische specificaties').'</td>';
			if (strstr($_COOKIE['compare_'.$categories['categories_id']], '_'.$product_id['products_id'])) {
				$text .= '<td><a href="#" class="compare_add">'.Translate('Niet meer vergelijken').'</a></td>';
			} else {
				$text .= '<td><a href="#" class="compare_add">'.Translate('Vergelijken').'</a></td>';
			}
		} else {
			$text .= '<td colspan="2">'.Translate('Technische specificaties').'</td>';
		}
		$text .= '</tr>';
		while ($productspecs = tep_db_fetch_array($productspecs_query))
		{
			$specifications_query = tep_db_query("SELECT value, title FROM specifications WHERE hoofdkenmerk = '".$productspecs['hoofdkenmerk']."' AND subkenmerk = '".$productspecs['subkenmerk']."' AND language_id = '".(int)$languages_id."' order by subkenmerk");
			while ($specifications = tep_db_fetch_array($specifications_query))
			{
				/*if ($showtooltip == true) {
					$tooltip = '<span class="tltp_container">'.tep_get_tooltip($productspecs['hoofdkenmerk'].$productspecs['subkenmerk'], $specifications['value']).'</span>';
				} else {
					$tooltip = '';
				}
				*/
				if ($specifications['title'] != 1)
				{
					$count++;
					if ($count%2) { $tr_class=' even'; } else { $tr_class=' odd'; }
					$text .= '<tr class="data'.$tr_class.'">';
					$text .= '<td width="50%" class="first">'.$tooltip.$specifications['value'].' : </td>';
					$text .= '<td width="50%" class="last">';
					if ($specifications['title'] != 1)
					{
						$specsdescription_query = tep_db_query("SELECT value FROM specsdescription WHERE subkenmerk = '".$productspecs['value']."' AND language_id = '".(int)$languages_id."' order by language_id");
						while ($specsdescription = tep_db_fetch_array($specsdescription_query))
						{
							if ($showicons == true) {
								$waarde = ShowSpecificationIcons($specsdescription['value']);
							} else {
								$waarde = $specsdescription['value'];
							}
							$text .= $waarde;
						}
					}
					$text .= '</td>';
					$text .= '</tr>';
				}
				else
				{
					$text .= '<tr class="title"><td colspan="2" class="first">'.$tooltip.$specifications['value'].'</td></tr>';
				}
			}
		}
		$text .= '</table>';
		$text .= '</div>';
	} else {
		$text = '';
	}
	return $text;
}
function ShowSpecificationIcons($text) {
	$text = str_replace(Translate('Ja'), '<img src="images/technical/yes.gif" alt="'.Translate('Ja').'" />', $text);
	$text = str_replace(Translate('Nee'), '<img src="images/technical/no.gif" alt="'.Translate('Nee').'" />', $text);
	return $text;
}
function tep_get_tooltip($name, $title) {
	global $languages_id;
	if ($languages_id == '1') {
		$langL = 'N';
	} else if ($languages_id == '2') {
		$langL = 'F';
	}
	if (is_file(DIR_FS_CATALOG.'/tp/'.$name.$langL.'.html')) {
		$tooltip = '&nbsp;<img src="images/technical/tp.gif" width="12" height="12" rel="get_tooltip.php?file='.$name.$langL.'" class="tooltip" alt="'.$title.'" />&nbsp;';
	} else {
		$tooltip = '';
	}
	return $tooltip;
}
function ShowSliderPrices() {
	global $customer_id;
	if (PRICE_SLIDER_PRICES == "always") {
		return 'true';
	} elseif (PRICE_SLIDER_PRICES == "logged in") {
		if (tep_session_is_registered('customer_id')) {
			return 'true';
		} else {
			return 'false';	
		}
	} elseif (PRICE_SLIDER_PRICES == "never") {
		return 'false';	
	}
}

?>