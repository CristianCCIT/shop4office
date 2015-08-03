<?php
function tep_count_navigation_children($nav_id)
{
	$navigatie_count = 0;
	$navigatie_query = tep_db_query("select id from navigatie where parent_id = '" . (int)$nav_id . "'");
	while ($navigatie = tep_db_fetch_array($navigatie_query)) {
		$navigatie_count++;
	}
	return $navigatie_count;
}

function getParentList($parent_id, $level, $parent_list = '')
{
	global $languages_id;
	$nav_item_query = tep_db_query('SELECT id, link, custom, sort_order, status, name FROM navigatie WHERE parent_id = "' . $parent_id . '" AND status = "1" ORDER BY sort_order asc');
	$level_count = tep_db_num_rows($nav_item_query);
	$i = 1;
	if ($level > 1) {
		$this_class_level = $level - 1;
		$parent_list .= '<ul class="level_' . $this_class_level . '">';
	}
	while ($nav_item = tep_db_fetch_array($nav_item_query)) {
		$level_class = '';
		$count_columns = tep_count_navigation_children($nav_item['id']);
		$parent_list .= '<li>';
		if (!empty($nav_item['custom'])) {
			if (!empty($nav_item['name'])) {
				$name = $nav_item['name'];
			} else {
				$name = $nav_item['custom'];
			}
			$parent_list .= '<a href="' . tep_href_link($nav_item['custom']) . '" title="' . $name . ' - ' . STORE_NAME . '" class="' . $level_class_count . '">' . $name . '</a>';
		} else {
			$name = $nav_item['link'];
			if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $nav_item['link'])) {
				$page_id = explode('_', $nav_item['link']);
				$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "' . $page_id[1] . '" AND language_id = "' . (int)$languages_id . '"');
				$infopage_name = tep_db_fetch_array($infopage_name_query);
				/*language fallback*/
				if ((LANGUAGE_FALLBACK == 'true') && ($infopage_name['infopages_title'] == '')) {
					$language_fallback_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "' . $page_id[1] . '" AND language_id = "1"');
					$language_fallback = tep_db_fetch_array($language_fallback_query);
					$infopage_name['infopages_title'] = $language_fallback['infopages_title'];
				}
				/*language fallback*/
				$name = $infopage_name['infopages_title'];
				$parent_list .= '<a href="' . tep_href_link(FILENAME_INFOPAGE,
						'page=' . $page_id[1]) . '" title="' . $name . ' - ' . STORE_NAME . '" class="' . $level_class_count . '">' . $name . '</a>';
			} else {
				if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $nav_item['link'])) {
					$page_id = explode('_', $nav_item['link']);
					$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "' . $page_id[1] . '" AND language_id = "' . (int)$languages_id . '"');
					$categorie_name = tep_db_fetch_array($categorie_name_query);
					/*language fallback*/
					if ((LANGUAGE_FALLBACK == 'true') && ($categorie_name['categories_name'] == '')) {
						$language_fallback_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "' . $page_id[1] . '" AND language_id = "1"');
						$language_fallback = tep_db_fetch_array($language_fallback_query);
						$categorie_name['categories_name'] = $language_fallback['categories_name'];
					}
					/*language fallback*/
					$name = $categorie_name['categories_name'];
					$parent_list .= '<a href="' . tep_href_link(FILENAME_DEFAULT,
							'cPath=' . $page_id[1]) . '" title="' . $name . ' - ' . STORE_NAME . '" class="' . $level_class_count . '">' . $name . '</a>';
				} else {
					if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $nav_item['link'])) {
						$page_id = explode('_', $nav_item['link']);
						$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "' . $page_id[1] . '" AND language_id = "' . (int)$languages_id . '"');
						$product_name = tep_db_fetch_array($product_name_query);
						/*language fallback*/
						if ((LANGUAGE_FALLBACK == 'true') && ($product_name['products_name'] == '')) {
							$language_fallback_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "' . $page_id[1] . '" AND language_id = "1"');
							$language_fallback = tep_db_fetch_array($language_fallback_query);
							$product_name['products_name'] = $language_fallback['products_name'];
						}
						/*language fallback*/
						$name = $product_name['products_name'];
						$parent_list .= '<a href="' . tep_href_link(FILENAME_DEFAULT,
								'products_id=' . $page_id[1]) . '" title="' . $name . ' - ' . STORE_NAME . '" class="' . $level_class_count . '">' . $name . '</a>';
					} else {
						if (!empty($nav_item['name'])) {
							$name = $nav_item['name'];
						}
						$parent_list .= '<a href="' . tep_href_link($nav_item['link']) . '" title="' . $name . ' - ' . STORE_NAME . '" class="' . $level_class_count . '">' . $name . '</a>';
					}
				}
			}
		}
		if (tep_count_navigation_children($nav_item['id']) > 0) {
			$new_level = $level + 1;
			$parent_list .= getParentList($nav_item['id'], $new_level);
		}
		$i++;
	}
	if ($level > 1) {
		$parent_list .= '</ul>';
	}
	return $parent_list;
}

