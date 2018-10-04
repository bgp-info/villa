<?php

include_once("usr_menu.inc");
include_once("../private/func.inc");
/*
$sql = "select id, name from city";
$sth = my_exec($sql);
$arrCity = array();
while ($row = mysql_fetch_array($sth)) {
	$arrCity[$row[id]] = $row[name];
}
$sql = "select id, name from manager where type='m' order by name";
$sth = my_exec($sql);
$arrManager = array();
while ($row = mysql_fetch_array($sth)) {
	$arrManager[$row[id]] = $row[name];
}
*/

if ($cgi[date_y]) $cgi[date] = $cgi[date_y]."-".$cgi[date_m]."-".$cgi[date_d];

if(isset($cgi['add_task'])) {
	if ($cgi['task'] && $cgi['task'] != 'Необходимо сделать:') {
    #print_arr($cgi);
		$sql = "insert into company_project_plan set company_project_id = ".$cgi[cp_id].", date = '".$cgi[date]."', task = '".addslashes($cgi[task])."'";
    #echo $sql;
		my_exec($sql);
  } else {
    red("Не заполнены обязательные поля!!!");
  }
}

if(isset($cgi['upd_task'])) {
  foreach($cgi as $k=>$v) {
    if (ereg("text_([0-9]+)",$k,$regs)) {
      $sql = "update company_project_plan set text = '".addslashes($v)."' where id = ".$regs[1];
      #echo $sql;
      my_exec($sql);
    }
  }	
}

if(isset($cgi['upd_status'])) {
  #print_arr($cgi);
  $sql = "update company_project set status = '".$cgi[status]."' where id = ".$cgi[cp_id];
  #echo $sql;
  my_exec($sql);
  $body .= "<script language=javascript>opener.location.href='http://exhibition.mosc.ru/company.php?page=upd&num_str=".$cgi[cid]."';\nwindow.close();</script>";
}

$sql = "select company.id as cid, company.name as c_name, exhibition.name as p_name, exhibition.is_stop, cp.status from company_project cp, exhibition, company where company.id = cp.company_id and exhibition.id = cp.project_id and cp.id='".(int)$cgi[cp_id]."'";
#		echo $sql;
$sth = my_exec($sql);
$row = mysql_fetch_array($sth);
$is_stop = $row[is_stop];
$cid = $row[cid];

$body .= "<form method=post>\n";
$body .= "<input type=hidden name=cid value=".$cid.">\n";
$body .= "<input type=hidden name=cp_id value=".(int)$cgi[cp_id].">\n";
$body .= "<p align=center><b>Планировщик зачач по экспоненту ".$row['c_name']." в рамках проекта ".$row['p_name']."</b></p>";

$body .= "<table class=small align=center border=0 cellspacing=0 cellpadding=5 bgcolor=efefef>";
if ($is_stop == 'N') {
  $body .= "<tr valign=middle><td>Статус работ:</td><td>".do_combobox('status',$lists[arrCompanyProjectStatus],$row['status'],'','')."</td><td><input type=submit name=upd_status value='Изменить'></td></tr>\n";
} else {
  $body .= "<tr valign=middle><td>Статус работ:</td><td><b>".$lists[arrCompanyProjectStatus][$row['status']]."</b></td></tr>\n";
}
$body .= "</table><br>";

$sql = "select * from company_project_plan where company_project_id='".(int)$cgi[cp_id]."' order by date desc";
#		echo $sql;
$sth = my_exec($sql);

$body .= "<table class=small width=100% border=1 cellspacing=0 cellpadding=5 bordercolorlight=black bordercolordark=white>";
$body .= "<tr valign=top align=center bgcolor=e5e5e5><td>&nbsp;</td><td><b>Дата</b></td><td><b>Задание</b></td><td><b>Результат</b></td></tr>\n";

while ($row = mysql_fetch_array($sth)) {
  if ($row['text']) {
    $bg = "bgcolor = #efefef";
  } else {
    $bg = "";
  }
  $body .= "<tr valign=top $bg><td>&nbsp;</td><td height=26>".mysql2date2($row['date'])."</td><td>".stripslashes($row['task'])."&nbsp;</td><td>";
  if ($is_stop == 'N') {
    $body .= "<textarea name=text_".$row['id']." cols=50 rows=2>".stripslashes($row['text'])."</textarea>";
  } else {
    $body .= stripslashes($row['text'])."&nbsp;";
  }
  $body .= "</td></tr>\n";
}
if ($is_stop == 'N') {
  $body .= "<tr valign=top><td><input type=submit name=add_task value='+'></td><td>".do_input('date','','DATE','','','','')."</td><td colspan=2><textarea name=task cols=50 rows=2>Необходимо сделать:</textarea></td></tr>";
}
$body .= "</table><br>";

$body .= "<center>";
if ($is_stop == 'N') {
  $body .= "<input type=submit name=upd_task value='Обновить'>&nbsp;&nbsp;&nbsp;";
}
$body .= "<input type=button onClick=window.close() value='  Закрыть окно  '>\n";
$body .= "</form></center>\n";

include_once("empty_templ.php");


?>