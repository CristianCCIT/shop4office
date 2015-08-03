<?php
function NewDocRequest($order_id) {
	global $customer_id, $abo_id;
	FB::info('NewDocRequest Nummer'.$abo_id);
	if (SOAP_SERVER!='') {
		$client = new SoapClient(null, Array(
			'location' => SOAP_SERVER,
			'uri' => SOAP_NAMESPACE,
			'trace' => true,
			'connection_timeout' => 5
		));
		$response = $client->__doRequest('<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://test" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
		  <SOAP-ENV:Body>
			<NewDocRequest xmlns="test">
				<Nummer>'.$abo_id.'</Nummer>
				<Soort>B</Soort>
				<Weborder>'.$order_id.'</Weborder>
			</NewDocRequest>
		  </SOAP-ENV:Body>
		</SOAP-ENV:Envelope>', SOAP_SERVER, SOAP_NAMESPACE, SOAP_1_2);
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
	return $result;
}
?>