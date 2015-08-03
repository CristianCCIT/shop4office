<?php
setlocale(LC_TIME, 'nl_NL.ISO_8859-1');
define('DATE_FORMAT_SHORT', '%d/%m/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A %d %B, %Y'); // this is used for strftime()
define('DATE_FORMAT', 'd/m/Y'); // this is used for date()
define('PHP_DATE_TIME_FORMAT', 'd/m/Y H:i:s'); // this is used for date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');

function tep_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
  }
}

define('HTML_PARAMS','dir="ltr" lang="nl"');
define('CHARSET', 'iso-8859-1');
define('TEXT_DUTCH_SELECT', 'Klik hier om in het Nederlands verder te gaan.');
define('TEXT_FRENCH_SELECT', 'Cliquez ici pour continuer en Franais.');
// HOOFDMENU
define('BOX_HEADING_MAIN', 'Hoofdpagina');
define('BOX_ITEM_SECTIONS', 'Merken en groepen');
define('BOX_ITEM_NEWS', 'Nieuwsitems');
define('BOX_ITEM_ALG_NEWS', 'Algemene info');
define('BOX_ITEM_SPECIALS', 'Speciale aanbiedingen');
//HOOFDMENU
// PRODUCTEN
define('TABLE_HEADING_SECTIONS', 'Selecteer fabrikanten en categorien.');
define('IMAGE_SAVE', 'Wijzigingen opslaan');
// PRODUCTEN
//NIEUWSITEMS
define('NEWSITEMS_HEADING_TITLE', 'Nieuwsitems');
define('HEADING_NEWSITEMS_EDIT', 'Nieuwsitem wijzigen');

define('TABLE_HEADING_NEWSITEMS', 'Nieuwsitem');
define('TEXT_NEWSITEM_DATE_ADDED', 'Toegevoegd');
define('TEXT_NEWSLETTER_DATE_START', 'Startdatum');
define('TEXT_NEWSLETTER_DATE_EXPIRES', 'Vervaldatum');
define('ACTION_NEWSITEMS', 'Acties');
define('TEXT_NEWSITEMS_TITLE', 'Nieuwsitem Titel:');
define('TEXT_NEWSITEMS_CONTENT', 'Inhoud:');
define('TEXT_CONFIRM_DELETE_NEWS', 'Verwijder nieuwsitem: %s?');
define('TEXT_DISPLAY_NUMBER_OF_NEWSITEMS', 'Toon <b>%d</b> tot <b>%d</b> (van <b>%d</b> nieuwsitems)');
define('TEXT_RESULT_PAGE', 'Pagina %s van %d');
define('EDIT_NEWSITEMS', 'Wijzig dit nieuwsitem');
define('REMOVE_NEWSITEMS', 'Verwijder dit nieuwsitem');
define('IMAGE_NEW_NEWSITEMS', 'Nieuwsitem toevoegen');
define('HEADING_NEWSLETTER_EDIT', 'Nieuwsitem wijzigen');
define('TEXT_NEWSLETTER_TITLE', 'Titel nieuwsitem');
define('TEXT_NEWSLETTER_CONTENT', 'Volledige tekst');
define('TEXT_NEWSLETTER_PREVIEW', 'Inleidende tekst');
define('IMAGE_CANCEL', 'Wijzigingen annuleren');
define('IMAGE_DELETE', 'Verwijderen');

define('TEXT_NEWSITEMS_DATE_ADDED', 'Datum toegevoegd');
define('TEXT_NEWSITEMS_DATE_START', 'Startdatum');
define('TEXT_NEWSITEMS_DATE_EXPIRES', 'Vervaldatum');
//NIEUWSITEMS

//Promotion
define('IMAGE_NEW_PROMOTION', 'Promotie Toevoegen');

define('PROMOTIONS_HEADING_TITLE', 'Promotie acties');
define('TABLE_HEADING_PROMOTION', 'Promotie actie');
define('TEXT_PROMOTIONITEMS_DATE_ADDED', 'Toegevoegd');
define('TEXT_PROMOTIONITEMS_DATE_START', 'Startdatum');
define('TEXT_PROMOTIONITEMS_DATE_EXPIRES', 'Vervaldatum');
define('ACTION_PROMOTION', 'Acties');

define('HEADING_PROMOTION_EDIT', 'Wijzig promotie actie');
define('TEXT_PROMOTION_TITLE', 'Promotie actie Titel:');
define('TEXT_PROMOTION_DATE_ADDED', 'Toegevoegd');
define('TEXT_PROMOTION_DATE_START', 'Startdatum');
define('TEXT_PROMOTION_DATE_EXPIRES', 'Vervaldatum');
define('TEXT_PROMOTION_CONTENT', 'Volledige tekst');

//Promotion

//ALGEMENE INFO
define('ALGINFO_HEADING_TITLE', 'Algemene Info');
define('SEO_HEADING_TITLE', 'Zoekmachine Optimalisatie');
define('EXTRA_CONFIG_HEADING_TITLE', 'Extra configuratie');
define('TITLE_ALGINFO', 'Algemene info');
define('TITLE_SEO', 'Eigenschap');
define('ACTION_ALGINFO', 'Acties');
define('ACTION_SEO', 'Acties');
define('EDIT_ALGINFO', 'Wijzig dit item');
define('EDIT_SEO', 'Wijzig dit item');

