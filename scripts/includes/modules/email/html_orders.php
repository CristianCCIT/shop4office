<?php
$html_email_orders = "<html>";
$html_email_orders .= "<head>";
$html_email_orders .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\"> ";
$html_email_orders .= "<style type='text/css'>";
$html_email_orders .= "* {font-family: Verdana, Geneva, sans-serif; font-size:12px;}";
$html_email_orders .= "TD {font-family: Verdana, Geneva, sans-serif; font-size:12px;}";
$html_email_orders .= "a {color: #403735;text-decoration: none;}";
$html_email_orders .= "h1 {font-size: 16px;margin: 0;}";
$html_email_orders .= ".bordertop {display:block;width: 602px;height: 70px;border-top: 1px solid #979797;border-left: 1px solid #979797;border-right: 1px solid #979797;}";
$html_email_orders .= ".borderbottom {border-left: 1px solid #979797;border-right: 1px solid #979797;border-bottom: 1px solid #979797;display:block;}";
$html_email_orders .= ".grijs {background: #ECECEC;width: 130px;height: 20px;padding: 5px 5px 5px 5px;}";
$html_email_orders .= ".padding-left-5 {padding-left: 5px;}";
$html_email_orders .= ".copyright {font-size: 11px; text-align: right;}";
$html_email_orders .= ".footer {font-size: 11px;text-align: center;}";
$html_email_orders .= "</style>";
$html_email_orders .= "</head>";
$html_email_orders .= "<body>";
$html_email_orders .= "<table width=\"600\"  border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
$html_email_orders .= "<tr>";
$html_email_orders .= "<td class='bordertop'>";
$html_email_orders .= "$Vartable1";
$html_email_orders .= "<tr>";
$html_email_orders .= "<td align=\"left\">$Varlogo</td>";
$html_email_orders .= "</tr>";
$html_email_orders .= "</table>";
$html_email_orders .= "</td>";
$html_email_orders .= "</tr>";
$html_email_orders .= "<tr>";
$html_email_orders .= "<td class='borderbottom'>";
$html_email_orders .= "$Vartable2";
$html_email_orders .= "<tr> ";
$html_email_orders .= "<td> ";
$html_email_orders .= $VarTitle;
$html_email_orders .= "</td> ";
$html_email_orders .= "</tr> ";
$html_email_orders .= "<tr> ";
$html_email_orders .= "<td> ";
$html_email_orders .= "$Vartable3";
$html_email_orders .= "<tr> ";
$html_email_orders .= "<td> ";
$html_email_orders .= $Vartext1;
$html_email_orders .= "</td> ";
$html_email_orders .= "</tr> ";
$html_email_orders .= "<tr> ";
$html_email_orders .= "<td>$Vartext2</td>";
$html_email_orders .= "</tr>";
$html_email_orders .= "<tr> ";
$html_email_orders .= "<td>$Varbody</td>";
$html_email_orders .= "</tr>";
$html_email_orders .= "</table> ";
$html_email_orders .= "</td> ";
$html_email_orders .= "<tr>";
$html_email_orders .= "<td class='copyright'>".$Varcopyright."</td>";
$html_email_orders .= "</tr>";
$html_email_orders .= "</table>";
$html_email_orders .= "</tr>";
$html_email_orders .= "<tr>";
$html_email_orders .= "<td class='footer'>";
$html_email_orders .= "$Varmailfooter";
$html_email_orders .= "</td>";
$html_email_orders .= "</tr>";
$html_email_orders .= "</table>";
$html_email_orders .= "</body>";
$html_email_orders .= "</html>";
?>