<?php
$html_email_order = '<div style="display:none">'.$varHiddenTest.'</div>';
$html_email_order .= "<style type='text/css'>";
$html_email_order .= "* {font-family: Arial, Verdana, Geneva, sans-serif; font-size:12px;}";
$html_email_order .= "TD {font-family: Arial, Verdana, Geneva, sans-serif; font-size:12px;}";
$html_email_order .= "a {color: #1E9CE4;text-decoration: underline;}";
$html_email_order .= "h1 {font-size: 16px;}";
$html_email_order .= ".bordertop {display:block;width: 602px;height: 70px;border-top: 1px solid #979797;border-left: 1px solid #979797;border-right: 1px solid #979797;}";
$html_email_order .= ".borderbottom {border-left: 1px solid #979797;border-right: 1px solid #979797;border-bottom: 1px solid #979797;display:block;}";
$html_email_order .= ".grijs {background: #ECECEC;width: 130px;height: 20px;padding: 5px 5px 5px 5px;}";
$html_email_order .= ".padding-left-5 {padding-left: 5px;}";
$html_email_order .= ".copyright {font-size: 11px; text-align: right;}";
$html_email_order .= ".retour {font-size: 11px; text-align: left;border:1px solid #000;padding:10px;}";
$html_email_order .= ".footer {font-size: 11px;text-align: center;padding:10px;}";
$html_email_order .= ".boxmail {background: #b6b6b6;color:#000;}";
$html_email_order .= ".odd {background: #dfdfdf;}";
$html_email_order .= ".tableur {border-top: 1px solid #dfdfdf;}";
$html_email_order .= "</style>";
$html_email_order .= "<table width=\"600\"  border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
$html_email_order .= "<tr>";
$html_email_order .= "<td class='bordertop'>";
$html_email_order .= "$Vartable1";
$html_email_order .= "<tr>";
$html_email_order .= "<td align=\"left\">$Varlogo</td>";
$html_email_order .= "</tr>";
$html_email_order .= "</table>";
$html_email_order .= "</td>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr>";
$html_email_order .= "<td class='borderbottom'>";
$html_email_order .= "$Vartable2";
$html_email_order .= "<tr>";
$html_email_order .= "<td>$Vartext1</td>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr> ";
$html_email_order .= "<td>$Vartext2</td>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr>";
$html_email_order .= "<td><table width=\"594\"  border=\"0\" cellpadding=\"3\" cellspacing=\"1\" bgcolor=white>";
$html_email_order .= "<tr> ";
$html_email_order .= "<td class=\"boxmail\" align=\"left\" width=\"290\">$VarArticles</td>";
$html_email_order .= "<td class=\"boxmail\" align=\"left\" width=\"149\">$VarModele</td>";
if (USE_PRICES_TO_QTY == 'true') {
$html_email_order .= "<td class=\"boxmail\" align=\"left\" width=\"149\">$VarMaat</td>";	
}
$html_email_order .= "<td class=\"boxmail\" align=\"center\" width=\"40\">$VarQte</td>";
$html_email_order .= "<td class=\"boxmail\" align=\"right\" width=\"100\">$VarTotal</td>";
$html_email_order .= "</tr>";
$html_email_order .= $html_products;
$html_email_order .= "<tr height=\"14\"> ";
$html_email_order .= "<td height=\"14\" width=\"290\" valign=\"top\" align=\"left\">$Vardetail</td>";
if (USE_PRICES_TO_QTY == 'true') {
	$html_email_order .= "<td colspan=\"4\" width=\"289\" valign=\"top\" align=\"right\" class=\"tableur\">$Vartaxe</td>";
} else {
	$html_email_order .= "<td colspan=\"3\" width=\"289\" valign=\"top\" align=\"right\" class=\"tableur\">$Vartaxe</td>";
}
$html_email_order .= "</tr>";
$html_email_order .= "</table></td>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr>";
$html_email_order .= "<td><table width=\"594\"  border=\"0\" cellpadding=\"3\" cellspacing=\"1\" bgcolor=white>";
$html_email_order .= "<tr>";
$html_email_order .= "<td class=\"boxmail\" width=\"290\">";
$html_email_order .= $VarAddresship;
$html_email_order .= "</td>";
$html_email_order .= "<td class=\"boxmail\" width=\"289\">";
$html_email_order .= $VarAddressbill;
$html_email_order .= "</td>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr>";
$html_email_order .= "<td valign='top'>";
$html_email_order .= $Varshipaddress;
$html_email_order .= "</td>";
$html_email_order .= "<td valign='top'>";
$html_email_order .= $Varadpay;
$html_email_order .= "</td>";
$html_email_order .= "</tr>";
$html_email_order .= "</table></td>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr>";
$html_email_order .= "<td><table width=\"594\"  border=\"0\" cellpadding=\"3\" cellspacing=\"1\" bgcolor=white>";
$html_email_order .= "<tr>";
$html_email_order .= "<td class=\"boxmail\" width=\"290\">";
$html_email_order .= $Varmetodship;
$html_email_order .= "</td>";
$html_email_order .= "<td class=\"boxmail\" width=\"289\">";
$html_email_order .= $Varmetodpaye;
$html_email_order .= "</td>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr>";
$html_email_order .= "<td valign='top'>";
$html_email_order .= $Varmodeship;
$html_email_order .= "</td>";
$html_email_order .= "<td valign='top'>";
$html_email_order .= $Varmodpay;
$html_email_order .= "</td>";
$html_email_order .= "</tr>";
$html_email_order .= $Varcomment;
$html_email_order .= "</table></td>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr>";
$html_email_order .= "<td class='copyright'>".$Varcopyright."</td>";
$html_email_order .= "</tr>";
$html_email_order .= "</table>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr>";
$html_email_order .= "<td class='footer'>";
$html_email_order .= "$Varmailfooter";
$html_email_order .= "</td>";
$html_email_order .= "</tr>";
$html_email_order .= "<tr>";
$html_email_order .= "<td class='retour'>".$Varretour."</td>";
$html_email_order .= "</tr>";
$html_email_order .= "</table>";
?>