function abo_get_navigation($parent_id = 0, $level = 0, $return = false)
{
	global $languages_id;

	//Start output var.
	$output = "";
	if ($return) {
		$output = array();
	}

	// Check classes that should be assigned
	$class = ($level == 0 ? "navigation-item" : "navigation-subitem");

	$query = "SELECT * FROM navigatie WHERE parent_id = " . $parent_id . " AND status = '1' ORDER BY sort_order ASC";
	$resource = tep_db_query($query);

	while ($nav_element = tep_db_fetch_array($resource)) {

		// Basic vars for each iteration.
		$infopage = false;
		$categorie = false;
		$product = false;

		// Start the list-item
		if (!$return) {
			$output .= "<li class='" . $class . "'>";
		}

		// Always try to use the custom name tag.
		if (isset ($nav_element['name']) && $nav_element['name'] != '') {
			$name = $nav_element['name'];
		} elseif (isset($nav_element['custom']) && $nav_element['custom'] != '') {
			$name = $nav_element['custom'];
		} elseif (isset($nav_element['link']) && $nav_element['link'] != '') {

			if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $nav_element['link'])) {
				// This nav-item is an infopage.
				$infopage = true;

				$temp = explode('_', $nav_element['link']);
				$page_id = $temp[1];

				$infopages_query = "SELECT * FROM infopages_text WHERE infopages_id = " . $page_id . " AND language_id = " . $languages_id;
				$infopages_resource = tep_db_query($infopages_query);
				$infopages_result = tep_db_fetch_array($infopages_resource);

				if (isset($infopages_result['infopages_title']) && $infopages_result['infopages_title'] != '') {
					$name = $infopages_result['infopages_title'];
				} elseif (LANGUAGE_FALLBACK == 'true') {
					// Language Fallback.
					$infopages_query = "SELECT * FROM infopages_text WHERE infopages_id = " . $page_id . " AND language_id = 1";
					$infopages_resource = tep_db_query($infopages_query);
					$infopages_result = tep_db_fetch_array($infopages_resource);

					if (isset($infopages_result['infopages_title']) && $infopages_result['infopages_title'] != '') {
						$name = $infopages_result['infopages_title'];
					} else {
						$name = $nav_element['link'];
					}

				} else {
					$name = $nav_element['link'];
				}

			} elseif (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $nav_element['link'])) {
				$categorie = true;

				$temp = explode('_', $nav_element['link']);
				$cat_id = $temp[1];

				$categorie_query = "SELECT * FROM categories_description WHERE categories_id = " . $cat_id . " AND language_id = " . $languages_id;
				$categorie_resource = tep_db_query($categorie_query);
				$categorie_result = tep_db_fetch_array($categorie_resource);

				if (isset($categorie_result['categories_name']) && $categorie_result['categories_name'] != '') {
					$name = $categorie_result['categories_name'];
				} elseif (LANGUAGE_FALLBACK == 'true') {
					// Language Fallback.
					$categorie_query = "SELECT * FROM infopages_text WHERE infopages_id = " . $cat_id . " AND language_id = 1";
					$categorie_resource = tep_db_query($categorie_query);
					$categorie_result = tep_db_fetch_array($categorie_resource);

					if (isset($categorie_result['categories_name']) && $categorie_result['categories_name'] != '') {
						$name = $categorie_result['categories_name'];
					} else {
						$name = $nav_element['link'];
					}

				} else {
					$name = $nav_element['link'];
				}
			} elseif (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $nav_element['link'])) {
				$product = true;

				$temp = explode('_', $nav_element['link']);
				$prod_id = $temp[1];

				$product_query = "SELECT * FROM products_description WHERE products_id = " . $prod_id . " AND language_id = " . $languages_id;
				$product_resource = tep_db_query($product_query);
				$product_result = tep_db_fetch_array($product_resource);

				if (isset($product_result['products_name']) && $product_result['products_name'] != '') {
					$name = $product_result['products_name'];
				} elseif (LANGUAGE_FALLBACK == 'true') {
					// Language Fallback.
					$product_query = "SELECT * FROM infopages_text WHERE products_id = " . $prod_id . " AND language_id = 1";
					$product_resource = tep_db_query($product_query);
					$product_result = tep_db_fetch_array($product_resource);

					if (isset($product_result['products_name']) && $product_result['products_name'] != '') {
						$name = $product_result['products_name'];
					} else {
						$name = $nav_element['link'];
					}
				} else {
					$name = $nav_element['link'];
				}
			}
		}

		// Now set the link

		if (isset($nav_element['custom']) && $nav_element['custom'] != '') {
			$link = tep_href_link($nav_element['custom']);
		} else {
			if ($infopage) {
				$link = tep_href_link(FILENAME_INFOPAGE, 'page=' . $page_id);
			} elseif ($categorie) {
				$link = tep_href_link(FILENAME_DEFAULT, 'cPath=' . $cat_id);
			} elseif ($product) {
				$link = tep_href_link(FILENAME_DEFAULT, 'products_id=' . $prod_id);
			} else {
				$link = tep_href_link($nav_element['link']);
			}
		}

		// Set the title tag.
		$title = STORE_NAME . ' - ' . $name;

		if ($return) {
			$output[] = array(
				'link' => $link,
				'title' => $title,
				'name' => $name,
				'kids' => ((abo_has_subnavigation($nav_element['id'])) ? abo_get_navigation($nav_element['id'],
					($level + 1), $return) : null)
			);
		} else {
			$output .= "<a href='" . $link . "' title='" . $title . "'>" . $name . "</a>";

			if (abo_has_subnavigation($nav_element['id'])) {
				$output .= '<ul class="' . $class . ' level_' . ($level + 1) . '">';
				$output .= abo_get_navigation($nav_element['id'], ($level + 1), $return);
				$output .= '</ul>';
			}

			$output .= "</li>";
		}
	}

	return $output;
}

