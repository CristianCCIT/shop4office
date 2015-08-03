<?php
function SoapPriceRequest($product_model, $abo_id, $maat, $aantal) {
	//FB::info('message'.$product_model.'---'.$maat);
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
		  <Maat>'.$maat.'</Maat>
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
			//Klantgegevens
			$prijsCategorie = '';
			$kortingGlobaal = '';
			$KLANT = new DOMDocument();
			$KLANT->loadXML('<xml>'.$xPath->evaluate("//Klant")->item(0)->nodeValue.'</xml>');
			$klantPath = new DOMXPath($KLANT);
			$klant = $klantPath->evaluate("//xml/*");
			foreach ($klant as $categorie) {
				if ($categorie->nodeName == 'PrijsCategorie') {
					$prijsCategorie = $categorie->nodeValue;
				} else if ($categorie->nodeName == 'KortingGlobaal') {
					$kortingGlobaal = $categorie->nodeValue;
				}
			}
			$result['Klant']['prijsCategorie'] = $prijsCategorie;
			$result['Klant']['kortingGlobaal'] = $kortingGlobaal;
			//eof Klantgegevens
			//Article contains XML inside, need to reparse it
			$dom->loadXML('<xml>'.$xPath->evaluate("//Artikel")->item(0)->nodeValue.'</xml>');
			$xPath = new DOMXPath($dom);
	
			if ($maat=='') {
				$elements = $xPath->evaluate("//xml/PrijzenTabel/MatenReeks/*");
				$reeks = '';
				foreach ($elements AS $element) {
					if ($element->nodeName == 'Reeks') {
						$reeks = $element->nodeValue;
					} else {
						if ($element->nodeValue != '0.00') {
							$result['Data'][$reeks][$element->nodeName] = $element->nodeValue;
						}
					}
				}
			} else if ($maat=='*ALL*') {
				
			} else {
				$elements = $xPath->evaluate("//xml/Maten/Maat/*");
				$reeks = '';
				foreach ($elements AS $element) {
					$result['Data'][$element->nodeName] = $element->nodeValue;
					if ($element->nodeName == 'Key') {
						$reeks = $element->nodeValue;
					} else {
						if (($maat == 'S' || $maat =='M' || $maat == 'L' || $maat == 'XL') && $reeks == 'Basis') {
							$result['Data'][$reeks][$element->nodeName] = $element->nodeValue;
						} else if (($maat == 'XXL' || $maat =='XXXL') && $reeks == '2XL;3XL') {
							$result['Data'][$reeks][$element->nodeName] = $element->nodeValue;
						} else if (($maat == 'XXXXL' || $maat =='XXXXXL') && $reeks == '4XL;5XL') {
							$result['Data'][$reeks][$element->nodeName] = $element->nodeValue;
						}
					}
				}
			}
		}
		return $result;
	} else {
		tep_mail('ABO Service Monitor', 'mattias@aboservice.be', 'SOAP Server offline', 'De SOAP Server op '.STORE_NAME.' - '.HTTP_SERVER.' is offline', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
		return Translate('Server offline');
	}
}
?>