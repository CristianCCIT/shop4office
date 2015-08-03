<?php
Header('Content-type: text/html; charset=UTF-8');
ob_start();
require_once('includes/application_top.php');
require_once('includes/classes/sitemap.php');
$keys_query = tep_db_query('SELECT name, value FROM seo WHERE type="sitemap"');
while ($keys = tep_db_fetch_array($keys_query)) {
	define('SITEMAP_'.strtoupper(str_replace('&euml;', 'e',$keys['name'])), $keys['value']);
}
$google = new GoogleSitemap();
$submit = true;
/****************/
/*	Products	*/
/****************/
$product_sitemap = false;
if (SITEMAP_PRODUCTEN == '1') {
	$product_sitemap = true;
	if ($google->GenerateProductSitemap()){
		echo 'De producten sitemap is succesvol aangemaakt!' . "<br />";
	} else {
		$prods_query = tep_db_query('SELECT products_id FROM products limit 1');
		if (tep_db_num_rows($prods_query) > 0) {
			$submit = false;
			echo 'ERROR: De producten sitemap is niet correct aangemaakt!' . "<br />";						
		} else {
			$product_sitemap = false;
			echo 'OPGELET: De producten sitemap is niet aangemaakt! Er zijn geen producten!' . "<br />";
		}
	}
}
/****************/
/*	Categories	*/
/****************/			
$category_sitemap = false;
if (SITEMAP_CATEGORIEEN == '1') {
	$category_sitemap = true;
	if ($google->GenerateCategorySitemap()){
		echo 'De Categorie&euml;n sitemap is succesvol aangemaakt!' . "<br />";
	} else {
		$cats_query = tep_db_query('SELECT categories_id FROM categories limit 1');
		if (tep_db_num_rows($cats_query) > 0) {
			$submit = false;
			echo 'ERROR: De categorie&euml;n sitemap is niet correct aangemaakt!' . "<br />";						
		} else {
			$category_sitemap = false;
			echo 'OPGELET: De categorie&euml;n sitemap is niet aangemaakt! Er zijn geen categorie&euml;n!' . "<br />";
		}
	}
}
/********************/
/*	Manufacturers	*/
/********************/			
$showManufacturers = false;
if (SITEMAP_FABRIKANTEN == '1') {
	$showManufacturers = true;
	if ($google->GenerateManufacturerSitemap()){
		echo 'De fabrikanten sitemap is succesvol aangemaakt!' . "<br /><br />";
	} else {
		$manufacturers_query = tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS . " limit 1");
		if (tep_db_num_rows($manufacturers_query) > 0) {
			$submit = false;
			echo 'ERROR: De fabrikanten sitemap is niet correct aangemaakt!' . "<br />";
		} else {
			$showManufacturers = false;
			echo 'OPGELET: De fabrikanten sitemap is niet aangemaakt! Er zijn geen fabrikanten!' . "<br />";
		} 
	}
}
/****************/
/*	Specials	*/
/****************/			
$showSpecials = false;
if (SITEMAP_PROMOTIES == '1') {
	$showSpecials = true;
	if ($google->GenerateSpecialsSitemap($languages_id)){
		echo 'De promotie sitemap is succesvol aangemaakt!' . "<br /><br />";
	} else {
		$specials_query = tep_db_query("select products_id from specials WHERE expires_date > NOW() AND status = '1' limit 1");
		if (tep_db_num_rows($specials_query) > 0) {
			$submit = false;
			echo 'ERROR: De promotie sitemap is niet correct aangemaakt!' . "<br />";
		} else {
			$showSpecials = false;
			echo 'OPGELET: De promotie sitemap is niet aangemaakt! Er zijn geen promoties!' . "<br />";
		}
	}
}
/****************/
/*	Infopages	*/
/****************/			
$showInfopages = false;
if (SITEMAP_INFOPAGINA == '1') {
	$showInfopages = true;
	if ($google->GeneratePagesSitemap()){
		echo 'De infopagina sitemap is succesvol aangepast!' . "<br />";
	} else {
		$infopages_query = tep_db_query("select infopages_id from infopages WHERE infopages_status = '1' limit 1");
		if (tep_db_num_rows($infopages_query) > 0) {
			$submit = false;
			echo 'ERROR: De infopagina sitemap is niet correct aangemaakt!' . "<br />";
		} else {
			$showInfopages = false;
			echo 'OPGELET: De infopagina sitemap is niet aangemaakt! Er zijn geen infopagina\'s!' . "<br />";
		}
	}
}
/************/
/*	Index	*/
/************/
if ($google->GenerateSitemapIndex($showInfopages, $showManufacturers, $showSpecials, $category_sitemap, $product_sitemap)){
	echo 'De index sitemap is succesvol aangemaakt!' . "<br />";
} else {
	$submit = false;
}
/************/
/*	Output	*/
/************/		
if ($submit) {
	echo "<br />".'Alle sitemaps zijn correct aangemaakt!' . "<br /><br />";
	echo 'Dit is de index sitemap: <a href="'.$google->base_url.'sitemapindex.xml">'.$google->base_url.'sitemapindex.xml</a><br />';
	if ($product_sitemap)
		echo 'Dit is de producten sitemap: <a href="'.$google->base_url.'sitemapproducts.xml">'.$google->base_url.'sitemapproducts.xml</a> ('.$google->debug['QUERY']['PRODUCTS']['NUM_ROWS'].')<br />';
	if ($category_sitemap)
		echo 'Dit is de categorie&euml;n sitemap: <a href="'.$google->base_url.'sitemapcategories.xml">'.$google->base_url.'sitemapcategories.xml</a> ('.$google->debug['QUERY']['CATEGORY']['NUM_ROWS'].')<br />';
	if ($showManufacturers)
		echo 'Dit is de fabrikanten sitemap: <a href="' . $google->base_url . 'sitemapmanufacturers.xml">' . $google->base_url . 'sitemapmanufacturers.xml</a> ('.$google->debug['QUERY']['MANUFACTURERS']['NUM_ROWS'].')<br />';
	if ($showSpecials)
		echo 'Dit is de promotie sitemap: <a href="' . $google->base_url . 'sitemapspecials.xml">' . $google->base_url . 'sitemapspecials.xml</a> ('.$google->debug['QUERY']['SPECIALS']['NUM_ROWS'].')<br />';
	if ($showInfopages)
		echo 'Dit is de infopagina sitemap: <a href="' . $google->base_url . 'sitemappages.xml">' . $google->base_url . 'sitemappages.xml</a> ('.$google->debug['QUERY']['PAGES']['NUM_ROWS'].')<br />';
} else {
	echo '<pre>';
	print_r($google->debug);
	echo '</pre>';
}
echo iconv('ISO-8859-1', 'UTF-8', ob_get_clean());
?>