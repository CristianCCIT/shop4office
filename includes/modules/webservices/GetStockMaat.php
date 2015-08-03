<?php
function GetStockMaat($product_id, $maat, $data) {
	if (SOAP_SERVER!='')
	{
		$get_model_query = tep_db_query("select products_model, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
		$get_model = tep_db_fetch_array($get_model_query);
		$client = new SoapClient(null, Array(
			'location' => SOAP_SERVER,
			'uri' => SOAP_NAMESPACE,
			'trace' => true,
			'connection_timeout' => 5
		));
		$response = $client->__doRequest('<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://test" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
		  <SOAP-ENV:Body>
			<ns1:stockRequest>
			  <Artikel>'.$get_model['products_model'].'</Artikel>
			  <Maat>'.$maat.'</Maat>
			</ns1:stockRequest>
		  </SOAP-ENV:Body>
		</SOAP-ENV:Envelope>', SOAP_SERVER, SOAP_NAMESPACE, SOAP_1_2);
		if ($response)
		{
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$xPath = new DOMXPath($dom);
			
			if ($xPath->evaluate("//Status")->item(0)->nodeValue == 0)
				$result = $xPath->evaluate("//StatusTekst")->item(0)->nodeValue;
			else
			{
				//Article contains XML inside, need to reparse it
				$dom->loadXML('<xml>'.$xPath->evaluate("//Artikel")->item(0)->nodeValue.'</xml>');
				$xPath = new DOMXPath($dom);
		
				$elements = $xPath->evaluate("//xml/*");
				if ($maat=='')
				{
					foreach ($elements AS $element)
					{
						if ($element->nodeName == 'Maten')
						 continue;
							 if ($element->nodeName==$data)
							$result .= $element->nodeValue.' ';
					}
				}
				else
				{
					$maats = $xPath->evaluate("//xml/Maten/Maat");
					if ($maats->length > 0)
					{
						foreach ($maats AS $maat)
						{
							$elements = $xPath->evaluate("child::*", $maat);
							foreach ($elements AS $element)
								if ($element->nodeName==$data)
								$result .= $element->nodeValue;
						}
					}
				}
			}
		} else {
			tep_mail('ABO Service Monitor', 'mattias@aboservice.be', 'SOAP Server offline', 'De SOAP Server op '.STORE_NAME.' - '.HTTP_SERVER.' is offline', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
			$result .= $get_model['products_quantity'];
		}
	}
	else
	{
		$result = 'No SOAP server defined. Please check configuration';	
	}
	return $result;
}
?>