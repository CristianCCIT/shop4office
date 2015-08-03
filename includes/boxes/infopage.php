<?php
/*
  $Id: whats_new.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
echo '<ul class="right_menu">';
$navid_query = tep_db_query('SELECT id, parent_id, link, custom, name FROM navigatie WHERE link = "i_'.$_GET['page'].'" AND status="1"');
if (tep_db_num_rows($navid_query) > 0) {
	$navid = tep_db_fetch_array($navid_query);
	if ($navid['parent_id'] != '0') {
		$parent_query = tep_db_query('SELECT link, custom, name FROM navigatie WHERE status = "1" AND id = "'.$navid['parent_id'].'"');
		if (tep_db_num_rows($parent_query) > 0) {
			$parent = tep_db_fetch_array($parent_query);
			echo '<li class="rm_title">';
			if (!empty($parent['custom'])) {
				if (!empty($parent['name'])) {
					$name = $parent['name'];
				} else {
					$name = $parent['custom'];
				}
				echo '<a href="'.tep_href_link($parent['custom']).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
			} else {
				$name = $parent['link'];
				if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $parent['link'])) {
					$page_id = explode('_', $parent['link']);
					$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$page_id[1].'" AND language_id = "1"');
					$infopage_name = tep_db_fetch_array($infopage_name_query);
					$name = $infopage_name['infopages_title'];
					echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
				} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $parent['link'])) {
					$page_id = explode('_', $parent['link']);
					$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "1"');
					$categorie_name = tep_db_fetch_array($categorie_name_query);
					$name = $categorie_name['categories_name'];
					echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
				} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $parent['link'])) {
					$page_id = explode('_', $parent['link']);
					$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "1"');
					$product_name = tep_db_fetch_array($product_name_query);
					$name = $categorie_name['products_name'];
					echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
				} else {
					if (!empty($parent['name'])) {
						$name = $parent['name'];
					}
					echo '<a href="'.tep_href_link($parent['link']).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
				}
			}
			echo '</li>';
		}
		$parent_id = $navid['parent_id'];
	} else {
		echo '<li class="rm_title">';
		if (!empty($navid['custom'])) {
			if (!empty($navid['name'])) {
				$name = $navid['name'];
			} else {
				$name = $navid['custom'];
			}
			echo '<a href="'.tep_href_link($navid['custom']).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
		} else {
			$name = $navid['link'];
			if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $navid['link'])) {
				$page_id = explode('_', $navid['link']);
				$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$page_id[1].'" AND language_id = "1"');
				$infopage_name = tep_db_fetch_array($infopage_name_query);
				$name = $infopage_name['infopages_title'];
				echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
			} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $navid['link'])) {
				$page_id = explode('_', $navid['link']);
				$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "1"');
				$categorie_name = tep_db_fetch_array($categorie_name_query);
				$name = $categorie_name['categories_name'];
				echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
			} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $navid['link'])) {
				$page_id = explode('_', $navid['link']);
				$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "1"');
				$product_name = tep_db_fetch_array($product_name_query);
				$name = $categorie_name['products_name'];
				echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
			} else {
				if (!empty($navid['name'])) {
					$name = $navid['name'];
				}
				echo '<a href="'.$navid['link'].'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
			}
		}
		echo '</li>';
		$parent_id = $navid['id'];
	}
	$sp_query = tep_db_query('SELECT link, custom, name, id FROM navigatie WHERE status = "1" AND parent_id = "'.$parent_id.'" ORDER BY sort_order asc');
	while($subpages = tep_db_fetch_array($sp_query)) {
		$class = '';
		echo '<li>';
		if (!empty($subpages['custom'])) {
			if (!empty($subpages['name'])) {
				$name = $subpages['name'];
			} else {
				$name = $subpages['custom'];
			}
			echo '<a href="'.tep_href_link($subpages['custom']).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
		} else {
			$name = $subpages['link'];
			if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $subpages['link'])) {
				$page_id = explode('_', $subpages['link']);
				if ($_GET['page'] == $page_id[1]) {
					$class = ' rm_current';
				}
				$infopage_name_query = tep_db_query('SELECT it.infopages_title, i.type, i.infopages_id FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND i.infopages_id = "'.$page_id[1].'" AND it.language_id = "'.(int)$languages_id.'"');
				$infopage_name = tep_db_fetch_array($infopage_name_query);
				$name = $infopage_name['infopages_title'];
				echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link'.$class.'">'.$name.'</a>';
				$ssp_query = tep_db_query('SELECT link, custom, name FROM navigatie WHERE status = "1" AND parent_id = "'.$subpages['id'].'" ORDER BY sort_order asc');
				if (tep_db_num_rows($ssp_query) > 0 && $_GET['page'] == $page_id[1]) {
					echo '<ul class="csp_list">';
					while ($ssp = tep_db_fetch_array($ssp_query)) {
						echo '<li>';
						if (!empty($ssp['custom'])) {
							if (!empty($ssp['name'])) {
								$name = $ssp['name'];
							} else {
								$name = $ssp['custom'];
							}
							echo '<a href="'.tep_href_link($ssp['custom']).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
						} else {
							$name = $ssp['link'];
							if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $ssp['link'])) {
								$page_id = explode('_', $ssp['link']);
								$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$page_id[1].'" AND language_id = "1"');
								$infopage_name = tep_db_fetch_array($infopage_name_query);
								$name = $infopage_name['infopages_title'];
								echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
							} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $ssp['link'])) {
								$page_id = explode('_', $ssp['link']);
								$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "1"');
								$categorie_name = tep_db_fetch_array($categorie_name_query);
								$name = $categorie_name['categories_name'];
								echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
							} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $ssp['link'])) {
								$page_id = explode('_', $ssp['link']);
								$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "1"');
								$product_name = tep_db_fetch_array($product_name_query);
								$name = $categorie_name['products_name'];
								echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
							} else {
								if (!empty($ssp['name'])) {
									$name = $ssp['name'];
								}
								echo '<a href="'.$ssp['link'].'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
							}
						}
						echo '</li>';
					}
					echo '</ul>';
				} else {
					if ($infopage_name['type'] == 'categories') {
						$csp_query = tep_db_query('SELECT i.infopages_id, it.infopages_title FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND i.parent_id = "'.$infopage_name['infopages_id'].'" AND it.language_id = "'.(int)$languages_id.'" ORDER BY i.sort_order asc');
						if (tep_db_num_rows($csp_query) > 0 && $_GET['page'] == $page_id[1]) {
							echo '<ul class="csp_list">';
							while ($csp = tep_db_fetch_array($csp_query)) {
								echo '<li><a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$csp['infopages_id']).'" title="'.$csp['infopages_title'].' - '.STORE_NAME.'">'.$csp['infopages_title'].'</a></li>';
							}
							echo '</ul>';
						}
					}
				}
			} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $subpages['link'])) {
				$page_id = explode('_', $subpages['link']);
				if ($_GET['page'] == $page_id[1]) {
					$class = ' rm_current';
				}
				$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "1"');
				$categorie_name = tep_db_fetch_array($categorie_name_query);
				$name = $categorie_name['categories_name'];
				echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link'.$class.'">'.$name.'</a>';
			} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $subpages['link'])) {
				$page_id = explode('_', $subpages['link']);
				if ($_GET['page'] == $page_id[1]) {
					$class = ' rm_current';
				}
				$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "1"');
				$product_name = tep_db_fetch_array($product_name_query);
				$name = $categorie_name['products_name'];
				echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link'.$class.'">'.$name.'</a>';
			} else {
				if (!empty($subpages['name'])) {
					$name = $subpages['name'];
				}
				echo '<a href="'.$navid['link'].'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
			}
		}
		echo '</li>';
	}
} else { //not in navigation OR status is inactive => could be if page is in a categorie
	$navid_query = tep_db_query('SELECT id, parent_id, link, custom, name FROM navigatie WHERE link = "i_'.$_GET['page'].'"');
	if (tep_db_num_rows($navid_query) > 0) {
		$navid = tep_db_fetch_array($navid_query);
		if ($navid['parent_id'] != '0') {
			$parent_query = tep_db_query('SELECT link, custom, name FROM navigatie WHERE status = "1" AND id = "'.$navid['parent_id'].'"');
			if (tep_db_num_rows($parent_query) > 0) {
				$parent = tep_db_fetch_array($parent_query);
				echo '<li class="rm_title">';
				if (!empty($parent['custom'])) {
					if (!empty($parent['name'])) {
						$name = $parent['name'];
					} else {
						$name = $parent['custom'];
					}
					echo '<a href="'.tep_href_link($parent['custom']).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
				} else {
					$name = $parent['link'];
					if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $parent['link'])) {
						$page_id = explode('_', $parent['link']);
						$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$page_id[1].'" AND language_id = "1"');
						$infopage_name = tep_db_fetch_array($infopage_name_query);
						$name = $infopage_name['infopages_title'];
						echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
					} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $parent['link'])) {
						$page_id = explode('_', $parent['link']);
						$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "1"');
						$categorie_name = tep_db_fetch_array($categorie_name_query);
						$name = $categorie_name['categories_name'];
						echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
					} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $parent['link'])) {
						$page_id = explode('_', $parent['link']);
						$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "1"');
						$product_name = tep_db_fetch_array($product_name_query);
						$name = $categorie_name['products_name'];
						echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
					} else {
						if (!empty($parent['name'])) {
							$name = $parent['name'];
						}
						echo '<a href="'.tep_href_link($parent['link']).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
					}
				}
				echo '</li>';
			}
			$parent_id = $navid['parent_id'];
		} else {
			echo '<li class="rm_title">';
			if (!empty($navid['custom'])) {
				if (!empty($navid['name'])) {
					$name = $navid['name'];
				} else {
					$name = $navid['custom'];
				}
				echo '<a href="'.tep_href_link($navid['custom']).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
			} else {
				$name = $navid['link'];
				if (preg_match('/^([i]{1})+([_]{1})+([0-9]+$)/i', $navid['link'])) {
					$page_id = explode('_', $navid['link']);
					$infopage_name_query = tep_db_query('SELECT infopages_title FROM infopages_text WHERE infopages_id = "'.$page_id[1].'" AND language_id = "1"');
					$infopage_name = tep_db_fetch_array($infopage_name_query);
					$name = $infopage_name['infopages_title'];
					echo '<a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
				} else if (preg_match('/^([c]{1})+([_]{1})+([0-9]+$)/i', $navid['link'])) {
					$page_id = explode('_', $navid['link']);
					$categorie_name_query = tep_db_query('SELECT categories_name FROM categories_description WHERE categories_id = "'.$page_id[1].'" AND language_id = "1"');
					$categorie_name = tep_db_fetch_array($categorie_name_query);
					$name = $categorie_name['categories_name'];
					echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
				} else if (preg_match('/^([p]{1})+([_]{1})+([0-9]+$)/i', $navid['link'])) {
					$page_id = explode('_', $navid['link']);
					$product_name_query = tep_db_query('SELECT products_name FROM products_description WHERE products_id = "'.$page_id[1].'" AND language_id = "1"');
					$product_name = tep_db_fetch_array($product_name_query);
					$name = $categorie_name['products_name'];
					echo '<a href="'.tep_href_link(FILENAME_DEFAULT, 'products_id='.$page_id[1]).'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
				} else {
					if (!empty($navid['name'])) {
						$name = $navid['name'];
					}
					echo '<a href="'.$navid['link'].'" title="'.$name.' - '.STORE_NAME.'" class="rm_link">'.$name.'</a>';
				}
			}
			echo '</li>';
			$parent_id = $navid['id'];
		}
		$cat_query = tep_db_query('SELECT parent_id FROM infopages WHERE infopages_id = "'.$_GET['page'].'"');
		$cat = tep_db_fetch_array($cat_query);
		$cp_query = tep_db_query('SELECT i.infopages_id, it.infopages_title FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND it.language_id = "'.(int)$languages_id.'" AND i.parent_id = "'.$cat['parent_id'].'" ORDER BY i.sort_order asc');
		if (tep_db_num_rows($cp_query) > 0) {
			while($cp = tep_db_fetch_array($cp_query)) {
				$class = '';
				if ($_GET['page'] == $cp['infopages_id']) {
					$class = ' rm_current';
				}
				echo '<li><a href="'.tep_href_link(FILENAME_INFOPAGE, 'page='.$cp['infopages_id']).'" title="'.$cp['infopages_title'].' - '.STORE_NAME.'" class="rm_link'.$class.'">'.$cp['infopages_title'].'</a></li>';
			}
		}
	}
}
echo '</ul>';
?>