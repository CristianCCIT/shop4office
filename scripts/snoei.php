<?php

require('includes/application_top.php');

set_time_limit(0);

// OPRUIMEN LEGE CATEGORIEËN EN SUBCATEGORIEËN

$categories_id = 0;

$categories = tep_get_category_tree($categories_id, '', '0', '', true);

// 'snoeit' tot 4 subcategorieën diep
for ($t=0; $t<=4; $t++)   {
	for ($i=0, $n=sizeof($categories); $i<$n; $i++)
	{
		if (tep_childs_in_category_count($categories[$i]['id']) == 0)
		{
			if (tep_products_in_category_count($categories[$i]['id'], $include_deactivated = true) == 0)
			{
			tep_remove_category($categories[$i]['id']);
			}
		}
	} 
}

// OPRUIMEN LEGE MERKEN

$manuf_query = tep_db_query("SELECT manufacturers_id FROM " . TABLE_MANUFACTURERS);
$row = mysql_fetch_array($manuf_query);

while ($row)
{
	$manuf_id[] = $row[0];
	$row = mysql_fetch_array($manuf_query);
}

sort($manuf_id);	// array met alle manufacturers-id's
$aantal = count($manuf_id);

$prod_manufs_query = tep_db_query("SELECT DISTINCT manufacturers_id FROM " . TABLE_PRODUCTS);
$row2 = mysql_fetch_array($prod_manufs_query);

while ($row2)
{
	$prod_manufs[] = $row2[0];
	$row2 = mysql_fetch_array($prod_manufs_query);
}

sort($prod_manufs);		// array met alle gebruikte manufacturers-id's (uit tabel 'products')
$aantal2 = count($prod_manufs);

$vacant = (array_diff($manuf_id, $prod_manufs));	// verschil tussen beide arrays = niet-gebruikte manufacturers

foreach($vacant as $waarde)
{
  snoei($waarde);	// ga array met niet-gebruikte af en verwijder ze via functie 'snoei'
}

function snoei($merk_id)
{
	tep_db_query("DELETE FROM " . TABLE_MANUFACTURERS . " where manufacturers_id = " . $merk_id);
	if( mysql_num_rows( mysql_query("SHOW TABLES LIKE 'seo_urls'"))) {
		mysql_query("DELETE FROM `seo_urls` WHERE manufacturers_id = '".$merk_id."'");
	}
}
$products_query = tep_db_query('SELECT products_id FROM products');
while ($products = tep_db_fetch_array($products_query)) {
	$descr_query = tep_db_query('SELECT products_id FROM products_description WHERE products_id = "'.$products['products_id'].'"');
	if (tep_db_num_rows($descr_query) < 1) {
		tep_db_query('DELETE FROM products WHERE products_id = "'.$products['products_id'].'"');
		tep_db_query('DELETE FROM products_to_categories WHERE products_id = "'.$products['products_id'].'"');
	}
}

/*sitemap update*/
$seo_extension = str_replace('/admin', '/administrator', DIR_FS_ADMIN);
chdir($seo_extension);
if (file_exists('extensions/seo/sitemap.class.php')) {
	require_once('extensions/seo/sitemap.class.php');
	$keys_query = tep_db_query('SELECT name, value FROM seo WHERE type="sitemap"');
	while ($keys = tep_db_fetch_array($keys_query)) {
		define('SITEMAP_'.strtoupper(str_replace('&euml;', 'e',$keys['name'])), $keys['value']);
	}
	
	function createGoogleSitemap() {
		$google = new GoogleSitemap();
		$submit = true;
	
		$product_sitemap = false;
		if (SITEMAP_PRODUCTEN == '1') {
			$product_sitemap = true;
			if ($google->GenerateProductSitemap()){
				//echo 'De producten sitemap is succesvol aangemaakt!' . "<br />";
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
		
		$category_sitemap = false;
		if (SITEMAP_CATEGORIEEN == '1') {
			$category_sitemap = true;
			if ($google->GenerateCategorySitemap()){
				//echo 'De Categorie&euml;n sitemap is succesvol aangemaakt!' . "<br />";
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
		
		$showManufacturers = false;
		if (SITEMAP_FABRIKANTEN == '1') {
			$showManufacturers = true;
			if ($google->GenerateManufacturerSitemap()){
				//echo 'De fabrikanten sitemap is succesvol aangemaakt!' . "<br />";
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
		
		$showSpecials = false;
		if (SITEMAP_PROMOTIES == '1') {
			$showSpecials = true;
			if ($google->GenerateSpecialsSitemap($languages_id)){
				//echo 'De promotie sitemap is succesvol aangemaakt!' . "<br />";
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
		
		$showInfopages = false;
		if (SITEMAP_INFOPAGINA == '1') {
			$showInfopages = true;
			if ($google->GeneratePagesSitemap()){
				//echo 'De infopagina sitemap is succesvol aangepast!' . "<br />";
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

		if ($google->GenerateSitemapIndex($showInfopages, $showManufacturers, $showSpecials, $category_sitemap, $product_sitemap)){
			//echo 'De index sitemap is succesvol aangemaakt!' . "<br />";
		} else {
			$submit = false;
		}
		
		if ($submit) {
			//echo "<br />".'Alle sitemaps zijn correct aangemaakt!' . "<br /><br />";
			/*echo 'Dit is de index sitemap: ' . $google->base_url . 'sitemapindex.xml' . "<br />";
			if ($product_sitemap)
				echo 'Dit is de producten sitemap: ' . $google->base_url . 'sitemapproducts.xml' . "<br />";
			if ($category_sitemap)
				echo 'Dit is de categorie&euml;n sitemap: ' . $google->base_url . 'sitemapcategories.xml' . "<br />";
			if ($showManufacturers)
				echo 'Dit is de fabrikanten sitemap: ' . $google->base_url . 'sitemapmanufacturers.xml' . "<br />";
			if ($showSpecials)
				echo 'Dit is de promotie sitemap: ' . $google->base_url . 'sitemapspecials.xml' . "<br />";
			if ($showInfopages)
				echo 'Dit is de infopagina sitemap: ' . $google->base_url . 'sitemappages.xml' . "<br />";
			*/
		} else {
			echo '<pre>';
			print_r($google->debug);
			echo '</pre>';
		}
	}
	echo createGoogleSitemap();
} else {
	echo 'Geen sitemaps gevonden';	
}
//productfeed
if (PRODUCT_FEED_BESLIST=='true') {
    tep_export('beslist');
}
if (PRODUCT_FEED_KIESKEURIG=='true') {
    tep_export('kieskeurig');
}
//leegmaken cache
$dir = DIR_FS_CATALOG.DIR_FS_CACHE;//cache dir
if (is_dir($dir)) {
	reset_cache_dir($dir);
}
/********************************/
/*	Optimize database tables	*/
/********************************/
$tables_query = tep_db_query('SHOW TABLES FROM '.DB_DATABASE);
while ($table = tep_db_fetch_row($tables_query)) {
	tep_db_query('OPTIMIZE TABLE '.$table[0]);
}

/*sitemap update*/
tep_db_query('DELETE FROM `productspecs` WHERE `products_model` NOT IN (SELECT products_model FROM products)');
tep_db_query('DELETE FROM `specifications` where (hoofdkenmerk,subkenmerk) not in (SELECT hoofdkenmerk, subkenmerk from productspecs)');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>