define('HEADING_ALGINFO_EDIT', 'Algemene info wijzigen');
define('HEADING_SEO_EDIT', 'Zoekmachine optimalisatie instellingen wijzigen');
define('HEADING_EXTRA_CONFIG_EDIT', 'Extra configuratie instellingen wijzigen');
define('TEXT_ALGINFO_TITLE', 'Titel algemene info');
define('TEXT_SEO_TITLE', 'Eigenschap');
define('TEXT_ALGINFO_CONTENT', 'Tekst algemene info');
define('TEXT_SEO_CONTENT', 'Inhoud');
define('IMAGE_CANCEL', 'Wijzigingen annuleren');
//ALGEMENE INFO
//SPECIALS

define('TABLE_HEADING_PRODUCTS', 'Producten');
define('TABLE_HEADING_PRODUCTS_PRICE', 'Prijs');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Actie');

define('TEXT_SPECIALS_PRODUCT', 'Product:');
define('TEXT_SPECIALS_SPECIAL_PRICE', 'Speciale Prijs:');
define('TEXT_SPECIALS_EXPIRES_DATE', 'Vervaldatum:');
define('TEXT_SPECIALS_PRICE_TIP', '<b>Help:</b><ul><li>Als je een nieuwe prijs invult moet het decimaalteken een \'.\' (decimale punt) zijn, bijv.: <b>49.99</b></li><li>Laat het Vervaldatum veld leeg om geen vervaldatum in te geven</li></ul>');

define('TEXT_INFO_DATE_ADDED', 'Datum Toegevoegd:');
define('TEXT_INFO_LAST_MODIFIED', 'Laatst Aangepast:');
define('TEXT_INFO_NEW_PRICE', 'Nieuwe Prijs:');
define('TEXT_INFO_ORIGINAL_PRICE', 'Orginele Prijs:');
define('TEXT_INFO_PERCENTAGE', 'Percentage:');
define('TEXT_INFO_EXPIRES_DATE', 'Vervalt op:');
define('TEXT_INFO_STATUS_CHANGE', 'Status Verandering:');

define('TEXT_INFO_HEADING_DELETE_SPECIALS', 'Verwijder speciale aanbieding');
define('TEXT_INFO_DELETE_INTRO', 'Weet u zeker dat u deze speciale aanbieding wilt verwijderen?');

define('TEXT_DISPLAY_NUMBER_OF_SPECIALS', 'Toon <b>%d</b> tot <b>%d</b> (van <b>%d</b> speciale aanbiedingen)');
define('PREVNEXT_BUTTON_PREV', '&lt;&lt;');
define('PREVNEXT_BUTTON_NEXT', '&gt;&gt;');
define('IMAGE_NEW_SPECIAL', 'Nieuwe speciale aanbieding');
define('IMAGE_EDIT_SPECIAL', 'Wijzig speciale aanbieding');
define('IMAGE_DELETE_SPECIAL', 'Verwijder speciale aanbieding');
define('IMAGE_UPDATE_SPECIAL', 'Wijzig speciale aanbieding');
define('IMAGE_INSERT_SPECIAL', 'Speciale aanbieding invoegen');
//SPECIALS
define('IMAGE_ICON_INFO', 'Meer informatie');
define('IMAGE_ICON_STATUS_RED', 'Niet gepubliceerd');
define('IMAGE_ICON_STATUS_GREEN', 'Gepubliceerd');
define('IMAGE_ICON_STATUS_RED_LIGHT', 'Niet publiceren');
define('IMAGE_ICON_STATUS_GREEN_LIGHT', 'Publiceren');
define('TEXT_NEWSITEMS_HELP', 'De datum die u hier selecteert is de laatste dag waarop het nieuwsitem wordt weergegeven.');
define('TEXT_INFO_DATE_START', 'Startdatum:');

define('TEXT_LOGOFF', 'Uitloggen');
define('BOX_ITEM_CHANGE_PASSWORD', 'Wachtwoord wijzigen');
define('TEXT_SELECT_LANGUAGE', 'Selecteer uw taal:');

//Files upload
define('UPLOAD_UNKNOWN_ERROR', 'Onbekende fout');
define('UPLOAD_ERROR_FILE_EXISTS', 'U hebt reeds een foto met deze naam.');
define('TEXT_DELETE_IMAGE', 'Verwijder foto %s'); //%s - image name
define('TEXT_ADD_IMAGE', 'Foto toevoegen');
define('TEXT_UPLOADING_IMAGE', 'Uploading... Even geduld aub.');
define('BOX_ITEM_IMAGES_CAROUSEL', 'Foto\'s homepage');
define('TABLE_HEADING_IMAGES_CAROUSEL', 'Foto\'s homepage');

