<?php
function NewDocDetail($document, $art_nummer, $hoev, $verkoop, $maat, $recyclage, $bebat, $bestelbon) {
	FB::info('NewDocDetail Nummer'.$document);
	if (SOAP_SERVER!='') {
		$client = new SoapClient(null, Array(
			'location' => SOAP_SERVER,
			'uri' => SOAP_NAMESPACE,
			'trace' => true,
			'connection_timeout' => 5
		));
		$xml_envelope = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://test" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
		  <SOAP-ENV:Body>
			<NewDocDetail xmlns="test">
			  <Document>'.$document.'</Document>
			  <Soort>B</Soort>
			  <Artikelen>
			  	<Artikel>
					 <Art_nummer>'.$art_nummer.'</Art_nummer>
					 <Hoev>'.$hoev.'</Hoev>
					 <Verkoop>'.$verkoop.'</Verkoop>
					 <Maat>'.$maat.'</Maat>
					 <Recyclage>'.$recyclage.'</Recyclage>
					 <Bebat>'.$bebat.'</Bebat>
					 <Bestelbon>'.$bestelbon.'</Bestelbon>
				   </Artikel>
				</Artikelen>
			</NewDocDetail>
		  </SOAP-ENV:Body>
		</SOAP-ENV:Envelope>';
		$response = $client->__doRequest($xml_envelope, SOAP_SERVER, SOAP_NAMESPACE, SOAP_1_2);
		if ($response) {
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$xPath = new DOMXPath($dom);
			$result = array();
		
			if ($xPath->evaluate("//Status")->item(0)->nodeValue == 0) {
				$result = $xPath->evaluate("//StatusTekst")->item(0)->nodeValue;
			} else {
				$result = $xPath->evaluate("//Document")->item(0)->nodeValue;
			}
		}
	}
}
?>