function abo_has_subnavigation($parent_id)
{
	$query = "SELECT * FROM navigatie WHERE parent_id = " . $parent_id . " AND status = 1";
	$resource = tep_db_query($query);

	if (tep_db_num_rows($resource) > 0) {
		return true;
	} else {
		return false;
	}
}

function getCategoryList($parent_id, $level)
{
	global $category_list, $languages_id;
	$cat_query = tep_db_query('SELECT DISTINCT c.categories_id, c.sort_order, cd.categories_name FROM categories c, categories_description cd WHERE c.categories_id = cd.categories_id AND c.parent_id = "' . $parent_id . '" AND cd.language_id = "' . (int)$languages_id . '"ORDER BY c.sort_order asc, cd.categories_name asc');
	while ($categories = tep_db_fetch_array($cat_query)) {
		/*language fallback*/
		if ((LANGUAGE_FALLBACK == 'true') && ($categories['categories_name'] == '')) {
			$language_fallback_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "' . $categories['categories_id'] . '" AND language_id = "1"');
			$language_fallback = tep_db_fetch_array($language_fallback_query);
			$categories['categories_name'] = $language_fallback['categories_name'];
		}
		/*language fallback*/
		$category_list .= '<li><a href="' . tep_href_link(FILENAME_DEFAULT,
				'cPath=' . $categories['categories_id']) . '" title="' . $categories['categories_name'] . ' - ' . STORE_NAME . '">' . $categories['categories_name'] . '</a>';
		if (tep_has_category_subcategories($categories['categories_id'])) {
			$category_list .= '<ul class="level_' . $level . '">';
			$new_level = $level + 1;
			getCategoryList($categories['categories_id'], $new_level);
			$category_list .= '</ul>';
		}
		$category_list .= '</li>';
	}
	return $category_list;
}

