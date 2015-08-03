<?php
// input misspelled word
$right_input = $_GET['keywords'];
 
// array of words to check against
$right_words  = array('web','website','webshop','e-commerce','synchronisatie','business','seo','zoekmachineoptmalisatie','google','internet','mobiel','pagina','site','cms','uitbreiding','webdesign','kenmerken','klant','prijs','prijzen','contract','statisch','dynamisch','huisstijl','design','voorwaarden','diensten','dynamiek','dynamic','newsletter','nieuwsbrief','mailing','administrator','martketing','professioneel','geavanceerd','infopagina','WYSIWYG','lay-out','url','url-friendly','meta','sleutelwoorden','nieuws','categoriën','flash','afbeelding','navigatie','sitemap','galerij','analytics','html','javascript','css','stylesheet','add-on','google maps','google analytics','fotogalerij','forum','rss feed','rss','zoekfunctie','uitgebreid zoeken','enquête','breadcrumb','tagcloud','zoekrobot','keyword','productsynchro','adminsynchro','synchro','flexibel','aanbiedingen','notificatie','fabrikant','betaling','vergelijk','ogone','rembours','catalogus','zoekmachine','PayPal','thumbnail','btw','cross-selling','selectie','filter','klantgroep','betaaldienst','verzendbedrijf','verzendbedrijven','verzendnota','facturatie','content','content management','content management system','integratie','magento','oscommerce','joomla','wordpress');
$product_models_query = tep_db_query("SELECT DISTINCT products_model FROM products WHERE products_status = '1'");
if (tep_db_num_rows($product_models_query) > 0) {
	while ($products_models = tep_db_fetch_array($product_models_query)) {
		$right_words[] .= $products_model['products_model'];
	}
}
// no shortest distance found, yet
$right_shortest = -1;
 
// loop through words to find the closest
foreach ($right_words as $right_word) {
 
	// calculate the distance between the input word,
	// and the current word
	$right_lev = levenshtein($right_input, $right_word);
 
	// check for an exact match
	if ($right_lev == 0) {
 
		// closest word is this one (exact match)
		$right_closest = $right_word;
		$right_shortest = 0;
 
		// break out of the loop; we've found an exact match
		break;
	}
 
	// if this distance is less than the next found shortest
	// distance, OR if a next shortest word has not yet been found
	if ($right_lev <= $right_shortest || $right_shortest < 0) {
		// set the closest match, and shortest distance
		$right_closest  = $right_word;
		$right_shortest = $right_lev;
	}
}
 
if ($right_shortest == 0) {
	$right_text .= Translate('Extact resultaat gevonden').": <strong>$right_closest</strong>\n";
} else {
	$right_text .= Translate('Misschien bedoelt u').": <a href=".tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords='.$right_closest).">".$right_closest."</a>?\n";
}
?>
<div class="box_title">
    <?php echo Translate('Zoekterm').": $right_input\n"; ?>
</div>
<div class="box_content">
    <?php echo $right_text; ?>
</div>
