<?php
/* adm_schr.php */

include_once("adm_menu.inc");
include_once("../../private/func.inc");
$text_menu = $objAdmMenu->GetMenu('');
#echo $cgi[adm_login].", ".$cgi[adm_passw]."<br>";
#echo $cookie_adm.", ".$cookie_adm_passwd."<br>";

if (authenticateAdmin($cookie_adm, $cookie_adm_passwd)) {
	$body .= "<br><br><center>�� ������������!<br>�������� ������������ ��� ����������<br><br><br><br><br><br>\n";
} else {
	if (authenticateAdmin($cgi[adm_login], $cgi[adm_passw])) {
		setcookie("cookie_adm", $cgi[adm_login]);
		setcookie("cookie_adm_passwd", $cgi[adm_passw]);
		header("Location:http://$HTTP_HOST/admin/index.php");
		exit();
	} else {
#    print_r($GLOBALS);
		$body = "<center><h4>�� ������ ��������������...</h4>";
		$body .= "<form method=post>\n";
		$body .= "<input type=hidden name=num_str value=$num_str>\n";
		$body .= "<table class=small alert=center border=0 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
		$body .= "<tr><td align=right><b>�����:</b>&nbsp;&nbsp;</td><td><input name=adm_login value='' size=25></td></tr>\n"; 
		$body .= "<tr><td align=right><b>������:</b>&nbsp;&nbsp;</td><td><input type=password name=adm_passw value='' size=25></td></tr>\n"; 
		$body .= "</table><BR>\n";
		$body .= "<input type=submit name=submit value='��������������'>\n";
		$body .= "</form></center>\n";
		$body .= "<br><br><br><br><br><br>\n";
	}
}
$body .= "<p align=right>� ������ ������������� ������� � ��������������� (������� � ���� ��������� ;)), <br>��� ��������, ���������� ���������������� ������� �����������,<br> <a href='mailto:stas@alink.ru'>��������� ���� ��������� ������������</a>.<br> ��� � ������������� �������!";

include_once("adm_templ.php");


?>