<?php
class osC_CategoryTree {
var $root_category_id = 0;

function osC_CategoryTree($catID = 0, $load_from_database = true) {
$this->root_category_id = $catID;
} //end class osC_CategoryTree
function buildCategories($parent_id) {
	global $languages_id;
	$result = '';
	$categories_query = tep_db_query("SELECT c.categories_id, cd.categories_name, c.parent_id FROM categories c, categories_description cd WHERE c.categories_id = cd.categories_id AND cd.language_id = '".(int)$languages_id."' AND c.parent_id = '".$parent_id."' ORDER BY c.sort_order, cd.categories_name");
	$colcount = tep_db_num_rows($categories_query);
	if ($colcount > 0) {
		if ($colcount > 4) {
			$colcount = 4;
		}
		$result .= '<ul id="products_sitemap" class="col'.$colcount.'">';
		$count = 0;
		while ($categories = tep_db_fetch_array($categories_query)) {
			if ($count == 0) {
				$result .= '<li class="first">';
			} else {
				$result .= '<li>';
			}
			$result .= '<a href="'.tep_href_link(FILENAME_DEFAULT, 'cPath='.$categories['categories_id']).'" title="'.$categories['categories_name'].' - '.STORE_NAME.'" class="sitemap">'.$categories['categories_name'].'</a>';
			if (tep_has_category_subcategories($categories['categories_id'])) {
				$result .= $this->buildCategories($categories['categories_id']);
			} else if (tep_count_products_in_category($categories['categories_id']) > 0) {
				//$result .= $this->buildProducts($categories['categories_id']);
			}
			$result .= '</li>';
			$count++;
		}
		$result .= '</ul>';
	}
	return $result;
}
function buildProducts($categories_id) {
	global $languages_id;
	$products = '';
	$products_query= tep_db_query("SELECt * FROM products p, products_to_categories p2c , products_description pd WHERE p.products_id = pd.products_id AND p.products_id = p2c.products_id AND p.products_status = '1' AND p2c.categories_id=".$categories_id." AND pd.language_id=".(int)$languages_id." ORDER BY pd.products_name");
	if (tep_db_num_rows($products_query) > 0) {
		$products .= '<ul>';
		while ($product = tep_db_fetch_array($products_query)) {
			$products .= '<li><a href="'.tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product['products_id']).'" title="'.$product['products_name'].' - '.STORE_NAME.'">'.$product['products_name'].'</a></li>';
		}
		$products .= '</ul>';
	}
	return $products;
}
function buildTree() { return $this->buildCategories($this->root_category_id);}}
?>