<?php
if ((defined('PHPLIST_LIST_DB')) && (PHPLIST_LIST_DB!='')) {
tep_db_connect(PHPLIST_DB_SERVER, PHPLIST_DB_USER, PHPLIST_DB_PASSWORD, PHPLIST_LIST_DB, 'db_list_link');
tep_db_list_connect();	
$list_ids = explode(';', PHPLIST_LISTNUMBERS);
$list_array = array();
for ($i = 0; $i < count($list_ids); $i++)
{											
	$list_ids_query = tep_db_list_query("select name, id from " . PHPLIST_TABLE_PREFIX . "list where active = '1' AND id = '".$list_ids[$i]."'");
	$list_ids_array = tep_db_fetch_array($list_ids_query);
	$list_arrays[] = array('id' => $list_id_array['id'], 'text' => $list_ids_array['name']);
	$list_array[] = array('id' => $list_ids_array['id'], 'text' => $list_ids_array['name']);
}
$checkboxes = '';
foreach ($list_array as $key=>$value)
{
	$checkboxes .= tep_draw_checkbox_field('list', $value['id'], $checked = false, $parameters = '').' '.$value['text'].'<br />';
}
//tep_db_list_close();
tep_db_connect();
//get user specifics for PHPlist		
$email_address = $_POST['email'];
$firstname = $_POST['name'];
if (count($list_ids) > 1) {
	$str2 = 'var str2 = getSelectedCheckboxValue(document.newsletter.list)';
	$dropdown = '
	<tr>
		<td height="7"></td>
	</tr>
	<tr>
		<td colspan="2">
			'.$checkboxes.'
		</td>
	</tr>
	<tr>
		<td height="7"></td>
	</tr>';
	$count_selected = '
	var count_selected = getSelectedCheckboxValue(document.newsletter.list);
	if (count_selected < 1)
	{
		alert("'.ENTRY_NIEUWSBRIEF_CHECK_ERROR.'");
		return false;
	}
	else
	{
		if (document.newsletter.email.value)
		{
			var email = document.newsletter.email.value;
			var AtPos = email.indexOf("@")
			var StopPos = email.lastIndexOf(".")
			if (AtPos == -1 || StopPos == -1) {
				alert("'.Translate('Geef a.u.b. een bestaand e-mail adres!').'");
				document.newsletter.email.focus;
			} else {
				checkNewsletter(type);
			}
		}
		else
		{
			alert("'.Translate('Geef a.u.b. een bestaand e-mail adres!').'");
		}
	}';
}
else
{
	$str2 = 'var str2 = \'&list[]='.PHPLIST_LISTNUMBERS.'\'';

	$dropdown = '';
	$count_selected = '
	if (document.newsletter.email.value)
	{
		var email = document.newsletter.email.value;
		var AtPos = email.indexOf("@")
		var StopPos = email.lastIndexOf(".")
		if (AtPos == -1 || StopPos == -1) {
			alert("'.Translate('Geef a.u.b. een bestaand e-mail adres!').'");
			document.newsletter.email.focus;
		} else {
			checkNewsletter(type);
		}
	} else {
		alert("'.Translate('Geef a.u.b. een bestaand e-mail adres!').'");
	}';
} 

$newsletter_content =

		'
		<table cellspacing="0" cellpadding="0" width="100%" border="0">
			<tr>
            	<td>
                	<script type="text/javascript" language="javascript">
					var xmlhttp;
					function GetSelectedItem()
					{
						len = document.newsletter.list.length;
						i = 0;
						chosen = "none"	;				
						for (i = 0; i < len; i++)
						{
							if (document.newsletter.list[i].selected)
							{
								chosen = document.newsletter.list[i].value;
							}
						}
						return chosen;
					}
					function getSelectedCheckbox(buttonGroup)
					{
					   // Go through all the check boxes. return an array of all the ones
					   // that are selected (their position numbers). if no boxes were checked,
					   // returned array will be empty (length will be zero)
					   var retArr = new Array();
					   var lastElement = 0;
					   if (buttonGroup[0]) // if the button group is an array (one check box is not an array)
					   { 
						  for (var i=0; i<buttonGroup.length; i++)
						  {
							 if (buttonGroup[i].checked)
							 {
								retArr.length = lastElement;
								retArr[lastElement] = i;
								lastElement++;
							 }
						  }
					   }
					   else // There is only one check box (its not an array)
					   {
						  if (buttonGroup.checked) // if the one check box is checked
						  {
							 retArr.length = lastElement;
							 retArr[lastElement] = 0; // return zero as the only array value
						  }
					   }
					   return retArr;
					} // Ends the "getSelectedCheckbox" function
					function getSelectedCheckboxValue(buttonGroup)
					{
					   // return an array of values selected in the check box group. if no boxes
					   // were checked, returned array will be empty (length will be zero)
					   var retArr = new Array(); // set up empty array for the return values
					   var retStr = "";
					   var selectedItems = getSelectedCheckbox(buttonGroup);
					   if (selectedItems.length != 0)// if there was something selected
					   { 
						  retArr.length = selectedItems.length;
						  for (var i=0; i<selectedItems.length; i++)
						  {
							 if (buttonGroup[selectedItems[i]]) // Make sure its an array
							 {
								retArr[i] = buttonGroup[selectedItems[i]].value;
								retStr = retStr + "&list[]=" + buttonGroup[selectedItems[i]].value;
							 }
							 else // Its not an array (theres just one check box and its selected)
							 {
								retArr[i] = buttonGroup.value;// return that value
							 }
						  }
					   }
					   return retStr;
					} // Ends the "getSelectedCheckBoxValue" function
					function checkInputInfo(type)
					{
						'.$count_selected.'
					}
					function checkNewsletter(type)
					{
						var str = document.getElementById(\'newsletter_input\').value;
						var lang = document.getElementById(\'lang\').value;';

$newsletter_content .= $str2.";\n\n";
$newsletter_content .= $listname.";\n\n";
$newsletter_content .= '
						xmlhttp=GetXmlHttpObject();
						if (xmlhttp==null)
						{
							alert ("Browser does not support HTTP Request");
							return;
						}
						var url="check_newsletter.php";
						url=url+"?email="+str+str2+"&lang="+lang+"&"+type+"=yes&store_name='.STORE_NAME.'&store_owner_email='.STORE_OWNER_EMAIL_ADDRESS.'";
						url=url+"&sid="+Math.random();
						xmlhttp.onreadystatechange=stateChanged;
						xmlhttp.open("GET",url,true);
						xmlhttp.send(null);
					}
					function stateChanged()
					{
						if (xmlhttp.readyState==4)
						{
							if (xmlhttp.responseText == \'\')
							{
								document.getElementById("ajax_output").innerHTML=xmlhttp.responseText;
							}
							else
							{
								document.getElementById("ajax_output").innerHTML=xmlhttp.responseText;
							}
						}
					}
					function GetXmlHttpObject()
					{
						if (window.XMLHttpRequest)
						{
							return new XMLHttpRequest();
						}
						if (window.ActiveXObject)
						{
							// code for IE6, IE5
							return new ActiveXObject("Microsoft.XMLHTTP");
						}
						return null;
					}
					</script>
                	<div id="ajax_output">'.Translate('Hier kan u zich inschrijving op onze nieuwsbrief.<br />Op deze manier blijft u altijd op de hoogte van onze laatste updates, beurzen, gelegenheden, ...').'</div>
                </td>
            </tr>
			<tr>
				<td>';
$newsletter_content .= tep_draw_form("newsletter", tep_href_link(basename($_SERVER['PHP_SELF']), tep_get_all_get_params()), "post", 'onsubmit="checkInputInfo(\'subscribe\');return false;"');
$newsletter_content .= '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
$newsletter_content .= $dropdown;
$newsletter_content .= '<tr><td colspan="2" height="5"></td></tr>';
$newsletter_content .= '
								<tr>
									<td colspan="2">
										<input type="hidden" name="lang" value="'.$language.'" id="lang" />
										<input type="text" name="email" class="inputbox" id="newsletter_input" ';
$newsletter_content .= 'onblur="if(this.value==\'\') this.value=\''.Translate('Typ hier uw email adres...').'\';" onfocus="if(this.value==\''. Translate('Typ hier uw email adres...').'\') this.value=\'\';"';
$newsletter_content .= 'value="'.Translate('Typ hier uw email adres...').'" ';
$newsletter_content .= ' />
									</td>
                            </tr>
                            <tr>
                            	<td colspan="2" height="3"></td>
                            </tr>
							<tr>
								<td align="left" colspan="2">
									<table cellspacing="0" cellpadding="0">
										<tr>
											<td align="left" colspan="2">
												<input type="button" class="button-a" value="'.Translate('Inschrijven').'" onClick="checkInputInfo(\'subscribe\')" />
											</td>
											<td align="left" style="padding-left: 3px;">
												<input type="button" class="button-a" value="'.Translate('Uitschrijven').'" onClick="checkInputInfo(\'unsubscribe\')" />
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</form>
				</td>
			</tr>
		</table>';
	echo $newsletter_content;
}
?>