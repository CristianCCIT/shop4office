<?php
function ViewDocRequest($volgnr, $soort = 'B') {
	global $customer_id, $abo_id;
	if (SOAP_SERVER!='') {
		$client = new SoapClient(null, Array(
			'location' => SOAP_SERVER,
			'uri' => SOAP_NAMESPACE,
			'trace' => true,
			'connection_timeout' => 5
		));
		$fields = 'art_nummer|omschr_new|maattabel|hoev|verkoop|korting';
		$response = $client->__doRequest('<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://test" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
		  <SOAP-ENV:Body>
			<ViewDocRequest xmlns="test">
				<Nummer>'.$abo_id.'</Nummer>
				<Soort>'.$soort.'</Soort>
				<Volgnr>'.$volgnr.'</Volgnr>
				<Fields>'.$fields.'</Fields>
				<Header>datum|ref|lev_date|verkoop</Header>
			</ViewDocRequest>
		  </SOAP-ENV:Body>
		</SOAP-ENV:Envelope>', SOAP_SERVER, SOAP_NAMESPACE, SOAP_1_2);
		FB::info('ViewDocRequest '.$response);

		if ($response) {
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$xPath = new DOMXPath($dom);
			if ($xPath->evaluate("//Status")->item(0)->nodeValue == 0) {
				$result = $xPath->evaluate("//StatusTekst")->item(0)->nodeValue;
			} else {
				$HEADER = new DOMDocument();
				$HEADER->loadXML('<xml>'.$xPath->evaluate("//Header")->item(0)->nodeValue.'</xml>');
				$headerPath = new DOMXPath($HEADER);
				$header = $headerPath->evaluate("//xml/Header/*");
				$result .= '<table width="100%" class="header-table">';
				$result .= '<tr>';
				$result .= '<td><strong>'.Translate('Bestelling').'</strong>: '.$_GET['order_id'].'</td>';
				
				foreach ($header AS $headeritem) {
					if ( ($headeritem->nodeName=='lev_date') && ($headeritem->nodeValue=='  /  /  ') ) {
						$headeritem->nodeValue = Translate('Onbekend');
					}
					$result .= '<td class="element-'.$headeritem->nodeName.'"><strong>'.Translate($headeritem->nodeName).'</strong>: '.$headeritem->nodeValue.'</td>';
				}
				$result .= '</tr>';
				$result .= '</table>';
				
				$dom->loadXML('<xml>'.$xPath->evaluate("//List")->item(0)->nodeValue.'</xml>');
				$xPath = new DOMXPath($dom);
		
				$elements = $xPath->evaluate("//xml/Document/Artikel/*");
				$result .= '<div class="box ViewDocRequest">';
				?>
				<script type="text/javascript">
				$(document).ready(function(){								
					$('.title-korting').hide();
					$('.element-korting').hide();
					$('#ViewDocRequest').tablesorter();
				});
				</script>
				<?php
				$result .= '<table class="data-table tablesorter" id="ViewDocRequest" width="100%">';
				$result .= '<thead>';
				$result .= '<tr class="title">';
				$result .= '<th class="title-art_nummer">'.Translate('Artikel Nummer').'</th>';
				$result .= '<th class="title-omschr_new">'.Translate('Omschrijving').'</th>';
				$result .= '<th class="title-maat">'.Translate('Maat').'</th>';
				$result .= '<th class="title-hoev">'.Translate('Hoeveelheid').'</th>';
				$result .= '<th class="title-verkoop">'.Translate('Prijs').'</th>';
				$result .= '<th class="title-korting">'.Translate('Korting').'</th>';
				$result .= '<th class="title-totaal">'.Translate('Totaal').'</th>';
				if ($soort=='B') {
				$result .= '<th class="title-afroep">'.Translate('Afroep').'</th>';
				}
				$result .= '</tr>';
				$result .= '</thead>';
				$result .= '<tbody>';
				$oe_count=0;
				foreach ($elements AS $element) {
					if ($element->nodeName=='art_nummer') {
						$oe_count++;
						if ($oe_count%2) {
							$extra_class = ' odd';
						} else {
							$extra_class = ' even';
						}
						$result .= '<tr class="data'.$extra_class.'">';
					}
					if ($element->nodeName=='hoev') {
						$hoev = $element->nodeValue;
					} elseif ($element->nodeName=='verkoop') {
						$prijs = $element->nodeValue;
					} elseif ($element->nodeName=='korting') {
						$korting = $element->nodeValue;
						if ($korting>0) {
							?>
                            <script type="text/javascript">
							$(document).ready(function(){								
								$('.title-korting').show();
								$('.element-korting').show();
							});
							</script>
                            <?php
						}
					} elseif ($element->nodeName=='art_nummer') {
						$model = $element->nodeValue;
						$product_query = tep_db_query('SELECT products_id FROM '.TABLE_PRODUCTS.' WHERE products_model = "'.$model.'"');
						$product = tep_db_fetch_array($product_query);
						if (tep_db_num_rows($product_query)>0) {
							$element->nodeValue = '<a href="'.tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$product['products_id']).'">'.$element->nodeValue.'</a>';
						}
					} elseif ($element->nodeName=='omschr_new') {
						if (tep_db_num_rows($product_query)>0) {
							$element->nodeValue = '<a href="'.tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$product['products_id']).'">'.$element->nodeValue.'</a>';
						}
					} elseif ($element->nodeName=='maattabel') {
						$maat = $element->nodeValue;
					}
					$result .= '<td class="element-'.$element->nodeName.'">'.$element->nodeValue.'</td>';
					if ($element->nodeName=='korting') {
						$totaal = (($prijs*$hoev)-$korting);
						$result .= '<td class="element-totaal">'.$totaal.'</td>';
							if ($soort=='B') {
								$result .= '<td class="element-afroep">';
								for ($s=0; $s<$hoev; $s++) {
									$z =  $s+1;
									$options[$product['products_id']][] = array('id' => $z,'text' => $z);	
								}
								if (isset($options[$product['products_id']])){
								$result .= tep_draw_form('cart_quantity', tep_href_link(FILENAME_SHOPPING_CART, 'action=add_product'));
								$result .= tep_draw_pull_down_menu('quantity', $options[$product['products_id']], $products[$i]['quantity']);
								$result .= tep_draw_hidden_field('products_id', $product['products_id']);
								$result .= tep_draw_hidden_field('backorder', $volgnr);
								
								$attr_query = tep_db_query("SELECT products_options_values_id FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." WHERE products_options_values_name = '".$maat."'");
								$attr = tep_db_fetch_array($attr_query);
								$optie_query = tep_db_query("SELECT options_id FROM ".TABLE_PRODUCTS_ATTRIBUTES." WHERE options_values_id = '".$attr['products_options_values_id']."' AND products_id = '".$product['products_id']."'");
								$optie = tep_db_fetch_array($optie_query);
								
								$result .= tep_draw_hidden_field('id['.$optie['options_id'].']', $attr['products_options_values_id']);
								$result .= '<input type="submit" value="'.Translate('Ok').'" class="formbutton button-a" />';
								$result .= '</form>';
								}
								unset($options[$product['products_id']]);
								$result .= '</td>';
							}
						$result .= '</tr>';
					}
				}
				$result .= '</tbody>';
				$result .= '</table>';
				$result .= '</div>';
			}
		} else {
			$result .= 'Server offline';
		}
	} else {
		$result = 'No SOAP server defined. Please check configuration';	
	}
	return $result;
}
?>
