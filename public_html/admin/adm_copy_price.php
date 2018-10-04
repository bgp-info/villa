<?php

#include_once("adm_menu.inc");
include_once("../../private/conf.inc");
include_once("../../private/func.inc");
if (!authenticateAdmin ($cookie_adm, $cookie_adm_passwd)) {
	header("Location:http://$HTTP_HOST/admin/index.php");
	exit();
}
#$text_menu = $objAdmMenu->GetMenu('Поставщики');
$text_menu = "";

if ($cgi[do_copy]) {
	if ($cgi[exhib_to]== $cgi[exhib_from]) {
		echo red("Нельзя так копировать!");
		exit;
	}
	$arrFrom = $arrTo = array();
	$sql = "select * from price";
	$sth = my_exec($sql);
	while ($row = mysql_fetch_array($sth)) {
		if ($row[exhibition_id] == $cgi[exhib_from]) {
			$arrFrom[$row[type_id].$row[name].$row[min]] = array($row[type_id],$row[name],$row[name_engl],$row[ed_izm],$row[min],$row[price_rub],$row[price_eur]);
		} elseif ($row[exhibition_id] == $cgi[exhib_to]) {
			$arrTo[$row[type_id].$row[name].$row[min]] = 1;
		}
	}
	/*
	print_arr($arrFrom);
	print "<hr>";
	print_arr($arrTo);
	*/
	foreach ($arrFrom as $k=>$v) {
		if (!$arrTo[$k]) {
			$query = "insert into price (exhibition_id,type_id,name,name_engl,ed_izm,min,price_rub,price_eur) values ('".$cgi[exhib_to]."','".$v[0]."','".$v[1]."','".$v[2]."','".$v[3]."','".$v[4]."','".$v[5]."','".$v[6]."')";
			my_exec($query);
		}
	}
	echo "<script language=javascript>\n";
#	echo "opener.document.getElementById('refresh').click(); \n";
	echo "opener.location.href='adm_price.php?exhib=".$cgi[exhib_to]."'; \n";
#	echo "opener.focus();\n";
	echo "window.close();\n";
  echo "</script>\n";
	
}



$arrExh=array();
$sql = "select * from exhibition order by name";
$sth = my_exec($sql);
while ($row = mysql_fetch_array($sth)) {
	$arrExh[$row[id]]=$row[name];
}

$body = "<center><h4>Копирование прайс-листов</h4></center>";

$body .= "<form method=post>\n";
$body .= "<table class=small width=95% border=0 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>"; 
$body .= "<tr><td><b>Выставка, с которой копировать:</b>&nbsp;&nbsp;".do_combobox('exhib_from',$arrExh,'','','')."</td></tr>\n";
$body .= "<tr><td><b>Выставка, на которую копировать:</b>&nbsp;&nbsp;".do_combobox('exhib_to',$arrExh,'','','')."</td></tr>\n";
$body .= "</table><BR>\n";
$body .= "<center>\n";
$body .= "<input type=submit name=do_copy value='  Копировать  '>&nbsp;&nbsp;&nbsp;\n";
$body .= "<input type=button value='Закрыть окно' onClick='window.close()'>";
$body .= "</center></form>\n";

include_once("adm_templ.php");

?>