<?php
function SoapCustomerPriceRequest($product_model, $abo_id, $aantal) {
	//FB::info('soap-message'.$product_model.'---'.$maat.'---'.$abo_id.'---'.$aantal);
	$client = new SoapClient(null, Array(
		'location' => SOAP_SERVER,
		'uri' => SOAP_NAMESPACE,
		'trace' => true,
		'connection_timeout' => 5
	));
	$response = $client->__doRequest('<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://test" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
	  <SOAP-ENV:Body>
		<ns1:PriceRequest>
		  <ABO_ID>'.$abo_id.'</ABO_ID>
		  <Artikel>'.$product_model.'</Artikel>
		  <Aantal>'.$aantal.'</Aantal>
		</ns1:PriceRequest>
	  </SOAP-ENV:Body>
	</SOAP-ENV:Envelope>', SOAP_SERVER, SOAP_NAMESPACE, SOAP_1_2);
	if ($response)
	{
		$dom = new DOMDocument();
		$dom->loadXML($response);
		$xPath = new DOMXPath($dom);
		$result = array();
		if ($xPath->evaluate("//Status")->item(0)->nodeValue == 0) {
			$result = $xPath->evaluate("//StatusTekst")->item(0)->nodeValue;
		} else {
			$prijsCategorie = '';
			$kortingGlobaal = '';
			$KLANT = new DOMDocument();
			$KLANT->loadXML('<xml>'.$xPath->evaluate("//Klant")->item(0)->nodeValue.'</xml>');
			$klantPath = new DOMXPath($KLANT);
			$klant = $klantPath->evaluate("//xml/*");
			foreach ($klant as $categorie) {
				if ($categorie->nodeName == 'PrijsCategorie') {
					$prijsCategorie = 'Prijs'.$categorie->nodeValue;
				}
			}
			$result['Klant']['prijsCategorie'] = $prijsCategorie;
			$dom->loadXML('<xml>'.$xPath->evaluate("//Artikel")->item(0)->nodeValue.'</xml>');
			$xPath = new DOMXPath($dom);
			$elements = $xPath->evaluate("//xml/PrijzenTabel/*");
			$CategoriePrijs = '';
			foreach ($elements AS $element) {
				if ($element->nodeName == $prijsCategorie) {
					$CategoriePrijs = $element->nodeValue;
				}
			}
		}
		return $CategoriePrijs;
	} else {
		tep_mail('ABO Service Monitor', 'mattias@aboservice.be', 'SOAP Server offline', 'De SOAP Server op '.STORE_NAME.' - '.HTTP_SERVER.' is offline', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
		//$result .= Translate('Server offline');
	}
}
?>