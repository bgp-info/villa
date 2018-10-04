<?php

$root = $GLOBALS['_SERVER']['DOCUMENT_ROOT']; 
include_once ($root."/../private/func.inc");  
$title = "Администратор";

if(isset($cgi['upd_row'])) {

	#print_arr($cgi);
  $temp = ":";
	foreach($cgi as $k=>$v) {
		if (substr($k,0,6) == 'tools_') {
      $temp .= $v.":";
		}
	}
  $sql = "update company set work_type='".$temp."' where id=".$cgi[num_str];
  my_exec($sql);
#	$body .= "<script language=javascript>window.close();</script>\n";
	$body .= "<script language=javascript>opener.location.href='http://exhibition.mosc.ru/company.php?page=upd&num_str=".$cgi[num_str]."'; window.close();</script>\n";
	#$body .= "<script>opener.location.href='adm_admin.php?page=upd&num_str=".$cgi[num_str]."'; window.close();</script>";
  
}

if (isset($cgi[num_str])) {
	$body .= "<center><b>Виды деятельности данного клиента</b></center><br>\n";

	$body .= "<form name=form_1 method=post>";
	$body .= "<table width=100% border=1 cellspacing=0 cellpadding=2 bordercolorlight=black bordercolordark=white>\n";
	$body .= "<tr bgcolor=eeeeee align=center>";
	$body .= "<td><b>Вид деятельности</b></td>";
	$body .= "<td><b>Есть</b></td>";
	$body .= "</tr>\n";

	$sql = "select * from company where id=".(int)$cgi[num_str];
	$sth = my_exec($sql);
  $row = mysql_fetch_array($sth);
  $arrWT = array();
  if (strlen($row[work_type]) > 2) {
    $arrTemp = split(":",substr($row[work_type],1,-1));
    foreach($arrTemp as $kwt=>$vwt) {
      $arrWT[] = $vwt;
    }
  }

	$sql = "select * from work_type order by name";
	$sth = my_exec($sql);
	while ($row = mysql_fetch_array($sth)) {
		if (in_array($row[id],$arrWT)) {
			$checked = " checked";
		} else {
			$checked = "";
		}
		$body .= "<tr valign=top><td><b>".$row[name]."</b></td><td bgcolor=efefef align=center><input type=checkbox name=tools_".$row[id]." value=".$row[id]." $checked></td></tr>\n";
	}
	$body .= "</table><br>\n";
	$body .= "<input type=hidden name=num_str value=".$cgi[num_str].">";
	$body .= "<center><input type=submit name=upd_row value='Обновить'>&nbsp;&nbsp;&nbsp;";
	$body .= "<input type=button onClick='window.close();' value='Закрыть окно'></center>";
	$body .= "</form>\n";
} else {
  $body .= "<center><b>Виды деятельности данного клиента</b><br><br><br>\n";
  $body .= "Интересно, а что вы здесь хотели увидеть???<br><br>\n";
  $body .= "<input type=button onClick='window.close();' value='Закрыть окно'></center>";
}

include_once("empty_templ.php");
?>