define('BOX_ITEM_FEATURED_PRODUCTS', 'Deze maand');
define('BOX_ITEM_PROMOTIONS', 'Promotie acties');
define('TABLE_HEADING_FEATURED_PRODUCTS', 'Deze maand');
define('TEXT_PRODUCT', 'Product');
define('TEXT_NO_PRODUCT', 'Geen product');
define('TEXT_SECTIONS_SAVED', 'Wijzigingen bewaard');
define('IS_LOADING', 'Bezig met laden, even geduld aub...');

define('SEO_HELPTEXT_2', 'De Meta Description is het stukje tekst dat meestal getoond wordt bij de zoekmachine-resultaten, direct onder de link.');
define('SEO_HELPTEXT_3', 'De Meta Keywords zijn een belangrijke selectie zoekwoorden, waarmee u goed wil scoren in de zoekmachines.<br />Een twintigtal zoekwoorden is hier optimaal. De zoekwoorden worden automatisch verder aangevuld met enkele merken.');

define('HEADING_OPENINGSUREN_EDIT', 'Openingsuren wijzigen');
define('BOX_ITEM_OPENINGSUREN', 'Openingsuren');
define('OPENINGSUREN_DAG', 'Dag');
define('OPENINGSUREN_VOORMIDDAG_OPEN', 'Voormiddag open');
define('OPENINGSUREN_MIDDAG_GESLOTEN', 'Middag gesloten');
define('OPENINGSUREN_MIDDAG_OPEN', 'Middag open');
define('OPENINGSUREN_AVOND_GESLOTEN', 'Avond gesloten');
define('OPENINGSUREN_HELP', 'Laat voor sluitingsdagen alle 4 de velden leeg. Als de winkel doorlopend open is, laat "Middag gesloten" en "Middag open" leeg.');

define('OPENINGSUREN_MAANDAG', 'Maandag');
define('OPENINGSUREN_DINSDAG', 'Dinsdag');
define('OPENINGSUREN_WOENSDAG', 'Woensdag');
define('OPENINGSUREN_DONDERDAG', 'Donderdag');
define('OPENINGSUREN_VRIJDAG', 'Vijdag');
define('OPENINGSUREN_ZATERDAG', 'Zaterdag');
define('OPENINGSUREN_ZONDAG', 'Zondag');


define('BOX_ITEM_ADRESGEGEVENS', 'Adresgegevens');
define('HEADING_ADRESGEGEVENS_EDIT', 'Adresgegevens wijzigen');
define('ADRESGEGEVENS_COMPANY', 'Bedrijfsnaam');
define('ADRESGEGEVENS_STREET_ADDRESS', 'Straat en huisnummer');
define('ADRESGEGEVENS_POSTCODE', 'Postcode');
define('ADRESGEGEVENS_CITY', 'Stad/Gemeente');
define('ADRESGEGEVENS_STATE', 'Provincie');
define('ADRESGEGEVENS_COUNTRY', 'Land');
define('ADRESGEGEVENS_TELEPHONE', 'Telefoon');
define('ADRESGEGEVENS_FAX', 'Fax');
define('ADRESGEGEVENS_URL', 'Website');
define('ADRESGEGEVENS_MAIL', 'E-mailadres');
define('ADRESGEGEVENS_MAIL_HELP', 'Dit e-mailadres wordt gebruikt als contactmedium voor informatieaanvragen en orderbevestigingen.');
define('ADRESGEGEVENS_IMAGE', 'Foto');
define('ADRESGEGEVENS_IMAGE_HELP', 'Deze foto wordt gebruikt op de contact pagina.');
define('ADRESGEGEVENS_BTW', 'BTW Nr');
define('ADRESGEGEVENS_REKENINGNR', 'Rekening Nr');
define('ADRESGEGEVENS_RPR', 'Rechtspersonenregister (RPR)');
define('ADRESGEGEVENS_PART_OFF', 'Onderdeel van (moeder bedrijf)');
define('ADRESGEGEVENS_COORDINATES', 'Co&ouml;rdinaten');

define('HEADING_RIGHT_COLUMN_EDIT', 'Rechterkolom');
define('BOXES_NAME', 'Naam');
define('BOXES_SORT_ORDER', 'Sorteervolgorde');
define('BOXES_DESCRIPTION', 'Omschrijving');
define('BOXES_URL', 'URL');
define('IMAGE_NEW_ITEM', 'Nieuw Item');

//Bovenste waarde in categorie dropdown (wanneer geen categorie is geselecteerd)
define('TEXT_NO_CATEGORIE', 'Geen categorie');
//Bovenste waarde bij btw tariefgroep dropdown (wanner deze 0 is)
define('TEXT_NO_TAX_GROUP', 'Geen BTW Groep');
//Bovenste waarde bij fabrikanten dropdown (wanneer geen fabrikant is geselecteerd)
define('TEXT_NO_MANUFACTURER', 'Geen Fabrikant');

define('REMOVE_IMAGE', 'Verwijder afbeelding');
define('TEXT_TOP_CATEGORIE', 'Geen bovenliggende categorie');
?>