function getStyledCategoryList($parent_id = 0, $level = 0, $return = false)
{
	global $languages_id;

	$output = '';
	if ($return) {
		$output = array();
	}

	$query = "SELECT c.categories_id, c.sort_order, cd.categories_name FROM categories c, categories_description cd WHERE c.categories_id = cd.categories_id AND c.parent_id = '" . $parent_id . "' AND cd.language_id = '" . (int)$languages_id . "' ORDER BY c.sort_order ASC";
	$resource = tep_db_query($query);

	while ($category = tep_db_fetch_array($resource)) {

		if ($category['categories_name'] == '' || $category['categories_name'] == ' ') {
			// Language fallback.
			$fallbackQuery = "SELECT categories_name FROM categories_description WHERE categories_id = " . $category['categories_id'] . " AND language_id = 1";
			$fallbackResource = tep_db_query($fallbackQuery);;
			$fallback = tep_db_fetch_array($fallbackResource);

			$category['categories_name'] = $fallback['categories_name'];
		}

		// Make a difference between top-level category and sub-category.
		$class = ($level == 0 ? "category" : "sub-category level_" . $level);

		if ($return) {
			$output[] = array(
				'link' => tep_href_link(FILENAME_DEFAULT, 'cPath=' . $category['categories_id']),
				'title' => $category['categories_name'] . ' - ' . STORE_NAME,
				'name' => $category['categories_name'],
				'kids' => ((tep_has_category_subcategories($category['categories_id'])) ? getStyledCategoryList($category['categories_id'],
					$level + 1, true) : null)
			);
		} else {
			$output .= '<li class="' . $class . '">';
			$output .= '<a class="category_title" href="' . tep_href_link(FILENAME_DEFAULT,
					'cPath=' . $category['categories_id']) . '" title="' . $category['categories_name'] . ' - ' . STORE_NAME . '">';
			$output .= $category['categories_name'];
			$output .= '</a>';

			if (tep_has_category_subcategories($category['categories_id'])) {

				$catlevel = $level + 1;

				$class = ($level == 0 ? "category level_" . $catlevel : "sub-category level_" . $catlevel);
				$output .= '<ul class="' . $class . '">';
				$output .= getStyledCategoryList($category['categories_id'], $catlevel);
				$output .= '</ul>';
			}

			$output .= '</li>';
		}
	}
	return $output;
}


function tep_get_full_navigatie($path)
{
	$parent_navigatie_query = tep_db_query("select parent_id from navigatie where id = '" . (int)$path . "'");
	while ($parent_navigatie = tep_db_fetch_array($parent_navigatie_query)) {
		if (($parent_navigatie['parent_id'] != $path) && ($parent_navigatie['parent_id'] != 0)) {
			$path = tep_get_full_navigatie($parent_navigatie['parent_id']) . '_' . $path;
		}
	}
	return $path;
}

?>